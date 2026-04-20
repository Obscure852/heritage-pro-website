<?php

namespace App\Services\Library;

use App\Models\Copy;
use App\Models\Library\LibraryAuditLog;
use App\Models\Library\LibraryFine;
use App\Models\Library\LibrarySetting;
use App\Models\Library\LibraryTransaction;
use Illuminate\Support\Facades\DB;

class CirculationService {
    protected CopyStatusService $copyStatusService;

    public function __construct(CopyStatusService $copyStatusService) {
        $this->copyStatusService = $copyStatusService;
    }

    // ==================== CHECKOUT ====================

    /**
     * Check out a copy to a borrower.
     *
     * Validates borrowing eligibility, transitions copy status,
     * creates transaction record, and logs the audit trail.
     *
     * @param Copy $copy The copy to check out
     * @param string $borrowerType Morph type: 'student' or 'user'
     * @param int $borrowerId The borrower's ID
     * @param int $checkedOutBy The librarian's user ID
     * @param string|null $notes Optional checkout notes
     * @return LibraryTransaction
     *
     * @throws \RuntimeException If borrower is blocked from borrowing
     * @throws \InvalidArgumentException If copy status transition is invalid
     */
    public function checkout(Copy $copy, string $borrowerType, int $borrowerId, int $checkedOutBy, ?string $notes = null): LibraryTransaction {
        return DB::transaction(function () use ($copy, $borrowerType, $borrowerId, $checkedOutBy, $notes) {
            // Verify borrower is eligible
            $this->assertCanBorrow($borrowerType, $borrowerId);

            // If copy is on_hold, verify borrower matches the reservation holder
            if ($copy->status === CopyStatusService::STATUS_ON_HOLD) {
                $readyReservation = \App\Models\Library\LibraryReservation::where('book_id', $copy->book_id)
                    ->where('status', 'ready')
                    ->where('borrower_type', $borrowerType)
                    ->where('borrower_id', $borrowerId)
                    ->first();

                if (!$readyReservation) {
                    throw new \RuntimeException(
                        'This copy is on hold for another borrower. Cancel the reservation first or check out a different copy.'
                    );
                }

                // Matched -- mark reservation as fulfilled
                $readyReservation->update([
                    'status' => 'fulfilled',
                    'fulfilled_at' => now(),
                ]);

                \App\Models\Library\LibraryAuditLog::log($readyReservation, 'reservation_fulfilled', null, [
                    'copy_id' => $copy->id,
                    'borrower_type' => $borrowerType,
                    'borrower_id' => $borrowerId,
                ]);
            }

            // Transition copy status (handles locking and validation internally)
            $this->copyStatusService->transition($copy, CopyStatusService::STATUS_CHECKED_OUT, 'Library checkout');

            // Calculate due date based on borrower type and item type
            $copy->load('book');
            $itemType = $copy->book->format ?? null;
            $dueDate = now()->addDays($this->getLoanPeriod($borrowerType, $itemType));

            // Create the transaction record
            $transaction = LibraryTransaction::create([
                'copy_id' => $copy->id,
                'borrower_type' => $borrowerType,
                'borrower_id' => $borrowerId,
                'checkout_date' => now(),
                'due_date' => $dueDate,
                'status' => 'checked_out',
                'renewal_count' => 0,
                'checked_out_by' => $checkedOutBy,
                'notes' => $notes,
            ]);

            // Audit log
            LibraryAuditLog::log($transaction, 'checkout', null, [
                'copy_id' => $copy->id,
                'borrower_type' => $borrowerType,
                'borrower_id' => $borrowerId,
                'due_date' => $dueDate->toDateString(),
            ]);

            return $transaction;
        });
    }

    // ==================== CHECKIN ====================

    /**
     * Check in (return) a copy.
     *
     * Finds the active transaction, transitions copy back to available,
     * and marks the transaction as returned.
     *
     * @param Copy $copy The copy being returned
     * @param int $checkedInBy The librarian's user ID
     * @param string|null $notes Optional return notes
     * @return LibraryTransaction The updated transaction
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If no active transaction found
     */
    public function checkin(Copy $copy, int $checkedInBy, ?string $notes = null): LibraryTransaction {
        return DB::transaction(function () use ($copy, $checkedInBy, $notes) {
            // Find active transaction for this copy (with lock)
            $transaction = LibraryTransaction::where('copy_id', $copy->id)
                ->active()
                ->lockForUpdate()
                ->firstOrFail();

            // Save old values for audit
            $oldValues = [
                'status' => $transaction->status,
                'return_date' => null,
            ];

            // Check for pending reservations BEFORE transitioning copy status
            $copy->load('book');
            $reservationService = app(\App\Services\Library\ReservationService::class);
            $fulfilled = $reservationService->fulfillNextInQueue($copy->book_id, $copy);

            if (!$fulfilled) {
                // No pending reservations -- transition to available as before
                $this->copyStatusService->transition($copy, CopyStatusService::STATUS_AVAILABLE, 'Library return');
            }
            // If fulfilled, copy is already on_hold (done inside fulfillNextInQueue)

            // Update transaction
            $transaction->update([
                'return_date' => now(),
                'status' => 'returned',
                'checked_in_by' => $checkedInBy,
                'notes' => $notes ? (($transaction->notes ? $transaction->notes . ' | ' : '') . $notes) : $transaction->notes,
            ]);

            // Audit log
            LibraryAuditLog::log($transaction, 'checkin', $oldValues, [
                'status' => 'returned',
                'return_date' => now()->toDateString(),
                'checked_in_by' => $checkedInBy,
                'reservation_fulfilled' => $fulfilled ? $fulfilled->id : null,
            ]);

            return $transaction;
        });
    }

    // ==================== RENEWAL ====================

    /**
     * Renew a checked-out transaction.
     *
     * Extends due date from today (not from old due date),
     * increments renewal count, and resets status to checked_out.
     *
     * @param LibraryTransaction $transaction The transaction to renew
     * @param int $renewedBy The librarian's user ID
     * @param string|null $notes Optional renewal notes
     * @return LibraryTransaction The updated transaction
     *
     * @throws \RuntimeException If transaction cannot be renewed
     */
    public function renew(LibraryTransaction $transaction, int $renewedBy, ?string $notes = null): LibraryTransaction {
        return DB::transaction(function () use ($transaction, $renewedBy, $notes) {
            // Re-fetch with lock to prevent race conditions
            $transaction = LibraryTransaction::lockForUpdate()->findOrFail($transaction->id);

            // Validate transaction is in a renewable state
            if (!in_array($transaction->status, ['checked_out', 'overdue'])) {
                throw new \RuntimeException('Only checked out or overdue books can be renewed.');
            }

            // Check max renewals (per item type if available)
            $transaction->load('copy.book');
            $itemType = $transaction->copy->book->format ?? null;
            $maxRenewals = $this->getMaxRenewals($transaction->borrower_type, $itemType);
            if ($transaction->renewal_count >= $maxRenewals) {
                throw new \RuntimeException(
                    "Maximum renewal limit reached ({$maxRenewals}). Book must be returned."
                );
            }

            // Check borrower eligibility (exclude THIS transaction from overdue check)
            $this->assertCanBorrow($transaction->borrower_type, $transaction->borrower_id, $transaction->id);

            // Save old values for audit
            $oldValues = [
                'due_date' => $transaction->due_date->toDateString(),
                'renewal_count' => $transaction->renewal_count,
                'status' => $transaction->status,
            ];

            // Calculate new due date from TODAY (not old due date)
            $newDueDate = now()->addDays($this->getLoanPeriod($transaction->borrower_type, $itemType));

            // Update transaction
            $transaction->update([
                'due_date' => $newDueDate,
                'renewal_count' => $transaction->renewal_count + 1,
                'status' => 'checked_out', // Reset from overdue if applicable
                'notes' => $notes ? (($transaction->notes ? $transaction->notes . ' | ' : '') . $notes) : $transaction->notes,
            ]);

            // Audit log
            LibraryAuditLog::log($transaction, 'renewal', $oldValues, [
                'due_date' => $newDueDate->toDateString(),
                'renewal_count' => $transaction->renewal_count,
                'status' => 'checked_out',
                'renewed_by' => $renewedBy,
            ]);

            return $transaction;
        });
    }

    // ==================== BLOCK CONDITIONS ====================

    /**
     * Assert that a borrower is eligible to borrow.
     *
     * Checks borrowing limit, overdue books, and fine threshold.
     * Throws RuntimeException if any block condition is met.
     *
     * @param string $borrowerType Morph type: 'student' or 'user'
     * @param int $borrowerId The borrower's ID
     * @param int|null $excludeTransactionId Transaction ID to exclude from overdue check (for renewals)
     *
     * @throws \RuntimeException If borrower is blocked
     */
    public function assertCanBorrow(string $borrowerType, int $borrowerId, ?int $excludeTransactionId = null): void {
        // Check 1: Borrowing limit
        $activeCount = LibraryTransaction::forBorrower($borrowerType, $borrowerId)
            ->active()
            ->count();
        $maxBooks = $this->getMaxBooks($borrowerType);

        if ($activeCount >= $maxBooks) {
            throw new \RuntimeException(
                "Borrowing limit reached. Currently has {$activeCount} of {$maxBooks} allowed books."
            );
        }

        // Check 2: Overdue books
        $overdueQuery = LibraryTransaction::forBorrower($borrowerType, $borrowerId)
            ->active()
            ->where('due_date', '<', now()->toDateString());

        if ($excludeTransactionId) {
            $overdueQuery->where('id', '!=', $excludeTransactionId);
        }

        if ($overdueQuery->exists()) {
            throw new \RuntimeException(
                'Cannot borrow while overdue books remain unreturned.'
            );
        }

        // Check 3: Fine threshold
        $outstandingFines = LibraryFine::forBorrower($borrowerType, $borrowerId)
            ->unpaid()
            ->get()
            ->sum('outstanding');
        $fineThreshold = LibrarySetting::get('fine_threshold', ['amount' => 50.00])['amount'];

        if ($outstandingFines >= $fineThreshold) {
            throw new \RuntimeException(
                "Outstanding fines (P" . number_format($outstandingFines, 2) . ") exceed the threshold of P" . number_format($fineThreshold, 2) . ". Please settle fines before borrowing."
            );
        }
    }

    /**
     * Get block reasons for a borrower (non-throwing version of assertCanBorrow).
     *
     * Returns an array of human-readable block reason strings.
     * Empty array means the borrower can borrow.
     *
     * @param string $borrowerType Morph type: 'student' or 'user'
     * @param int $borrowerId The borrower's ID
     * @param int|null $excludeTransactionId Transaction ID to exclude from overdue check
     * @return array
     */
    public function getBlockReasons(string $borrowerType, int $borrowerId, ?int $excludeTransactionId = null): array {
        $reasons = [];

        // Check 1: Borrowing limit
        $activeCount = LibraryTransaction::forBorrower($borrowerType, $borrowerId)
            ->active()
            ->count();
        $maxBooks = $this->getMaxBooks($borrowerType);

        if ($activeCount >= $maxBooks) {
            $reasons[] = "Borrowing limit reached ({$activeCount}/{$maxBooks} books).";
        }

        // Check 2: Overdue books
        $overdueQuery = LibraryTransaction::forBorrower($borrowerType, $borrowerId)
            ->active()
            ->where('due_date', '<', now()->toDateString());

        if ($excludeTransactionId) {
            $overdueQuery->where('id', '!=', $excludeTransactionId);
        }

        if ($overdueQuery->exists()) {
            $overdueCount = $overdueQuery->count();
            $reasons[] = "Has {$overdueCount} overdue " . ($overdueCount === 1 ? 'book' : 'books') . ".";
        }

        // Check 3: Fine threshold
        $outstandingFines = LibraryFine::forBorrower($borrowerType, $borrowerId)
            ->unpaid()
            ->get()
            ->sum('outstanding');
        $fineThreshold = LibrarySetting::get('fine_threshold', ['amount' => 50.00])['amount'];

        if ($outstandingFines >= $fineThreshold) {
            $reasons[] = "Outstanding fines (P" . number_format($outstandingFines, 2) . ") exceed threshold of P" . number_format($fineThreshold, 2) . ".";
        }

        return $reasons;
    }

    // ==================== BULK OPERATIONS ====================

    /**
     * Bulk checkout multiple copies to one borrower.
     *
     * Pre-validates block conditions once, then processes each copy individually.
     * Returns per-book success/error results.
     *
     * @param array $copyIds Array of Copy IDs to check out
     * @param string $borrowerType Morph type: 'student' or 'user'
     * @param int $borrowerId The borrower's ID
     * @param int $checkedOutBy The librarian's user ID
     * @param string|null $notes Optional notes for all checkouts
     * @return array ['success' => [...], 'errors' => [...]]
     */
    public function bulkCheckout(array $copyIds, string $borrowerType, int $borrowerId, int $checkedOutBy, ?string $notes = null): array {
        $results = ['success' => [], 'errors' => []];

        // Pre-validate block conditions once
        $blockReasons = $this->getBlockReasons($borrowerType, $borrowerId);
        if (!empty($blockReasons)) {
            foreach ($copyIds as $copyId) {
                $copy = Copy::find($copyId);
                $results['errors'][] = [
                    'copy_id' => $copyId,
                    'accession_number' => $copy ? $copy->accession_number : 'unknown',
                    'message' => implode(' ', $blockReasons),
                ];
            }
            return $results;
        }

        // Check remaining capacity
        $activeCount = LibraryTransaction::forBorrower($borrowerType, $borrowerId)
            ->active()
            ->count();
        $maxBooks = $this->getMaxBooks($borrowerType);
        $remaining = $maxBooks - $activeCount;

        if (count($copyIds) > $remaining) {
            foreach ($copyIds as $copyId) {
                $copy = Copy::find($copyId);
                $results['errors'][] = [
                    'copy_id' => $copyId,
                    'accession_number' => $copy ? $copy->accession_number : 'unknown',
                    'message' => "Cannot check out " . count($copyIds) . " books. Only {$remaining} of {$maxBooks} slots available.",
                ];
            }
            return $results;
        }

        // Process each copy individually
        foreach ($copyIds as $copyId) {
            try {
                $copy = Copy::findOrFail($copyId);
                $transaction = $this->checkout($copy, $borrowerType, $borrowerId, $checkedOutBy, $notes);

                $results['success'][] = [
                    'copy_id' => $copy->id,
                    'accession_number' => $copy->accession_number,
                    'transaction_id' => $transaction->id,
                    'due_date' => $transaction->due_date->format('d M Y'),
                ];
            } catch (\Exception $e) {
                $copy = Copy::find($copyId);
                $results['errors'][] = [
                    'copy_id' => $copyId,
                    'accession_number' => $copy ? $copy->accession_number : 'unknown',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Bulk checkin multiple copies by accession number.
     *
     * Processes each copy individually with error isolation.
     *
     * @param array $accessionNumbers Array of accession numbers
     * @param int $checkedInBy The librarian's user ID
     * @return array ['success' => [...], 'errors' => [...]]
     */
    public function bulkCheckin(array $accessionNumbers, int $checkedInBy): array {
        $results = ['success' => [], 'errors' => []];

        foreach ($accessionNumbers as $accessionNumber) {
            try {
                $copy = Copy::where('accession_number', $accessionNumber)->firstOrFail();
                $transaction = $this->checkin($copy, $checkedInBy);

                $transaction->load('borrower');

                $results['success'][] = [
                    'accession_number' => $accessionNumber,
                    'transaction_id' => $transaction->id,
                    'borrower_name' => $transaction->borrower ? ($transaction->borrower->name ?? $transaction->borrower->first_name . ' ' . $transaction->borrower->last_name) : 'Unknown',
                    'return_date' => $transaction->return_date->format('d M Y'),
                ];
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'accession_number' => $accessionNumber,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    // ==================== SETTINGS HELPERS ====================

    /**
     * Get the loan period (in days) for a borrower type, optionally per item type.
     */
    protected function getLoanPeriod(string $borrowerType, ?string $itemType = null): int {
        if ($itemType) {
            $itemTypes = LibrarySetting::get('catalog_item_types', []);
            foreach ($itemTypes as $type) {
                if ($type['name'] === $itemType) {
                    $key = 'loan_period_' . $this->settingsKey($borrowerType);
                    if (!empty($type[$key])) {
                        return (int) $type[$key];
                    }
                    break;
                }
            }
        }
        // Fall back to global
        $settings = LibrarySetting::get('loan_period', ['student' => 14, 'staff' => 30]);
        return (int) ($settings[$this->settingsKey($borrowerType)] ?? 14);
    }

    /**
     * Get the maximum number of books a borrower type can have checked out.
     */
    protected function getMaxBooks(string $borrowerType): int {
        $settings = LibrarySetting::get('max_books', ['student' => 3, 'staff' => 5]);
        return (int) ($settings[$this->settingsKey($borrowerType)] ?? 3);
    }

    /**
     * Get the maximum number of renewals allowed for a borrower type, optionally per item type.
     */
    protected function getMaxRenewals(string $borrowerType, ?string $itemType = null): int {
        if ($itemType) {
            $itemTypes = LibrarySetting::get('catalog_item_types', []);
            foreach ($itemTypes as $type) {
                if ($type['name'] === $itemType) {
                    $key = 'max_renewals_' . $this->settingsKey($borrowerType);
                    if (isset($type[$key]) && $type[$key] !== null && $type[$key] !== '') {
                        return (int) $type[$key];
                    }
                    break;
                }
            }
        }
        // Fall back to global
        $settings = LibrarySetting::get('max_renewals', ['student' => 1, 'staff' => 2]);
        return (int) ($settings[$this->settingsKey($borrowerType)] ?? 1);
    }

    /**
     * Map morph borrower_type to settings key.
     *
     * CRITICAL: Morph map uses 'student' and 'user' but settings use 'student' and 'staff'.
     * 'student' -> 'student', anything else (including 'user') -> 'staff'
     */
    protected function settingsKey(string $borrowerType): string {
        return $borrowerType === 'student' ? 'student' : 'staff';
    }
}
