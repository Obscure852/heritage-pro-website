<?php

namespace App\Http\Controllers\StaffAttendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffAttendance\UpdateStaffAttendanceSettingsRequest;
use App\Models\StaffAttendance\AttendanceDevice;
use App\Models\StaffAttendance\StaffAttendanceCode;
use App\Models\StaffAttendance\StaffAttendanceSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

/**
 * Controller for staff attendance module settings management.
 */
class StaffAttendanceSettingsController extends Controller {
    /**
     * Display the staff attendance settings page.
     *
     * @return View
     */
    public function index(): View {
        // Load all settings as key-value pairs
        $settings = StaffAttendanceSetting::all()->pluck('value', 'key')->toArray();

        // Get active devices for the device sync tab
        $devices = AttendanceDevice::active()->get();

        // Get attendance codes for the codes tab
        $codes = StaffAttendanceCode::orderBy('order')->get();
        $codeStats = [
            'total' => $codes->count(),
            'active' => $codes->where('is_active', true)->count(),
            'present_codes' => $codes->where('counts_as_present', true)->count(),
        ];

        return view('staff-attendance.settings.index', [
            'settings' => $settings,
            'devices' => $devices,
            'codes' => $codes,
            'codeStats' => $codeStats,
        ]);
    }

    /**
     * Update staff attendance settings.
     *
     * @param UpdateStaffAttendanceSettingsRequest $request
     * @return JsonResponse
     */
    public function update(UpdateStaffAttendanceSettingsRequest $request): JsonResponse {
        $validated = $request->validated();
        $userId = auth()->id();

        try {
            // General settings - Working hours
            if (isset($validated['work_start_time'])) {
                StaffAttendanceSetting::set('work_start_time', ['time' => $validated['work_start_time']], $userId);
            }

            if (isset($validated['work_end_time'])) {
                StaffAttendanceSetting::set('work_end_time', ['time' => $validated['work_end_time']], $userId);
            }

            if (isset($validated['grace_period_minutes'])) {
                StaffAttendanceSetting::set('grace_period_minutes', ['minutes' => (int) $validated['grace_period_minutes']], $userId);
            }

            // Hour thresholds
            if (isset($validated['half_day_hours'])) {
                StaffAttendanceSetting::set('half_day_hours', ['hours' => (float) $validated['half_day_hours']], $userId);
            }

            if (isset($validated['full_day_hours'])) {
                StaffAttendanceSetting::set('full_day_hours', ['hours' => (float) $validated['full_day_hours']], $userId);
            }

            if (isset($validated['overtime_threshold_hours'])) {
                StaffAttendanceSetting::set('overtime_threshold_hours', ['hours' => (float) $validated['overtime_threshold_hours']], $userId);
            }

            // Self-service settings
            if (array_key_exists('self_clock_in_enabled', $validated)) {
                StaffAttendanceSetting::set('self_clock_in_enabled', ['enabled' => (bool) $validated['self_clock_in_enabled']], $userId);
            }

            // Manual attendance settings
            if (array_key_exists('manual_attendance_enabled', $validated)) {
                StaffAttendanceSetting::set('manual_attendance_enabled', ['enabled' => (bool) $validated['manual_attendance_enabled']], $userId);
            }

            return response()->json([
                'success' => true,
                'message' => 'Staff attendance settings saved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger a manual sync for a specific device or all devices.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function triggerSync(Request $request): JsonResponse {
        $validated = $request->validate([
            'device_id' => ['nullable', 'integer', 'exists:attendance_devices,id'],
        ]);

        try {
            $params = ['--hours' => 24];

            if (!empty($validated['device_id'])) {
                $params['--device'] = $validated['device_id'];
            }

            $exitCode = Artisan::call('attendance:sync-biometric', $params);
            $output = Artisan::output();

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sync completed successfully.',
                    'output' => $output,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Sync completed with errors.',
                'output' => $output,
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during sync: ' . $e->getMessage(),
            ], 500);
        }
    }
}
