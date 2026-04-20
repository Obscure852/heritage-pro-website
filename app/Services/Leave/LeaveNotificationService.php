<?php

namespace App\Services\Leave;

use App\Helpers\LinkSMSHelper;
use App\Mail\Leave\LeaveReminder;
use App\Mail\Leave\LeaveRequestApproved;
use App\Mail\Leave\LeaveRequestRejected;
use App\Mail\Leave\LeaveRequestSubmitted;
use App\Mail\Leave\PendingApprovalReminder;
use App\Models\Leave\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Service for sending leave-related notifications via email and SMS.
 *
 * Handles all notification requirements (NOTF-01 through NOTF-06):
 * - NOTF-01: Staff notification on request submission
 * - NOTF-02: Manager notification of new pending request
 * - NOTF-03: Staff notification on approval
 * - NOTF-04: Staff notification on rejection
 * - NOTF-05: Staff reminder before leave starts
 * - NOTF-06: Manager reminder for pending requests
 */
class LeaveNotificationService {
    /** @var LinkSMSHelper */
    protected LinkSMSHelper $smsHelper;

    /**
     * Create a new LeaveNotificationService instance.
     *
     * @param LinkSMSHelper $smsHelper
     */
    public function __construct(LinkSMSHelper $smsHelper) {
        $this->smsHelper = $smsHelper;
    }

    // ==================== SUBMISSION NOTIFICATIONS ====================

    /**
     * Notify staff that their request was submitted (NOTF-01).
     *
     * Sends email confirmation to the staff member who submitted the request.
     *
     * @param LeaveRequest $request
     * @return void
     */
    public function notifyRequestSubmitted(LeaveRequest $request): void {
        $user = $request->user;

        if (!$user || empty($user->email)) {
            Log::warning('Cannot send submission notification: user or email missing', [
                'request_id' => $request->id,
            ]);
            return;
        }

        // Send email
        $this->sendEmail($user->email, new LeaveRequestSubmitted($request));

        // Send SMS
        $smsText = $this->getSubmissionSmsText($request);
        $this->sendSms($user, $smsText);

        Log::info('Leave submission notification sent', [
            'request_id' => $request->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Notify manager of new pending request (NOTF-02).
     *
     * Sends SMS notification to the manager about a new pending request.
     *
     * @param LeaveRequest $request
     * @param User $manager
     * @return void
     */
    public function notifyManagerNewRequest(LeaveRequest $request, User $manager): void {
        if (!$manager) {
            Log::warning('Cannot send manager notification: manager not provided', [
                'request_id' => $request->id,
            ]);
            return;
        }

        // Send SMS to manager
        $smsText = $this->getNewRequestSmsText($request);
        $this->sendSms($manager, $smsText);

        Log::info('Manager new request notification sent', [
            'request_id' => $request->id,
            'manager_id' => $manager->id,
        ]);
    }

    // ==================== APPROVAL/REJECTION NOTIFICATIONS ====================

    /**
     * Notify staff that their request was approved (NOTF-03).
     *
     * Sends email and SMS to the staff member when their request is approved.
     *
     * @param LeaveRequest $request
     * @return void
     */
    public function notifyRequestApproved(LeaveRequest $request): void {
        $user = $request->user;

        if (!$user || empty($user->email)) {
            Log::warning('Cannot send approval notification: user or email missing', [
                'request_id' => $request->id,
            ]);
            return;
        }

        // Send email
        $this->sendEmail($user->email, new LeaveRequestApproved($request));

        // Send SMS
        $smsText = $this->getApprovalSmsText($request);
        $this->sendSms($user, $smsText);

        Log::info('Leave approval notification sent', [
            'request_id' => $request->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Notify staff that their request was rejected (NOTF-04).
     *
     * Sends email and SMS to the staff member when their request is rejected.
     *
     * @param LeaveRequest $request
     * @return void
     */
    public function notifyRequestRejected(LeaveRequest $request): void {
        $user = $request->user;

        if (!$user || empty($user->email)) {
            Log::warning('Cannot send rejection notification: user or email missing', [
                'request_id' => $request->id,
            ]);
            return;
        }

        // Send email
        $this->sendEmail($user->email, new LeaveRequestRejected($request));

        // Send SMS
        $smsText = $this->getRejectionSmsText($request);
        $this->sendSms($user, $smsText);

        Log::info('Leave rejection notification sent', [
            'request_id' => $request->id,
            'user_id' => $user->id,
        ]);
    }

    // ==================== REMINDER NOTIFICATIONS ====================

    /**
     * Send staff reminder before leave starts (NOTF-05).
     *
     * Sends email reminder to staff about their upcoming approved leave.
     *
     * @param LeaveRequest $request
     * @param int $daysUntilStart
     * @return void
     */
    public function sendLeaveReminder(LeaveRequest $request, int $daysUntilStart): void {
        $user = $request->user;

        if (!$user || empty($user->email)) {
            Log::warning('Cannot send leave reminder: user or email missing', [
                'request_id' => $request->id,
            ]);
            return;
        }

        // Send email
        $this->sendEmail($user->email, new LeaveReminder($request, $daysUntilStart));

        Log::info('Leave reminder notification sent', [
            'request_id' => $request->id,
            'user_id' => $user->id,
            'days_until_start' => $daysUntilStart,
        ]);
    }

    /**
     * Send manager reminder for pending requests (NOTF-06).
     *
     * Sends email reminder to manager about pending leave requests.
     *
     * @param User $manager
     * @param Collection $pendingRequests
     * @return void
     */
    public function sendPendingApprovalReminder(User $manager, Collection $pendingRequests): void {
        if (!$manager || empty($manager->email)) {
            Log::warning('Cannot send pending approval reminder: manager or email missing');
            return;
        }

        if ($pendingRequests->isEmpty()) {
            Log::info('No pending requests to remind about', [
                'manager_id' => $manager->id,
            ]);
            return;
        }

        // Send email
        $this->sendEmail($manager->email, new PendingApprovalReminder($manager, $pendingRequests));

        Log::info('Pending approval reminder notification sent', [
            'manager_id' => $manager->id,
            'pending_count' => $pendingRequests->count(),
        ]);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Send email notification (queued).
     *
     * @param string $email
     * @param mixed $mailable
     * @return void
     */
    protected function sendEmail(string $email, $mailable): void {
        try {
            Mail::to($email)->queue($mailable);
            Log::info('Leave notification email queued', [
                'to' => $email,
                'type' => get_class($mailable),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue leave notification email', [
                'to' => $email,
                'type' => get_class($mailable),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send SMS notification.
     *
     * @param User $user
     * @param string $message
     * @return void
     */
    protected function sendSms(User $user, string $message): void {
        if (empty($user->phone) || !$user->hasValidPhoneNumber()) {
            Log::debug('Skipping SMS: invalid or missing phone number', [
                'user_id' => $user->id,
            ]);
            return;
        }

        try {
            $this->smsHelper->sendMessage(
                $message,
                $user->phone,
                $user->id,
                'user',
                'Leave Notification',
                1
            );
            Log::info('Leave notification SMS sent', [
                'to' => $user->phone,
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send leave notification SMS', [
                'to' => $user->phone,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get SMS text for request submission (NOTF-01).
     *
     * @param LeaveRequest $request
     * @return string
     */
    protected function getSubmissionSmsText(LeaveRequest $request): string {
        $leaveType = $request->leaveType->name ?? 'Leave';
        $days = $request->total_days;
        $startDate = $request->start_date->format('d M');

        return "Your {$leaveType} request ({$days} days from {$startDate}) has been submitted and is pending approval.";
    }

    /**
     * Get SMS text for new pending request (to manager) (NOTF-02).
     *
     * @param LeaveRequest $request
     * @return string
     */
    protected function getNewRequestSmsText(LeaveRequest $request): string {
        $staffName = $request->user->name ?? 'A staff member';
        $leaveType = $request->leaveType->name ?? 'leave';
        $days = $request->total_days;

        return "{$staffName} has requested {$days} day(s) of {$leaveType}. Please review in the leave management system.";
    }

    /**
     * Get SMS text for approval (NOTF-03).
     *
     * @param LeaveRequest $request
     * @return string
     */
    protected function getApprovalSmsText(LeaveRequest $request): string {
        $leaveType = $request->leaveType->name ?? 'Leave';
        $startDate = $request->start_date->format('d M');
        $endDate = $request->end_date->format('d M');

        return "Your {$leaveType} request ({$startDate} to {$endDate}) has been approved.";
    }

    /**
     * Get SMS text for rejection (NOTF-04).
     *
     * @param LeaveRequest $request
     * @return string
     */
    protected function getRejectionSmsText(LeaveRequest $request): string {
        $leaveType = $request->leaveType->name ?? 'Leave';
        $startDate = $request->start_date->format('d M');

        return "Your {$leaveType} request starting {$startDate} has been rejected. Please check your email for details.";
    }
}
