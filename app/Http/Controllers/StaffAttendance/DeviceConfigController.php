<?php

namespace App\Http\Controllers\StaffAttendance;

use App\Contracts\BiometricDeviceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StaffAttendance\StoreDeviceRequest;
use App\Http\Requests\StaffAttendance\UpdateDeviceRequest;
use App\Models\StaffAttendance\AttendanceDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller for managing attendance device configuration.
 *
 * Provides CRUD operations for biometric devices and connection testing.
 */
class DeviceConfigController extends Controller
{
    /**
     * Display a listing of configured devices.
     *
     * @return View
     */
    public function index(): View
    {
        // Get all devices with sync stats
        $devices = AttendanceDevice::withCount('syncLogs')
            ->orderBy('name')
            ->get();

        // Calculate stats
        $stats = [
            'total' => $devices->count(),
            'active' => $devices->where('is_active', true)->count(),
            'last_sync' => $devices->max('last_sync_at'),
        ];

        return view('staff-attendance.devices.index', compact('devices', 'stats'));
    }

    /**
     * Show the form for creating a new device.
     *
     * @return View
     */
    public function create(): View
    {
        $timezones = timezone_identifiers_list();

        return view('staff-attendance.devices.create', compact('timezones'));
    }

    /**
     * Store a newly created device in storage.
     *
     * @param StoreDeviceRequest $request
     * @return RedirectResponse
     */
    public function store(StoreDeviceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Generate webhook secret for push mode devices
        if (($validated['connectivity_mode'] ?? AttendanceDevice::MODE_PULL) === AttendanceDevice::MODE_PUSH) {
            $validated['webhook_secret'] = AttendanceDevice::generateWebhookSecret();
        }

        $device = AttendanceDevice::create($validated);

        // Redirect with success message
        $message = 'Device added successfully.';
        if ($device->connectivity_mode === AttendanceDevice::MODE_PUSH) {
            $message .= ' Configure your device to push events to the webhook URL shown in the edit page.';
        } elseif ($device->connectivity_mode === AttendanceDevice::MODE_AGENT) {
            $message .= ' Set up the on-premise agent using the API endpoint shown in the edit page.';
        }

        return redirect()
            ->route('staff-attendance.devices.index')
            ->with('message', $message);
    }

    /**
     * Show the form for editing the specified device.
     *
     * @param AttendanceDevice $device
     * @return View
     */
    public function edit(AttendanceDevice $device): View
    {
        $timezones = timezone_identifiers_list();

        return view('staff-attendance.devices.edit', compact('device', 'timezones'));
    }

    /**
     * Update the specified device in storage.
     *
     * @param UpdateDeviceRequest $request
     * @param AttendanceDevice $device
     * @return RedirectResponse
     */
    public function update(UpdateDeviceRequest $request, AttendanceDevice $device): RedirectResponse
    {
        $validated = $request->validated();

        // Only update password if provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        // Generate webhook secret when switching to push mode (if not already set)
        $newMode = $validated['connectivity_mode'] ?? $device->connectivity_mode;
        if ($newMode === AttendanceDevice::MODE_PUSH && empty($device->webhook_secret)) {
            $validated['webhook_secret'] = AttendanceDevice::generateWebhookSecret();
        }

        $device->update($validated);

        return redirect()
            ->route('staff-attendance.devices.index')
            ->with('message', 'Device updated successfully.');
    }

    /**
     * Remove the specified device from storage (soft delete).
     *
     * @param AttendanceDevice $device
     * @return RedirectResponse
     */
    public function destroy(AttendanceDevice $device): RedirectResponse
    {
        $device->delete();

        return redirect()
            ->route('staff-attendance.devices.index')
            ->with('message', 'Device deleted successfully.');
    }

    /**
     * Test connection to the specified device.
     *
     * @param AttendanceDevice $device
     * @return JsonResponse
     */
    public function testConnection(AttendanceDevice $device): JsonResponse
    {
        try {
            /** @var BiometricDeviceInterface $driver */
            $driver = app(BiometricDeviceInterface::class, ['device' => $device]);

            $success = $driver->testConnection();

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful! Device is reachable.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Connection failed: Device did not respond correctly.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Toggle the active status of the specified device.
     *
     * @param AttendanceDevice $device
     * @return JsonResponse
     */
    public function toggleActive(AttendanceDevice $device): JsonResponse
    {
        $device->is_active = !$device->is_active;
        $device->save();

        return response()->json([
            'success' => true,
            'is_active' => $device->is_active,
            'message' => $device->is_active ? 'Device activated.' : 'Device deactivated.',
        ]);
    }
}
