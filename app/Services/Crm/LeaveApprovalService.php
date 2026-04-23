<?php

namespace App\Services\Crm;

use App\Models\CrmLeaveApprovalTrail;
use App\Models\CrmLeaveRequest;
use App\Models\CrmLeaveSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveApprovalService
{
    public function __construct(
        private readonly LeaveBalanceService $balanceService,
        private readonly LeaveAttendanceSyncService $attendanceSyncService,
    ) {
    }

    public function approve(CrmLeaveRequest $request, User $reviewer, ?string $comment = null): CrmLeaveRequest
    {
        if (! $request->isPending()) {
            throw ValidationException::withMessages([
                'status' => ['This request is no longer pending approval.'],
            ]);
        }

        $this->authorizeReview($request, $reviewer);

        return DB::transaction(function () use ($request, $reviewer, $comment) {
            $request->update([
                'status' => 'approved',
                'approved_by' => $reviewer->id,
                'approved_at' => now(),
                'current_approver_id' => null,
            ]);

            CrmLeaveApprovalTrail::create([
                'leave_request_id' => $request->id,
                'user_id' => $reviewer->id,
                'action' => 'approved',
                'level' => $request->escalation_level,
                'comment' => $comment,
            ]);

            $balance = $this->balanceService->getOrCreateBalance(
                $request->user,
                $request->leaveType,
                $this->balanceService->currentLeaveYear()
            );
            $this->balanceService->confirmUsedDays($balance, (float) $request->total_days);

            $this->attendanceSyncService->syncOnApproval($request);

            return $request->fresh();
        });
    }

    public function reject(CrmLeaveRequest $request, User $reviewer, string $reason, ?string $comment = null): CrmLeaveRequest
    {
        if (! $request->isPending()) {
            throw ValidationException::withMessages([
                'status' => ['This request is no longer pending approval.'],
            ]);
        }

        $this->authorizeReview($request, $reviewer);

        return DB::transaction(function () use ($request, $reviewer, $reason, $comment) {
            $request->update([
                'status' => 'rejected',
                'rejected_by' => $reviewer->id,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
                'current_approver_id' => null,
            ]);

            CrmLeaveApprovalTrail::create([
                'leave_request_id' => $request->id,
                'user_id' => $reviewer->id,
                'action' => 'rejected',
                'level' => $request->escalation_level,
                'comment' => $comment ?? $reason,
            ]);

            $balance = $this->balanceService->getOrCreateBalance(
                $request->user,
                $request->leaveType,
                $this->balanceService->currentLeaveYear()
            );
            $this->balanceService->releasePendingDays($balance, (float) $request->total_days);

            return $request->fresh();
        });
    }

    public function escalateOverdueRequests(): int
    {
        $settings = CrmLeaveSetting::instance();
        $cutoff = now()->subHours($settings->escalation_after_hours);
        $escalated = 0;

        $overdueRequests = CrmLeaveRequest::query()
            ->where('status', 'pending')
            ->where('submitted_at', '<=', $cutoff)
            ->where('escalation_level', '<', $settings->max_escalation_levels)
            ->whereNotNull('current_approver_id')
            ->with(['currentApprover.reportsTo', 'user'])
            ->get();

        foreach ($overdueRequests as $request) {
            $nextApprover = $this->findNextApprover($request);

            if (! $nextApprover) {
                continue;
            }

            DB::transaction(function () use ($request, $nextApprover) {
                $newLevel = $request->escalation_level + 1;

                $request->update([
                    'current_approver_id' => $nextApprover->id,
                    'escalation_level' => $newLevel,
                ]);

                CrmLeaveApprovalTrail::create([
                    'leave_request_id' => $request->id,
                    'user_id' => $request->current_approver_id,
                    'action' => 'escalated',
                    'level' => $newLevel,
                    'comment' => 'Auto-escalated due to no response.',
                ]);
            });

            $escalated++;
        }

        return $escalated;
    }

    public function pendingCountForUser(User $user): int
    {
        return CrmLeaveRequest::forApprover($user->id)->count();
    }

    private function authorizeReview(CrmLeaveRequest $request, User $reviewer): void
    {
        $isAssignedApprover = $request->current_approver_id === $reviewer->id;
        $isAdmin = $reviewer->canAccessCrmModule('leave', 'admin');

        if (! $isAssignedApprover && ! $isAdmin) {
            throw ValidationException::withMessages([
                'approver' => ['You are not authorized to review this request.'],
            ]);
        }
    }

    private function findNextApprover(CrmLeaveRequest $request): ?User
    {
        $currentApprover = $request->currentApprover;

        if ($currentApprover && $currentApprover->reports_to_user_id) {
            $nextApprover = User::where('active', true)->find($currentApprover->reports_to_user_id);

            if ($nextApprover && $nextApprover->id !== $request->user_id) {
                return $nextApprover;
            }
        }

        return User::where('active', true)
            ->where('role', 'admin')
            ->where('id', '!=', $request->user_id)
            ->where('id', '!=', $currentApprover?->id)
            ->first();
    }
}
