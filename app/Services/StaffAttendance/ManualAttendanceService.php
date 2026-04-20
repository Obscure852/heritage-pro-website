<?php

namespace App\Services\StaffAttendance;

use App\Models\StaffAttendance\StaffAttendanceCode;
use App\Models\StaffAttendance\StaffAttendanceRecord;
use Illuminate\Support\Facades\DB;

/**
 * Service for manual attendance entry.
 *
 * Handles batch saving of manual attendance entries and
 * deriving status from attendance codes.
 */
class ManualAttendanceService
{
    /**
     * Derive attendance status from an attendance code.
     *
     * Maps attendance code to the appropriate status constant:
     * - A => absent
     * - L => late
     * - HD => half_day
     * - OL, SL => on_leave
     * - Others (P, WFH, H) => present
     *
     * @param StaffAttendanceCode|null $code The attendance code
     * @return string The derived status constant
     */
    public function deriveStatusFromCode(?StaffAttendanceCode $code): string
    {
        if ($code === null) {
            return StaffAttendanceRecord::STATUS_PRESENT;
        }

        return match ($code->code) {
            'A' => StaffAttendanceRecord::STATUS_ABSENT,
            'L' => StaffAttendanceRecord::STATUS_LATE,
            'HD' => StaffAttendanceRecord::STATUS_HALF_DAY,
            'OL', 'SL' => StaffAttendanceRecord::STATUS_ON_LEAVE,
            default => StaffAttendanceRecord::STATUS_PRESENT,
        };
    }

    /**
     * Batch save manual attendance entries.
     *
     * Uses upsert for efficient batch operations. Each entry is keyed
     * by user_id + date and will update if the combination exists.
     *
     * IMPORTANT: Leave-synced records are protected and will not be overwritten.
     * This preserves the link between leave requests and attendance records.
     *
     * @param array $attendances Array of attendance entries with keys:
     *                           user_id, date, attendance_code_id, notes
     * @param int $recordedBy User ID of the person recording the attendance
     * @return int Number of records processed
     */
    public function batchSave(array $attendances, int $recordedBy): int
    {
        return DB::transaction(function () use ($attendances, $recordedBy) {
            $now = now();
            $records = [];

            // Get all leave-synced records for the dates being processed
            // These should not be overwritten to preserve leave-attendance link
            $leaveProtectedKeys = $this->getLeaveProtectedKeys($attendances);

            foreach ($attendances as $attendance) {
                $userId = $attendance['user_id'];
                $date = $attendance['date'];
                $key = $userId . '_' . $date;

                // Skip if this is a leave-synced record (protected)
                if (in_array($key, $leaveProtectedKeys)) {
                    continue;
                }

                $attendanceCodeId = $attendance['attendance_code_id'] ?? null;
                $notes = $attendance['notes'] ?? null;

                // Load attendance code if provided
                $code = $attendanceCodeId
                    ? StaffAttendanceCode::find($attendanceCodeId)
                    : null;

                // Derive status from code
                $status = $this->deriveStatusFromCode($code);

                $records[] = [
                    'user_id' => $userId,
                    'date' => $date,
                    'attendance_code_id' => $attendanceCodeId,
                    'status' => $status,
                    'notes' => $notes,
                    'entry_type' => StaffAttendanceRecord::ENTRY_MANUAL,
                    'recorded_by' => $recordedBy,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (empty($records)) {
                return 0;
            }

            StaffAttendanceRecord::upsert(
                $records,
                ['user_id', 'date'],
                ['attendance_code_id', 'status', 'notes', 'entry_type', 'recorded_by', 'updated_at']
            );

            return count($records);
        });
    }

    /**
     * Get keys (user_id_date) for records that are leave-synced and should be protected.
     *
     * @param array $attendances
     * @return array
     */
    protected function getLeaveProtectedKeys(array $attendances): array
    {
        if (empty($attendances)) {
            return [];
        }

        // Extract unique user_id + date combinations
        $userDates = collect($attendances)->map(function ($a) {
            return [
                'user_id' => $a['user_id'],
                'date' => $a['date'],
            ];
        })->unique(function ($item) {
            return $item['user_id'] . '_' . $item['date'];
        });

        // Query for leave-synced records
        $leaveRecords = StaffAttendanceRecord::where('entry_type', StaffAttendanceRecord::ENTRY_LEAVE_SYNC)
            ->whereNotNull('leave_request_id')
            ->where(function ($query) use ($userDates) {
                foreach ($userDates as $ud) {
                    $query->orWhere(function ($q) use ($ud) {
                        $q->where('user_id', $ud['user_id'])
                          ->where('date', $ud['date']);
                    });
                }
            })
            ->get(['user_id', 'date']);

        return $leaveRecords->map(function ($r) {
            return $r->user_id . '_' . $r->date->format('Y-m-d');
        })->toArray();
    }
}
