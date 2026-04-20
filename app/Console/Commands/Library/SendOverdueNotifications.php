<?php

namespace App\Console\Commands\Library;

use App\Models\Library\LibraryOverdueNotice;
use App\Models\Library\LibrarySetting;
use App\Services\Library\LibraryNotificationService;
use App\Services\Library\OverdueService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendOverdueNotifications extends Command {
    protected $signature = 'library:send-overdue-notifications
                            {--dry-run : Show what would be sent without sending}';

    protected $description = 'Send overdue notifications to borrowers based on configurable schedule';

    public function __construct(
        protected LibraryNotificationService $notificationService,
        protected OverdueService $overdueService
    ) {
        parent::__construct();
    }

    public function handle(): int {
        $isDryRun = $this->option('dry-run');

        $this->info('Library Overdue Notifications');
        $this->info('=============================');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No notifications will be sent');
        }

        // Read configurable schedule: defaults to days [1, 7, 14]
        $schedule = LibrarySetting::get('overdue_notification_schedule', ['days' => [1, 7, 14]]);
        $scheduleDays = $schedule['days'] ?? [1, 7, 14];

        // Sort days ascending for predictable processing
        sort($scheduleDays);

        $this->info('Notification schedule: days ' . implode(', ', $scheduleDays));
        $this->newLine();

        // Get all overdue transactions (already have days_overdue computed)
        $transactions = $this->overdueService->getOverdueTransactions();
        $this->info("Found {$transactions->count()} overdue transaction(s)");

        $sent = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($transactions as $transaction) {
            $daysOverdue = $transaction->days_overdue;

            // For each schedule threshold, check if this transaction qualifies
            foreach ($scheduleDays as $scheduleDay) {
                // Transaction must be at least this many days overdue
                if ($daysOverdue < $scheduleDay) {
                    continue;
                }

                // Check if already sent at this threshold (dedup)
                if (LibraryOverdueNotice::alreadySent($transaction->id, 'overdue_reminder', $scheduleDay)) {
                    $skipped++;
                    continue;
                }

                $borrowerName = $transaction->borrower->full_name
                    ?? $transaction->borrower->name
                    ?? 'Unknown';
                $bookTitle = optional($transaction->copy)->book->title ?? 'Unknown Book';

                if ($isDryRun) {
                    $this->line("  Would send day-{$scheduleDay} notice to {$borrowerName} for \"{$bookTitle}\" ({$daysOverdue} days overdue)");
                    $sent++;
                    continue;
                }

                try {
                    $result = $this->notificationService->sendOverdueNotification($transaction, $scheduleDay);

                    if (isset($result['skipped']) && $result['skipped']) {
                        $skipped++;
                        $this->line("  Skipped: {$borrowerName} - \"{$bookTitle}\" ({$result['reason']})");
                    } elseif (isset($result['sent']) && $result['sent']) {
                        $sent++;
                        $channelsList = implode(', ', $result['channels'] ?? []);
                        $this->line("  Sent day-{$scheduleDay} notice to {$borrowerName} for \"{$bookTitle}\" via [{$channelsList}]");
                    } else {
                        $errors++;
                        $errorMsg = $result['error'] ?? 'unknown';
                        $this->error("  Failed: {$borrowerName} - \"{$bookTitle}\" ({$errorMsg})");
                    }
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Failed to send overdue notification', [
                        'transaction_id' => $transaction->id,
                        'schedule_day' => $scheduleDay,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error("  Error: {$borrowerName} - {$e->getMessage()}");
                }
            }
        }

        // Summary table
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Notifications sent', $sent],
                ['Skipped (already sent)', $skipped],
                ['Errors', $errors],
            ]
        );

        Log::info('Library overdue notifications completed', [
            'sent' => $sent,
            'skipped' => $skipped,
            'errors' => $errors,
            'dry_run' => $isDryRun,
        ]);

        return self::SUCCESS;
    }
}
