<?php

namespace App\Services\StaffAttendance;

use App\Models\StaffAttendance\AttendanceDevice;
use App\Models\StaffAttendance\AttendanceSyncLog;
use Illuminate\Support\Collection;

/**
 * Service for managing device synchronization operations.
 *
 * Handles sync log creation, completion, error logging, and retry management.
 * Supports DEV-07 requirements for error_message, error_details, and retry_count.
 */
class DeviceSyncService
{
    // ==================== SYNC LIFECYCLE ====================

    /**
     * Start a new sync operation.
     *
     * Creates a new sync log entry with status 'running' and records the start time.
     *
     * @param int $deviceId The device being synced
     * @param string $syncType Type of sync (use AttendanceSyncLog::SYNC_* constants)
     * @return AttendanceSyncLog The created sync log entry
     */
    public function startSync(int $deviceId, string $syncType): AttendanceSyncLog
    {
        return AttendanceSyncLog::create([
            'device_id' => $deviceId,
            'sync_type' => $syncType,
            'status' => AttendanceSyncLog::STATUS_RUNNING,
            'started_at' => now(),
            'records_processed' => 0,
            'records_failed' => 0,
            'retry_count' => 0,
        ]);
    }

    /**
     * Complete a sync operation.
     *
     * Updates the sync log with results. Sets status based on whether there
     * were any failures (success if no failures, partial if some failures).
     *
     * @param AttendanceSyncLog $log The sync log to complete
     * @param int $recordsProcessed Number of records processed
     * @param int $recordsFailed Number of records that failed (default 0)
     * @return AttendanceSyncLog The updated sync log
     */
    public function completeSync(
        AttendanceSyncLog $log,
        int $recordsProcessed,
        int $recordsFailed = 0
    ): AttendanceSyncLog {
        $status = $recordsFailed > 0
            ? AttendanceSyncLog::STATUS_PARTIAL
            : AttendanceSyncLog::STATUS_SUCCESS;

        $log->status = $status;
        $log->completed_at = now();
        $log->records_processed = $recordsProcessed;
        $log->records_failed = $recordsFailed;
        $log->save();

        return $log->fresh();
    }

    // ==================== ERROR LOGGING (DEV-07) ====================

    /**
     * Log a sync error.
     *
     * Sets the sync log to failed status and records error details.
     * DEV-07 requirement: captures error_message, error_details, retry_count.
     *
     * @param AttendanceSyncLog $log The sync log to update
     * @param string $errorMessage Human-readable error message
     * @param array|null $errorDetails Optional detailed error information
     * @return AttendanceSyncLog The updated sync log
     */
    public function logSyncError(
        AttendanceSyncLog $log,
        string $errorMessage,
        ?array $errorDetails = null
    ): AttendanceSyncLog {
        $log->status = AttendanceSyncLog::STATUS_FAILED;
        $log->error_message = $errorMessage;
        $log->error_details = $errorDetails;
        $log->completed_at = now();
        
        // Increment retry count if this is a retry attempt
        if ($log->last_retry_at !== null) {
            $log->retry_count = ($log->retry_count ?? 0) + 1;
        }
        
        $log->save();

        return $log->fresh();
    }

    // ==================== SYNC HISTORY ====================

    /**
     * Get sync history for a specific device.
     *
     * Returns sync logs ordered by most recent first.
     *
     * @param int $deviceId The device ID
     * @param int $days Number of days to look back (default 30)
     * @return Collection Collection of AttendanceSyncLog models
     */
    public function getDeviceSyncHistory(int $deviceId, int $days = 30): Collection
    {
        return AttendanceSyncLog::forDevice($deviceId)
            ->recent($days)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all failed sync logs from recent days.
     *
     * Eager loads device relationship for display purposes.
     *
     * @param int $days Number of days to look back (default 30)
     * @return Collection Collection of failed AttendanceSyncLog models
     */
    public function getFailedSyncs(int $days = 30): Collection
    {
        return AttendanceSyncLog::failed()
            ->recent($days)
            ->with('device')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // ==================== RETRY MANAGEMENT ====================

    /**
     * Check if a sync operation should be retried.
     *
     * Returns true if retry count is below maximum.
     *
     * @param AttendanceSyncLog $log The sync log to check
     * @param int $maxRetries Maximum retry attempts (default 3)
     * @return bool True if should retry, false otherwise
     */
    public function shouldRetry(AttendanceSyncLog $log, int $maxRetries = 3): bool
    {
        return ($log->retry_count ?? 0) < $maxRetries;
    }

    /**
     * Mark a sync log for retry.
     *
     * Increments retry count, sets last_retry_at, and resets status to running.
     *
     * @param AttendanceSyncLog $log The sync log to retry
     * @return AttendanceSyncLog The updated sync log
     */
    public function markForRetry(AttendanceSyncLog $log): AttendanceSyncLog
    {
        $log->retry_count = ($log->retry_count ?? 0) + 1;
        $log->last_retry_at = now();
        $log->status = AttendanceSyncLog::STATUS_RUNNING;
        $log->error_message = null;
        $log->error_details = null;
        $log->completed_at = null;
        $log->save();

        return $log->fresh();
    }
}
