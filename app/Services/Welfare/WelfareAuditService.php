<?php

namespace App\Services\Welfare;

use App\Models\Welfare\WelfareAuditLog;
use Illuminate\Support\Facades\Auth;

class WelfareAuditService
{
    /**
     * Log an audit action.
     *
     * @param string $action The action performed (create, view, update, delete, etc.)
     * @param string $entityType The type of entity (welfare_case, counseling_session, etc.)
     * @param int $entityId The ID of the entity
     * @param array|null $data Additional data to log
     * @return WelfareAuditLog
     */
    public function log(string $action, string $entityType, int $entityId, ?array $data = null): WelfareAuditLog
    {
        return WelfareAuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'data' => $data ? json_encode($data) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log a view action with access justification for sensitive records.
     *
     * @param string $entityType
     * @param int $entityId
     * @param string|null $justification
     * @return WelfareAuditLog
     */
    public function logSensitiveAccess(string $entityType, int $entityId, ?string $justification = null): WelfareAuditLog
    {
        return $this->log('sensitive_view', $entityType, $entityId, [
            'justification' => $justification,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get audit history for an entity.
     *
     * @param string $entityType
     * @param int $entityId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHistory(string $entityType, int $entityId, int $limit = 50)
    {
        return WelfareAuditLog::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get all audit logs for a user.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserActivity(int $userId, int $limit = 100)
    {
        return WelfareAuditLog::where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent activity across all welfare entities.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentActivity(int $limit = 50)
    {
        return WelfareAuditLog::with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
