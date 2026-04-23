<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\LeaveReviewRequest;
use App\Models\CrmLeaveRequest;
use App\Services\Crm\LeaveApprovalService;
use App\Services\Crm\LeaveAttendanceSyncService;
use App\Services\Crm\LeaveNotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class LeaveApprovalController extends CrmController
{
    public function __construct(
        private readonly LeaveApprovalService $approvalService,
        private readonly LeaveAttendanceSyncService $attendanceSyncService,
        private readonly LeaveNotificationService $notificationService,
    ) {
    }

    public function index(): View
    {
        $this->authorizeModuleAccess('leave', 'edit');

        $user = $this->crmUser();

        $pendingRequests = CrmLeaveRequest::query()
            ->with(['user', 'leaveType'])
            ->forApprover($user->id)
            ->latest('submitted_at')
            ->paginate(20);

        $recentlyReviewed = CrmLeaveRequest::query()
            ->with(['user', 'leaveType'])
            ->where(function ($q) use ($user) {
                $q->where('approved_by', $user->id)
                    ->orWhere('rejected_by', $user->id);
            })
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        return view('crm.leave.approvals', compact('pendingRequests', 'recentlyReviewed'));
    }

    public function review(LeaveReviewRequest $request, CrmLeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorizeModuleAccess('leave', 'edit');
        $user = $this->crmUser();
        $validated = $request->validated();

        if ($validated['action'] === 'approve') {
            $this->approvalService->approve($leaveRequest, $user, $validated['comment'] ?? null);
            $this->notificationService->notifyApproved($leaveRequest->load(['user', 'approver', 'leaveType']));
            $message = 'Leave request approved.';
        } else {
            $this->approvalService->reject($leaveRequest, $user, $validated['reason'], $validated['comment'] ?? null);
            $this->notificationService->notifyRejected($leaveRequest->load(['user', 'rejector', 'leaveType']));
            $message = 'Leave request rejected.';
        }

        return redirect()
            ->route('crm.leave.approvals')
            ->with('crm_success', $message);
    }
}
