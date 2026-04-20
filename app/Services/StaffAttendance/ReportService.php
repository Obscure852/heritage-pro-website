<?php

namespace App\Services\StaffAttendance;

use App\Models\StaffAttendance\StaffAttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service for generating staff attendance report data.
 *
 * Provides data layer for all attendance reports including daily, monthly,
 * department comparison, punctuality, absenteeism, and hours worked reports.
 * Consumed by ReportController and export classes.
 */
class ReportService {
    /**
     * Get daily attendance report for a specific date.
     *
     * Returns all attendance records for a given date with optional filters
     * by department or specific staff member.
     *
     * @param Carbon $date The report date
     * @param string|null $department Filter by department name (string match on User.department)
     * @param int|null $userId Filter by specific staff member
     * @return Collection Collection of attendance records with user and attendanceCode relationships
     */
    public function getDailyReport(Carbon $date, ?string $department = null, ?int $userId = null): Collection {
        $query = StaffAttendanceRecord::forDate($date)
            ->with(['user:id,firstname,lastname,department', 'attendanceCode:id,code,name,color']);

        if ($userId !== null) {
            $query->forUser($userId);
        }

        $records = $query->get();

        // Filter by department if provided (User.department is string, not FK)
        if ($department !== null) {
            $records = $records->filter(function ($record) use ($department) {
                return $record->user && $record->user->department === $department;
            });
        }

        // Sort by user's full name
        return $records->sortBy(function ($record) {
            return $record->user
                ? ($record->user->firstname . ' ' . $record->user->lastname)
                : '';
        })->values();
    }

    /**
     * Get monthly attendance summary per staff member.
     *
     * Returns aggregated attendance data for each staff member for a given month,
     * including counts by status and total hours worked.
     *
     * @param int $year Year
     * @param int $month Month (1-12)
     * @param string|null $department Filter by department
     * @param int|null $userId Filter by specific staff
     * @return Collection Collection with per-user aggregated data
     */
    public function getMonthlyReport(int $year, int $month, ?string $department = null, ?int $userId = null): Collection {
        $query = StaffAttendanceRecord::forMonth($year, $month)
            ->with('user:id,firstname,lastname,department');

        if ($userId !== null) {
            $query->forUser($userId);
        }

        $records = $query->get();

        // Filter by department if provided
        if ($department !== null) {
            $records = $records->filter(function ($record) use ($department) {
                return $record->user && $record->user->department === $department;
            });
        }

        // Group by user_id and aggregate
        return $records->groupBy('user_id')->map(function ($userRecords) {
            $user = $userRecords->first()->user;
            $userName = $user ? ($user->firstname . ' ' . $user->lastname) : 'Unknown';

            return [
                'user_id' => $userRecords->first()->user_id,
                'user_name' => $userName,
                'department' => $user->department ?? null,
                'days_present' => $userRecords->where('status', StaffAttendanceRecord::STATUS_PRESENT)->count(),
                'days_absent' => $userRecords->where('status', StaffAttendanceRecord::STATUS_ABSENT)->count(),
                'days_late' => $userRecords->where('status', StaffAttendanceRecord::STATUS_LATE)->count(),
                'days_on_leave' => $userRecords->where('status', StaffAttendanceRecord::STATUS_ON_LEAVE)->count(),
                'days_half_day' => $userRecords->where('status', StaffAttendanceRecord::STATUS_HALF_DAY)->count(),
                'total_hours' => (float) $userRecords->sum('hours_worked'),
            ];
        })->sortBy('user_name')->values();
    }

    /**
     * Get department comparison report for date range.
     *
     * Returns aggregated attendance statistics per department, including
     * attendance rates and counts by status.
     *
     * @param Carbon $startDate Start of date range
     * @param Carbon $endDate End of date range
     * @return Collection Collection with per-department aggregated stats
     */
    public function getDepartmentReport(Carbon $startDate, Carbon $endDate): Collection {
        $records = StaffAttendanceRecord::betweenDates($startDate, $endDate)
            ->with('user:id,department')
            ->get();

        // Group by department (handle null department as 'Unassigned')
        return $records->groupBy(function ($record) {
            return $record->user->department ?? 'Unassigned';
        })->map(function ($deptRecords, $department) {
            $totalRecords = $deptRecords->count();

            // Present includes both 'present' and 'late' status (they showed up)
            $presentCount = $deptRecords->whereIn('status', [
                StaffAttendanceRecord::STATUS_PRESENT,
                StaffAttendanceRecord::STATUS_LATE,
            ])->count();

            $absentCount = $deptRecords->where('status', StaffAttendanceRecord::STATUS_ABSENT)->count();
            $lateCount = $deptRecords->where('status', StaffAttendanceRecord::STATUS_LATE)->count();
            $onLeaveCount = $deptRecords->where('status', StaffAttendanceRecord::STATUS_ON_LEAVE)->count();

            // Calculate attendance rate (handle division by zero)
            $attendanceRate = $totalRecords > 0
                ? round(($presentCount / $totalRecords) * 100, 1)
                : 0.0;

            return [
                'department' => $department,
                'total_records' => $totalRecords,
                'present' => $presentCount,
                'absent' => $absentCount,
                'late' => $lateCount,
                'on_leave' => $onLeaveCount,
                'attendance_rate' => $attendanceRate,
            ];
        })->sortBy('department')->values();
    }

    /**
     * Get punctuality analysis for late arrivals.
     *
     * Returns per-user late arrival patterns including total late days,
     * minutes, averages, and recent incidents.
     *
     * @param Carbon $startDate Start of date range
     * @param Carbon $endDate End of date range
     * @param string|null $department Filter by department
     * @param int|null $userId Filter by specific staff
     * @return Collection Per-user late arrival patterns
     */
    public function getPunctualityReport(Carbon $startDate, Carbon $endDate, ?string $department = null, ?int $userId = null): Collection {
        $query = StaffAttendanceRecord::betweenDates($startDate, $endDate)
            ->byStatus(StaffAttendanceRecord::STATUS_LATE)
            ->with('user:id,firstname,lastname,department');

        if ($userId !== null) {
            $query->forUser($userId);
        }

        $records = $query->get();

        // Filter by department if provided
        if ($department !== null) {
            $records = $records->filter(function ($record) use ($department) {
                return $record->user && $record->user->department === $department;
            });
        }

        // Group by user_id and aggregate
        return $records->groupBy('user_id')->map(function ($userRecords) {
            $user = $userRecords->first()->user;
            $userName = $user ? ($user->firstname . ' ' . $user->lastname) : 'Unknown';

            $totalLateMinutes = (int) $userRecords->sum('late_minutes');
            $recordCount = $userRecords->count();
            $avgLateMinutes = $recordCount > 0
                ? round($totalLateMinutes / $recordCount, 1)
                : 0.0;

            // Get last 5 incidents sorted by date desc
            $latestIncidents = $userRecords->sortByDesc('date')
                ->take(5)
                ->map(function ($record) {
                    return [
                        'date' => $record->date->format('Y-m-d'),
                        'clock_in' => $record->clock_in ? $record->clock_in->format('H:i') : null,
                        'late_minutes' => (int) $record->late_minutes,
                    ];
                })->values()->toArray();

            return [
                'user_id' => $userRecords->first()->user_id,
                'user_name' => $userName,
                'department' => $user->department ?? null,
                'total_late_days' => $recordCount,
                'total_late_minutes' => $totalLateMinutes,
                'average_late_minutes' => $avgLateMinutes,
                'latest_incidents' => $latestIncidents,
            ];
        })->sortByDesc('total_late_days')->values();
    }

    /**
     * Get absenteeism patterns and alerts.
     *
     * Returns per-user absence data including absence rates and consecutive
     * absence detection for early intervention.
     *
     * @param Carbon $startDate Start of date range
     * @param Carbon $endDate End of date range
     * @param string|null $department Filter by department
     * @param int|null $userId Filter by specific staff
     * @param int $consecutiveThreshold Days for consecutive absence alert (default 3)
     * @return Collection Per-user absence data with alerts
     */
    public function getAbsenteeismReport(Carbon $startDate, Carbon $endDate, ?string $department = null, ?int $userId = null, int $consecutiveThreshold = 3): Collection {
        $query = StaffAttendanceRecord::betweenDates($startDate, $endDate)
            ->byStatus(StaffAttendanceRecord::STATUS_ABSENT)
            ->with('user:id,firstname,lastname,department');

        if ($userId !== null) {
            $query->forUser($userId);
        }

        $records = $query->get();

        // Filter by department if provided
        if ($department !== null) {
            $records = $records->filter(function ($record) use ($department) {
                return $record->user && $record->user->department === $department;
            });
        }

        // Calculate workdays for absence rate
        $workDays = $this->countWeekdays($startDate, $endDate);

        // Group by user_id and aggregate
        return $records->groupBy('user_id')->map(function ($userRecords) use ($workDays, $consecutiveThreshold) {
            $user = $userRecords->first()->user;
            $userName = $user ? ($user->firstname . ' ' . $user->lastname) : 'Unknown';
            $absentDays = $userRecords->count();

            // Calculate absence rate
            $absenceRate = $workDays > 0
                ? round(($absentDays / $workDays) * 100, 1)
                : 0.0;

            // Check for consecutive absences
            $consecutiveInfo = $this->detectConsecutiveAbsences(
                $userRecords->pluck('date')->sort()->values(),
                $consecutiveThreshold
            );

            return [
                'user_id' => $userRecords->first()->user_id,
                'user_name' => $userName,
                'department' => $user->department ?? null,
                'total_absent_days' => $absentDays,
                'absence_rate' => $absenceRate,
                'has_consecutive_alert' => $consecutiveInfo !== null,
                'consecutive_days' => $consecutiveInfo,
            ];
        })->sortByDesc('total_absent_days')->values();
    }

    /**
     * Get hours worked summary with overtime.
     *
     * Returns per-user hours worked data including total hours, averages,
     * and overtime calculation based on daily threshold.
     *
     * @param Carbon $startDate Start of date range
     * @param Carbon $endDate End of date range
     * @param string|null $department Filter by department
     * @param int|null $userId Filter by specific staff
     * @param float $dailyHoursThreshold Hours per day for overtime calculation (default 8.0)
     * @return Collection Per-user hours summary
     */
    public function getHoursWorkedReport(Carbon $startDate, Carbon $endDate, ?string $department = null, ?int $userId = null, float $dailyHoursThreshold = 8.0): Collection {
        $query = StaffAttendanceRecord::betweenDates($startDate, $endDate)
            ->whereNotNull('hours_worked')
            ->with('user:id,firstname,lastname,department');

        if ($userId !== null) {
            $query->forUser($userId);
        }

        $records = $query->get();

        // Filter by department if provided
        if ($department !== null) {
            $records = $records->filter(function ($record) use ($department) {
                return $record->user && $record->user->department === $department;
            });
        }

        // Group by user_id and aggregate
        return $records->groupBy('user_id')->map(function ($userRecords) use ($dailyHoursThreshold) {
            $user = $userRecords->first()->user;
            $userName = $user ? ($user->firstname . ' ' . $user->lastname) : 'Unknown';

            $daysWorked = $userRecords->count();
            $totalHours = (float) $userRecords->sum('hours_worked');
            $avgHours = $daysWorked > 0
                ? round($totalHours / $daysWorked, 2)
                : 0.0;

            // Calculate overtime: sum of (hours_worked - threshold) for each day where > threshold
            $overtimeHours = $userRecords->sum(function ($record) use ($dailyHoursThreshold) {
                $hours = (float) $record->hours_worked;
                return max(0, $hours - $dailyHoursThreshold);
            });

            return [
                'user_id' => $userRecords->first()->user_id,
                'user_name' => $userName,
                'department' => $user->department ?? null,
                'days_worked' => $daysWorked,
                'total_hours' => $totalHours,
                'average_hours' => $avgHours,
                'overtime_hours' => round((float) $overtimeHours, 2),
            ];
        })->sortByDesc('total_hours')->values();
    }

    /**
     * Count weekdays between two dates.
     *
     * @param Carbon $start Start date
     * @param Carbon $end End date
     * @return int Number of weekdays
     */
    private function countWeekdays(Carbon $start, Carbon $end): int {
        $days = 0;
        $current = $start->copy();
        while ($current <= $end) {
            if ($current->isWeekday()) {
                $days++;
            }
            $current->addDay();
        }
        return $days;
    }

    /**
     * Detect consecutive absences from a sorted collection of dates.
     *
     * @param Collection $dates Sorted collection of Carbon dates
     * @param int $threshold Minimum consecutive days to report
     * @return int|null Number of consecutive days if >= threshold, null otherwise
     */
    private function detectConsecutiveAbsences(Collection $dates, int $threshold): ?int {
        if ($dates->isEmpty()) {
            return null;
        }

        $maxConsecutive = 1;
        $currentConsecutive = 1;
        $prevDate = null;

        foreach ($dates as $date) {
            if ($prevDate !== null) {
                // Check if dates are consecutive (1 day apart)
                if ($prevDate->copy()->addDay()->format('Y-m-d') === $date->format('Y-m-d')) {
                    $currentConsecutive++;
                    $maxConsecutive = max($maxConsecutive, $currentConsecutive);
                } else {
                    $currentConsecutive = 1;
                }
            }
            $prevDate = $date;
        }

        return $maxConsecutive >= $threshold ? $maxConsecutive : null;
    }
}
