<?php

namespace App\Services\Crm;

use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceHoliday;
use App\Models\CrmAttendanceRecord;
use App\Models\CrmUserDepartment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AttendanceGridService
{
    public function __construct(
        private readonly AttendanceShiftResolver $shiftResolver
    ) {
    }

    public function buildPersonalGrid(User $user, Carbon $start, Carbon $end): array
    {
        $records = CrmAttendanceRecord::query()
            ->with('code')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (CrmAttendanceRecord $record) => $record->date->toDateString());

        $holidays = $this->holidaysForRange($start, $end);

        $days = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $dateString = $current->toDateString();
            $dayOfWeek = $current->dayOfWeekIso - 1;
            $shiftDay = $this->shiftResolver->resolveShiftDay($user, $current);
            $isWorkingDay = $shiftDay?->is_working_day ?? false;
            $holiday = $this->findHolidayForUserOnDate($holidays, $user, $current);
            $record = $records->get($dateString);

            $days[] = [
                'date' => $current->copy(),
                'date_string' => $dateString,
                'day_of_week' => $dayOfWeek,
                'day_label' => $current->format('D'),
                'day_number' => $current->format('d'),
                'is_working_day' => $isWorkingDay,
                'is_holiday' => $holiday !== null,
                'holiday_name' => $holiday?->name,
                'is_today' => $current->isToday(),
                'is_weekend' => $dayOfWeek >= 5,
                'record' => $record,
                'code' => $record?->code,
                'clocked_in_at' => $record?->clocked_in_at,
                'clocked_out_at' => $record?->clocked_out_at,
                'total_minutes' => $record?->total_minutes,
                'is_late' => $record?->is_late ?? false,
                'is_early_out' => $record?->is_early_out ?? false,
                'auto_closed' => $record?->auto_closed ?? false,
                'status' => $record?->status ?? null,
            ];

            $current->addDay();
        }

        return $days;
    }

    public function personalStats(User $user, Carbon $monthStart, Carbon $monthEnd): array
    {
        $records = CrmAttendanceRecord::query()
            ->with('code')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $totalMinutes = $records->sum('total_minutes') ?? 0;
        $daysPresent = $records->filter(fn ($r) => $r->code && $r->code->category === 'presence')->count();
        $daysAbsent = $records->filter(fn ($r) => $r->code && $r->code->category === 'absence')->count();
        $daysLate = $records->filter(fn ($r) => $r->is_late)->count();

        return [
            'total_hours' => round($totalMinutes / 60, 1),
            'days_present' => $daysPresent,
            'days_absent' => $daysAbsent,
            'days_late' => $daysLate,
        ];
    }

    public function weeklyHours(User $user, Carbon $weekStart, Carbon $weekEnd): array
    {
        $records = CrmAttendanceRecord::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('date')
            ->get();

        $days = [];
        $current = $weekStart->copy();

        while ($current->lte($weekEnd)) {
            $record = $records->firstWhere('date', $current->toDateString());
            $shiftDay = $this->shiftResolver->resolveShiftDay($user, $current);

            $days[] = [
                'date' => $current->copy(),
                'label' => $current->format('D'),
                'actual_hours' => round(($record->total_minutes ?? 0) / 60, 1),
                'expected_hours' => ($shiftDay && $shiftDay->is_working_day)
                    ? round(Carbon::parse($shiftDay->start_time)->diffInMinutes(Carbon::parse($shiftDay->end_time)) / 60, 1)
                    : 0,
            ];

            $current->addDay();
        }

        return $days;
    }

    public function buildTeamGrid(User $viewer, Carbon $start, Carbon $end, array $filters = []): array
    {
        $usersQuery = User::query()
            ->where('active', true)
            ->whereIn('role', array_keys(config('heritage_crm.roles', [])));

        if ($viewer->isManager() && ! $viewer->isAdmin()) {
            $usersQuery->where('department_id', $viewer->department_id);
        }

        if (! empty($filters['department_ids'])) {
            $usersQuery->whereIn('department_id', $filters['department_ids']);
        }

        if (! empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $usersQuery->where(function (Builder $q) use ($term) {
                $columns = Schema::getColumnListing((new User())->getTable());
                $q->where('email', 'like', $term);

                foreach (['name', 'firstname', 'lastname', 'username'] as $col) {
                    if (in_array($col, $columns, true)) {
                        $q->orWhere($col, 'like', $term);
                    }
                }
            });
        }

        $users = $usersQuery->orderBy('email')->get();
        $userIds = $users->pluck('id');

        $records = CrmAttendanceRecord::query()
            ->with('code')
            ->whereIn('user_id', $userIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('user_id');

        $holidays = $this->holidaysForRange($start, $end);

        $dateHeaders = $this->buildDateHeaders($start, $end, $holidays);

        $departments = CrmUserDepartment::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $grouped = [];
        $noDeptKey = '__none__';

        foreach ($users as $user) {
            $deptId = $user->department_id ?: $noDeptKey;
            $userRecords = $records->get($user->id, collect())
                ->keyBy(fn (CrmAttendanceRecord $r) => $r->date->toDateString());

            $dayCells = [];
            foreach ($dateHeaders as $header) {
                $dateString = $header['date_string'];
                $record = $userRecords->get($dateString);
                $shiftDay = $this->shiftResolver->resolveShiftDay($user, $header['date']);
                $isWorkingDay = $shiftDay?->is_working_day ?? false;
                $holiday = $this->findHolidayForUserOnDate($holidays, $user, $header['date']);

                $dayCells[] = [
                    'date_string' => $dateString,
                    'record' => $record,
                    'record_id' => $record?->id,
                    'code' => $record?->code,
                    'is_working_day' => $isWorkingDay,
                    'is_holiday' => $holiday !== null,
                    'is_today' => $header['is_today'],
                    'is_weekend' => $header['is_weekend'],
                    'clocked_in_at' => $record?->clocked_in_at,
                    'clocked_out_at' => $record?->clocked_out_at,
                    'total_minutes' => $record?->total_minutes,
                    'status' => $record?->status,
                ];
            }

            if (! isset($grouped[$deptId])) {
                $dept = $deptId !== $noDeptKey ? $departments->get($deptId) : null;
                $grouped[$deptId] = [
                    'department_id' => $deptId !== $noDeptKey ? $deptId : null,
                    'department_name' => $dept?->name ?? 'No Department',
                    'users' => [],
                ];
            }

            $grouped[$deptId]['users'][] = [
                'user' => $user,
                'days' => $dayCells,
            ];
        }

        foreach ($grouped as $key => $group) {
            $grouped[$key]['staff_count'] = count($group['users']);
        }

        return [
            'departments' => array_values($grouped),
            'date_headers' => $dateHeaders,
            'total_users' => $users->count(),
        ];
    }

    public function buildDateHeaders(Carbon $start, Carbon $end, ?Collection $holidays = null): array
    {
        $holidays = $holidays ?? $this->holidaysForRange($start, $end);
        $headers = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $dayOfWeek = $current->dayOfWeekIso - 1;
            $hasHoliday = $holidays->contains(function (CrmAttendanceHoliday $h) use ($current) {
                if ($h->is_recurring) {
                    return $h->date->month === $current->month && $h->date->day === $current->day;
                }
                return $h->date->toDateString() === $current->toDateString();
            });

            $headers[] = [
                'date' => $current->copy(),
                'date_string' => $current->toDateString(),
                'day_label' => $current->format('D'),
                'day_number' => $current->format('d'),
                'month_label' => $current->format('M'),
                'is_today' => $current->isToday(),
                'is_weekend' => $dayOfWeek >= 5,
                'is_holiday' => $hasHoliday,
            ];

            $current->addDay();
        }

        return $headers;
    }

    private function holidaysForRange(Carbon $start, Carbon $end): Collection
    {
        return CrmAttendanceHoliday::query()
            ->where('is_active', true)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('is_recurring', true);
                    });
            })
            ->get();
    }

    private function findHolidayForUserOnDate(Collection $holidays, User $user, Carbon $date): ?CrmAttendanceHoliday
    {
        return $holidays->first(function (CrmAttendanceHoliday $holiday) use ($user, $date) {
            if ($holiday->is_recurring) {
                if ($holiday->date->month !== $date->month || $holiday->date->day !== $date->day) {
                    return false;
                }
            } else {
                if ($holiday->date->toDateString() !== $date->toDateString()) {
                    return false;
                }
            }

            return $holiday->appliesToUser($user);
        });
    }
}
