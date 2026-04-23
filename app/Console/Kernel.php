<?php

namespace App\Console;

use App\Jobs\CloseOvernightRecordsJob;
use App\Jobs\MarkAbsenteesJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    protected function schedule(Schedule $schedule) {
        $schedule->job(new CloseOvernightRecordsJob())->dailyAt('00:05');

        $markAbsentAt = config('heritage_crm.attendance.mark_absent_at', '17:30');
        $schedule->job(new MarkAbsenteesJob())->dailyAt($markAbsentAt);

        // Leave management
        $schedule->command('leave:escalate-overdue')->hourly();
        $schedule->command('leave:approval-reminder')->dailyAt('09:00');
    }

    protected function commands() {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
