<?php

namespace App\Services\StaffAttendance\Drivers;

use App\Contracts\BiometricDeviceInterface;
use App\Models\StaffAttendance\AttendanceDevice;
use App\Models\StaffAttendance\BiometricRawEvent;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Hikvision ISAPI driver for biometric attendance devices.
 *
 * Implements the BiometricDeviceInterface for Hikvision devices using the ISAPI
 * protocol with HTTP digest authentication. Fetches access control events from
 * the AcsEvent endpoint with pagination support.
 *
 * ISAPI Reference:
 * - Device Info: GET /ISAPI/System/deviceInfo
 * - Access Events: POST /ISAPI/AccessControl/AcsEvent?format=json
 * - Event Types: major=5 (access control), minor=75 (authenticated events)
 *
 * @see https://www.hikvision.com/en/support/download/sdk/
 */
class HikvisionDriver implements BiometricDeviceInterface
{
    /**
     * The attendance device configuration.
     *
     * @var AttendanceDevice
     */
    private AttendanceDevice $device;

    /**
     * Base URL for ISAPI requests.
     *
     * @var string
     */
    private string $baseUrl;

    /**
     * Maximum results per page for event pagination.
     */
    private const MAX_RESULTS_PER_PAGE = 1000;

    /**
     * Response status indicating more results available.
     */
    private const RESPONSE_STATUS_MORE = 'MORE';

    /**
     * Create a new HikvisionDriver instance.
     *
     * @param AttendanceDevice $device The device configuration with IP, port, and credentials
     */
    public function __construct(AttendanceDevice $device)
    {
        $this->device = $device;
        $this->baseUrl = "http://{$device->ip_address}:{$device->port}";
    }

    /**
     * Test connection to the Hikvision device.
     *
     * Makes a GET request to /ISAPI/System/deviceInfo to verify the device
     * is reachable and credentials are valid.
     *
     * @return bool True if device responds successfully, false otherwise
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::timeout(10)
                ->withDigestAuth($this->device->username, $this->device->password)
                ->get("{$this->baseUrl}/ISAPI/System/deviceInfo");

            return $response->successful();
        } catch (ConnectionException $e) {
            return false;
        }
    }

    /**
     * Fetch attendance events from the Hikvision device.
     *
     * Retrieves access control events using pagination. The ISAPI endpoint
     * returns 'MORE' in responseStatusStrg when additional pages are available.
     *
     * @param Carbon $startTime Start of time range (inclusive)
     * @param Carbon $endTime End of time range (inclusive)
     * @return Collection Collection of normalized event arrays
     * @throws RuntimeException If device returns an error response
     */
    public function fetchEvents(Carbon $startTime, Carbon $endTime): Collection
    {
        $events = collect();
        $searchId = Str::uuid()->toString();
        $position = 0;

        do {
            $response = $this->fetchEventsPage($searchId, $startTime, $endTime, $position);

            if (!$response->successful()) {
                throw new RuntimeException(
                    "Failed to fetch events from Hikvision device: HTTP {$response->status()}"
                );
            }

            $data = $response->json('AcsEvent') ?? [];
            $infoList = $data['InfoList'] ?? [];

            foreach ($infoList as $event) {
                $events->push($this->mapEvent($event));
            }

            $position += count($infoList);
            $responseStatus = $data['responseStatusStrg'] ?? null;

        } while ($responseStatus === self::RESPONSE_STATUS_MORE);

        return $events;
    }

    /**
     * Fetch a single page of events from the device.
     *
     * Makes a POST request to the AcsEvent endpoint with search parameters
     * for pagination and time range filtering.
     *
     * @param string $searchId UUID for tracking paginated search
     * @param Carbon $startTime Start of time range
     * @param Carbon $endTime End of time range
     * @param int $position Current position in result set
     * @return \Illuminate\Http\Client\Response
     */
    private function fetchEventsPage(
        string $searchId,
        Carbon $startTime,
        Carbon $endTime,
        int $position
    ) {
        return Http::timeout(60)
            ->connectTimeout(10)
            ->retry(3, 100, fn($exception) => $exception instanceof ConnectionException)
            ->withDigestAuth($this->device->username, $this->device->password)
            ->post("{$this->baseUrl}/ISAPI/AccessControl/AcsEvent?format=json", [
                'AcsEventCond' => [
                    'searchID' => $searchId,
                    'searchResultPosition' => $position,
                    'maxResults' => self::MAX_RESULTS_PER_PAGE,
                    'major' => 5,  // Access control events
                    'minor' => 75, // Authenticated events only
                    'startTime' => $startTime->toIso8601String(),
                    'endTime' => $endTime->toIso8601String(),
                ],
            ]);
    }

    /**
     * Map a Hikvision event to the normalized format.
     *
     * @param array $event Raw event data from device
     * @return array Normalized event array
     */
    private function mapEvent(array $event): array
    {
        return [
            'employee_number' => $event['employeeNoString'] ?? null,
            'event_timestamp' => Carbon::parse($event['time'])->setTimezone('UTC'),
            'event_type' => $this->mapEventType($event),
            'raw_payload' => $event,
        ];
    }

    /**
     * Map Hikvision attendance status to internal event type.
     *
     * Hikvision uses attendanceStatus field with values like 'checkIn', 'checkOut',
     * 'breakOut', 'breakIn'. Unknown statuses default to CLOCK_IN for safety.
     *
     * @param array $event Raw event data
     * @return string BiometricRawEvent event type constant
     */
    private function mapEventType(array $event): string
    {
        $status = $event['attendanceStatus'] ?? null;

        return match ($status) {
            'checkIn' => BiometricRawEvent::CLOCK_IN,
            'checkOut' => BiometricRawEvent::CLOCK_OUT,
            'breakOut' => BiometricRawEvent::BREAK_START,
            'breakIn' => BiometricRawEvent::BREAK_END,
            default => BiometricRawEvent::CLOCK_IN,
        };
    }
}
