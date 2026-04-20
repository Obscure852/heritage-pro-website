<?php

namespace App\Services\StaffAttendance;

use App\Models\StaffAttendance\StaffAttendanceAuditLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service for retrieving and querying staff attendance audit logs.
 *
 * Provides methods to fetch audit history for attendance records,
 * following the LeaveAuditService pattern.
 */
class StaffAttendanceAuditService
{
    // ==================== AUDIT RETRIEVAL BY MODEL ====================

    /**
     * Get audit logs for a specific model instance.
     *
     * Returns audit logs related to a specific model, ordered by most recent first.
     *
     * @param string $modelType The model class name (e.g., StaffAttendanceRecord::class)
     * @param int $modelId The model ID
     * @param int $limit Maximum number of records to return (default 50)
     * @return Collection Collection of StaffAttendanceAuditLog models
     */
    public function getLogsForModel(string $modelType, int $modelId, int $limit = 50): Collection
    {
        return StaffAttendanceAuditLog::forModel($modelType, $modelId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    // ==================== AUDIT RETRIEVAL BY USER ====================

    /**
     * Get audit logs for actions performed by a specific user.
     *
     * Returns audit logs where the specified user performed the action,
     * ordered by most recent first.
     *
     * @param int $userId The user ID
     * @param int $limit Maximum number of records to return (default 50)
     * @return Collection Collection of StaffAttendanceAuditLog models
     */
    public function getLogsForUser(int $userId, int $limit = 50): Collection
    {
        return StaffAttendanceAuditLog::byUser($userId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    // ==================== AUDIT RETRIEVAL BY DATE ====================

    /**
     * Get audit logs within a date range.
     *
     * Returns all audit logs between two dates (inclusive),
     * ordered by most recent first.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return Collection Collection of StaffAttendanceAuditLog models
     */
    public function getLogsForDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return StaffAttendanceAuditLog::with(['user', 'auditable'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // ==================== RECENT ACTIVITY ====================

    /**
     * Get most recent audit log entries.
     *
     * Useful for dashboards and activity feeds.
     *
     * @param int $limit Maximum number of records to return (default 100)
     * @return Collection Collection of StaffAttendanceAuditLog models
     */
    public function getRecentActivity(int $limit = 100): Collection
    {
        return StaffAttendanceAuditLog::with(['user', 'auditable'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    // ==================== AUDIT RETRIEVAL BY ACTION ====================

    /**
     * Get audit logs filtered by action type.
     *
     * Uses the byAction scope to filter logs by a specific action.
     *
     * @param string $action The action type (use StaffAttendanceAuditLog::ACTION_* constants)
     * @param int $limit Maximum number of records to return (default 50)
     * @return Collection Collection of StaffAttendanceAuditLog models
     */
    public function getLogsByAction(string $action, int $limit = 50): Collection
    {
        return StaffAttendanceAuditLog::byAction($action)
            ->with(['user', 'auditable'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
