<?php

namespace App\Services\StaffAttendance;

use App\Models\StaffAttendance\BiometricIdMapping;
use App\Models\StaffAttendance\BiometricRawEvent;
use App\Models\StaffAttendance\StaffAttendanceRecord;
use App\Models\StaffAttendance\StaffAttendanceSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for processing raw biometric events into attendance records.
 *
 * This is the core processing pipeline that transforms raw punch data
 * into meaningful daily attendance records with correct status assignment.
 */
class AttendanceProcessingService
{
    /**
     * @var AttendanceRecordService
     */
    protected AttendanceRecordService $recordService;

    /**
     * @var StaffMappingService
     */
    protected StaffMappingService $mappingService;

    /**
     * Create a new AttendanceProcessingService instance.
     *
     * @param AttendanceRecordService $recordService
     * @param StaffMappingService $mappingService
     */
    public function __construct(
        AttendanceRecordService $recordService,
        StaffMappingService $mappingService
    ) {
        $this->recordService = $recordService;
        $this->mappingService = $mappingService;
    }

    // ==================== MAIN PROCESSING ====================

    /**
     * Process unprocessed biometric events into attendance records.
     *
     * Groups events by employee_number + date, then processes each group
     * to create/update attendance records with clock in/out times and status.
     *
     * @param int $limit Maximum number of events to process (default 500)
     * @param Carbon|null $specificDate Process only events for this date
     * @return array{processed: int, skipped: int, failed: int}
     */
    public function processUnprocessedEvents(int $limit = 500, ?Carbon $specificDate = null): array
    {
        $counts = [
            'processed' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        // Build query for unprocessed events
        $query = BiometricRawEvent::unprocessed()
            ->orderBy('event_timestamp', 'asc');

        // Filter by specific date if provided
        if ($specificDate) {
            $query->whereDate('event_timestamp', $specificDate->toDateString());
        }

        // Get events with limit
        $events = $query->limit($limit)->get();

        if ($events->isEmpty()) {
            return $counts;
        }

        // Group events by employee_number + date (Africa/Gaborone timezone)
        $grouped = $events->groupBy(function ($event) {
            $date = Carbon::parse($event->event_timestamp)
                ->setTimezone('Africa/Gaborone')
                ->toDateString();
            return $event->employee_number . '|' . $date;
        });

        // Process each employee-day group
        foreach ($grouped as $key => $dayEvents) {
            try {
                [$employeeNumber, $dateString] = explode('|', $key);
                $date = Carbon::parse($dateString, 'Africa/Gaborone');

                $result = $this->processEmployeeDayEvents($employeeNumber, $date, $dayEvents);

                if ($result === 'skipped') {
                    $counts['skipped'] += $dayEvents->count();
                } else {
                    $counts['processed'] += $dayEvents->count();
                }
            } catch (\Exception $e) {
                $counts['failed'] += $dayEvents->count();
                \Log::error('Failed to process employee day events', [
                    'key' => $key,
                    'error' => $e->getMessage(),
                    'event_ids' => $dayEvents->pluck('id')->toArray(),
                ]);
            }
        }

        return $counts;
    }

    // ==================== EMPLOYEE DAY PROCESSING ====================

    /**
     * Process all events for a single employee on a single day.
     *
     * Determines clock in (first punch), clock out (last punch if different),
     * assigns status based on work schedule, and creates/updates attendance record.
     *
     * @param string $employeeNumber The employee number from the device
     * @param Carbon $date The date of the events
     * @param Collection $dayEvents Collection of BiometricRawEvent for the day
     * @return string 'processed' or 'skipped'
     */
    protected function processEmployeeDayEvents(
        string $employeeNumber,
        Carbon $date,
        Collection $dayEvents
    ): string {
        // Get the first event for timestamp reference
        $firstEvent = $dayEvents->first();

        // Get user_id from mapping service (may auto-map or track as unmapped)
        $userId = $this->mappingService->findOrCreateMapping(
            $employeeNumber,
            $firstEvent->event_timestamp
        );

        // If no user mapping found, mark events as processed with skip flag
        if ($userId === null) {
            $this->markEventsProcessed($dayEvents, 'skipped_unmapped');
            return 'skipped';
        }

        // Sort events by timestamp to get first and last
        $sortedEvents = $dayEvents->sortBy('event_timestamp');
        $firstEventOfDay = $sortedEvents->first();
        $lastEventOfDay = $sortedEvents->last();

        // Get clock in time (first punch)
        $clockIn = Carbon::parse($firstEventOfDay->event_timestamp)
            ->setTimezone('Africa/Gaborone');

        // Get clock out time (last punch, if different from first)
        $clockOut = null;
        $isSinglePunch = ($firstEventOfDay->id === $lastEventOfDay->id);

        if (!$isSinglePunch) {
            $clockOut = Carbon::parse($lastEventOfDay->event_timestamp)
                ->setTimezone('Africa/Gaborone');
        }

        // Determine status based on clock in time
        $status = $this->determineStatus($clockIn, $date);

        // Get device ID from first event
        $clockInDeviceId = $firstEventOfDay->device_id;
        $clockOutDeviceId = $isSinglePunch ? null : $lastEventOfDay->device_id;

        // Process within transaction
        DB::transaction(function () use (
            $userId,
            $date,
            $clockIn,
            $clockOut,
            $clockInDeviceId,
            $clockOutDeviceId,
            $status,
            $isSinglePunch,
            $dayEvents
        ) {
            // Get or create attendance record for this user and date
            $record = $this->recordService->getOrCreateForDate($userId, $date);

            // Update clock in
            $record = $this->recordService->updateClockIn($record, $clockIn, $clockInDeviceId);

            // Update clock out if exists
            if ($clockOut !== null) {
                $record = $this->recordService->updateClockOut($record, $clockOut, $clockOutDeviceId);
            }

            // Update status
            $record->update(['status' => $status]);

            // Add note for single punch
            if ($isSinglePunch) {
                $record->update(['notes' => 'Single punch - missing clock-out']);
            }

            // Mark all events as processed
            $this->markEventsProcessed($dayEvents);
        });

        return 'processed';
    }

    // ==================== STATUS DETERMINATION ====================

    /**
     * Determine attendance status based on clock in time.
     *
     * Compares clock in against configured work_start_time and grace_period_minutes.
     * If clock in is within grace period, status is 'present', otherwise 'late'.
     *
     * @param Carbon $clockIn The clock in time
     * @param Carbon $date The date (for constructing work start time)
     * @return string StaffAttendanceRecord status constant
     */
    protected function determineStatus(Carbon $clockIn, Carbon $date): string
    {
        // Get work start time from settings (default 07:30)
        $workStartSetting = StaffAttendanceSetting::get('work_start_time', ['time' => '07:30']);
        $workStartTime = $workStartSetting['time'] ?? '07:30';

        // Get grace period from settings (default 15 minutes)
        $graceSetting = StaffAttendanceSetting::get('grace_period_minutes', ['minutes' => 15]);
        $gracePeriodMinutes = $graceSetting['minutes'] ?? 15;

        // Create work start Carbon for the date
        $workStart = Carbon::parse(
            $date->toDateString() . ' ' . $workStartTime,
            'Africa/Gaborone'
        );

        // Calculate grace end time
        $graceEnd = $workStart->copy()->addMinutes($gracePeriodMinutes);

        // Compare clock in to grace end
        if ($clockIn->lte($graceEnd)) {
            return StaffAttendanceRecord::STATUS_PRESENT;
        }

        return StaffAttendanceRecord::STATUS_LATE;
    }

    // ==================== EVENT MARKING ====================

    /**
     * Mark events as processed.
     *
     * Updates processed flag and timestamp on all provided events.
     *
     * @param Collection $events Collection of BiometricRawEvent models
     * @param string|null $skipReason Optional reason if events were skipped
     * @return void
     */
    protected function markEventsProcessed(Collection $events, ?string $skipReason = null): void
    {
        $eventIds = $events->pluck('id')->toArray();

        BiometricRawEvent::whereIn('id', $eventIds)->update([
            'processed' => true,
            'processed_at' => now(),
        ]);
    }

    // ==================== DAILY RECORD CREATION ====================

    /**
     * Create attendance records for all staff with biometric mappings.
     *
     * This method ensures every mapped staff member has an attendance record
     * for the given date. Staff who haven't punched will get a record with
     * status 'absent' (the default in getOrCreateForDate).
     *
     * Used by the scheduled job at end of work day to close the "absent"
     * detection gap - staff who never punch would otherwise have no record.
     *
     * @param Carbon $date The date to create records for
     * @return array{created: int, existing: int}
     */
    public function createDailyRecordsForAllStaff(Carbon $date): array
    {
        $counts = [
            'created' => 0,
            'existing' => 0,
        ];

        // Get all distinct user IDs that have biometric mappings
        // The mapping itself is the filter - if someone has a mapping, they should be tracked
        $userIds = BiometricIdMapping::distinct()->pluck('user_id');

        foreach ($userIds as $userId) {
            // getOrCreateForDate returns existing record or creates new with status='absent'
            $record = $this->recordService->getOrCreateForDate($userId, $date);

            // Check if this was a new record (status is absent and no clock times)
            // New records will have status='absent' and null clock_in
            if ($record->status === StaffAttendanceRecord::STATUS_ABSENT && $record->clock_in === null) {
                // Could be newly created OR existing absent record - check wasRecentlyCreated
                if ($record->wasRecentlyCreated) {
                    $counts['created']++;
                } else {
                    $counts['existing']++;
                }
            } else {
                // Record exists with punches (present/late status)
                $counts['existing']++;
            }
        }

        Log::info('Daily attendance records created', [
            'date' => $date->toDateString(),
            'created' => $counts['created'],
            'existing' => $counts['existing'],
            'total_mapped_staff' => $userIds->count(),
        ]);

        return $counts;
    }
}
