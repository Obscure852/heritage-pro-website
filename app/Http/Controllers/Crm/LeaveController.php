<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\LeaveApplyRequest;
use App\Models\CrmLeaveRequest;
use App\Models\CrmLeaveType;
use App\Services\Crm\LeaveApplicationService;
use App\Services\Crm\LeaveBalanceService;
use App\Services\Crm\LeaveNotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeaveController extends CrmController
{
    public function __construct(
        private readonly LeaveApplicationService $applicationService,
        private readonly LeaveBalanceService $balanceService,
        private readonly LeaveNotificationService $notificationService,
    ) {
    }

    public function index(): View
    {
        $user = $this->crmUser();
        $year = $this->balanceService->currentLeaveYear();
        $balances = $this->balanceService->balancesForUser($user, $year);

        $recentRequests = CrmLeaveRequest::query()
            ->with(['leaveType', 'currentApprover', 'approver'])
            ->forUser($user->id)
            ->latest('created_at')
            ->limit(10)
            ->get();

        $pendingCount = CrmLeaveRequest::forApprover($user->id)->count();

        return view('crm.leave.index', compact('balances', 'recentRequests', 'year', 'pendingCount'));
    }

    public function create(): View
    {
        $user = $this->crmUser();
        $leaveTypes = CrmLeaveType::active()->forGender($user->gender)->ordered()->get();
        $year = $this->balanceService->currentLeaveYear();
        $balances = $this->balanceService->balancesForUser($user, $year);

        return view('crm.leave.apply', compact('leaveTypes', 'balances', 'year'));
    }

    public function store(LeaveApplyRequest $request): RedirectResponse
    {
        $leaveRequest = $this->applicationService->apply(
            $this->crmUser(),
            $request->validated()
        );

        $this->notificationService->notifySubmitted($leaveRequest->load(['user', 'leaveType', 'currentApprover']));

        return redirect()
            ->route('crm.leave.show', $leaveRequest)
            ->with('crm_success', 'Leave request submitted successfully.');
    }

    public function show(CrmLeaveRequest $leaveRequest): View
    {
        $user = $this->crmUser();

        abort_unless(
            $leaveRequest->user_id === $user->id
            || $leaveRequest->current_approver_id === $user->id
            || $user->canAccessCrmModule('leave', 'admin'),
            403
        );

        $leaveRequest->load([
            'user', 'leaveType', 'currentApprover', 'approver', 'rejector',
            'attachments', 'approvalTrail.user',
        ]);

        return view('crm.leave.show', compact('leaveRequest'));
    }

    public function cancel(Request $request, CrmLeaveRequest $leaveRequest): RedirectResponse
    {
        $user = $this->crmUser();

        abort_unless(
            $leaveRequest->user_id === $user->id || $user->canAccessCrmModule('leave', 'admin'),
            403
        );

        $this->applicationService->cancel(
            $leaveRequest,
            $user,
            $request->input('reason')
        );

        $this->notificationService->notifyCancelled($leaveRequest->load('user'));

        return redirect()
            ->route('crm.leave.index')
            ->with('crm_success', 'Leave request cancelled.');
    }

    public function history(Request $request): View
    {
        $user = $this->crmUser();

        $requests = CrmLeaveRequest::query()
            ->with(['leaveType', 'approver'])
            ->forUser($user->id)
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->input('type'), fn ($q, $type) => $q->where('leave_type_id', $type))
            ->latest('created_at')
            ->paginate(20);

        $leaveTypes = CrmLeaveType::active()->ordered()->get();

        return view('crm.leave.history', compact('requests', 'leaveTypes'));
    }

    public function balances(): View
    {
        $user = $this->crmUser();
        $year = $this->balanceService->currentLeaveYear();
        $balances = $this->balanceService->balancesForUser($user, $year);

        return view('crm.leave.balances', compact('balances', 'year'));
    }

    public function teamCalendar(Request $request): View
    {
        $this->authorizeModuleAccess('leave', 'edit');

        $user = $this->crmUser();
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $monthStart = \Illuminate\Support\Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();

        // Build team member query
        $teamQuery = \App\Models\User::where('active', true);

        if (! $user->isAdmin() && ! $user->isManager()) {
            $teamQuery->where(function ($q) use ($user) {
                $q->where('reports_to_user_id', $user->id)
                    ->orWhere('id', $user->id);
            });
        }

        $teamMembers = $teamQuery->orderBy('name')->get();
        $teamUserIds = $teamMembers->pluck('id')->toArray();

        // Get leave requests that overlap with this month
        $leaveRequests = CrmLeaveRequest::query()
            ->with(['user', 'leaveType'])
            ->whereIn('status', ['approved', 'pending'])
            ->whereIn('user_id', $teamUserIds)
            ->where('start_date', '<=', $monthEnd->toDateString())
            ->where('end_date', '>=', $monthStart->toDateString())
            ->get();

        return view('crm.leave.team-calendar', compact('leaveRequests', 'teamMembers', 'month', 'year'));
    }

    public function teamBalances(Request $request): View
    {
        $this->authorizeModuleAccess('leave', 'edit');

        $user = $this->crmUser();
        $year = $this->balanceService->currentLeaveYear();
        $leaveTypes = CrmLeaveType::active()->ordered()->get();

        $teamQuery = \App\Models\User::where('active', true)
            ->with(['leaveBalances' => fn ($q) => $q->where('year', $year)]);

        if (! $user->isAdmin() && ! $user->isManager()) {
            $teamQuery->where('reports_to_user_id', $user->id);
        }

        $teamMembers = $teamQuery->get();

        return view('crm.leave.team-balances', compact('teamMembers', 'leaveTypes', 'year'));
    }

    public function calculateDays(Request $request): JsonResponse
    {
        $request->validate([
            'leave_type_id' => 'required|exists:crm_leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_half' => 'in:full,first_half,second_half',
            'end_half' => 'in:full,first_half,second_half',
        ]);

        $totalDays = $this->applicationService->calculateTotalDays(
            $this->crmUser(),
            \Illuminate\Support\Carbon::parse($request->start_date),
            \Illuminate\Support\Carbon::parse($request->end_date),
            $request->input('start_half', 'full'),
            $request->input('end_half', 'full'),
        );

        $leaveType = CrmLeaveType::find($request->leave_type_id);
        $balance = $this->balanceService->getOrCreateBalance($this->crmUser(), $leaveType);

        return response()->json([
            'total_days' => $totalDays,
            'available_days' => $balance->effective_available_days,
            'has_enough' => $leaveType->default_days_per_year === null || $balance->effective_available_days >= $totalDays,
        ]);
    }
}
