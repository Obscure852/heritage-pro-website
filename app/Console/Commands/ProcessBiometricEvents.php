<?php

namespace App\Console\Commands;

use App\Services\StaffAttendance\AttendanceProcessingService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Artisan command to process raw biometric events into attendance records.
 *
 * Transforms raw punch data from biometric_raw_events into meaningful
 * daily attendance records with clock in/out times and status.
 *
 * Usage:
 *   php artisan attendance:process-events                    # Process up to 500 events
 *   php artisan attendance:process-events --limit=100        # Process up to 100 events
 *   php artisan attendance:process-events --date=2026-01-28  # Process specific date only
 */
class ProcessBiometricEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:process-events
                            {--limit=500 : Maximum events to process per run}
                            {--date= : Process specific date only (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process raw biometric events into attendance records';

    /**
     * Execute the console command.
     *
     * @param AttendanceProcessingService $service
     * @return int
     */
    public function handle(AttendanceProcessingService $service): int
    {
        $limit = (int) $this->option('limit');
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : null;

        $dateInfo = $date ? " for {$date->toDateString()}" : '';
        $this->info("Processing up to {$limit} events{$dateInfo}...");

        try {
            $result = $service->processUnprocessedEvents($limit, $date);

            $this->info("Processed: {$result['processed']}");
            $this->info("Skipped (unmapped): {$result['skipped']}");
            $this->info("Failed: {$result['failed']}");

            if ($result['failed'] > 0) {
                $this->warn("Some events failed to process. Check logs for details.");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Processing failed: {$e->getMessage()}");
            Log::error('attendance:process-events failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }
}
