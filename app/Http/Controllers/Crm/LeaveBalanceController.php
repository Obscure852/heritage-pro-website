<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\LeaveBalanceAdjustRequest;
use App\Models\CrmLeaveBalance;
use App\Models\CrmLeaveType;
use App\Models\User;
use App\Services\Crm\LeaveBalanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeaveBalanceController extends CrmController
{
    public function __construct(
        private readonly LeaveBalanceService $balanceService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorizeModuleAccess('leave', 'admin');

        $year = $request->input('year', $this->balanceService->currentLeaveYear());
        $leaveTypes = CrmLeaveType::active()->ordered()->get();

        $usersQuery = User::where('active', true)
            ->with(['leaveBalances' => fn ($q) => $q->where('year', $year)])
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%");
                });
            });

        $users = $usersQuery->paginate(25);

        return view('crm.leave.settings.balance-management', compact('users', 'leaveTypes', 'year'));
    }

    public function adjust(LeaveBalanceAdjustRequest $request, CrmLeaveBalance $balance): RedirectResponse
    {
        $this->authorizeModuleAccess('leave', 'admin');

        $this->balanceService->adjustBalance($balance, (float) $request->validated('adjustment'));

        return redirect()
            ->back()
            ->with('crm_success', 'Balance adjusted successfully.');
    }
}
