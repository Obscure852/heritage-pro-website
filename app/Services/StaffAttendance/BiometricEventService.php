<?php

namespace App\Services\StaffAttendance;

use App\Models\StaffAttendance\BiometricRawEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service for managing biometric events from attendance devices.
 *
 * Handles raw event storage, user matching by employee number,
 * and event processing for attendance record creation.
 */
class BiometricEventService
{
    // ==================== RAW EVENT STORAGE ====================

    /**
     * Store a raw biometric event from a device.
     *
     * Creates a new unprocessed event record that will be processed
     * by the event processing job.
     *
     * @param int $deviceId The device that captured the event
     * @param string $employeeNumber The employee number from the device
     * @param \DateTime $eventTimestamp When the event occurred
     * @param string $eventType Event type (use BiometricRawEvent constants)
     * @param array|null $rawPayload Optional raw device response for debugging
     * @return BiometricRawEvent The created event record
     */
    public function storeRawEvent(
        int $deviceId,
        string $employeeNumber,
        \DateTime $eventTimestamp,
        string $eventType,
        ?array $rawPayload = null
    ): BiometricRawEvent {
        return BiometricRawEvent::create([
            'device_id' => $deviceId,
            'employee_number' => $employeeNumber,
            'event_timestamp' => $eventTimestamp,
            'event_type' => $eventType,
            'raw_payload' => $rawPayload,
            'processed' => false,
        ]);
    }

    // ==================== USER MATCHING ====================

    /**
     * Find a user by their employee number.
     *
     * Tries exact match on users.id_number first, then falls back to
     * normalized comparison (trim whitespace, handle leading zeros).
     *
     * @param string $employeeNumber The employee number from the device
     * @return User|null The matched user or null if not found
     */
    public function findUserByEmployeeNumber(string $employeeNumber): ?User
    {
        // Try exact match first
        $user = User::where('id_number', $employeeNumber)->first();
        
        if ($user) {
            return $user;
        }

        // Normalize and try again (trim whitespace, handle leading zeros)
        $normalized = ltrim(trim($employeeNumber), '0');
        
        // Try matching with normalized employee number
        return User::where('id_number', $normalized)
            ->orWhere('id_number', 'LIKE', '%' . $normalized)
            ->first();
    }

    // ==================== EVENT RETRIEVAL ====================

    /**
     * Get unprocessed events for processing.
     *
     * Returns events that have not been processed yet, ordered by
     * event timestamp to maintain chronological processing order.
     *
     * @param int $limit Maximum number of events to return
     * @return Collection Collection of unprocessed BiometricRawEvent models
     */
    public function getUnprocessedEvents(int $limit = 100): Collection
    {
        return BiometricRawEvent::unprocessed()
            ->orderBy('event_timestamp', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get events for a specific employee within an optional date range.
     *
     * @param string $employeeNumber The employee number
     * @param Carbon|null $startDate Optional start date filter
     * @param Carbon|null $endDate Optional end date filter
     * @return Collection Collection of BiometricRawEvent models
     */
    public function getEventsForEmployee(
        string $employeeNumber,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): Collection {
        $query = BiometricRawEvent::forEmployee($employeeNumber)
            ->orderBy('event_timestamp', 'asc');

        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        } elseif ($startDate) {
            $query->where('event_timestamp', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('event_timestamp', '<=', $endDate);
        }

        return $query->get();
    }

    // ==================== EVENT PROCESSING ====================

    /**
     * Mark a biometric event as processed.
     *
     * Updates the event to indicate it has been processed into an
     * attendance record.
     *
     * @param BiometricRawEvent $event The event to mark as processed
     * @return void
     */
    public function markAsProcessed(BiometricRawEvent $event): void
    {
        $event->processed = true;
        $event->processed_at = now();
        $event->save();
    }
}
