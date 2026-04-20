<?php

namespace App\Listeners\Leave;

use App\Events\Leave\LeaveRequestSubmitted;
use App\Services\Leave\LeaveNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener that sends submission confirmation to staff (NOTF-01).
 *
 * Sends email and SMS notification to the staff member who submitted
 * the leave request, confirming receipt of their request.
 */
class SendLeaveSubmittedNotification implements ShouldQueue {
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
     * @param LeaveRequestSubmitted $event
     * @return void
     */
    public function handle(LeaveRequestSubmitted $event): void {
        try {
            $this->notificationService->notifyRequestSubmitted($event->leaveRequest);
        } catch (\Exception $e) {
            Log::error('Failed to send leave submission notification', [
                'request_id' => $event->leaveRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
