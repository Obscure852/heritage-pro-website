<?php

namespace App\Console\Commands;

use App\Models\Leave\LeaveRequest;
use App\Models\Leave\LeaveSetting;
use App\Services\Leave\LeaveNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Artisan command to send reminders to staff for upcoming approved leave (NOTF-05).
 *
 * Scheduled to run daily at 8 AM. Sends email reminders to staff members
 * whose approved leave is starting in X days (configurable via leave_reminder_days_before setting).
 */
class SendLeaveReminders extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to staff for upcoming approved leave';

    /**
     * @var LeaveNotificationService
     */
    protected LeaveNotificationService $notificationService;

    /**
     * Create a new command instance.
     *
     * @param LeaveNotificationService $notificationService
     */
    public function __construct(LeaveNotificationService $notificationService) {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        // Get reminder days setting (default: 3)
        $settingValue = LeaveSetting::get('leave_reminder_days_before', ['days' => 3]);
        $reminderDays = is_array($settingValue) ? ($settingValue['days'] ?? 3) : 3;

        // Calculate target date
        $targetDate = Carbon::today()->addDays($reminderDays);

        $this->info("Looking for approved leave starting on {$targetDate->toDateString()} ({$reminderDays} days from now)...");

        // Find approved requests starting on target date
        $requests = LeaveRequest::approved()
            ->whereDate('start_date', $targetDate)
            ->with(['user', 'leaveType'])
            ->get();

        $count = 0;
        foreach ($requests as $request) {
            $this->notificationService->sendLeaveReminder($request, $reminderDays);
            $this->line("  - Sent reminder to {$request->user->name} for {$request->leaveType->name}");
            $count++;
        }

        $this->info("Sent {$count} leave reminder(s).");

        return Command::SUCCESS;
    }
}
