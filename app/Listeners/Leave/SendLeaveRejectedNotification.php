<?php

namespace App\Listeners\Leave;

use App\Events\Leave\LeaveRequestRejected;
use App\Services\Leave\LeaveNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener that sends rejection notification to staff (NOTF-04).
 *
 * Sends email and SMS notification to the staff member when their
 * leave request has been rejected, including the reason for rejection.
 */
class SendLeaveRejectedNotification implements ShouldQueue {
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
     * @param LeaveRequestRejected $event
     * @return void
     */
    public function handle(LeaveRequestRejected $event): void {
        try {
            $this->notificationService->notifyRequestRejected($event->leaveRequest);
        } catch (\Exception $e) {
            Log::error('Failed to send leave rejection notification', [
                'request_id' => $event->leaveRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
