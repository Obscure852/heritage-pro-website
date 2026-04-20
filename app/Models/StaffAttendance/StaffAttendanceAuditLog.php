<?php

namespace App\Models\StaffAttendance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Model for staff attendance audit logs.
 *
 * Records all changes to staff attendance records for auditing purposes.
 * Uses polymorphic relationship to track changes across different attendance models.
 * Follows the LeaveAuditLog pattern.
 *
 * @property int $id
 * @property string $auditable_type
 * @property int $auditable_id
 * @property string $action
 * @property int|null $user_id
 * @property array|null $old_values
 * @property array|null $new_values
 * @property string|null $notes
 * @property string|null $ip_address
 * @property \Carbon\Carbon $created_at
 * @property-read User|null $user
 * @property-read Model $auditable
 * @property-read string $action_label
 */
class StaffAttendanceAuditLog extends Model
{
    /**
     * Disable Laravel's automatic timestamps.
     * We manage created_at manually.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'action',
        'user_id',
        'old_values',
        'new_values',
        'notes',
        'ip_address',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    // ==================== ACTION CONSTANTS ====================

    /**
     * Record created action.
     */
    const ACTION_CREATE = 'create';

    /**
     * Record updated action.
     */
    const ACTION_UPDATE = 'update';

    /**
     * Record deleted action.
     */
    const ACTION_DELETE = 'delete';

    /**
     * Device sync action.
     */
    const ACTION_SYNC = 'sync';

    /**
     * Event processed action.
     */
    const ACTION_PROCESS = 'process';

    /**
     * Sync retry action.
     */
    const ACTION_RETRY = 'retry';

    // ==================== STATIC METHODS ====================

    /**
     * Get all action types with their human-readable labels.
     *
     * @return array<string, string>
     */
    public static function actions(): array
    {
        return [
            self::ACTION_CREATE => 'Created',
            self::ACTION_UPDATE => 'Updated',
            self::ACTION_DELETE => 'Deleted',
            self::ACTION_SYNC => 'Synced',
            self::ACTION_PROCESS => 'Processed',
            self::ACTION_RETRY => 'Retried',
        ];
    }

    /**
     * Log an audit entry for a model.
     *
     * Creates a new audit log record with the current user's ID and IP address.
     * Should be called within a database transaction for consistency.
     *
     * @param Model $model The model being audited
     * @param string $action The action being performed (use ACTION_* constants)
     * @param array|null $oldValues The state before the action (null for create)
     * @param array|null $newValues The state after the action (null for delete)
     * @param string|null $notes Additional context or comments
     * @return self The created audit log entry
     */
    public static function log(
        Model $model,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $notes = null
    ): self {
        return self::create([
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'user_id' => Auth::id(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'notes' => $notes,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the user who performed the action.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model (polymorphic).
     *
     * @return MorphTo
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    // ==================== SCOPES ====================

    /**
     * Scope to filter by a specific model type and ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type The model class name
     * @param int $id The model ID
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForModel($query, string $type, int $id)
    {
        return $query->where('auditable_type', $type)
            ->where('auditable_id', $id);
    }

    /**
     * Scope to filter by user who performed the action.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by action type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get logs from recent days.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days Number of days to look back
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ==================== ACCESSORS ====================

    /**
     * Get the human-readable label for the action.
     *
     * @return string
     */
    public function getActionLabelAttribute(): string
    {
        return self::actions()[$this->action] ?? ucfirst($this->action);
    }
}
