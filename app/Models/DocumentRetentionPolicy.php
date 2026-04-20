<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for automated document retention and cleanup rules.
 *
 * Conditions are stored as JSON for flexible matching criteria
 * (category, folder, age, status). Each policy can archive, delete,
 * or notify the owner when retention criteria are met.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property array $conditions
 * @property string $action
 * @property int $retention_days
 * @property int $grace_period_days
 * @property bool $is_active
 * @property \Carbon\Carbon|null $last_run_at
 * @property \Carbon\Carbon|null $next_run_at
 * @property int $created_by_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $createdBy
 */
class DocumentRetentionPolicy extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'conditions',
        'action',
        'retention_days',
        'grace_period_days',
        'is_active',
        'last_run_at',
        'next_run_at',
        'created_by_user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
        'retention_days' => 'integer',
        'grace_period_days' => 'integer',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    // ==================== ACTION CONSTANTS ====================

    /** Archive action — move documents to archived status. */
    const ACTION_ARCHIVE = 'archive';

    /** Delete action — permanently delete documents. */
    const ACTION_DELETE = 'delete';

    /** Notify owner action — send notification to document owner. */
    const ACTION_NOTIFY = 'notify_owner';

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the user who created this retention policy.
     */
    public function createdBy(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
