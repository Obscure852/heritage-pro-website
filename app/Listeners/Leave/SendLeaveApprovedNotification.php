<?php

namespace App\Listeners\Leave;

use App\Events\Leave\LeaveRequestApproved;
use App\Services\Leave\LeaveNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener that sends approval notification to staff (NOTF-03).
 *
 * Sends email and SMS notification to the staff member when their
 * leave request has been approved.
 */
class SendLeaveApprovedNotification implements ShouldQueue {
    /**
     * @var LeaveNotificationService
     */
    protected LeaveNotificationService $notificationService;

    /**
     * Create the listener.
     *
     * @param LeaveNotificationService $notificationService
     */
    public function __construct(LeaveNotificationService $notificationService) {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     *
     * @param LeaveRequestApproved $event
     * @return void
     */
    public function handle(LeaveRequestApproved $event): void {
        try {
            $this->notificationService->notifyRequestApproved($event->leaveRequest);
        } catch (\Exception $e) {
            Log::error('Failed to send leave approval notification', [
                'request_id' => $event->leaveRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
