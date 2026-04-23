<?php

namespace App\Jobs;

use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceHoliday;
use App\Models\CrmAttendanceRecord;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncHolidayAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly CrmAttendanceHoliday $holiday
    ) {
        $this->onQueue(config('heritage_crm.attendance.queue.queue', 'crm-attendance'));
    }

    public function handle(): void
    {
        if (! $this->holiday->is_active) {
            return;
        }

        $holidayCode = CrmAttendanceCode::query()->where('code', 'H')->first();
        $absentCode = CrmAttendanceCode::query()->where('code', 'A')->first();

        if (! $holidayCode) {
            return;
        }

        $users = User::query()
            ->where('active', true)
            ->whereIn('role', array_keys(config('heritage_crm.roles', [])))
            ->get();

        $date = $this->holiday->date;

        foreach ($users as $user) {
            if (! $this->holiday->appliesToUser($user)) {
                continue;
            }

            $existing = CrmAttendanceRecord::query()
                ->where('user_id', $user->id)
                ->whereDate('date', $date)
                ->first();

            if (! $existing) {
                CrmAttendanceRecord::create([
                    'user_id' => $user->id,
                    'date' => $date,
                    'attendance_code_id' => $holidayCode->id,
                    'source' => 'system',
                    'status' => 'active',
                ]);
                continue;
            }

            if ($absentCode && (int) $existing->attendance_code_id === (int) $absentCode->id) {
                $existing->update(['attendance_code_id' => $holidayCode->id]);
            }
        }
    }
}
