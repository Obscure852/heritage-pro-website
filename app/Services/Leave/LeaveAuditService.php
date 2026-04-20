<?php

namespace App\Services\Leave;

use App\Models\Leave\LeaveAuditLog;
use App\Models\Leave\LeaveBalance;
use App\Models\Leave\LeaveRequest;
use Illuminate\Support\Collection;

/**
 * Service for retrieving and formatting leave audit logs.
 *
 * Provides methods to fetch audit history for leave requests and balances,
 * and helper methods for formatting changes for display.
 */
class LeaveAuditService
{
    // ==================== AUDIT RETRIEVAL METHODS ====================

    /**
     * Get audit history for a leave request.
     *
     * Retrieves all audit logs related to the request, ordered by most recent first.
     * Includes the user who performed each action.
     *
     * @param LeaveRequest $request The leave request to get audit history for
     * @return Collection Collection of LeaveAuditLog models
     */
    public function getAuditHistoryForRequest(LeaveRequest $request): Collection
    {
        return LeaveAuditLog::forModel(LeaveRequest::class, $request->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get audit history for a leave balance.
     *
     * Retrieves all audit logs related to the balance, ordered by most recent first.
     * Includes the user who performed each action.
     *
     * @param LeaveBalance $balance The leave balance to get audit history for
     * @return Collection Collection of LeaveAuditLog models
     */
    public function getAuditHistoryForBalance(LeaveBalance $balance): Collection
    {
        return LeaveAuditLog::forModel(LeaveBalance::class, $balance->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get recent audit logs with optional filters.
     *
     * Retrieves recent audit logs, optionally filtered by action type or user.
     * Useful for admin dashboards and audit reports.
     *
     * @param int $limit Maximum number of records to return
     * @param string|null $action Filter by action type (use LeaveAuditLog::ACTION_* constants)
     * @param int|null $userId Filter by user who performed the action
     * @return Collection Collection of LeaveAuditLog models
     */
    public function getRecentAuditLogs(int $limit = 50, ?string $action = null, ?int $userId = null): Collection
    {
        $query = LeaveAuditLog::with(['user', 'auditable'])
            ->orderBy('created_at', 'desc');

        if ($action) {
            $query->byAction($action);
        }

        if ($userId) {
            $query->byUser($userId);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get audit logs for a specific date.
     *
     * Retrieves all leave audit logs for a given date.
     * Useful for daily audit reports.
     *
     * @param string $date Date in Y-m-d format
     * @return Collection Collection of LeaveAuditLog models
     */
    public function getAuditLogsForDate(string $date): Collection
    {
        return LeaveAuditLog::with(['user', 'auditable'])
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get audit logs for a date range.
     *
     * Retrieves all leave audit logs between two dates (inclusive).
     * Useful for periodic audit reports.
     *
     * @param string $startDate Start date in Y-m-d format
     * @param string $endDate End date in Y-m-d format
     * @return Collection Collection of LeaveAuditLog models
     */
    public function getAuditLogsForDateRange(string $startDate, string $endDate): Collection
    {
        return LeaveAuditLog::with(['user', 'auditable'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // ==================== FORMATTING METHODS ====================

    /**
     * Format changes for display (diff between old and new values).
     *
     * Compares old and new values and returns a list of changes.
     * Handles create (no old values), delete (no new values), and update actions.
     * Skips internal fields like created_at, updated_at, and id.
     *
     * @param array|null $oldValues The state before the action
     * @param array|null $newValues The state after the action
     * @return array List of changes, each with 'field', 'old_value', 'new_value'
     */
    public function formatChanges(?array $oldValues, ?array $newValues): array
    {
        $changes = [];
        $excludeFields = ['created_at', 'updated_at', 'id', 'ulid'];

        // For create actions (no old values)
        if ($oldValues === null && $newValues !== null) {
            foreach ($newValues as $field => $value) {
                if (!in_array($field, $excludeFields)) {
                    $changes[] = [
                        'field' => $this->formatFieldName($field),
                        'old_value' => '-',
                        'new_value' => $this->formatValue($value),
                    ];
                }
            }
            return $changes;
        }

        // For delete actions (no new values)
        if ($oldValues !== null && $newValues === null) {
            foreach ($oldValues as $field => $value) {
                if (!in_array($field, $excludeFields)) {
                    $changes[] = [
                        'field' => $this->formatFieldName($field),
                        'old_value' => $this->formatValue($value),
                        'new_value' => '-',
                    ];
                }
            }
            return $changes;
        }

        // For update actions (both old and new values)
        if ($oldValues !== null && $newValues !== null) {
            $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));

            foreach ($allKeys as $field) {
                if (in_array($field, $excludeFields)) {
                    continue;
                }

                $oldVal = $oldValues[$field] ?? null;
                $newVal = $newValues[$field] ?? null;

                // Only include if values are different
                if ($oldVal !== $newVal) {
                    $changes[] = [
                        'field' => $this->formatFieldName($field),
                        'old_value' => $this->formatValue($oldVal),
                        'new_value' => $this->formatValue($newVal),
                    ];
                }
            }
        }

        return $changes;
    }

    /**
     * Format field name for display.
     *
     * Converts snake_case field names to Title Case.
     *
     * @param string $field The field name in snake_case
     * @return string The formatted field name
     */
    public function formatFieldName(string $field): string
    {
        // Map common field names to better display names
        $fieldMappings = [
            'user_id' => 'Staff Member',
            'leave_type_id' => 'Leave Type',
            'leave_balance_id' => 'Leave Balance',
            'approved_by' => 'Approved By',
            'cancelled_by' => 'Cancelled By',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'start_half_day' => 'Start Half Day',
            'end_half_day' => 'End Half Day',
            'total_days' => 'Total Days',
            'ip_address' => 'IP Address',
        ];

        if (isset($fieldMappings[$field])) {
            return $fieldMappings[$field];
        }

        return ucwords(str_replace('_', ' ', $field));
    }

    /**
     * Format value for display.
     *
     * Handles null, boolean, array, and date values appropriately.
     *
     * @param mixed $value The value to format
     * @return string The formatted value
     */
    public function formatValue($value): string
    {
        if ($value === null) {
            return '-';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        // Check if it looks like a date (ISO 8601 or similar)
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            try {
                $date = \Carbon\Carbon::parse($value);
                // If it has time component and it's not midnight
                if (strlen($value) > 10 && $date->format('H:i:s') !== '00:00:00') {
                    return $date->format('d M Y H:i');
                }
                return $date->format('d M Y');
            } catch (\Exception $e) {
                // If parsing fails, return as-is
                return (string) $value;
            }
        }

        return (string) $value;
    }

    // ==================== STATISTICS METHODS ====================

    /**
     * Get audit log statistics for a given period.
     *
     * Returns counts grouped by action type.
     *
     * @param int $days Number of days to look back
     * @return array Action counts keyed by action type
     */
    public function getAuditStats(int $days = 30): array
    {
        return LeaveAuditLog::recent($days)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();
    }
}
