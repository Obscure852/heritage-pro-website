<?php

namespace App\Listeners\Leave;

use App\Events\Leave\LeaveRequestSubmitted;
use App\Services\Leave\LeaveApprovalService;
use App\Services\Leave\LeaveNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener that notifies manager of new pending request (NOTF-02).
 *
 * Sends SMS notification to the designated approver (manager)
 * when a new leave request is submitted by one of their direct reports.
 */
class SendManagerNewRequestNotification implements ShouldQueue {
    /**
     * @var LeaveNotificationService
     */
    protected LeaveNotificationService $notificationService;

    /**
     * @var LeaveApprovalService
     */
    protected LeaveApprovalService $approvalService;

    /**
     * Create the listener.
     *
     * @param LeaveNotificationService $notificationService
     * @param LeaveApprovalService $approvalService
     */
    public function __construct(
        LeaveNotificationService $notificationService,
        LeaveApprovalService $approvalService
    ) {
        $this->notificationService = $notificationService;
        $this->approvalService = $approvalService;
    }

    /**
     * Handle the event.
     *
     * @param LeaveRequestSubmitted $event
     * @return void
     */
    public function handle(LeaveRequestSubmitted $event): void {
        try {
            $request = $event->leaveRequest;
            $user = $request->user;

            if (!$user) {
                Log::warning('Cannot send manager notification: user not found', [
                    'request_id' => $request->id,
                ]);
                return;
            }

            // Get the designated approver for this staff member
            $manager = $this->approvalService->getApprover($user);

            if (!$manager) {
                Log::warning('Cannot send manager notification: no approver found', [
                    'request_id' => $request->id,
                    'user_id' => $user->id,
                ]);
                return;
            }

            $this->notificationService->notifyManagerNewRequest($request, $manager);
        } catch (\Exception $e) {
            Log::error('Failed to send manager new request notification', [
                'request_id' => $event->leaveRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
