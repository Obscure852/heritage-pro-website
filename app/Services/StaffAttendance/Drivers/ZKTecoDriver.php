<?php

namespace App\Services\StaffAttendance\Drivers;

use App\Contracts\BiometricDeviceInterface;
use App\Models\StaffAttendance\AttendanceDevice;
use App\Models\StaffAttendance\BiometricRawEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Jmrashed\Zkteco\Lib\ZKTeco;
use RuntimeException;

/**
 * ZKTeco driver for biometric attendance devices.
 *
 * Implements the BiometricDeviceInterface for ZKTeco devices using direct
 * TCP/UDP socket communication via the jmrashed/zkteco library. Fetches
 * attendance records and maps punch states to event types.
 *
 * Protocol Details:
 * - Default port: 4370 (UDP)
 * - Uses proprietary binary protocol with variable-length records
 * - Timestamps in device local time, converted to UTC
 *
 * Punch State Mapping:
 * - 0: Check-In (CLOCK_IN)
 * - 1: Check-Out (CLOCK_OUT)
 * - 2: Overtime-In (CLOCK_IN)
 * - 3: Overtime-Out (CLOCK_OUT)
 * - 4: Break-Out (BREAK_START)
 * - 5: Break-In (BREAK_END)
 *
 * @see https://github.com/jmrashed/zkteco
 */
class ZKTecoDriver implements BiometricDeviceInterface
{
    /**
     * The attendance device configuration.
     *
     * @var AttendanceDevice
     */
    private AttendanceDevice $device;

    /**
     * Default ZKTeco device port.
     */
    private const DEFAULT_PORT = 4370;

    /**
     * Create a new ZKTecoDriver instance.
     *
     * @param AttendanceDevice $device The device configuration with IP, port, and credentials
     */
    public function __construct(AttendanceDevice $device)
    {
        $this->device = $device;
    }

    /**
     * Test connection to the ZKTeco device.
     *
     * Attempts to connect to the device and immediately disconnects.
     * Used to verify network connectivity and device availability.
     *
     * @return bool True if device is reachable, false otherwise
     */
    public function testConnection(): bool
    {
        $zk = $this->createConnection();

        try {
            $connected = $zk->connect();
            if ($connected) {
                $zk->disconnect();
            }
            return $connected;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Fetch attendance events from the ZKTeco device.
     *
     * Retrieves all attendance records from the device and filters them
     * by the specified time range. Events are normalized to the standard
     * format defined by BiometricDeviceInterface.
     *
     * Note: getAttendance() returns ALL records from the device; filtering
     * is performed in PHP after retrieval.
     *
     * @param Carbon $startTime Start of time range (inclusive)
     * @param Carbon $endTime End of time range (inclusive)
     * @return Collection Collection of normalized event arrays
     * @throws RuntimeException If device connection fails
     */
    public function fetchEvents(Carbon $startTime, Carbon $endTime): Collection
    {
        $zk = $this->createConnection();

        try {
            if (!$zk->connect()) {
                throw new RuntimeException(
                    "Failed to connect to ZKTeco device at {$this->device->ip_address}"
                );
            }

            $attendance = $zk->getAttendance();

            return collect($attendance)
                ->filter(function ($record) use ($startTime, $endTime) {
                    $timestamp = Carbon::parse($record['timestamp']);
                    return $timestamp->between($startTime, $endTime);
                })
                ->map(fn($record) => $this->mapEvent($record));
        } finally {
            $zk->disconnect();
        }
    }

    /**
     * Create a new ZKTeco connection instance.
     *
     * @return ZKTeco
     */
    private function createConnection(): ZKTeco
    {
        return new ZKTeco(
            $this->device->ip_address,
            $this->device->port ?: self::DEFAULT_PORT
        );
    }

    /**
     * Map a ZKTeco attendance record to the normalized format.
     *
     * @param array $record Raw attendance record from device
     * @return array Normalized event array
     */
    private function mapEvent(array $record): array
    {
        return [
            'employee_number' => (string) ($record['id'] ?? $record['uid'] ?? ''),
            'event_timestamp' => Carbon::parse($record['timestamp'])->setTimezone('UTC'),
            'event_type' => $this->mapPunchState((int) ($record['state'] ?? 0)),
            'raw_payload' => $record,
        ];
    }

    /**
     * Map ZKTeco punch state to BiometricRawEvent constant.
     *
     * ZKTeco devices use numeric punch states:
     * - 0: Check-In
     * - 1: Check-Out
     * - 2: Overtime-In (treated as clock in)
     * - 3: Overtime-Out (treated as clock out)
     * - 4: Break-Out (leaving for break)
     * - 5: Break-In (returning from break)
     *
     * Unknown states default to CLOCK_IN for safety.
     *
     * @param int $state The punch state value from the device
     * @return string BiometricRawEvent event type constant
     */
    private function mapPunchState(int $state): string
    {
        return match ($state) {
            0 => BiometricRawEvent::CLOCK_IN,
            1 => BiometricRawEvent::CLOCK_OUT,
            2 => BiometricRawEvent::CLOCK_IN,      // OT-In
            3 => BiometricRawEvent::CLOCK_OUT,     // OT-Out
            4 => BiometricRawEvent::BREAK_START,   // Break-Out
            5 => BiometricRawEvent::BREAK_END,     // Break-In
            default => BiometricRawEvent::CLOCK_IN,
        };
    }
}
