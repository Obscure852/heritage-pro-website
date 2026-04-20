<?php

namespace App\Providers;

use App\Contracts\BiometricDeviceInterface;
use App\Models\StaffAttendance\AttendanceDevice;
use App\Services\StaffAttendance\Drivers\HikvisionDriver;
use App\Services\StaffAttendance\Drivers\ZKTecoDriver;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

/**
 * Service provider for biometric device driver resolution.
 *
 * Binds the BiometricDeviceInterface to the appropriate driver implementation
 * based on the device type. Allows resolving drivers via:
 *
 *   app(BiometricDeviceInterface::class, ['device' => $device])
 *
 * Supported device types:
 * - hikvision: HikvisionDriver (ISAPI protocol)
 * - zkteco: ZKTecoDriver (TCP/UDP protocol)
 */
class BiometricServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(BiometricDeviceInterface::class, function ($app, array $params) {
            $device = $params['device'] ?? null;

            if (!$device instanceof AttendanceDevice) {
                throw new InvalidArgumentException(
                    'BiometricDeviceInterface requires an AttendanceDevice instance. ' .
                    'Usage: app(BiometricDeviceInterface::class, [\'device\' => $device])'
                );
            }

            return match ($device->type) {
                AttendanceDevice::TYPE_HIKVISION => new HikvisionDriver($device),
                AttendanceDevice::TYPE_ZKTECO => new ZKTecoDriver($device),
                default => throw new InvalidArgumentException(
                    "Unknown device type: {$device->type}. " .
                    'Supported types: ' . AttendanceDevice::TYPE_HIKVISION . ', ' . AttendanceDevice::TYPE_ZKTECO
                ),
            };
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        // No boot logic needed
    }
}
