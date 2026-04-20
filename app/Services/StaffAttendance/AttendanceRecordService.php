<?php

namespace App\Services\StaffAttendance;

use App\Models\StaffAttendance\StaffAttendanceAuditLog;
use App\Models\StaffAttendance\StaffAttendanceRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing staff attendance records.
 *
 * Handles attendance record CRUD operations, clock in/out updates,
 * and hours calculation. Respects unique constraint on user_id + date.
 */
class AttendanceRecordService
{
    // ==================== RECORD CREATION ====================

    /**
     * Get or create an attendance record for a user on a specific date.
     *
     * Uses firstOrCreate to respect the unique constraint on user_id + date.
     * If creating a new record, sets default status to 'absent'.
     *
     * @param int $userId The user ID
     * @param Carbon $date The date
     * @return StaffAttendanceRecord The existing or new record
     */
    public function getOrCreateForDate(int $userId, Carbon $date): StaffAttendanceRecord
    {
        return DB::transaction(function () use ($userId, $date) {
            try {
                return StaffAttendanceRecord::firstOrCreate(
                    [
                        'user_id' => $userId,
                        'date' => $date->toDateString(),
                    ],
                    [
                        'status' => StaffAttendanceRecord::STATUS_ABSENT,
                    ]
                );
            } catch (\Illuminate\Database\QueryException $e) {
                // Handle potential race condition - record was just created
                if ($e->getCode() == '23000') { // Integrity constraint violation
                    return StaffAttendanceRecord::where('user_id', $userId)
                        ->where('date', $date->toDateString())
                        ->first();
                }
                throw $e;
            }
        });
    }

    // ==================== CLOCK IN/OUT UPDATES ====================

    /**
     * Update the clock-in time for an attendance record.
     *
     * Updates clock_in and clock_in_device_id. If clock_out exists,
     * recalculates hours_worked. Creates an audit log entry.
     *
     * @param StaffAttendanceRecord $record The record to update
     * @param \DateTime $clockIn The clock-in time
     * @param int|null $deviceId Optional device ID
     * @return StaffAttendanceRecord The updated record
     */
    public function updateClockIn(
        StaffAttendanceRecord $record,
        \DateTime $clockIn,
        ?int $deviceId = null
    ): StaffAttendanceRecord {
        return DB::transaction(function () use ($record, $clockIn, $deviceId) {
            $oldValues = $record->toArray();

            $record->clock_in = $clockIn;
            $record->clock_in_device_id = $deviceId;
            $record->status = StaffAttendanceRecord::STATUS_PRESENT;

            // Recalculate hours if clock_out exists
            if ($record->clock_out) {
                $record->hours_worked = $this->calculateHoursWorked(
                    $clockIn,
                    $record->clock_out
                );
            }

            $record->save();

            // Create audit log entry
            StaffAttendanceAuditLog::log(
                $record,
                StaffAttendanceAuditLog::ACTION_UPDATE,
                $oldValues,
                $record->fresh()->toArray(),
                'Clock in updated'
            );

            return $record->fresh();
        });
    }

    /**
     * Update the clock-out time for an attendance record.
     *
     * Updates clock_out and clock_out_device_id. Recalculates hours_worked.
     * Creates an audit log entry.
     *
     * @param StaffAttendanceRecord $record The record to update
     * @param \DateTime $clockOut The clock-out time
     * @param int|null $deviceId Optional device ID
     * @return StaffAttendanceRecord The updated record
     */
    public function updateClockOut(
        StaffAttendanceRecord $record,
        \DateTime $clockOut,
        ?int $deviceId = null
    ): StaffAttendanceRecord {
        return DB::transaction(function () use ($record, $clockOut, $deviceId) {
            $oldValues = $record->toArray();

            $record->clock_out = $clockOut;
            $record->clock_out_device_id = $deviceId;

            // Calculate hours worked
            $record->hours_worked = $this->calculateHoursWorked(
                $record->clock_in,
                $clockOut
            );

            $record->save();

            // Create audit log entry
            StaffAttendanceAuditLog::log(
                $record,
                StaffAttendanceAuditLog::ACTION_UPDATE,
                $oldValues,
                $record->fresh()->toArray(),
                'Clock out updated'
            );

            return $record->fresh();
        });
    }

    // ==================== HOURS CALCULATION ====================

    /**
     * Calculate hours worked between clock in and clock out.
     *
     * Returns the difference in hours as a decimal (e.g., 8.5 for 8 hours 30 minutes).
     *
     * @param \DateTime|null $clockIn The clock-in time
     * @param \DateTime|null $clockOut The clock-out time
     * @return float|null Hours worked or null if either time is null
     */
    public function calculateHoursWorked(?\DateTime $clockIn, ?\DateTime $clockOut): ?float
    {
        if ($clockIn === null || $clockOut === null) {
            return null;
        }

        $clockInCarbon = Carbon::instance($clockIn);
        $clockOutCarbon = Carbon::instance($clockOut);

        // Calculate difference in minutes, then convert to hours
        $minutes = $clockInCarbon->diffInMinutes($clockOutCarbon);
        
        return round($minutes / 60, 2);
    }

    // ==================== RECORD RETRIEVAL ====================

    /**
     * Get attendance records for a user within a date range.
     *
     * Eager loads user and device relationships.
     *
     * @param int $userId The user ID
     * @param Carbon $startDate Start of date range
     * @param Carbon $endDate End of date range
     * @return Collection Collection of StaffAttendanceRecord models
     */
    public function getRecordsForUser(
        int $userId,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        return StaffAttendanceRecord::forUser($userId)
            ->betweenDates($startDate, $endDate)
            ->with(['user', 'clockInDevice', 'clockOutDevice'])
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Get a single attendance record for a user on a specific date.
     *
     * @param int $userId The user ID
     * @param Carbon $date The date
     * @return StaffAttendanceRecord|null The record or null if not found
     */
    public function getRecordForUserDate(int $userId, Carbon $date): ?StaffAttendanceRecord
    {
        return StaffAttendanceRecord::forUser($userId)
            ->forDate($date)
            ->first();
    }
}
