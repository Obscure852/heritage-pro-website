<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel{

    protected $bootstrappers = [
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        \App\Bootstrap\HandleExceptions::class,
        \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];

    protected $commands = [
        Commands\PopulateTerms::class,
        Commands\UpdateTermDates::class,
    ];

    protected function schedule(Schedule $schedule) {
        $schedule->command('logging:archive')->weekly();
        $schedule->command('backup:run')->dailyAt('02:00');

        // Leave reminders - run daily at 8 AM
        $schedule->command('leave:send-reminders')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/leave-reminders.log'));

        // Pending approval reminders - run daily at 9 AM
        $schedule->command('leave:send-pending-reminders')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/leave-pending-reminders.log'));

        // Biometric device sync - every 10 minutes
        $schedule->command('attendance:sync-biometric')
            ->everyTenMinutes()
            ->withoutOverlapping(30)  // Lock expires after 30 minutes
            ->appendOutputTo(storage_path('logs/biometric-sync.log'))
            ->onFailure(function () {
                Log::error('Biometric sync scheduled job failed');
            });

        // Process biometric events into attendance records every 15 minutes
        $schedule->command('attendance:process-events')
            ->everyFifteenMinutes()
            ->withoutOverlapping(30)  // Lock expires after 30 minutes
            ->appendOutputTo(storage_path('logs/attendance-processing.log'));

        // Create daily attendance records (mark absent for no-shows) at 6 PM
        $schedule->command('attendance:create-daily-records')
            ->dailyAt('18:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/attendance-daily-records.log'));

        // Sync public holiday attendance at 6 AM (before work day starts)
        $schedule->command('sync:holiday-attendance')
            ->dailyAt('06:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/holiday-attendance-sync.log'));

        // Fee module scheduled commands
        // Apply late fees to overdue invoices at 00:05 daily
        $schedule->command('fee:apply-late-fees')
            ->dailyAt('00:05')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/fee-late-fees.log'));

        // Send payment reminders at 08:00 daily
        $schedule->command('fee:send-reminders')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/fee-reminders.log'));

        // Send overdue notifications at 09:00 daily
        $schedule->command('fee:send-overdue-notifications')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/fee-overdue-notifications.log'));

        // Document review deadline reminders - daily at 08:00
        $schedule->command('documents:send-review-reminders')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/document-review-reminders.log'));

        // Document expiration checks - daily at 07:00
        $schedule->command('documents:check-expirations')
            ->dailyAt('07:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/document-expirations.log'));

        // Purge permanently deleted documents - daily at 03:00
        $schedule->command('documents:purge-trash')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/document-trash-purge.log'));

        // Library overdue detection - daily at 06:30
        $schedule->command('library:detect-overdue')
            ->dailyAt('06:30')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/library-overdue-detection.log'));

        // Library escalations and lost declaration - daily at 07:00
        $schedule->command('library:process-escalations')
            ->dailyAt('07:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/library-escalations.log'));

        // Library fine assessment - daily at 07:15 (after detection at 06:30, before notifications at 08:30)
        $schedule->command('library:assess-fines')
            ->dailyAt('07:15')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/library-fine-assessment.log'));

        // Library hold expiry - daily at 07:30 (after fine assessment, before notifications)
        $schedule->command('library:expire-holds')
            ->dailyAt('07:30')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/library-expire-holds.log'));

        // Library overdue notifications - daily at 08:30 (after detection at 06:30)
        $schedule->command('library:send-overdue-notifications')
            ->dailyAt('08:30')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/library-overdue-notifications.log'));

        $schedule->command('whatsapp:sync-templates')
            ->dailyAt('05:30')
            ->when(fn () => (bool) settings('whatsapp.sync_enabled', true))
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/whatsapp-template-sync.log'));
    }


    protected function commands(){
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
