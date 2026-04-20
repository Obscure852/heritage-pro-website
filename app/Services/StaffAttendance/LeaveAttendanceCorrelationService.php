<?php

namespace App\Services\StaffAttendance;

use App\Models\Leave\LeaveRequest;
use App\Models\StaffAttendance\StaffAttendanceCode;
use App\Models\StaffAttendance\StaffAttendanceRecord;
use App\Services\Leave\PublicHolidayService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for correlating leave requests to attendance records.
 *
 * When leave is approved, this service creates attendance records for each
 * applicable day in the leave range. Handles weekend/holiday exclusion and
 * prevents overwriting existing biometric records.
 */
class LeaveAttendanceCorrelationService {
    /**
     * @var PublicHolidayService
     */
    protected PublicHolidayService $holidayService;

    /**
     * Create service instance.
     *
     * @param PublicHolidayService $holidayService
     */
    public function __construct(PublicHolidayService $holidayService) {
        $this->holidayService = $holidayService;
    }

    /**
     * Sync leave request to attendance records.
     *
     * Creates attendance records for each applicable day in the leave range.
     * Supports half-day leave with appropriate status and notes.
     * Skips weekends, public holidays, and days with existing non-leave-sync records.
     *
     * @param LeaveRequest $leaveRequest
     * @return void
     */
    public function syncLeaveToAttendance(LeaveRequest $leaveRequest): void {
        DB::transaction(function () use ($leaveRequest) {
            // Delete existing leave-sync records for idempotent re-sync
            $this->removeLeaveAttendanceRecords($leaveRequest);

            // Eager load leaveType if not already loaded
            if (!$leaveRequest->relationLoaded('leaveType')) {
                $leaveRequest->load('leaveType');
            }

            // Get the appropriate attendance code for this leave type
            $attendanceCodeId = $this->getLeaveAttendanceCodeId($leaveRequest);

            // Get leave type name for notes
            $leaveTypeName = $leaveRequest->leaveType?->name ?? 'Leave';

            // Iterate through each date in the leave range
            $period = CarbonPeriod::create(
                $leaveRequest->start_date,
                $leaveRequest->end_date
            );

            foreach ($period as $date) {
                // Skip weekends
                if ($date->isSaturday() || $date->isSunday()) {
                    continue;
                }

                // Skip public holidays
                if ($this->holidayService->isHoliday($date)) {
                    continue;
                }

                // Skip if existing non-leave-sync record exists (don't overwrite biometric/manual)
                $existingRecord = StaffAttendanceRecord::where('user_id', $leaveRequest->user_id)
                    ->where('date', $date->format('Y-m-d'))
                    ->where('entry_type', '!=', StaffAttendanceRecord::ENTRY_LEAVE_SYNC)
                    ->exists();

                if ($existingRecord) {
                    continue;
                }

                // Determine half-day period (returns 'am', 'pm', or null for full-day)
                $halfDayPeriod = $this->getHalfDayPeriod($leaveRequest, $date);

                // Set status and notes based on half-day or full-day
                if ($halfDayPeriod) {
                    $periodLabel = strtoupper($halfDayPeriod); // 'AM' or 'PM'
                    $status = StaffAttendanceRecord::STATUS_HALF_DAY;
                    $notes = "{$periodLabel} Leave - {$leaveTypeName}";
                } else {
                    $status = StaffAttendanceRecord::STATUS_ON_LEAVE;
                    $notes = "Approved leave: {$leaveTypeName}";
                }

                Log::debug('Creating leave attendance record', [
                    'user_id' => $leaveRequest->user_id,
                    'date' => $date->toDateString(),
                    'is_half_day' => !is_null($halfDayPeriod),
                    'half_day_period' => $halfDayPeriod,
                    'status' => $status,
                ]);

                // Create attendance record for leave
                StaffAttendanceRecord::create([
                    'user_id' => $leaveRequest->user_id,
                    'date' => $date->format('Y-m-d'),
                    'status' => $status,
                    'entry_type' => StaffAttendanceRecord::ENTRY_LEAVE_SYNC,
                    'leave_request_id' => $leaveRequest->id,
                    'attendance_code_id' => $attendanceCodeId,
                    'notes' => $notes,
                ]);
            }
        });
    }

    /**
     * Get the appropriate attendance code ID for a leave request.
     *
     * Returns SL (Sick Leave) code for sick leave types, OL (On Leave) for all others.
     *
     * @param LeaveRequest $leaveRequest
     * @return int|null
     */
    public function getLeaveAttendanceCodeId(LeaveRequest $leaveRequest): ?int {
        // Eager load leaveType if not already loaded
        if (!$leaveRequest->relationLoaded('leaveType')) {
            $leaveRequest->load('leaveType');
        }

        $leaveTypeName = $leaveRequest->leaveType?->name ?? '';

        // Check if it's a sick leave type (case-insensitive)
        if (stripos($leaveTypeName, 'sick') !== false) {
            return StaffAttendanceCode::where('code', 'SL')->value('id');
        }

        // Default to OL (On Leave) for all other leave types
        return StaffAttendanceCode::where('code', 'OL')->value('id');
    }

    /**
     * Remove all attendance records created from a leave request.
     *
     * Used for idempotent re-sync and leave cancellation cleanup.
     *
     * @param LeaveRequest $leaveRequest
     * @return int Number of records deleted
     */
    public function removeLeaveAttendanceRecords(LeaveRequest $leaveRequest): int {
        return StaffAttendanceRecord::where('leave_request_id', $leaveRequest->id)->delete();
    }

    /**
     * Determine if a specific date within a leave request should be a half-day.
     *
     * Logic:
     * - Single-day leave: half-day if EITHER start_half_day OR end_half_day is set (but not both)
     * - Multi-day leave: first day is half-day if start_half_day set, last day if end_half_day set
     * - Middle days are always full-day
     *
     * @param LeaveRequest $leaveRequest
     * @param Carbon $date
     * @return bool
     */
    public function isHalfDayDate(LeaveRequest $leaveRequest, Carbon $date): bool {
        $startDate = $leaveRequest->start_date;
        $endDate = $leaveRequest->end_date;
        $isSingleDay = $startDate->isSameDay($endDate);

        if ($isSingleDay) {
            // Single-day: half-day if only one of start_half_day or end_half_day is set
            $hasStartHalf = !empty($leaveRequest->start_half_day);
            $hasEndHalf = !empty($leaveRequest->end_half_day);

            // If both are set, it's a full day (AM + PM = full day)
            // If only one is set, it's a half day
            return ($hasStartHalf || $hasEndHalf) && !($hasStartHalf && $hasEndHalf);
        }

        // Multi-day leave
        if ($date->isSameDay($startDate) && !empty($leaveRequest->start_half_day)) {
            return true;
        }

        if ($date->isSameDay($endDate) && !empty($leaveRequest->end_half_day)) {
            return true;
        }

        // Middle days are always full-day
        return false;
    }

    /**
     * Get the half-day period ('am' or 'pm') for a specific date, or null if full-day.
     *
     * Logic:
     * - Single-day leave: return the set half-day value, or null if both are set (full day)
     * - Multi-day leave: first day returns start_half_day, last day returns end_half_day
     * - Middle days return null (full day)
     *
     * @param LeaveRequest $leaveRequest
     * @param Carbon $date
     * @return string|null 'am', 'pm', or null
     */
    public function getHalfDayPeriod(LeaveRequest $leaveRequest, Carbon $date): ?string {
        $startDate = $leaveRequest->start_date;
        $endDate = $leaveRequest->end_date;
        $isSingleDay = $startDate->isSameDay($endDate);

        if ($isSingleDay) {
            $hasStartHalf = !empty($leaveRequest->start_half_day);
            $hasEndHalf = !empty($leaveRequest->end_half_day);

            // If both are set, it's a full day
            if ($hasStartHalf && $hasEndHalf) {
                return null;
            }

            // Return whichever one is set
            if ($hasStartHalf) {
                return $leaveRequest->start_half_day;
            }
            if ($hasEndHalf) {
                return $leaveRequest->end_half_day;
            }

            // Neither is set - full day
            return null;
        }

        // Multi-day leave
        if ($date->isSameDay($startDate)) {
            return $leaveRequest->start_half_day; // May be null
        }

        if ($date->isSameDay($endDate)) {
            return $leaveRequest->end_half_day; // May be null
        }

        // Middle days are always full-day
        return null;
    }
}
