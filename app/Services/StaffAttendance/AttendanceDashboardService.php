<?php

namespace App\Services\StaffAttendance;

use App\Models\StaffAttendance\StaffAttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service for aggregating attendance dashboard data.
 *
 * Provides statistics for manager dashboards including today's summary,
 * weekly trends for charts, absent/late lists, and absenteeism alerts.
 *
 * Team filtering uses the reporting_to hierarchy via User->subordinates(),
 * NOT department string matching.
 */
class AttendanceDashboardService {
    /**
     * Get comprehensive dashboard data for a manager or admin.
     *
     * Main entry point that aggregates all dashboard sections into a single
     * array for the view.
     *
     * @param User $user The user viewing the dashboard
     * @param bool $isAdmin Whether to show all staff (admin) or just subordinates (manager)
     * @return array Dashboard data with summary, trends, lists, and alerts
     */
    public function getDashboardData(User $user, bool $isAdmin = false): array {
        return [
            'summary' => $this->getTodayStats($user, $isAdmin),
            'weekly_trends' => $this->getWeeklyTrends($user, $isAdmin),
            'absent_list' => $this->getAbsentStaff($user, $isAdmin),
            'late_list' => $this->getLateStaff($user, $isAdmin),
            'alerts' => $this->getAbsenteeismAlerts($user, $isAdmin, 3),
            'team_size' => count($this->getTeamUserIds($user, $isAdmin)),
        ];
    }

    /**
     * Get user IDs of staff to display.
     *
     * For admins: returns all active staff user IDs.
     * For managers: uses the subordinates() relationship for team hierarchy.
     *
     * @param User $user The user viewing the dashboard
     * @param bool $isAdmin Whether to return all staff (admin) or just subordinates (manager)
     * @return array Array of user IDs
     */
    public function getTeamUserIds(User $user, bool $isAdmin = false): array {
        if ($isAdmin) {
            // Admin sees all active staff (status = 'Current')
            return User::where('status', 'Current')
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', ['Teacher', 'Staff', 'Class Teacher', 'HOD', 'Deputy Principal', 'Principal']);
                })
                ->pluck('id')
                ->toArray();
        }

        // Manager sees only their direct reports
        return $user->subordinates()->pluck('id')->toArray();
    }

    /**
     * Get today's attendance statistics by status.
     *
     * Uses a single grouped query for efficiency instead of multiple count queries.
     *
     * @param User $user The user viewing the dashboard
     * @param bool $isAdmin Whether to show all staff (admin) or just subordinates (manager)
     * @return array Counts by status: present, absent, late, on_leave, half_day, holiday
     */
    public function getTodayStats(User $user, bool $isAdmin = false): array {
        $today = Carbon::today();
        $teamIds = $this->getTeamUserIds($user, $isAdmin);

        if (empty($teamIds)) {
            return [
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'on_leave' => 0,
                'half_day' => 0,
                'holiday' => 0,
            ];
        }

        $counts = StaffAttendanceRecord::forDate($today)
            ->whereIn('user_id', $teamIds)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'present' => $counts[StaffAttendanceRecord::STATUS_PRESENT] ?? 0,
            'absent' => $counts[StaffAttendanceRecord::STATUS_ABSENT] ?? 0,
            'late' => $counts[StaffAttendanceRecord::STATUS_LATE] ?? 0,
            'on_leave' => $counts[StaffAttendanceRecord::STATUS_ON_LEAVE] ?? 0,
            'half_day' => $counts[StaffAttendanceRecord::STATUS_HALF_DAY] ?? 0,
            'holiday' => $counts[StaffAttendanceRecord::STATUS_HOLIDAY] ?? 0,
        ];
    }

    /**
     * Get weekly attendance trends for chart rendering.
     *
     * Returns data formatted for ApexCharts bar chart with labels (Mon-Sun)
     * and count arrays for present, absent, late, and on_leave.
     *
     * Uses Carbon's startOfWeek()/endOfWeek() for consistent week boundaries.
     *
     * @param User $user The user viewing the dashboard
     * @param bool $isAdmin Whether to show all staff (admin) or just subordinates (manager)
     * @return array Chart data with labels and series arrays
     */
    public function getWeeklyTrends(User $user, bool $isAdmin = false): array {
        $teamIds = $this->getTeamUserIds($user, $isAdmin);
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Initialize result arrays
        $labels = [];
        $present = [];
        $absent = [];
        $late = [];
        $onLeave = [];

        if (empty($teamIds)) {
            // Return empty structure with day labels
            for ($date = $startOfWeek->copy(); $date <= $endOfWeek; $date->addDay()) {
                $labels[] = $date->format('D');
                $present[] = 0;
                $absent[] = 0;
                $late[] = 0;
                $onLeave[] = 0;
            }

            return [
                'labels' => $labels,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'on_leave' => $onLeave,
            ];
        }

        // Query once with groupBy for efficiency
        $records = StaffAttendanceRecord::betweenDates($startOfWeek, $endOfWeek)
            ->whereIn('user_id', $teamIds)
            ->selectRaw('DATE(date) as day, status, COUNT(*) as count')
            ->groupBy('day', 'status')
            ->get();

        // Build arrays for each day
        for ($date = $startOfWeek->copy(); $date <= $endOfWeek; $date->addDay()) {
            $dayKey = $date->format('Y-m-d');
            $labels[] = $date->format('D');

            $dayRecords = $records->where('day', $dayKey);
            $present[] = (int) $dayRecords->where('status', StaffAttendanceRecord::STATUS_PRESENT)->sum('count');
            $absent[] = (int) $dayRecords->where('status', StaffAttendanceRecord::STATUS_ABSENT)->sum('count');
            $late[] = (int) $dayRecords->where('status', StaffAttendanceRecord::STATUS_LATE)->sum('count');
            $onLeave[] = (int) $dayRecords->where('status', StaffAttendanceRecord::STATUS_ON_LEAVE)->sum('count');
        }

        return [
            'labels' => $labels,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'on_leave' => $onLeave,
        ];
    }

    /**
     * Get list of absent staff for today.
     *
     * Returns collection with user info for display in the dashboard absent list.
     * Uses eager loading to prevent N+1 queries.
     *
     * @param User $user The user viewing the dashboard
     * @param bool $isAdmin Whether to show all staff (admin) or just subordinates (manager)
     * @return Collection Collection of attendance records with user relationships
     */
    public function getAbsentStaff(User $user, bool $isAdmin = false): Collection {
        $today = Carbon::today();
        $teamIds = $this->getTeamUserIds($user, $isAdmin);

        if (empty($teamIds)) {
            return collect();
        }

        return StaffAttendanceRecord::forDate($today)
            ->whereIn('user_id', $teamIds)
            ->byStatus(StaffAttendanceRecord::STATUS_ABSENT)
            ->with('user:id,firstname,lastname,department')
            ->get();
    }

    /**
     * Get list of late arrivals for today.
     *
     * Returns collection with user info and late details for dashboard display.
     * Includes late_minutes and clock_in time for context.
     *
     * @param User $user The user viewing the dashboard
     * @param bool $isAdmin Whether to show all staff (admin) or just subordinates (manager)
     * @return Collection Collection of attendance records with user relationships
     */
    public function getLateStaff(User $user, bool $isAdmin = false): Collection {
        $today = Carbon::today();
        $teamIds = $this->getTeamUserIds($user, $isAdmin);

        if (empty($teamIds)) {
            return collect();
        }

        return StaffAttendanceRecord::forDate($today)
            ->whereIn('user_id', $teamIds)
            ->byStatus(StaffAttendanceRecord::STATUS_LATE)
            ->with('user:id,firstname,lastname,department')
            ->get();
    }

    /**
     * Get absenteeism alerts for staff with 3+ consecutive absences.
     *
     * Checks each team member for consecutive absence patterns.
     * Returns alerts for those exceeding the threshold.
     *
     * @param User $user The user viewing the dashboard
     * @param bool $isAdmin Whether to show all staff (admin) or just subordinates (manager)
     * @param int $minDays Minimum consecutive days to trigger alert (default 3)
     * @return array Array of alert objects with user info and absence details
     */
    public function getAbsenteeismAlerts(User $user, bool $isAdmin = false, int $minDays = 3): array {
        $teamIds = $this->getTeamUserIds($user, $isAdmin);
        $alerts = [];

        if (empty($teamIds)) {
            return $alerts;
        }

        // Get team members with their names
        $teamMembers = User::whereIn('id', $teamIds)
            ->select('id', 'firstname', 'lastname')
            ->get();

        foreach ($teamMembers as $member) {
            $absenceInfo = $this->getConsecutiveAbsences($member->id, $minDays);

            if ($absenceInfo !== null) {
                $alerts[] = [
                    'user_id' => $member->id,
                    'name' => $member->firstname . ' ' . $member->lastname,
                    'days' => $absenceInfo['days'],
                    'start_date' => $absenceInfo['start_date'],
                ];
            }
        }

        return $alerts;
    }

    /**
     * Check for consecutive absences for a specific user.
     *
     * Queries the last 30 days of absence records and checks for consecutive
     * calendar days. Returns info if the consecutive count meets the threshold.
     *
     * NOTE: This counts calendar days, not working days. Weekends and holidays
     * are included in the consecutive count. This behavior is documented and
     * may be enhanced in a future iteration to exclude non-working days.
     *
     * @param int $userId The user to check
     * @param int $minDays Minimum consecutive days to report (default 3)
     * @return array|null Array with 'days' and 'start_date' or null if below threshold
     */
    public function getConsecutiveAbsences(int $userId, int $minDays = 3): ?array {
        $recentAbsences = StaffAttendanceRecord::forUser($userId)
            ->where('date', '>=', Carbon::today()->subDays(30))
            ->where('date', '<=', Carbon::today())
            ->byStatus(StaffAttendanceRecord::STATUS_ABSENT)
            ->orderBy('date', 'desc')
            ->get(['date']);

        if ($recentAbsences->isEmpty()) {
            return null;
        }

        $consecutiveCount = 0;
        $startDate = null;
        $prevDate = null;

        foreach ($recentAbsences as $record) {
            $currentDate = $record->date;

            if ($prevDate === null) {
                // First absence in the sequence
                $consecutiveCount = 1;
                $startDate = $currentDate;
            } elseif ($prevDate->copy()->subDay()->format('Y-m-d') === $currentDate->format('Y-m-d')) {
                // Consecutive day (going backwards in time)
                $consecutiveCount++;
                $startDate = $currentDate;
            } else {
                // Gap found, stop counting
                break;
            }

            $prevDate = $currentDate;
        }

        if ($consecutiveCount >= $minDays) {
            return [
                'days' => $consecutiveCount,
                'start_date' => $startDate,
            ];
        }

        return null;
    }
}
