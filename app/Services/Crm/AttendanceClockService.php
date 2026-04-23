<?php

namespace App\Services\Crm;

use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceRecord;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceClockService
{
    public function __construct(
        private readonly AttendanceShiftResolver $shiftResolver
    ) {
    }

    public function currentStatus(User $user): array
    {
        $today = now()->copy()->startOfDay();

        $record = CrmAttendanceRecord::query()
            ->with('code')
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (! $record) {
            return [
                'state' => 'clocked_out',
                'record' => null,
                'clocked_in_at' => null,
                'elapsed_minutes' => 0,
            ];
        }

        if ($record->isClockedIn()) {
            $elapsed = (int) $record->clocked_in_at->diffInMinutes(now());

            return [
                'state' => 'clocked_in',
                'record' => $record,
                'clocked_in_at' => $record->clocked_in_at,
                'elapsed_minutes' => $elapsed,
            ];
        }

        return [
            'state' => 'clocked_out',
            'record' => $record,
            'clocked_in_at' => $record->clocked_in_at,
            'elapsed_minutes' => $record->total_minutes ?? 0,
        ];
    }

    public function clockIn(User $user, ?string $note = null): CrmAttendanceRecord
    {
        return DB::transaction(function () use ($user, $note) {
            $now = now();
            $today = $now->copy()->startOfDay();

            $existing = CrmAttendanceRecord::query()
                ->where('user_id', $user->id)
                ->whereDate('date', $today)
                ->first();

            if ($existing && $existing->isClockedIn()) {
                $debounceSeconds = (int) config('heritage_crm.attendance.clock_debounce_seconds', 60);
                $secondsSinceClockIn = $existing->clocked_in_at->diffInSeconds($now);

                if ($secondsSinceClockIn < $debounceSeconds) {
                    return $existing;
                }

                return $existing;
            }

            if ($existing && $existing->clocked_in_at && $existing->clocked_out_at) {
                return $existing;
            }

            $this->validateClockInWindow($user, $now);

            $shift = $this->shiftResolver->resolveForUserAndDate($user, $now);
            $shiftDay = $shift?->dayForWeekday($now->dayOfWeekIso - 1);

            $code = $this->resolveClockInCode($now, $shift, $shiftDay);
            $isLate = $code->code === 'LA';

            if ($existing) {
                $existing->update([
                    'clocked_in_at' => $now,
                    'attendance_code_id' => $code->id,
                    'source' => 'manual',
                    'clock_in_note' => $note,
                    'is_late' => $isLate,
                    'status' => 'active',
                ]);

                return $existing->fresh('code');
            }

            return CrmAttendanceRecord::create([
                'user_id' => $user->id,
                'date' => $today,
                'attendance_code_id' => $code->id,
                'clocked_in_at' => $now,
                'source' => 'manual',
                'clock_in_note' => $note,
                'is_late' => $isLate,
                'status' => 'active',
            ])->load('code');
        });
    }

    public function clockOut(User $user, ?string $note = null): CrmAttendanceRecord
    {
        return DB::transaction(function () use ($user, $note) {
            $now = now();
            $today = $now->copy()->startOfDay();

            $record = CrmAttendanceRecord::query()
                ->where('user_id', $user->id)
                ->whereDate('date', $today)
                ->whereNotNull('clocked_in_at')
                ->whereNull('clocked_out_at')
                ->first();

            abort_unless($record, 422, 'No open clock-in record found for today.');

            $shift = $this->shiftResolver->resolveForUserAndDate($user, $now);
            $shiftDay = $shift?->dayForWeekday($now->dayOfWeekIso - 1);

            $totalMinutes = (int) $record->clocked_in_at->diffInMinutes($now);
            $overtimeMinutes = $this->calculateOvertime($now, $shift, $shiftDay);
            $isEarlyOut = $this->isEarlyOut($now, $shift, $shiftDay);

            $record->update([
                'clocked_out_at' => $now,
                'clock_out_note' => $note,
                'total_minutes' => $totalMinutes,
                'overtime_minutes' => $overtimeMinutes,
                'is_early_out' => $isEarlyOut,
            ]);

            return $record->fresh('code');
        });
    }

    public function toggle(User $user, ?string $note = null): array
    {
        $status = $this->currentStatus($user);

        if ($status['state'] === 'clocked_in') {
            $record = $this->clockOut($user, $note);

            return [
                'action' => 'clocked_out',
                'record' => $record,
                'message' => 'Clocked out at ' . $record->clocked_out_at->format('H:i') . '. Total: ' . $this->formatMinutes($record->total_minutes) . '.',
            ];
        }

        if ($status['record'] && $status['record']->clocked_in_at && $status['record']->clocked_out_at) {
            return [
                'action' => 'already_completed',
                'record' => $status['record'],
                'message' => 'You have already clocked in and out for today.',
            ];
        }

        $record = $this->clockIn($user, $note);
        $lateLabel = $record->is_late ? ' (Late arrival)' : '';

        return [
            'action' => 'clocked_in',
            'record' => $record,
            'message' => 'Clocked in at ' . $record->clocked_in_at->format('H:i') . '.' . $lateLabel,
        ];
    }

    private function validateClockInWindow(User $user, Carbon $now): void
    {
        $shift = $this->shiftResolver->resolveForUserAndDate($user, $now);

        if (! $shift) {
            return;
        }

        if ($shift->earliest_clock_in) {
            $earliest = Carbon::parse($now->toDateString() . ' ' . $shift->earliest_clock_in);

            abort_if($now->lt($earliest), 422, 'Clock-in is not available until ' . $earliest->format('H:i') . '.');
        }

        if ($shift->latest_clock_in) {
            $latest = Carbon::parse($now->toDateString() . ' ' . $shift->latest_clock_in);

            abort_if($now->gt($latest), 422, 'Clock-in window has closed. The latest allowed time was ' . $latest->format('H:i') . '.');
        }
    }

    private function resolveClockInCode(Carbon $now, $shift, $shiftDay): CrmAttendanceCode
    {
        if ($shift && $shiftDay && $shiftDay->is_working_day) {
            $shiftStart = Carbon::parse($now->toDateString() . ' ' . $shiftDay->start_time);
            $graceEnd = $shiftStart->copy()->addMinutes($shift->grace_minutes);

            if ($now->gt($graceEnd)) {
                return CrmAttendanceCode::where('code', 'LA')->first()
                    ?? CrmAttendanceCode::where('code', 'P')->firstOrFail();
            }
        }

        return CrmAttendanceCode::where('code', 'P')->firstOrFail();
    }

    private function calculateOvertime(Carbon $now, $shift, $shiftDay): int
    {
        if (! $shift || ! $shiftDay || ! $shiftDay->is_working_day) {
            return 0;
        }

        $shiftEnd = Carbon::parse($now->toDateString() . ' ' . $shiftDay->end_time);
        $overtimeThreshold = $shiftEnd->copy()->addMinutes($shift->overtime_after_minutes);

        if ($now->gt($overtimeThreshold)) {
            return (int) $overtimeThreshold->diffInMinutes($now);
        }

        return 0;
    }

    private function isEarlyOut(Carbon $now, $shift, $shiftDay): bool
    {
        if (! $shift || ! $shiftDay || ! $shiftDay->is_working_day) {
            return false;
        }

        $shiftEnd = Carbon::parse($now->toDateString() . ' ' . $shiftDay->end_time);
        $earlyOutThreshold = $shiftEnd->copy()->subMinutes($shift->early_out_minutes);

        return $now->lt($earlyOutThreshold);
    }

    private function formatMinutes(?int $minutes): string
    {
        if ($minutes === null || $minutes === 0) {
            return '0m';
        }

        $hours = intdiv($minutes, 60);
        $remainder = $minutes % 60;

        if ($hours === 0) {
            return $remainder . 'm';
        }

        return $hours . 'h ' . $remainder . 'm';
    }
}
