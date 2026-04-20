<?php

namespace App\Console\Commands;

use App\Contracts\BiometricDeviceInterface;
use App\Models\StaffAttendance\AttendanceDevice;
use App\Models\StaffAttendance\AttendanceSyncLog;
use App\Models\StaffAttendance\BiometricRawEvent;
use App\Services\StaffAttendance\BiometricEventService;
use App\Services\StaffAttendance\DeviceSyncService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Artisan command to sync attendance events from biometric devices.
 *
 * Fetches attendance events from Hikvision (or other supported) devices
 * and stores them in the biometric_raw_events table for processing.
 *
 * Usage:
 *   php artisan attendance:sync-biometric                    # Sync all active devices
 *   php artisan attendance:sync-biometric --device=1        # Sync specific device
 *   php artisan attendance:sync-biometric --hours=2         # Look back 2 hours
 */
class SyncBiometricEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync-biometric
                            {--device= : Specific device ID to sync}
                            {--hours=1 : Hours to look back for events}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync attendance events from biometric devices';

    /**
     * Execute the console command.
     *
     * Only syncs devices configured for pull mode (server polls device).
     * Devices in push or agent mode receive events via webhooks instead.
     *
     * @param DeviceSyncService $syncService
     * @param BiometricEventService $eventService
     * @return int
     */
    public function handle(DeviceSyncService $syncService, BiometricEventService $eventService): int
    {
        $deviceId = $this->option('device');

        // Only sync devices in pull mode (server polls device)
        // Push and agent mode devices receive events via webhooks
        $devices = $deviceId
            ? AttendanceDevice::where('id', $deviceId)->active()->pullMode()->get()
            : AttendanceDevice::active()->pullMode()->get();

        if ($devices->isEmpty()) {
            $this->info('No active pull-mode devices found to sync');
            return Command::SUCCESS;
        }

        $hours = (int) $this->option('hours');
        $startTime = now()->subHours($hours);
        $endTime = now();

        $this->info("Syncing events from {$startTime->format('Y-m-d H:i:s')} to {$endTime->format('Y-m-d H:i:s')}");

        foreach ($devices as $device) {
            $this->syncDevice($device, $syncService, $eventService, $startTime, $endTime);
        }

        return Command::SUCCESS;
    }

    /**
     * Sync events from a single device.
     *
     * @param AttendanceDevice $device
     * @param DeviceSyncService $syncService
     * @param BiometricEventService $eventService
     * @param Carbon $startTime
     * @param Carbon $endTime
     * @return void
     */
    private function syncDevice(
        AttendanceDevice $device,
        DeviceSyncService $syncService,
        BiometricEventService $eventService,
        Carbon $startTime,
        Carbon $endTime
    ): void {
        $log = $syncService->startSync($device->id, AttendanceSyncLog::SYNC_PULL_EVENTS);
        $this->info("Syncing device: {$device->name}...");

        try {
            // Resolve the appropriate driver for this device
            $driver = app(BiometricDeviceInterface::class, ['device' => $device]);

            // Fetch events from device
            $events = $driver->fetchEvents($startTime, $endTime);
            $this->info("  Fetched {$events->count()} events");

            // Process each event
            $processed = 0;
            $failed = 0;

            foreach ($events as $eventData) {
                try {
                    $this->storeEvent($device->id, $eventData, $eventService);
                    $processed++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::warning("Failed to store event: {$e->getMessage()}", [
                        'device_id' => $device->id,
                        'event_data' => $eventData,
                    ]);
                }
            }

            // Complete sync log
            $syncService->completeSync($log, $processed, $failed);
            $device->update(['last_sync_at' => now()]);
            $this->info("  Processed: {$processed}, Failed: {$failed}");

        } catch (\Exception $e) {
            // Log error with details (DEV-07)
            $syncService->logSyncError($log, $e->getMessage(), [
                'exception_class' => get_class($e),
                'device_ip' => $device->ip_address,
            ]);
            $this->error("  Failed: {$e->getMessage()}");
            Log::error("Biometric sync failed for device {$device->name}", [
                'device_id' => $device->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Store a single event with deduplication.
     *
     * Uses updateOrCreate to prevent duplicate events when sync windows overlap.
     *
     * @param int $deviceId
     * @param array $eventData
     * @param BiometricEventService $eventService
     * @return void
     */
    private function storeEvent(int $deviceId, array $eventData, BiometricEventService $eventService): void
    {
        // Skip if employee_number is empty
        if (empty($eventData['employee_number'])) {
            return;
        }

        BiometricRawEvent::updateOrCreate(
            [
                'device_id' => $deviceId,
                'employee_number' => $eventData['employee_number'],
                'event_timestamp' => $eventData['event_timestamp'],
            ],
            [
                'event_type' => $eventData['event_type'],
                'raw_payload' => $eventData['raw_payload'],
                'processed' => false,
            ]
        );
    }
}
