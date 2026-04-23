<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\AttendanceCodeUpsertRequest;
use App\Http\Requests\Crm\AttendanceHolidayUpsertRequest;
use App\Http\Requests\Crm\AttendanceShiftUpsertRequest;
use App\Jobs\SyncHolidayAttendanceJob;
use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceDevice;
use App\Models\CrmAttendanceSetting;
use App\Models\CrmAttendanceHoliday;
use App\Models\CrmAttendanceShift;
use App\Models\CrmAttendanceShiftDay;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceSettingController extends CrmController
{
    public function index(Request $request): View
    {
        $this->authorizeAdminSettings();

        $codes = CrmAttendanceCode::query()->orderBy('sort_order')->orderBy('code')->get();

        $shifts = CrmAttendanceShift::query()
            ->with('days')
            ->withCount('users')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $holidays = CrmAttendanceHoliday::query()->orderBy('date')->get();

        $devices = CrmAttendanceDevice::query()
            ->withCount('logs')
            ->orderBy('name')
            ->get();

        $departments = $this->crmDepartmentsForSelect();
        $activeShifts = CrmAttendanceShift::query()->where('is_active', true)->orderBy('name')->get();
        $activeTab = $request->query('tab', 'codes');
        $attendanceSettings = CrmAttendanceSetting::resolve();

        return view('crm.settings.attendance', compact(
            'codes',
            'shifts',
            'holidays',
            'devices',
            'departments',
            'activeShifts',
            'activeTab',
            'attendanceSettings'
        ));
    }

    // ── Codes ───────────────────────────────────────────────

    public function storeCode(AttendanceCodeUpsertRequest $request): RedirectResponse
    {
        $this->authorizeAdminSettings();

        CrmAttendanceCode::create([
            'code' => $request->validated('code'),
            'label' => $request->validated('label'),
            'color' => $request->validated('color'),
            'category' => $request->validated('category'),
            'counts_as_working' => $request->validated('counts_as_working'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->validated('sort_order') ?? 0,
        ]);

        return redirect()
            ->route('crm.settings.attendance.index', ['tab' => 'codes'])
            ->with('crm_success', 'Attendance code created.');
    }

    public function updateCode(AttendanceCodeUpsertRequest $request, CrmAttendanceCode $attendanceCode): RedirectResponse
    {
        $this->authorizeAdminSettings();

        $attendanceCode->update([
            'code' => $request->validated('code'),
            'label' => $request->validated('label'),
            'color' => $request->validated('color'),
            'category' => $request->validated('category'),
            'counts_as_working' => $request->validated('counts_as_working'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->validated('sort_order') ?? $attendanceCode->sort_order,
        ]);

        return redirect()
            ->route('crm.settings.attendance.index', ['tab' => 'codes'])
            ->with('crm_success', 'Attendance code updated.');
    }

    public function destroyCode(CrmAttendanceCode $attendanceCode): RedirectResponse
    {
        $this->authorizeAdminSettings();

        abort_if($attendanceCode->is_system, 422, 'System codes cannot be deleted.');

        $attendanceCode->update(['is_active' => false]);

        return redirect()
            ->route('crm.settings.attendance.index', ['tab' => 'codes'])
            ->with('crm_success', 'Attendance code deactivated.');
    }

    // ── Shifts ──────────────────────────────────────────────

    public function storeShift(AttendanceShiftUpsertRequest $request): RedirectResponse
    {
        $this->authorizeAdminSettings();

        DB::transaction(function () use ($request) {
            $isDefault = $request->boolean('is_default');

            if ($isDefault) {
                CrmAttendanceShift::query()->where('is_default', true)->update(['is_default' => false]);
            }

            $shift = CrmAttendanceShift::create([
                'name' => $request->validated('name'),
                'is_default' => $isDefault,
                'grace_minutes' => $request->validated('grace_minutes'),
                'early_out_minutes' => $request->validated('early_out_minutes'),
                'overtime_after_minutes' => $request->validated('overtime_after_minutes'),
                'earliest_clock_in' => $request->validated('earliest_clock_in'),
                'latest_clock_in' => $request->validated('latest_clock_in'),
                'is_active' => $request->boolean('is_active'),
            ]);

            $this->syncShiftDays($shift, $request->validated('days'));
        });

        return redirect()
            ->route('crm.settings.attendance.index', ['tab' => 'shifts'])
            ->with('crm_success', 'Shift created.');
    }

    public function updateShift(AttendanceShiftUpsertRequest $request, CrmAttendanceShift $shift): RedirectResponse
    {
        $this->authorizeAdminSettings();

        DB::transaction(function () use ($request, $shift) {
            $isDefault = $request->boolean('is_default');

            if ($isDefault && ! $shift->is_default) {
                CrmAttendanceShift::query()->where('is_default', true)->update(['is_default' => false]);
            }

            $shift->update([
                'name' => $request->validated('name'),
                'is_default' => $isDefault,
                'grace_minutes' => $request->validated('grace_minutes'),
                'early_out_minutes' => $request->validated('early_out_minutes'),
                'overtime_after_minutes' => $request->validated('overtime_after_minutes'),
                'earliest_clock_in' => $request->validated('earliest_clock_in'),
                'latest_clock_in' => $request->validated('latest_clock_in'),
                'is_active' => $request->boolean('is_active'),
            ]);

            $this->syncShiftDays($shift, $request->validated('days'));
        });

        return redirect()
            ->route('crm.settings.attendance.index', ['tab' => 'shifts'])
            ->with('crm_success', 'Shift updated.');
    }

    public function destroyShift(CrmAttendanceShift $shift): RedirectResponse
    {
        $this->authorizeAdminSettings();

        abort_if($shift->is_default, 422, 'The default shift cannot be deactivated.');

        $shift->update(['is_active' => false]);

        return redirect()
            ->route('crm.settings.attendance.index', ['tab' => 'shifts'])
            ->with('crm_success', 'Shift deactivated.');
    }

    public function bulkAssignShift(Request $request): RedirectResponse
    {
        $this->authorizeAdminSettings();

        $validated = $request->validate([
            'shift_id' => ['required', 'exists:crm_attendance_shifts,id'],
            'department_id' => ['required', 'exists:crm_user_departments,id'],
        ]);

        User::query()
            ->where('department_id', $validated['department_id'])
            ->where('active', true)
            ->update(['shift_id' => $validated['shift_id']]);

        return redirect()
            ->route('crm.settings.attendance.index', ['tab' => 'shifts'])
            ->with('crm_success', 'Shift assigned to department.');
    }

    // ── Holidays ────────────────────────────────────────────

    public function storeHoliday(AttendanceHolidayUpsertRequest $request): RedirectResponse
    {
        $this->authorizeAdminSettings();

        $holiday = CrmAttendanceHoliday::create([
            'name' => $request->validated('name'),
            'date' => $request->validated('date'),
            'is_recurring' => $request->boolean('is_recurring'),
            'applies_to' => $request->validated('applies_to'),
            'scope_id' => $request->validated('scope_id'),
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $this->crmUser()->id,
        ]);

        SyncHolidayAttendanceJob::dispatch($holiday);

        return redirect()
            ->route('crm.settings.attendance.index', ['tab' => 'holidays'])
            ->with('crm_success', 'Holiday created.');
    }

    public function updateHoliday(AttendanceHolidayUpsertRequest $request, CrmAttendanceHoliday $holiday): RedirectResponse
    {
        $this->authorizeAdminSettings();

        $holiday->update([
            'name' => $request->validated('name'),
            'date' => $request->validated('date'),
            'is_recurring' => $request->boolean('is_recurring'),
            'applies_to' => $request->validated('applies_to'),
            'scope_id' => $request->validated('scope_id'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        SyncHolidayAttendanceJob::dispatch($holiday);

        return redirect()
            ->route('crm.settings.attendance.index', ['tab' => 'holidays'])
            ->with('crm_success', 'Holiday updated.');
    }

    public function destroyHoliday(CrmAttendanceHoliday $holiday): RedirectResponse
    {
        $this->authorizeAdminSettings();

        $holiday->delete();

        return redirect()
            ->route('crm.settings.attendance.index', ['tab' => 'holidays'])
            ->with('crm_success', 'Holiday deleted.');
    }

    // ── Devices ──────────────────────────────────────────────

    public function storeDevice(Request $request): RedirectResponse
    {
        $this->authorizeAdminSettings();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'brand' => ['required', 'string', 'max:30'],
            'model' => ['nullable', 'string', 'max:80'],
            'device_identifier' => ['required', 'string', 'max:50', 'unique:crm_attendance_devices,device_identifier'],
            'serial_number' => ['nullable', 'string', 'max:80'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'communication_key' => ['nullable', 'string', 'max:100'],
            'direction' => ['required', 'string', 'in:both,in,out'],
            'timezone' => ['nullable', 'string', 'max:40'],
            'heartbeat_interval' => ['nullable', 'integer', 'min:10', 'max:3600'],
            'push_interval' => ['nullable', 'integer', 'min:5', 'max:3600'],
            'location' => ['nullable', 'string', 'max:200'],
            'min_confidence' => ['required', 'numeric', 'min:0', 'max:1'],
            'is_active' => ['nullable'],
        ]);

        $brandConfig = config('heritage_crm.attendance.device_brands.' . $validated['brand'], []);

        $device = CrmAttendanceDevice::create([
            'name' => $validated['name'],
            'brand' => $validated['brand'],
            'model' => $validated['model'],
            'device_identifier' => $validated['device_identifier'],
            'serial_number' => $validated['serial_number'] ?? null,
            'ip_address' => $validated['ip_address'] ?? null,
            'port' => $validated['port'] ?? ($brandConfig['default_port'] ?? 80),
            'communication_key' => $validated['communication_key'] ?? null,
            'protocol' => $brandConfig['protocol'] ?? 'push',
            'direction' => $validated['direction'],
            'timezone' => $validated['timezone'] ?? null,
            'heartbeat_interval' => $validated['heartbeat_interval'] ?? 60,
            'push_interval' => $validated['push_interval'] ?? 30,
            'location' => $validated['location'] ?? null,
            'min_confidence' => $validated['min_confidence'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        $plainToken = $this->generateDeviceToken($device);

        $pushUrl = $device->isZkteco()
            ? url('/api/crm/attendance/iclock/cdata')
            : url('/api/crm/attendance/biometric-event');

        return redirect()
            ->route('crm.settings.attendance.index', ['tab' => 'devices'])
            ->with('crm_success', 'Device registered. Push URL: ' . $pushUrl . ' | API token: ' . $plainToken . ' (copy now — shown once).');
    }

    public function updateDevice(Request $request, CrmAttendanceDevice $device): RedirectResponse
    {
        $this->authorizeAdminSettings();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:80'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'communication_key' => ['nullable', 'string', 'max:100'],
            'direction' => ['required', 'string', 'in:both,in,out'],
            'timezone' => ['nullable', 'string', 'max:40'],
            'heartbeat_interval' => ['nullable', 'integer', 'min:10', 'max:3600'],
            'push_interval' => ['nullable', 'integer', 'min:5', 'max:3600'],
            'location' => ['nullable', 'string', 'max:200'],
            'min_confidence' => ['required', 'numeric', 'min:0', 'max:1'],
            'is_active' => ['nullable'],
        ]);

        $device->update([
            'name' => $validated['name'],
            'model' => $validated['model'] ?? $device->model,
            'ip_address' => $validated['ip_address'] ?? $device->ip_address,
            'port' => $validated['port'] ?? $device->port,
            'communication_key' => $validated['communication_key'] ?? $device->communication_key,
            'direction' => $validated['direction'],
            'timezone' => $validated['timezone'] ?? $device->timezone,
            'heartbeat_interval' => $validated['heartbeat_interval'] ?? $device->heartbeat_interval,
            'push_interval' => $validated['push_interval'] ?? $device->push_interval,
            'location' => $validated['location'] ?? $device->location,
            'min_confidence' => $validated['min_confidence'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('crm.settings.attendance.index', ['tab' => 'devices'])
            ->with('crm_success', 'Device updated.');
    }

    public function destroyDevice(CrmAttendanceDevice $device): RedirectResponse
    {
        $this->authorizeAdminSettings();

        if ($device->api_token_id) {
            \Laravel\Sanctum\PersonalAccessToken::find($device->api_token_id)?->delete();
        }

        $device->logs()->delete();
        $device->delete();

        return redirect()
            ->route('crm.settings.attendance.index', ['tab' => 'devices'])
            ->with('crm_success', 'Device "' . $device->name . '" has been permanently deleted.');
    }

    public function updateWidgetSettings(Request $request): RedirectResponse
    {
        $this->authorizeAdminSettings();

        $settings = CrmAttendanceSetting::resolve();

        $settings->update([
            'show_topbar_clock' => $request->boolean('show_topbar_clock'),
            'show_dashboard_clock' => $request->boolean('show_dashboard_clock'),
        ]);

        return redirect()
            ->route('crm.settings.attendance.index', ['tab' => 'codes'])
            ->with('crm_success', 'Clock widget visibility updated.');
    }

    public function regenerateDeviceToken(CrmAttendanceDevice $device): RedirectResponse
    {
        $this->authorizeAdminSettings();

        if ($device->api_token_id) {
            \Laravel\Sanctum\PersonalAccessToken::find($device->api_token_id)?->delete();
        }

        $plainToken = $this->generateDeviceToken($device);

        return redirect()
            ->route('crm.settings.attendance.index', ['tab' => 'devices'])
            ->with('crm_success', 'Token regenerated: ' . $plainToken . ' (copy now — it will not be shown again).');
    }

    private function generateDeviceToken(CrmAttendanceDevice $device): string
    {
        $tokenOwner = User::query()->where('role', 'admin')->where('active', true)->first();

        abort_unless($tokenOwner, 500, 'No active admin user found to create device token.');

        $token = $tokenOwner->createToken(
            'biometric-device-' . $device->device_identifier,
            ['attendance:biometric-push']
        );

        $device->update(['api_token_id' => $token->accessToken->id]);

        return $token->plainTextToken;
    }

    // ── Helpers ─────────────────────────────────────────────

    private function syncShiftDays(CrmAttendanceShift $shift, array $days): void
    {
        $shift->days()->delete();

        foreach ($days as $dayOfWeek => $dayData) {
            CrmAttendanceShiftDay::create([
                'shift_id' => $shift->id,
                'day_of_week' => (int) $dayOfWeek,
                'start_time' => $dayData['start_time'] . ':00',
                'end_time' => $dayData['end_time'] . ':00',
                'is_working_day' => ! empty($dayData['is_working_day']),
            ]);
        }
    }
}
