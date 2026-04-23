<?php

namespace App\Jobs;

use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceHoliday;
use App\Models\CrmAttendanceRecord;
use App\Models\User;
use App\Services\Crm\AttendanceShiftResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class MarkAbsenteesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue(config('heritage_crm.attendance.queue.queue', 'crm-attendance'));
    }

    public function handle(AttendanceShiftResolver $shiftResolver): int
    {
        $today = now()->copy()->startOfDay();

        $absentCode = CrmAttendanceCode::query()->where('code', 'A')->first();

        if (! $absentCode) {
            return 0;
        }

        $holidays = CrmAttendanceHoliday::query()
            ->where('is_active', true)
            ->where(function ($query) use ($today) {
                $query->whereDate('date', $today)
                    ->orWhere('is_recurring', true);
            })
            ->get();

        $usersWithRecords = CrmAttendanceRecord::query()
            ->whereDate('date', $today)
            ->pluck('user_id')
            ->toArray();

        $users = User::query()
            ->where('active', true)
            ->whereIn('role', array_keys(config('heritage_crm.roles', [])))
            ->whereNotIn('id', $usersWithRecords)
            ->get();

        $marked = 0;

        foreach ($users as $user) {
            if (! $shiftResolver->isWorkingDay($user, $today)) {
                continue;
            }

            $isHoliday = $holidays->contains(function (CrmAttendanceHoliday $holiday) use ($user, $today) {
                if ($holiday->is_recurring) {
                    if ($holiday->date->month !== $today->month || $holiday->date->day !== $today->day) {
                        return false;
                    }
                } else {
                    if ($holiday->date->toDateString() !== $today->toDateString()) {
                        return false;
                    }
                }

                return $holiday->appliesToUser($user);
            });

            if ($isHoliday) {
                continue;
            }

            CrmAttendanceRecord::create([
                'user_id' => $user->id,
                'date' => $today,
                'attendance_code_id' => $absentCode->id,
                'source' => 'system',
                'status' => 'active',
            ]);

            $marked++;
        }

        return $marked;
    }
}
