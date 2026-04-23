<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\LeaveTypeUpsertRequest;
use App\Models\CrmLeaveType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class LeaveTypeController extends CrmController
{
    public function index(): View
    {
        $this->authorizeModuleAccess('leave', 'admin');

        $leaveTypes = CrmLeaveType::ordered()->get();

        return view('crm.leave.settings.types-index', compact('leaveTypes'));
    }

    public function create(): View
    {
        $this->authorizeModuleAccess('leave', 'admin');

        return view('crm.leave.settings.types-form', ['leaveType' => null]);
    }

    public function store(LeaveTypeUpsertRequest $request): RedirectResponse
    {
        $this->authorizeModuleAccess('leave', 'admin');

        CrmLeaveType::create($request->validated());

        return redirect()
            ->route('crm.leave.types.index')
            ->with('crm_success', 'Leave type created.');
    }

    public function edit(CrmLeaveType $leaveType): View
    {
        $this->authorizeModuleAccess('leave', 'admin');

        return view('crm.leave.settings.types-form', compact('leaveType'));
    }

    public function update(LeaveTypeUpsertRequest $request, CrmLeaveType $leaveType): RedirectResponse
    {
        $this->authorizeModuleAccess('leave', 'admin');

        $leaveType->update($request->validated());

        return redirect()
            ->route('crm.leave.types.index')
            ->with('crm_success', 'Leave type updated.');
    }

    public function destroy(CrmLeaveType $leaveType): RedirectResponse
    {
        $this->authorizeModuleAccess('leave', 'admin');

        $inUse = $leaveType->requests()->exists() || $leaveType->balances()->exists();

        if ($inUse) {
            return redirect()
                ->route('crm.leave.types.index')
                ->with('crm_error', 'Cannot delete a leave type that is already in use. Deactivate it instead.');
        }

        $leaveType->delete();

        return redirect()
            ->route('crm.leave.types.index')
            ->with('crm_success', 'Leave type deleted.');
    }
}
