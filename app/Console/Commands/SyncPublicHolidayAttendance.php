<?php

namespace App\Console\Commands;

use App\Services\StaffAttendance\PublicHolidayAttendanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Artisan command to sync attendance records for public holidays.
 *
 * Creates 'H' (Holiday) attendance records for all active staff on public holidays.
 * Runs daily at 06:00 AM to mark holiday attendance before the work day starts.
 *
 * Usage:
 *   php artisan sync:holiday-attendance           # Sync for today
 *   php artisan sync:holiday-attendance 2026-12-25  # Sync for specific date
 */
class SyncPublicHolidayAttendance extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:holiday-attendance
                            {date? : The date to sync (YYYY-MM-DD, defaults to today)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create attendance records for all active staff on public holidays';

    /**
     * Execute the console command.
     *
     * @param PublicHolidayAttendanceService $service
     * @return int
     */
    public function handle(PublicHolidayAttendanceService $service): int{
        $dateArg = $this->argument('date');
        $date = $dateArg ? Carbon::parse($dateArg) : Carbon::today();

        $this->info("Checking for public holiday: {$date->format('Y-m-d')}");

        $result = $service->syncPublicHolidays($date);

        if ($result['holiday_name'] === null) {
            $this->info('Not a public holiday - no records created.');
            return Command::SUCCESS;
        }

        $this->info("Holiday: {$result['holiday_name']}");
        $this->info("Created: {$result['created']}");
        $this->info("Skipped: {$result['skipped']}");

        return Command::SUCCESS;
    }
}
