<?php

namespace App\Http\Controllers\StaffAttendance;

use App\Http\Controllers\Controller;
use App\Models\StaffAttendance\AttendanceDevice;
use App\Models\StaffAttendance\AttendanceSyncLog;
use App\Models\StaffAttendance\BiometricRawEvent;
use App\Services\StaffAttendance\DeviceSyncService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller for receiving biometric events via webhooks.
 *
 * Supports two connectivity modes:
 * - Push mode: Device (e.g., Hikvision) pushes events directly
 * - Agent mode: On-premise sync agent pushes events to cloud
 */
class WebhookController extends Controller
{
    /**
     * Handle Hikvision device push events (ISUP protocol).
     *
     * Hikvision devices can push access control events to a webhook URL.
     * This endpoint receives those events and stores them for processing.
     *
     * @param Request $request
     * @param AttendanceDevice $device
     * @return JsonResponse
     */
    public function hikvision(Request $request, AttendanceDevice $device): JsonResponse
    {
        // Verify device is configured for push mode
        if ($device->connectivity_mode !== AttendanceDevice::MODE_PUSH) {
            Log::warning('Hikvision webhook called for non-push device', [
                'device_id' => $device->id,
                'connectivity_mode' => $device->connectivity_mode,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Device is not configured for push mode.',
            ], 400);
        }

        // Verify device is active
        if (!$device->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Device is not active.',
            ], 403);
        }

        // Verify webhook signature if secret is configured
        if (!empty($device->webhook_secret)) {
            $signature = $request->header('X-Webhook-Signature');
            if (!$signature || !$device->verifyWebhookSignature($request->getContent(), $signature)) {
                Log::warning('Invalid webhook signature for Hikvision device', [
                    'device_id' => $device->id,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature.',
                ], 401);
            }
        }

        try {
            $payload = $request->all();
            $events = $this->parseHikvisionPayload($payload);

            if (empty($events)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No events to process.',
                    'processed' => 0,
                ]);
            }

            $processed = 0;
            $failed = 0;

            foreach ($events as $eventData) {
                try {
                    $this->storeEvent($device->id, $eventData);
                    $processed++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::warning('Failed to store Hikvision webhook event', [
                        'device_id' => $device->id,
                        'error' => $e->getMessage(),
                        'event_data' => $eventData,
                    ]);
                }
            }

            // Update last sync time
            $device->update(['last_sync_at' => now()]);

            Log::info('Hikvision webhook processed', [
                'device_id' => $device->id,
                'processed' => $processed,
                'failed' => $failed,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Events received.',
                'processed' => $processed,
                'failed' => $failed,
            ]);

        } catch (\Exception $e) {
            Log::error('Hikvision webhook error', [
                'device_id' => $device->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process events: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle events from on-premise sync agent.
     *
     * The agent runs locally at the school and pushes events to this endpoint.
     * Requires Sanctum authentication.
     *
     * @param Request $request
     * @param AttendanceDevice $device
     * @param DeviceSyncService $syncService
     * @return JsonResponse
     */
    public function agent(Request $request, AttendanceDevice $device, DeviceSyncService $syncService): JsonResponse
    {
        // Verify device is configured for agent mode
        if ($device->connectivity_mode !== AttendanceDevice::MODE_AGENT) {
            Log::warning('Agent webhook called for non-agent device', [
                'device_id' => $device->id,
                'connectivity_mode' => $device->connectivity_mode,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Device is not configured for agent mode.',
            ], 400);
        }

        // Verify device is active
        if (!$device->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Device is not active.',
            ], 403);
        }

        // Validate request
        $validated = $request->validate([
            'events' => 'required|array',
            'events.*.employee_number' => 'required|string|max:50',
            'events.*.event_timestamp' => 'required|date',
            'events.*.event_type' => 'required|string|in:clock_in,clock_out,break_start,break_end',
            'events.*.raw_payload' => 'nullable|array',
        ]);

        // Start sync log
        $log = $syncService->startSync($device->id, AttendanceSyncLog::SYNC_PULL_EVENTS);

        try {
            $processed = 0;
            $failed = 0;

            foreach ($validated['events'] as $eventData) {
                try {
                    // Skip events with empty employee numbers
                    if (empty($eventData['employee_number'])) {
                        continue;
                    }

                    $this->storeEvent($device->id, [
                        'employee_number' => $eventData['employee_number'],
                        'event_timestamp' => Carbon::parse($eventData['event_timestamp']),
                        'event_type' => $eventData['event_type'],
                        'raw_payload' => $eventData['raw_payload'] ?? [],
                    ]);
                    $processed++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::warning('Failed to store agent event', [
                        'device_id' => $device->id,
                        'error' => $e->getMessage(),
                        'event_data' => $eventData,
                    ]);
                }
            }

            // Complete sync log
            $syncService->completeSync($log, $processed, $failed);

            // Update last sync time
            $device->update(['last_sync_at' => now()]);

            Log::info('Agent webhook processed', [
                'device_id' => $device->id,
                'processed' => $processed,
                'failed' => $failed,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Events received.',
                'processed' => $processed,
                'failed' => $failed,
            ]);

        } catch (\Exception $e) {
            $syncService->logSyncError($log, $e->getMessage(), [
                'exception_class' => get_class($e),
            ]);

            Log::error('Agent webhook error', [
                'device_id' => $device->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process events: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse Hikvision ISUP/webhook payload to extract events.
     *
     * Hikvision sends events in various formats depending on firmware.
     * This method handles common formats.
     *
     * @param array $payload
     * @return array
     */
    private function parseHikvisionPayload(array $payload): array
    {
        $events = [];

        // Handle AcsEvent format (most common)
        if (isset($payload['AcsEvent'])) {
            $acsEvent = $payload['AcsEvent'];

            // Single event
            if (isset($acsEvent['employeeNoString']) || isset($acsEvent['employeeNo'])) {
                $events[] = $this->mapHikvisionEvent($acsEvent);
            }

            // Multiple events in InfoList
            if (isset($acsEvent['InfoList']) && is_array($acsEvent['InfoList'])) {
                foreach ($acsEvent['InfoList'] as $event) {
                    if (isset($event['employeeNoString']) || isset($event['employeeNo'])) {
                        $events[] = $this->mapHikvisionEvent($event);
                    }
                }
            }
        }

        // Handle direct event array format
        if (isset($payload['events']) && is_array($payload['events'])) {
            foreach ($payload['events'] as $event) {
                if (isset($event['employeeNoString']) || isset($event['employeeNo'])) {
                    $events[] = $this->mapHikvisionEvent($event);
                }
            }
        }

        // Handle EventNotificationAlert format (ISUP)
        if (isset($payload['EventNotificationAlert'])) {
            $alert = $payload['EventNotificationAlert'];
            if (isset($alert['AccessControllerEvent'])) {
                $event = $alert['AccessControllerEvent'];
                if (isset($event['employeeNoString']) || isset($event['employeeNo'])) {
                    $events[] = $this->mapHikvisionEvent($event);
                }
            }
        }

        return array_filter($events);
    }

    /**
     * Map a single Hikvision event to normalized format.
     *
     * @param array $event
     * @return array|null
     */
    private function mapHikvisionEvent(array $event): ?array
    {
        // Extract employee number (try both field names)
        $employeeNumber = $event['employeeNoString'] ?? $event['employeeNo'] ?? null;

        if (empty($employeeNumber)) {
            return null;
        }

        // Parse timestamp
        $timestamp = isset($event['time'])
            ? Carbon::parse($event['time'])
            : now();

        // Map event type
        $eventType = $this->mapHikvisionEventType($event['attendanceStatus'] ?? null);

        return [
            'employee_number' => (string) $employeeNumber,
            'event_timestamp' => $timestamp,
            'event_type' => $eventType,
            'raw_payload' => $event,
        ];
    }

    /**
     * Map Hikvision attendance status to event type.
     *
     * @param string|null $status
     * @return string
     */
    private function mapHikvisionEventType(?string $status): string
    {
        return match ($status) {
            'checkIn' => BiometricRawEvent::CLOCK_IN,
            'checkOut' => BiometricRawEvent::CLOCK_OUT,
            'breakOut' => BiometricRawEvent::BREAK_START,
            'breakIn' => BiometricRawEvent::BREAK_END,
            default => BiometricRawEvent::CLOCK_IN,
        };
    }

    /**
     * Store a single event with deduplication.
     *
     * @param int $deviceId
     * @param array $eventData
     * @return void
     */
    private function storeEvent(int $deviceId, array $eventData): void
    {
        BiometricRawEvent::updateOrCreate(
            [
                'device_id' => $deviceId,
                'employee_number' => $eventData['employee_number'],
                'event_timestamp' => $eventData['event_timestamp'],
            ],
            [
                'event_type' => $eventData['event_type'],
                'raw_payload' => $eventData['raw_payload'] ?? [],
                'processed' => false,
            ]
        );
    }
}
