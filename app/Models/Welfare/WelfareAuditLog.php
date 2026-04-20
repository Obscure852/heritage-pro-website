<?php

namespace App\Models\Welfare;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Welfare audit log model.
 *
 * Immutable audit trail for all welfare-related activities.
 * This model does NOT use SoftDeletes or Auditable traits
 * to maintain audit integrity.
 *
 * @property int $id
 * @property int|null $welfare_case_id
 * @property string $auditable_type
 * @property int $auditable_id
 * @property string $action
 * @property int|null $user_id
 * @property array|null $old_values
 * @property array|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $reason
 * @property \Carbon\Carbon $created_at
 */
class WelfareAuditLog extends Model
{
    use HasFactory;

    /**
     * Disable timestamps management - we only use created_at.
     */
    public $timestamps = false;

    protected $table = 'welfare_audit_log';

    protected $fillable = [
        'welfare_case_id',
        'auditable_type',
        'auditable_id',
        'action',
        'user_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    // Action constants
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_RESTORED = 'restored';
    public const ACTION_VIEWED = 'viewed';
    public const ACTION_EXPORTED = 'exported';
    public const ACTION_APPROVED = 'approved';
    public const ACTION_REJECTED = 'rejected';
    public const ACTION_ESCALATED = 'escalated';
    public const ACTION_ASSIGNED = 'assigned';
    public const ACTION_CLOSED = 'closed';
    public const ACTION_REOPENED = 'reopened';

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Auto-set created_at
        static::creating(function (self $model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function welfareCase()
    {
        return $this->belongsTo(WelfareCase::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model.
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    // ==================== SCOPES ====================

    public function scopeForCase(Builder $query, int $caseId): Builder
    {
        return $query->where('welfare_case_id', $caseId);
    }

    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByModel(Builder $query, string $modelClass): Builder
    {
        return $query->where('auditable_type', $modelClass);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    public function scopeModifications(Builder $query): Builder
    {
        return $query->whereIn('action', [
            self::ACTION_CREATED,
            self::ACTION_UPDATED,
            self::ACTION_DELETED,
        ]);
    }

    public function scopeAccessLogs(Builder $query): Builder
    {
        return $query->whereIn('action', [
            self::ACTION_VIEWED,
            self::ACTION_EXPORTED,
        ]);
    }

    public function scopeWorkflowActions(Builder $query): Builder
    {
        return $query->whereIn('action', [
            self::ACTION_APPROVED,
            self::ACTION_REJECTED,
            self::ACTION_ESCALATED,
            self::ACTION_ASSIGNED,
            self::ACTION_CLOSED,
            self::ACTION_REOPENED,
        ]);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get the changed fields.
     */
    public function getChangedFieldsAttribute(): array
    {
        if (empty($this->old_values) && empty($this->new_values)) {
            return [];
        }

        $allKeys = array_unique(array_merge(
            array_keys($this->old_values ?? []),
            array_keys($this->new_values ?? [])
        ));

        return $allKeys;
    }

    /**
     * Check if a specific field was changed.
     */
    public function fieldWasChanged(string $field): bool
    {
        $oldValue = $this->old_values[$field] ?? null;
        $newValue = $this->new_values[$field] ?? null;

        return $oldValue !== $newValue;
    }

    /**
     * Get the old value for a field.
     */
    public function getOldValue(string $field)
    {
        return $this->old_values[$field] ?? null;
    }

    /**
     * Get the new value for a field.
     */
    public function getNewValue(string $field)
    {
        return $this->new_values[$field] ?? null;
    }

    /**
     * Get human-readable action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'Created',
            self::ACTION_UPDATED => 'Updated',
            self::ACTION_DELETED => 'Deleted',
            self::ACTION_RESTORED => 'Restored',
            self::ACTION_VIEWED => 'Viewed',
            self::ACTION_EXPORTED => 'Exported',
            self::ACTION_APPROVED => 'Approved',
            self::ACTION_REJECTED => 'Rejected',
            self::ACTION_ESCALATED => 'Escalated',
            self::ACTION_ASSIGNED => 'Assigned',
            self::ACTION_CLOSED => 'Closed',
            self::ACTION_REOPENED => 'Reopened',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get action badge color for UI (Bootstrap 5 color classes).
     */
    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'success',
            self::ACTION_UPDATED => 'primary',
            self::ACTION_DELETED => 'danger',
            self::ACTION_RESTORED => 'info',
            self::ACTION_VIEWED => 'secondary',
            self::ACTION_EXPORTED => 'warning',
            self::ACTION_APPROVED => 'success',
            self::ACTION_REJECTED => 'danger',
            self::ACTION_ESCALATED => 'warning',
            self::ACTION_ASSIGNED => 'primary',
            self::ACTION_CLOSED => 'secondary',
            self::ACTION_REOPENED => 'info',
            default => 'secondary',
        };
    }

    /**
     * Get the model name in human-readable format.
     */
    public function getModelNameAttribute(): string
    {
        if (empty($this->auditable_type)) {
            return 'Unknown';
        }

        $className = class_basename($this->auditable_type);

        // Convert CamelCase to words
        return preg_replace('/(?<!^)[A-Z]/', ' $0', $className);
    }

    /**
     * Create an audit log entry.
     *
     * @param Model $model The model being audited
     * @param string $action The action performed
     * @param array|null $oldValues Previous values
     * @param array|null $newValues New values
     * @param string|null $reason Optional reason for the action
     * @return static
     */
    public static function log(
        Model $model,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $reason = null
    ): self {
        $caseId = null;

        // Try to get welfare_case_id from the model
        if ($model instanceof WelfareCase) {
            $caseId = $model->id;
        } elseif (isset($model->welfare_case_id)) {
            $caseId = $model->welfare_case_id;
        }

        return self::create([
            'welfare_case_id' => $caseId,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'user_id' => auth()->id(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'reason' => $reason,
        ]);
    }
}
