<?php

namespace App\Services\Library;

use App\Models\Library\LibraryAuditLog;
use App\Models\Library\LibraryFine;
use App\Models\Library\LibrarySetting;
use App\Models\Library\LibraryTransaction;
use Illuminate\Support\Facades\DB;

class FineService {
    // ==================== OVERDUE FINE ASSESSMENT ====================

    /**
     * Assess overdue fines for all active overdue and lost transactions.
     *
     * Creates one fine record per overdue transaction (rate snapshot on first assessment).
     * Updates existing overdue fines when days change. Skips transactions with lost fines.
     *
     * @param bool $dryRun If true, show what would be done without making changes
     * @return array ['assessed' => int, 'updated' => int, 'skipped' => int]
     */
    public function assessOverdueFines(bool $dryRun = false): array {
        $transactions = LibraryTransaction::whereIn('status', ['overdue', 'lost'])
            ->whereNull('return_date')
            ->with(['fines', 'copy.book'])
            ->get();

        $assessed = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($transactions as $transaction) {
            DB::transaction(function () use ($transaction, $dryRun, &$assessed, &$updated, &$skipped) {
                // Skip transactions that already have a lost fine
                if ($transaction->fines->where('fine_type', 'lost')->isNotEmpty()) {
                    $skipped++;
                    return;
                }

                // Find existing overdue fine for this transaction
                $existingFine = $transaction->fines->where('fine_type', 'overdue')->first();

                // Calculate days overdue
                $daysOverdue = $transaction->due_date->diffInDays(now());
                if ($daysOverdue <= 0) {
                    return; // Not yet overdue (edge case)
                }

                if ($existingFine) {
                    // Use STORED daily_rate (rate snapshot) -- never re-read from setting
                    $newAmount = bcmul((string) $existingFine->daily_rate, (string) $daysOverdue, 2);

                    // Floor: never reduce below what's already settled
                    $floor = bcadd((string) $existingFine->amount_paid, (string) $existingFine->amount_waived, 2);
                    if (bccomp($newAmount, $floor, 2) < 0) {
                        $newAmount = $floor;
                    }

                    // Only update if amount actually changed
                    if (bccomp($newAmount, (string) $existingFine->amount, 2) !== 0) {
                        if (!$dryRun) {
                            $newStatus = $this->calculateStatus($newAmount, (string) $existingFine->amount_paid, (string) $existingFine->amount_waived);
                            $existingFine->update([
                                'amount' => $newAmount,
                                'status' => $newStatus,
                            ]);
                        }
                        $updated++;
                    }
                } else {
                    // Snapshot rate from settings (frozen on first assessment)
                    $itemType = optional(optional($transaction->copy)->book)->format;
                    $dailyRate = $this->getDailyRate($transaction->borrower_type, $itemType);
                    $amount = bcmul((string) $dailyRate, (string) $daysOverdue, 2);

                    // Skip if zero rate configured
                    if (bccomp($amount, '0.00', 2) <= 0) {
                        return;
                    }

                    if (!$dryRun) {
                        $fine = LibraryFine::create([
                            'library_transaction_id' => $transaction->id,
                            'borrower_type' => $transaction->borrower_type,
                            'borrower_id' => $transaction->borrower_id,
                            'fine_type' => 'overdue',
                            'amount' => $amount,
                            'amount_paid' => 0,
                            'amount_waived' => 0,
                            'daily_rate' => $dailyRate,
                            'fine_date' => now()->toDateString(),
                            'status' => 'pending',
                        ]);

                        LibraryAuditLog::log($fine, 'fine_assessed', null, [
                            'amount' => $amount,
                            'daily_rate' => $dailyRate,
                            'fine_type' => 'overdue',
                            'days_overdue' => $daysOverdue,
                        ], 'Automatic overdue fine assessment');
                    }
                    $assessed++;
                }
            });
        }

        return ['assessed' => $assessed, 'updated' => $updated, 'skipped' => $skipped];
    }

    // ==================== PAYMENT ====================

    /**
     * Record a payment against a fine.
     *
     * Uses pessimistic locking to prevent race conditions on concurrent payments.
     *
     * @param LibraryFine $fine The fine to pay against
     * @param float $amount Payment amount
     * @param int $receivedBy User ID of the librarian receiving payment
     * @param string|null $notes Optional payment notes
     * @return LibraryFine The updated fine
     *
     * @throws \RuntimeException If fine is already settled or payment exceeds outstanding
     */
    public function recordPayment(LibraryFine $fine, float $amount, int $receivedBy, ?string $notes = null): LibraryFine {
        return DB::transaction(function () use ($fine, $amount, $receivedBy, $notes) {
            // Re-fetch with lock to prevent race conditions
            $fine = LibraryFine::lockForUpdate()->findOrFail($fine->id);

            // Validate status
            if (!in_array($fine->status, ['pending', 'partial'])) {
                throw new \RuntimeException('Cannot record payment: fine is already settled.');
            }

            // Calculate outstanding
            $outstanding = bcsub((string) $fine->amount, bcadd((string) $fine->amount_paid, (string) $fine->amount_waived, 2), 2);

            // Validate payment doesn't exceed outstanding
            if (bccomp((string) $amount, $outstanding, 2) > 0) {
                throw new \RuntimeException(
                    "Payment amount (P{$amount}) exceeds outstanding balance (P{$outstanding})."
                );
            }

            // Save old values for audit
            $oldValues = ['amount_paid' => (string) $fine->amount_paid, 'status' => $fine->status];

            // Calculate new amount_paid
            $newAmountPaid = bcadd((string) $fine->amount_paid, (string) $amount, 2);
            $newStatus = $this->calculateStatus((string) $fine->amount, $newAmountPaid, (string) $fine->amount_waived);

            // Update fine
            $fine->update([
                'amount_paid' => $newAmountPaid,
                'status' => $newStatus,
            ]);

            // Audit log
            LibraryAuditLog::log($fine, 'fine_payment', $oldValues, [
                'amount_paid' => $newAmountPaid,
                'payment_amount' => (string) $amount,
                'received_by' => $receivedBy,
                'status' => $newStatus,
            ], $notes);

            return $fine->fresh();
        });
    }

    // ==================== WAIVER ====================

    /**
     * Waive (part of) a fine.
     *
     * Uses pessimistic locking to prevent race conditions.
     *
     * @param LibraryFine $fine The fine to waive
     * @param float $amount Waiver amount
     * @param int $waivedBy User ID of the person authorizing the waiver
     * @param string $reason Reason for the waiver
     * @return LibraryFine The updated fine
     *
     * @throws \RuntimeException If fine is already settled or waiver exceeds outstanding
     */
    public function waiveFine(LibraryFine $fine, float $amount, int $waivedBy, string $reason): LibraryFine {
        return DB::transaction(function () use ($fine, $amount, $waivedBy, $reason) {
            // Re-fetch with lock to prevent race conditions
            $fine = LibraryFine::lockForUpdate()->findOrFail($fine->id);

            // Validate status
            if (!in_array($fine->status, ['pending', 'partial'])) {
                throw new \RuntimeException('Cannot waive: fine is already settled.');
            }

            // Calculate outstanding
            $outstanding = bcsub((string) $fine->amount, bcadd((string) $fine->amount_paid, (string) $fine->amount_waived, 2), 2);

            // Validate waiver doesn't exceed outstanding
            if (bccomp((string) $amount, $outstanding, 2) > 0) {
                throw new \RuntimeException(
                    "Waiver amount (P{$amount}) exceeds outstanding balance (P{$outstanding})."
                );
            }

            // Save old values for audit
            $oldValues = ['amount_waived' => (string) $fine->amount_waived, 'status' => $fine->status];

            // Calculate new amount_waived
            $newAmountWaived = bcadd((string) $fine->amount_waived, (string) $amount, 2);
            $newStatus = $this->calculateStatus((string) $fine->amount, (string) $fine->amount_paid, $newAmountWaived);

            // Update fine
            $fine->update([
                'amount_waived' => $newAmountWaived,
                'status' => $newStatus,
                'waived_by' => $waivedBy,
                'waiver_reason' => $reason,
            ]);

            // Audit log
            LibraryAuditLog::log($fine, 'fine_waiver', $oldValues, [
                'amount_waived' => $newAmountWaived,
                'waiver_amount' => (string) $amount,
                'waived_by' => $waivedBy,
                'status' => $newStatus,
            ], $reason);

            return $fine->fresh();
        });
    }

    // ==================== LOST BOOK FINE ====================

    /**
     * Assess a lost book fine for a transaction.
     *
     * Uses the book's replacement cost if available, otherwise falls back
     * to the configurable lost_book_fine setting.
     *
     * @param LibraryTransaction $transaction The lost transaction
     * @return LibraryFine The created fine
     */
    public function assessLostBookFine(LibraryTransaction $transaction): LibraryFine {
        $transaction->load('copy.book');

        // Determine amount: book price if available, otherwise fixed setting
        $bookPrice = optional(optional($transaction->copy)->book)->price;
        $fixedAmount = LibrarySetting::get('lost_book_fine', ['amount' => 100.00])['amount'];
        $amount = ($bookPrice && $bookPrice > 0) ? (string) $bookPrice : (string) $fixedAmount;

        $fine = LibraryFine::create([
            'library_transaction_id' => $transaction->id,
            'borrower_type' => $transaction->borrower_type,
            'borrower_id' => $transaction->borrower_id,
            'fine_type' => 'lost',
            'amount' => $amount,
            'amount_paid' => 0,
            'amount_waived' => 0,
            'daily_rate' => null,
            'fine_date' => now()->toDateString(),
            'status' => 'pending',
            'notes' => ($bookPrice && $bookPrice > 0) ? 'Replacement cost' : 'Fixed lost book fine',
        ]);

        LibraryAuditLog::log($fine, 'lost_fine_assessed', null, [
            'amount' => $amount,
            'fine_type' => 'lost',
            'source' => ($bookPrice && $bookPrice > 0) ? 'book_price' : 'setting',
        ], 'Lost book fine assessed');

        return $fine;
    }

    // ==================== STATUS CALCULATION ====================

    /**
     * Calculate fine status based on amounts.
     *
     * @param string $amount Total fine amount
     * @param string $amountPaid Total amount paid
     * @param string $amountWaived Total amount waived
     * @return string Status: 'pending', 'partial', 'paid', or 'waived'
     */
    public function calculateStatus(string $amount, string $amountPaid, string $amountWaived): string {
        $totalSettled = bcadd($amountPaid, $amountWaived, 2);

        if (bccomp($totalSettled, $amount, 2) >= 0) {
            // Fully settled -- determine if paid or waived
            if (bccomp($amountWaived, '0.00', 2) > 0 && bccomp($amountPaid, '0.00', 2) === 0) {
                return 'waived';
            }
            return 'paid';
        }

        if (bccomp($amountPaid, '0.00', 2) > 0 || bccomp($amountWaived, '0.00', 2) > 0) {
            return 'partial';
        }

        return 'pending';
    }

    // ==================== SETTINGS HELPERS ====================

    /**
     * Get the daily fine rate for a borrower type, optionally per item type.
     *
     * @param string $borrowerType Morph type: 'student' or 'user'
     * @param string|null $itemType The item type name (from book's format column)
     * @return string Rate as string (for bcmath)
     */
    protected function getDailyRate(string $borrowerType, ?string $itemType = null): string {
        if ($itemType) {
            $itemTypes = LibrarySetting::get('catalog_item_types', []);
            foreach ($itemTypes as $type) {
                if ($type['name'] === $itemType) {
                    $key = 'fine_rate_' . $this->settingsKey($borrowerType);
                    if (!empty($type[$key])) {
                        return (string) $type[$key];
                    }
                    break;
                }
            }
        }
        // Fall back to global
        $settings = LibrarySetting::get('fine_rate_per_day', ['student' => 1.00, 'staff' => 2.00]);
        return (string) ($settings[$this->settingsKey($borrowerType)] ?? '1.00');
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
