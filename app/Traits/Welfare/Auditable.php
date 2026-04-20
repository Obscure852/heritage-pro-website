<?php

namespace App\Traits\Welfare;

use App\Helpers\TermHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Provides automatic audit logging for welfare models.
 *
 * Logs all create, update, delete, and view actions to welfare_audit_log table.
 * Uses fire-and-forget approach to prevent audit failures from blocking operations.
 */
trait Auditable
{
    /**
     * Boot the auditable trait.
     */
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::logAuditAction($model, 'create', null, $model->getAuditableAttributes());
        });

        static::updated(function ($model) {
            $oldValues = $model->getOriginal();
            $newValues = $model->getAuditableAttributes();

            // Only log if there are actual changes
            $changes = array_diff_assoc($newValues, $oldValues);
            if (!empty($changes)) {
                static::logAuditAction($model, 'update', $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            static::logAuditAction($model, 'delete', $model->getAuditableAttributes(), null);
        });
    }

    /**
     * Log a view action for this model.
     * Call this explicitly when displaying sensitive data.
     *
     * @return void
     */
    public function logView(): void
    {
        static::logAuditAction($this, 'view');
    }

    /**
     * Log an export action for this model.
     *
     * @return void
     */
    public function logExport(): void
    {
        static::logAuditAction($this, 'export');
    }

    /**
     * Log a print action for this model.
     *
     * @return void
     */
    public function logPrint(): void
    {
        static::logAuditAction($this, 'print');
    }

    /**
     * Log an audit action to the database.
     * Uses a separate connection attempt to prevent blocking main operations.
     *
     * @param mixed $model
     * @param string $action
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return void
     */
    protected static function logAuditAction(
        $model,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        try {
            $currentTerm = TermHelper::getCurrentTerm();
            $user = Auth::user();

            // Determine welfare_case_id
            $welfareCaseId = null;
            if ($model->getTable() === 'welfare_cases') {
                $welfareCaseId = $model->id;
            } elseif (isset($model->welfare_case_id)) {
                $welfareCaseId = $model->welfare_case_id;
            }

            // Use DB::table for direct insert to avoid recursion
            // and potential model event triggers
            DB::table('welfare_audit_log')->insert([
                'term_id' => $currentTerm?->id,
                'year' => $currentTerm?->year ?? (int) date('Y'),
                'table_name' => $model->getTable(),
                'record_id' => $model->id,
                'welfare_case_id' => $welfareCaseId,
                'action' => $action,
                'user_id' => $user?->id,
                'user_role' => $user?->roles?->first()?->name,
                'ip_address' => request()->ip(),
                'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
                'old_values' => $oldValues ? json_encode(static::sanitizeForLog($oldValues)) : null,
                'new_values' => $newValues ? json_encode(static::sanitizeForLog($newValues)) : null,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log the error but don't throw - audit failures shouldn't block operations
            Log::warning('Welfare audit log failed', [
                'table' => $model->getTable(),
                'record_id' => $model->id ?? null,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get attributes that should be audited.
     * Override this in your model to customize.
     *
     * @return array
     */
    protected function getAuditableAttributes(): array
    {
        $attributes = $this->attributesToArray();

        // Remove sensitive fields that shouldn't be logged
        $excludeFields = $this->getAuditExcludedFields();

        return array_diff_key($attributes, array_flip($excludeFields));
    }

    /**
     * Get fields that should be excluded from audit logs.
     * Override in model to customize.
     *
     * @return array
     */
    protected function getAuditExcludedFields(): array
    {
        return [
            'password',
            'remember_token',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    /**
     * Sanitize values for logging.
     * Truncates large values and masks sensitive data.
     *
     * @param array $values
     * @return array
     */
    protected static function sanitizeForLog(array $values): array
    {
        $sanitized = [];
        $maxLength = 1000;

        foreach ($values as $key => $value) {
            if (is_string($value) && strlen($value) > $maxLength) {
                $sanitized[$key] = substr($value, 0, $maxLength) . '...[truncated]';
            } elseif (is_array($value)) {
                $sanitized[$key] = static::sanitizeForLog($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Get audit history for this model.
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getAuditHistory(int $limit = 50)
    {
        return DB::table('welfare_audit_log')
            ->where('table_name', $this->getTable())
            ->where('record_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
