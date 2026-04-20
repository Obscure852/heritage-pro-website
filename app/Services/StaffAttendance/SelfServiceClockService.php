<?php

namespace App\Services\StaffAttendance;

use App\Models\StaffAttendance\StaffAttendanceAuditLog;
use App\Models\StaffAttendance\StaffAttendanceCode;
use App\Models\StaffAttendance\StaffAttendanceRecord;
use App\Models\StaffAttendance\StaffAttendanceSetting;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Service for self-service clock in/out functionality.
 *
 * Handles clock in/out operations including validation, lateness calculation,
 * hours worked calculation, and audit logging.
 */
class SelfServiceClockService
{
    /**
     * @var AttendanceRecordService
     */
    protected AttendanceRecordService $attendanceRecordService;

    /**
     * Create a new SelfServiceClockService instance.
     *
     * @param AttendanceRecordService $attendanceRecordService
     */
    public function __construct(AttendanceRecordService $attendanceRecordService) {
        $this->attendanceRecordService = $attendanceRecordService;
    }

    /**
     * Clock in a user for today.
     *
     * @param User $user The user clocking in
     * @param string $ipAddress The IP address of the request
     * @return array Result with success, clock_in time, is_late, late_minutes
     * @throws Exception If user already clocked in today
     */
    public function clockIn(User $user, string $ipAddress): array {
        return DB::transaction(function () use ($user, $ipAddress) {
            $today = Carbon::today();
            $now = Carbon::now();

            // Check for existing record with clock_in set
            $existingRecord = StaffAttendanceRecord::forUser($user->id)
                ->forDate($today)
                ->whereNotNull('clock_in')
                ->first();

            if ($existingRecord) {
                throw new Exception('You have already clocked in today.');
            }

            // Get or create attendance record for today
            $record = $this->attendanceRecordService->getOrCreateForDate($user->id, $today);

            // Set clock in values
            $record->clock_in = $now;
            $record->entry_type = StaffAttendanceRecord::ENTRY_SELF_SERVICE;
            $record->recorded_by = $user->id;

            // Calculate lateness and set status
            $this->checkLateness($record);

            // Assign attendance code based on status
            if ($record->status === StaffAttendanceRecord::STATUS_LATE) {
                $lateCode = StaffAttendanceCode::where('code', 'L')->first();
                $record->attendance_code_id = $lateCode?->id;
            } else {
                $presentCode = StaffAttendanceCode::where('code', 'P')->first();
                $record->attendance_code_id = $presentCode?->id;
            }

            $record->save();

            // Create audit log
            StaffAttendanceAuditLog::log(
                $record,
                StaffAttendanceAuditLog::ACTION_CREATE,
                null,
                $record->toArray(),
                'Self-service clock in from IP: ' . $ipAddress
            );

            return [
                'success' => true,
                'clock_in' => $now->format('h:i A'),
                'is_late' => $record->status === StaffAttendanceRecord::STATUS_LATE,
                'late_minutes' => $record->late_minutes ?? 0,
            ];
        });
    }

    /**
     * Clock out a user for today.
     *
     * @param User $user The user clocking out
     * @param string $ipAddress The IP address of the request
     * @return array Result with success, clock_out time, hours_worked
     * @throws Exception If user has not clocked in or already clocked out
     */
    public function clockOut(User $user, string $ipAddress): array {
        return DB::transaction(function () use ($user, $ipAddress) {
            $today = Carbon::today();
            $now = Carbon::now();

            // Find existing record with clock_in
            $record = StaffAttendanceRecord::forUser($user->id)
                ->forDate($today)
                ->whereNotNull('clock_in')
                ->first();

            if (!$record) {
                throw new Exception('You have not clocked in today.');
            }

            if ($record->clock_out !== null) {
                throw new Exception('You have already clocked out today.');
            }

            // Store old values for audit
            $oldValues = $record->toArray();

            // Set clock out
            $record->clock_out = $now;

            // Calculate hours worked
            $clockIn = Carbon::parse($record->clock_in);
            $minutes = $clockIn->diffInMinutes($now);
            $record->hours_worked = round($minutes / 60, 2);

            $record->save();

            // Create audit log
            StaffAttendanceAuditLog::log(
                $record,
                StaffAttendanceAuditLog::ACTION_UPDATE,
                $oldValues,
                $record->fresh()->toArray(),
                'Self-service clock out from IP: ' . $ipAddress
            );

            return [
                'success' => true,
                'clock_out' => $now->format('h:i A'),
                'hours_worked' => $this->formatHoursWorked($record->hours_worked),
            ];
        });
    }

    /**
     * Get the current clock status for a user.
     *
     * @param User $user The user to check
     * @return array Status information
     */
    public function getStatus(User $user): array {
        $today = Carbon::today();

        // Get today's record (may be null)
        $record = StaffAttendanceRecord::forUser($user->id)
            ->forDate($today)
            ->first();

        // Get work times from settings
        $workStartSetting = StaffAttendanceSetting::get('work_start_time');
        $workEndSetting = StaffAttendanceSetting::get('work_end_time');

        $workStart = $workStartSetting['time'] ?? '07:30';
        $workEnd = $workEndSetting['time'] ?? '16:30';

        if (!$record) {
            return [
                'clocked_in' => false,
                'clocked_out' => false,
                'clock_in_time' => null,
                'clock_out_time' => null,
                'hours_worked' => null,
                'status' => 'not_clocked',
                'is_late' => false,
                'late_minutes' => 0,
                'work_start' => $workStart,
                'work_end' => $workEnd,
            ];
        }

        return [
            'clocked_in' => $record->clock_in !== null,
            'clocked_out' => $record->clock_out !== null,
            'clock_in_time' => $record->clock_in ? Carbon::parse($record->clock_in)->format('h:i A') : null,
            'clock_out_time' => $record->clock_out ? Carbon::parse($record->clock_out)->format('h:i A') : null,
            'hours_worked' => $record->hours_worked,
            'status' => $record->status ?? 'not_clocked',
            'is_late' => $record->status === StaffAttendanceRecord::STATUS_LATE,
            'late_minutes' => $record->late_minutes ?? 0,
            'work_start' => $workStart,
            'work_end' => $workEnd,
        ];
    }

    /**
     * Check if a clock-in is late and set the status and late_minutes.
     *
     * @param StaffAttendanceRecord $record The record to check
     * @return void
     */
    protected function checkLateness(StaffAttendanceRecord $record): void {
        // Get settings
        $workStartSetting = StaffAttendanceSetting::get('work_start_time');
        $gracePeriodsetting = StaffAttendanceSetting::get('grace_period_minutes');

        $workStartTime = $workStartSetting['time'] ?? '07:30';
        $graceMinutes = $gracePeriodsetting['minutes'] ?? 15;

        // Parse clock in time
        $clockIn = Carbon::parse($record->clock_in);

        // Build expected start time: date portion from record->date + time portion from setting
        $recordDate = Carbon::parse($record->date);
        $timeParts = explode(':', $workStartTime);
        $expectedStart = $recordDate->copy()->setTime((int)$timeParts[0], (int)$timeParts[1], 0);

        // Calculate grace end
        $graceEnd = $expectedStart->copy()->addMinutes($graceMinutes);

        // Check if late
        if ($clockIn->gt($graceEnd)) {
            $record->status = StaffAttendanceRecord::STATUS_LATE;
            $record->late_minutes = $clockIn->diffInMinutes($expectedStart);
        } else {
            $record->status = StaffAttendanceRecord::STATUS_PRESENT;
            $record->late_minutes = null;
        }
    }

    /**
     * Format hours worked as a human-readable string.
     *
     * @param float|null $hours Decimal hours
     * @return string Formatted string like "8h 30m"
     */
    protected function formatHoursWorked(?float $hours): string {
        if ($hours === null) {
            return '0h 0m';
        }

        $totalMinutes = (int) round($hours * 60);
        $h = (int) floor($totalMinutes / 60);
        $m = $totalMinutes % 60;

        return "{$h}h {$m}m";
    }
}
