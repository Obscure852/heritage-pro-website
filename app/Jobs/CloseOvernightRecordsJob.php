<?php

namespace App\Jobs;

use App\Models\CrmAttendanceRecord;
use App\Services\Crm\AttendanceNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class CloseOvernightRecordsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue(config('heritage_crm.attendance.queue.queue', 'crm-attendance'));
    }

    public function handle(): int
    {
        $today = now()->toDateString();
        $notificationService = app(AttendanceNotificationService::class);

        $openRecords = CrmAttendanceRecord::query()
            ->with('user')
            ->whereNotNull('clocked_in_at')
            ->whereNull('clocked_out_at')
            ->where('date', '<', $today)
            ->get();

        $closed = 0;

        foreach ($openRecords as $record) {
            $autoCloseTime = config('heritage_crm.attendance.auto_close_time', '23:59:59');
            $clockOutAt = Carbon::parse($record->date->toDateString() . ' ' . $autoCloseTime);
            $totalMinutes = (int) $record->clocked_in_at->diffInMinutes($clockOutAt);

            $record->update([
                'clocked_out_at' => $clockOutAt,
                'total_minutes' => $totalMinutes,
                'auto_closed' => true,
            ]);

            $notificationService->notifyAutoClose($record);

            $closed++;
        }

        return $closed;
    }
}
