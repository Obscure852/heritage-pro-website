<?php

namespace App\Contracts;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Contract for biometric device drivers.
 *
 * This interface provides a device-agnostic contract for integrating with
 * various biometric attendance devices (Hikvision, ZKTeco, etc.). Implementations
 * should handle device-specific protocols while normalizing output to a consistent format.
 *
 * Implementations:
 * - HikvisionDriver (Phase 2): ISAPI protocol with digest auth
 * - ZKTecoDriver (Phase 3): ZKTeco SDK integration
 */
interface BiometricDeviceInterface
{
    /**
     * Test connection to the biometric device.
     *
     * Verifies that the device is reachable and credentials are valid.
     * Should make a lightweight request to check connectivity.
     *
     * @return bool True if device is reachable and credentials are valid, false otherwise
     */
    public function testConnection(): bool;

    /**
     * Fetch attendance events from the device within a time range.
     *
     * Retrieves all clock in/out events between the specified timestamps.
     * Events are returned in a normalized format regardless of device manufacturer.
     *
     * @param Carbon $startTime Start of time range (inclusive)
     * @param Carbon $endTime End of time range (inclusive)
     * @return Collection Collection of normalized event arrays, each containing:
     *                    - employee_number (string|null): Employee identifier from device
     *                    - event_timestamp (Carbon): When the event occurred (UTC)
     *                    - event_type (string): One of BiometricRawEvent::CLOCK_IN, CLOCK_OUT, BREAK_START, BREAK_END
     *                    - raw_payload (array): Original device response for debugging/audit
     */
    public function fetchEvents(Carbon $startTime, Carbon $endTime): Collection;
}
