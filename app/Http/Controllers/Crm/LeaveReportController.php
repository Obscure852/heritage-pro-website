<?php

namespace App\Http\Controllers\Crm;

use App\Models\CrmLeaveRequest;
use App\Models\CrmLeaveType;
use App\Models\CrmUserDepartment;
use App\Services\Crm\LeaveBalanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LeaveReportController extends CrmController
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
        $departments = CrmUserDepartment::where('is_active', true)->orderBy('name')->get();

        $query = CrmLeaveRequest::query()
            ->with(['user', 'leaveType'])
            ->where('status', 'approved')
            ->whereYear('start_date', $year);

        if ($request->input('department_id')) {
            $query->whereHas('user', fn ($q) => $q->where('department_id', $request->department_id));
        }

        if ($request->input('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        $approvedRequests = $query->latest('start_date')->paginate(25);

        $summary = CrmLeaveRequest::query()
            ->selectRaw('leave_type_id, COUNT(*) as total_requests, SUM(total_days) as total_days_taken')
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->groupBy('leave_type_id')
            ->get()
            ->keyBy('leave_type_id');

        return view('crm.leave.reports', compact(
            'approvedRequests', 'leaveTypes', 'departments', 'year', 'summary'
        ));
    }
}
