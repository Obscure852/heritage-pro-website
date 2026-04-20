<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class ProcessNotificationEmails extends Command {
    protected $signature = 'lms:process-notification-emails {--limit=50 : Number of emails to process}';
    protected $description = 'Process pending notification emails from the queue';

    public function handle(NotificationService $notificationService): int {
        $limit = (int) $this->option('limit');

        $this->info("Processing up to {$limit} pending notification emails...");

        $sent = $notificationService->processPendingEmails($limit);

        $this->info("Processed {$sent} emails successfully.");

        return Command::SUCCESS;
    }
}
