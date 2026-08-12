<?php

namespace App\Console\Commands;

use App\Services\ClientSetup\ClientSetupNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendClientSetupReminders extends Command
{
    protected $signature = 'client-setup:reminders {--now= : Override the current time for manual checks}';

    protected $description = 'Send client setup reminders and retry failed notifications';

    public function handle(ClientSetupNotificationService $notificationService): int
    {
        $now = $this->option('now')
            ? Carbon::parse((string) $this->option('now'))
            : now();

        $reminders = $notificationService->sendOperationalReminders($now);
        $retries = $notificationService->retryDue($now);

        $this->info(sprintf(
            'Client setup reminders: %d draft, %d overdue, %d expiry; retries: %d attempted, %d sent, %d failed.',
            $reminders['draft_reminders'],
            $reminders['overdue_reviews'],
            $reminders['expiry_warnings'],
            $retries['attempted'],
            $retries['sent'],
            $retries['failed']
        ));

        return self::SUCCESS;
    }
}
