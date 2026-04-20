<?php

namespace App\Console\Commands;

use App\Services\StaffAttendance\AttendanceProcessingService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Artisan command to create daily attendance records for all mapped staff.
 *
 * Creates attendance records for staff who haven't punched, marking them
 * as absent. This closes the gap where staff with no punches would have
 * no attendance record for the day.
 *
 * Usage:
 *   php artisan attendance:create-daily-records                    # For today
 *   php artisan attendance:create-daily-records --date=2026-01-28  # Specific date
 */
class CreateDailyAttendanceRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:create-daily-records
                            {--date= : Specific date (Y-m-d), defaults to today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create attendance records for all mapped staff (marks absent if no punches)';

    /**
     * Execute the console command.
     *
     * @param AttendanceProcessingService $service
     * @return int
     */
    public function handle(AttendanceProcessingService $service): int
    {
        // Parse date option, default to today in Africa/Gaborone timezone
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'), 'Africa/Gaborone')
            : Carbon::now('Africa/Gaborone');

        $this->info("Creating daily attendance records for {$date->toDateString()}...");

        try {
            $result = $service->createDailyRecordsForAllStaff($date);

            $this->info("Created {$result['created']} absent records, {$result['existing']} staff already had records for {$date->toDateString()}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to create daily records: {$e->getMessage()}");
            Log::error('attendance:create-daily-records failed', [
                'date' => $date->toDateString(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }
}
