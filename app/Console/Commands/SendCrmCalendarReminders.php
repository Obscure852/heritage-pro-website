<?php

namespace App\Console\Commands;

use App\Services\Crm\CrmCalendarService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendCrmCalendarReminders extends Command
{
    protected $signature = 'crm:calendar-reminders {--now= : Override the current time for manual checks}';

    protected $description = 'Send due CRM calendar event reminder emails';

    public function handle(CrmCalendarService $calendarService): int
    {
        $now = $this->option('now')
            ? Carbon::parse((string) $this->option('now'))
            : now();

        $summary = $calendarService->sendDueEventReminders($now);

        $this->info(sprintf(
            'Sent %d calendar reminder email(s) for %d due reminder(s) across %d event(s).',
            $summary['emails'],
            $summary['reminders'],
            $summary['events']
        ));

        return self::SUCCESS;
    }
}
