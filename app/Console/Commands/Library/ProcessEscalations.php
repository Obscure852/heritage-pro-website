<?php

namespace App\Console\Commands\Library;

use App\Models\Library\LibraryOverdueNotice;
use App\Models\Library\LibrarySetting;
use App\Services\Library\LibraryNotificationService;
use App\Services\Library\OverdueService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessEscalations extends Command {
    protected $signature = 'library:process-escalations
                            {--dry-run : Show what would be done without making changes}';

    protected $description = 'Escalate overdue items to supervisors and declare lost after threshold';

    public function __construct(
        protected OverdueService $overdueService,
        protected LibraryNotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): int {
        $isDryRun = $this->option('dry-run');

        $this->info('Library Escalations & Lost Declaration');
        $this->info('======================================');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Read escalation config
        $escalationConfig = LibrarySetting::get('overdue_escalation', [
            'class_teacher_days' => 30,
            'hod_days' => 45,
        ]);
        $classTeacherDays = (int) ($escalationConfig['class_teacher_days'] ?? 30);
        $hodDays = (int) ($escalationConfig['hod_days'] ?? 45);

        $this->info("Escalation thresholds: class_teacher={$classTeacherDays}d, hod={$hodDays}d");

        // Get all overdue transactions
        $transactions = $this->overdueService->getOverdueTransactions();
        $this->info("Found {$transactions->count()} overdue transaction(s)");
        $this->newLine();

        $lostCount = 0;
        $hodEscalations = 0;
        $classTeacherEscalations = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($transactions as $transaction) {
            $daysOverdue = $transaction->days_overdue;
            $borrowerName = $transaction->borrower->full_name
                ?? $transaction->borrower->name
                ?? 'Unknown';
            $bookTitle = optional($transaction->copy)->book->title ?? 'Unknown Book';

            // Priority 1: Lost declaration (highest priority)
            $lostThreshold = $this->overdueService->getLostBookPeriod($transaction->borrower_type);
            if ($daysOverdue >= $lostThreshold) {
                $this->processLostDeclaration(
                    $transaction, $daysOverdue, $borrowerName, $bookTitle,
                    $lostThreshold, $isDryRun, $lostCount, $errors
                );
                continue; // Skip escalation for lost items
            }

            // Priority 2: HOD escalation
            if ($daysOverdue >= $hodDays) {
                if (LibraryOverdueNotice::alreadySent($transaction->id, 'escalation', $hodDays)) {
                    $skipped++;
                } else {
                    $this->processEscalation(
                        $transaction, $hodDays, 'hod', $borrowerName, $bookTitle,
                        $isDryRun, $hodEscalations, $errors
                    );
                }
            }

            // Priority 3: Class teacher escalation
            if ($daysOverdue >= $classTeacherDays) {
                if (LibraryOverdueNotice::alreadySent($transaction->id, 'escalation', $classTeacherDays)) {
                    $skipped++;
                } else {
                    $this->processEscalation(
                        $transaction, $classTeacherDays, 'class_teacher', $borrowerName, $bookTitle,
                        $isDryRun, $classTeacherEscalations, $errors
                    );
                }
            }
        }

        // Summary table
        $this->newLine();
        $this->table(
            ['Action', 'Count'],
            [
                ['Lost declarations', $lostCount],
                ['HOD escalations', $hodEscalations],
                ['Class teacher escalations', $classTeacherEscalations],
                ['Skipped (already processed)', $skipped],
                ['Errors', $errors],
            ]
        );

        Log::info('Library escalations completed', [
            'lost' => $lostCount,
            'hod_escalations' => $hodEscalations,
            'class_teacher_escalations' => $classTeacherEscalations,
            'skipped' => $skipped,
            'errors' => $errors,
            'dry_run' => $isDryRun,
        ]);

        return self::SUCCESS;
    }

    /**
     * Process lost declaration for a single transaction.
     */
    protected function processLostDeclaration(
        $transaction, int $daysOverdue, string $borrowerName, string $bookTitle,
        int $lostThreshold, bool $isDryRun, int &$lostCount, int &$errors
    ): void {
        if ($isDryRun) {
            $this->line("  [LOST] Would declare lost: {$borrowerName} - \"{$bookTitle}\" ({$daysOverdue}d overdue, threshold: {$lostThreshold}d)");
            $lostCount++;
            return;
        }

        try {
            $reason = "Auto-declared lost: overdue for {$daysOverdue} days (threshold: {$lostThreshold} days)";
            $this->overdueService->declareLost($transaction, $reason);
            $this->notificationService->sendLostDeclarationNotice($transaction, $daysOverdue);
            $lostCount++;
            $this->line("  [LOST] Declared lost: {$borrowerName} - \"{$bookTitle}\" ({$daysOverdue}d overdue)");
        } catch (\Exception $e) {
            $errors++;
            Log::error('Failed to declare lost', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            $this->error("  [ERROR] Lost declaration failed: {$borrowerName} - {$e->getMessage()}");
        }
    }

    /**
     * Process escalation for a single transaction.
     */
    protected function processEscalation(
        $transaction, int $dayThreshold, string $escalationType,
        string $borrowerName, string $bookTitle,
        bool $isDryRun, int &$escalationCount, int &$errors
    ): void {
        $label = str_replace('_', ' ', $escalationType);

        if ($isDryRun) {
            $this->line("  [ESCALATE] Would escalate to {$label}: {$borrowerName} - \"{$bookTitle}\" ({$transaction->days_overdue}d overdue)");
            $escalationCount++;
            return;
        }

        try {
            $result = $this->notificationService->sendEscalationNotification(
                $transaction, $dayThreshold, $escalationType
            );

            if (isset($result['skipped']) && $result['skipped']) {
                $this->line("  [SKIP] {$label}: {$borrowerName} - \"{$bookTitle}\" ({$result['reason']})");
            } elseif (isset($result['sent']) && $result['sent']) {
                $escalationCount++;
                $targetName = $result['target'] ?? 'Unknown';
                $this->line("  [ESCALATE] {$label} -> {$targetName}: {$borrowerName} - \"{$bookTitle}\"");
            } else {
                $errors++;
                $errorMsg = $result['error'] ?? 'unknown error';
                $this->error("  [ERROR] {$label}: {$borrowerName} - {$errorMsg}");
            }
        } catch (\Exception $e) {
            $errors++;
            Log::error('Failed to process escalation', [
                'transaction_id' => $transaction->id,
                'escalation_type' => $escalationType,
                'error' => $e->getMessage(),
            ]);
            $this->error("  [ERROR] {$label}: {$borrowerName} - {$e->getMessage()}");
        }
    }
}
