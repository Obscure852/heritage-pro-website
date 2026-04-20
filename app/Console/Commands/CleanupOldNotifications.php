<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CleanupOldNotifications extends Command {
    protected $signature = 'lms:cleanup-notifications {--days=90 : Delete notifications older than this many days}';
    protected $description = 'Clean up old read notifications';

    public function handle(NotificationService $notificationService): int {
        $days = (int) $this->option('days');

        $this->info("Cleaning up notifications older than {$days} days...");

        $deleted = $notificationService->deleteOldNotifications($days);

        $this->info("Deleted {$deleted} old notifications.");

        return Command::SUCCESS;
    }
}
