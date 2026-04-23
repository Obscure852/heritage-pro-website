<?php

namespace App\Services\Crm;

use App\Models\CrmAttendanceShift;
use App\Models\CrmAttendanceShiftDay;
use App\Models\CrmAttendanceShiftOverride;
use App\Models\User;
use Illuminate\Support\Carbon;

class AttendanceShiftResolver
{
    public function resolveForUserAndDate(User $user, Carbon $date): ?CrmAttendanceShift
    {
        $override = CrmAttendanceShiftOverride::query()
            ->where('user_id', $user->id)
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->latest('id')
            ->first();

        if ($override) {
            $shift = CrmAttendanceShift::query()
                ->with('days')
                ->where('is_active', true)
                ->find($override->shift_id);

            if ($shift) {
                return $shift;
            }
        }

        if ($user->shift_id) {
            $shift = CrmAttendanceShift::query()
                ->with('days')
                ->where('is_active', true)
                ->find($user->shift_id);

            if ($shift) {
                return $shift;
            }
        }

        return CrmAttendanceShift::query()
            ->with('days')
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    public function resolveShiftDay(User $user, Carbon $date): ?CrmAttendanceShiftDay
    {
        $shift = $this->resolveForUserAndDate($user, $date);

        if (! $shift) {
            return null;
        }

        $dayOfWeek = ($date->dayOfWeekIso - 1);

        return $shift->dayForWeekday($dayOfWeek);
    }

    public function isWorkingDay(User $user, Carbon $date): bool
    {
        $shiftDay = $this->resolveShiftDay($user, $date);

        return $shiftDay !== null && $shiftDay->is_working_day;
    }
}
