<?php

namespace App\Services\Library;

use App\Models\Library\LibraryAuditLog;
use App\Models\Library\LibrarySetting;
use App\Models\Library\LibraryTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OverdueService {
    protected CopyStatusService $copyStatusService;
    protected FineService $fineService;

    public function __construct(CopyStatusService $copyStatusService, FineService $fineService) {
        $this->copyStatusService = $copyStatusService;
        $this->fineService = $fineService;
    }

    // ==================== DETECTION ====================

    /**
     * Detect and mark overdue library items.
     *
     * Finds all transactions with status 'checked_out' whose due_date has passed
     * and marks them as 'overdue'. Idempotent: only selects 'checked_out', never
     * re-processes items already marked 'overdue'.
     *
     * @param bool $dryRun If true, show what would be done without making changes
     * @return array ['marked_overdue' => int, 'already_overdue' => int]
     */
    public function detectAndMarkOverdue(bool $dryRun = false): array {
        // Find checked_out transactions past their due date
        $overdueTransactions = LibraryTransaction::where('status', 'checked_out')
            ->where('due_date', '<', now()->toDateString())
            ->get();

        $markedCount = 0;

        if (!$dryRun && $overdueTransactions->isNotEmpty()) {
            DB::transaction(function () use ($overdueTransactions, &$markedCount) {
                foreach ($overdueTransactions as $transaction) {
                    $transaction->update(['status' => 'overdue']);

                    LibraryAuditLog::log(
                        $transaction,
                        'overdue_detected',
                        ['status' => 'checked_out'],
                        ['status' => 'overdue'],
                        'Automatic overdue detection'
                    );

                    $markedCount++;
                }
            });
        } else {
            $markedCount = $overdueTransactions->count();
        }

        // Count total overdue (including previously marked)
        $alreadyOverdue = LibraryTransaction::where('status', 'overdue')->count();

        return [
            'marked_overdue' => $dryRun ? 0 : $markedCount,
            'would_mark' => $dryRun ? $markedCount : 0,
            'already_overdue' => $alreadyOverdue,
        ];
    }

    // ==================== LOST DECLARATION ====================

    /**
     * Declare a transaction's copy as lost.
     *
     * Transitions the copy status via CopyStatusService, updates the transaction
     * status to 'lost', and logs an audit trail entry.
     *
     * @param LibraryTransaction $transaction The overdue transaction
     * @param string|null $reason Optional reason for lost declaration
     * @throws \RuntimeException If transaction is not in 'overdue' status
     */
    public function declareLost(LibraryTransaction $transaction, ?string $reason = null): void {
        DB::transaction(function () use ($transaction, $reason) {
            // Re-fetch with lock to prevent race conditions
            $transaction = LibraryTransaction::lockForUpdate()->findOrFail($transaction->id);

            if ($transaction->status !== 'overdue') {
                throw new \RuntimeException(
                    "Cannot declare lost: transaction #{$transaction->id} status is '{$transaction->status}', expected 'overdue'."
                );
            }

            $daysOverdue = $transaction->due_date->diffInDays(now());

            // Transition copy status to lost via CopyStatusService
            $this->copyStatusService->transition(
                $transaction->copy,
                CopyStatusService::STATUS_LOST,
                $reason ?? 'Auto-declared lost'
            );

            // Update transaction status
            $transaction->update(['status' => 'lost']);

            // Audit log
            LibraryAuditLog::log(
                $transaction,
                'lost_declaration',
                ['status' => 'overdue'],
                ['status' => 'lost'],
                "Declared lost after {$daysOverdue} days overdue" . ($reason ? ": {$reason}" : '')
            );

            // Assess lost book fine (replacement cost or configurable fixed amount)
            $transaction->load('copy.book');
            $this->fineService->assessLostBookFine($transaction);
        });
    }

    // ==================== SETTINGS HELPERS ====================

    /**
     * Get the lost book period in days for a given borrower type.
     *
     * Reads from LibrarySetting 'lost_book_period' and maps morph type
     * to settings key using the same pattern as CirculationService::settingsKey().
     *
     * @param string $borrowerType Morph type: 'student' or 'user'
     * @return int Number of days after which a book is declared lost
     */
    public function getLostBookPeriod(string $borrowerType): int {
        $settings = LibrarySetting::get('lost_book_period', ['student' => 60, 'staff' => 60]);
        return (int) ($settings[$this->settingsKey($borrowerType)] ?? 60);
    }

    /**
     * Map morph type to settings key.
     *
     * CRITICAL: Morph map uses 'student' and 'user' but settings use 'student' and 'staff'.
     * 'student' -> 'student', anything else (including 'user') -> 'staff'
     *
     * @param string $borrowerType
     * @return string
     */
    protected function settingsKey(string $borrowerType): string {
        return $borrowerType === 'student' ? 'student' : 'staff';
    }

    // ==================== QUERY HELPERS ====================

    /**
     * Get all overdue transactions with computed days_overdue.
     *
     * @return Collection
     */
    public function getOverdueTransactions(): Collection {
        $transactions = LibraryTransaction::with(['copy.book', 'borrower'])
            ->where('status', 'overdue')
            ->orderBy('due_date', 'asc')
            ->get();

        return $transactions->map(function ($transaction) {
            $transaction->days_overdue = $transaction->due_date->diffInDays(now());
            return $transaction;
        });
    }

    /**
     * Get overdue transactions grouped into time brackets.
     *
     * @return array Associative array of bracket_label => Collection
     */
    public function getOverdueBrackets(): array {
        $transactions = $this->getOverdueTransactions();

        return [
            '1-7' => $transactions->filter(fn ($t) => $t->days_overdue >= 1 && $t->days_overdue <= 7)->values(),
            '8-14' => $transactions->filter(fn ($t) => $t->days_overdue >= 8 && $t->days_overdue <= 14)->values(),
            '15-30' => $transactions->filter(fn ($t) => $t->days_overdue >= 15 && $t->days_overdue <= 30)->values(),
            '30+' => $transactions->filter(fn ($t) => $t->days_overdue > 30)->values(),
        ];
    }
}
