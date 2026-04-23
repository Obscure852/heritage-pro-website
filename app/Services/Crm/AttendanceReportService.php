<?php

namespace App\Services\Crm;

use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceDeviceLog;
use App\Models\CrmAttendanceRecord;
use App\Models\CrmUserDepartment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AttendanceReportService
{
    public function dailySummary(Carbon $date, array $filters = []): Collection
    {
        $query = CrmAttendanceRecord::query()
            ->with(['user', 'code'])
            ->whereDate('date', $date);

        $this->applyFilters($query, $filters);

        return $query->orderBy('user_id')->get()->map(fn (CrmAttendanceRecord $r) => [
            'user_name' => $r->user?->name ?? 'Unknown',
            'department' => $r->user?->crm_department_name ?? '—',
            'code' => $r->code?->code ?? '—',
            'code_label' => $r->code?->label ?? '—',
            'clocked_in' => $r->clocked_in_at?->format('H:i') ?? '—',
            'clocked_out' => $r->clocked_out_at?->format('H:i') ?? '—',
            'total_hours' => $r->total_minutes ? round($r->total_minutes / 60, 1) : 0,
            'source' => $r->source,
            'is_late' => $r->is_late,
            'is_early_out' => $r->is_early_out,
            'auto_closed' => $r->auto_closed,
        ]);
    }

    public function monthlyRegister(Carbon $month, ?int $departmentId = null): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $usersQuery = User::query()
            ->select(['id', 'name', 'firstname', 'lastname', 'username', 'email', 'department_id'])
            ->where('active', true)
            ->whereIn('role', array_keys(config('heritage_crm.roles', [])));

        if ($departmentId) {
            $usersQuery->where('department_id', $departmentId);
        }

        $users = $usersQuery->orderBy('name')->get();

        $records = CrmAttendanceRecord::query()
            ->with('code')
            ->whereIn('user_id', $users->pluck('id'))
            ->whereDate('date', '>=', $start->toDateString())->whereDate('date', '<=', $end->toDateString())
            ->get()
            ->groupBy('user_id');

        $days = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $days[] = [
                'date' => $current->copy(),
                'day' => $current->day,
                'label' => $current->format('D'),
                'is_weekend' => $current->isWeekend(),
            ];
            $current->addDay();
        }

        $rows = [];
        foreach ($users as $user) {
            $userRecords = $records->get($user->id, collect())
                ->keyBy(fn ($r) => $r->date->toDateString());

            $dayCodes = [];
            foreach ($days as $day) {
                $record = $userRecords->get($day['date']->toDateString());
                $dayCodes[] = $record?->code?->code ?? '';
            }

            $rows[] = [
                'user' => $user,
                'codes' => $dayCodes,
            ];
        }

        return [
            'month_label' => $month->format('F Y'),
            'days' => $days,
            'rows' => $rows,
        ];
    }

    public function hoursWorked(Carbon $from, Carbon $to, array $filters = []): Collection
    {
        $query = CrmAttendanceRecord::query()
            ->with(['user', 'code'])
            ->whereDate('date', '>=', $from->toDateString())->whereDate('date', '<=', $to->toDateString());

        $this->applyFilters($query, $filters);

        return $query->get()
            ->groupBy('user_id')
            ->map(function (Collection $records) {
                $user = $records->first()->user;
                $totalMinutes = $records->sum('total_minutes') ?? 0;
                $overtimeMinutes = $records->sum('overtime_minutes') ?? 0;
                $workingDays = $records->filter(fn ($r) => $r->code && (float) $r->code->counts_as_working > 0)->count();

                return [
                    'user_name' => $user?->name ?? 'Unknown',
                    'department' => $user?->crm_department_name ?? '—',
                    'working_days' => $workingDays,
                    'total_hours' => round($totalMinutes / 60, 1),
                    'overtime_hours' => round($overtimeMinutes / 60, 1),
                    'average_daily_hours' => $workingDays > 0 ? round(($totalMinutes / 60) / $workingDays, 1) : 0,
                ];
            })
            ->sortBy('user_name')
            ->values();
    }

    public function lateArrivals(Carbon $from, Carbon $to, array $filters = []): Collection
    {
        $query = CrmAttendanceRecord::query()
            ->with(['user', 'code'])
            ->where('is_late', true)
            ->whereDate('date', '>=', $from->toDateString())->whereDate('date', '<=', $to->toDateString());

        $this->applyFilters($query, $filters);

        return $query->orderBy('date')->get()->map(fn (CrmAttendanceRecord $r) => [
            'user_name' => $r->user?->name ?? 'Unknown',
            'department' => $r->user?->crm_department_name ?? '—',
            'date' => $r->date->format('D, d M Y'),
            'clocked_in' => $r->clocked_in_at?->format('H:i') ?? '—',
            'code' => $r->code?->code ?? '—',
        ]);
    }

    public function absenteeism(Carbon $from, Carbon $to, array $filters = []): Collection
    {
        $absentCode = CrmAttendanceCode::where('code', 'A')->first();

        if (! $absentCode) {
            return collect();
        }

        $query = CrmAttendanceRecord::query()
            ->with('user')
            ->where('attendance_code_id', $absentCode->id)
            ->whereDate('date', '>=', $from->toDateString())->whereDate('date', '<=', $to->toDateString());

        $this->applyFilters($query, $filters);

        return $query->get()
            ->groupBy('user_id')
            ->map(function (Collection $records) {
                $user = $records->first()->user;

                return [
                    'user_name' => $user?->name ?? 'Unknown',
                    'department' => $user?->crm_department_name ?? '—',
                    'absent_days' => $records->count(),
                    'dates' => $records->pluck('date')->map(fn ($d) => $d->format('d M'))->implode(', '),
                ];
            })
            ->sortByDesc('absent_days')
            ->values();
    }

    public function biometricAudit(Carbon $from, Carbon $to, ?int $deviceId = null): Collection
    {
        $query = CrmAttendanceDeviceLog::query()
            ->with(['device', 'matchedUser'])
            ->whereBetween('captured_at', [$from->startOfDay(), $to->endOfDay()]);

        if ($deviceId) {
            $query->where('device_id', $deviceId);
        }

        return $query->latest('captured_at')->get()->map(fn (CrmAttendanceDeviceLog $log) => [
            'device_name' => $log->device?->name ?? '—',
            'employee_identifier' => $log->employee_identifier,
            'event_type' => $log->event_type,
            'captured_at' => $log->captured_at->format('d M Y H:i:s'),
            'verification_method' => $log->verification_method ?? '—',
            'confidence' => $log->confidence_score !== null ? number_format((float) $log->confidence_score * 100, 1) . '%' : '—',
            'status' => $log->status,
            'matched_user' => $log->matchedUser?->name ?? '—',
            'error' => $log->error_message,
        ]);
    }

    public function todayStats(): array
    {
        $today = now()->copy()->startOfDay();
        $records = CrmAttendanceRecord::query()
            ->with('code')
            ->whereDate('date', $today)
            ->get();

        return [
            'total' => $records->count(),
            'present' => $records->filter(fn ($r) => $r->code && $r->code->code === 'P')->count(),
            'late' => $records->filter(fn ($r) => $r->is_late)->count(),
            'absent' => $records->filter(fn ($r) => $r->code && $r->code->code === 'A')->count(),
        ];
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['department_id'])) {
            $query->whereHas('user', fn ($q) => $q->where('department_id', $filters['department_id']));
        }
    }
}
