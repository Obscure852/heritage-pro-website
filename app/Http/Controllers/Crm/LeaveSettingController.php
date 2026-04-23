<?php

namespace App\Http\Controllers\Crm;

use App\Models\CrmLeaveSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeaveSettingController extends CrmController
{
    public function edit(): View
    {
        $this->authorizeModuleAccess('leave', 'admin');

        $settings = CrmLeaveSetting::instance();

        return view('crm.leave.settings.settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeModuleAccess('leave', 'admin');

        $validated = $request->validate([
            'attendance_integration_enabled' => ['boolean'],
            'auto_mark_attendance_on_approve' => ['boolean'],
            'auto_clear_attendance_on_cancel' => ['boolean'],
            'approval_reminder_hours' => ['integer', 'min:1', 'max:720'],
            'max_escalation_levels' => ['integer', 'min:1', 'max:5'],
            'escalation_after_hours' => ['integer', 'min:1', 'max:720'],
            'allow_retroactive_leave' => ['boolean'],
            'retroactive_limit_days' => ['integer', 'min:1', 'max:90'],
            'balance_year_start_month' => ['integer', 'min:1', 'max:12'],
        ]);

        $booleanFields = [
            'attendance_integration_enabled',
            'auto_mark_attendance_on_approve',
            'auto_clear_attendance_on_cancel',
            'allow_retroactive_leave',
        ];

        foreach ($booleanFields as $field) {
            $validated[$field] = $request->has($field);
        }

        $settings = CrmLeaveSetting::instance();
        $settings->update($validated);

        return redirect()
            ->route('crm.leave.settings')
            ->with('crm_success', 'Leave settings updated.');
    }
}
