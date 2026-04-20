<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for per-user document storage quota management.
 *
 * Default quota is 500MB (524288000 bytes) with configurable warning threshold.
 * Supports unlimited quota override for administrators (DOC-07 foundation).
 *
 * @property int $id
 * @property int $user_id
 * @property int $quota_bytes
 * @property int $used_bytes
 * @property int $warning_threshold_percent
 * @property \Carbon\Carbon|null $warning_sent_at
 * @property bool $is_unlimited
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $user
 * @property-read float $usage_percent
 * @property-read bool $is_warning
 */
class UserDocumentQuota extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'quota_bytes',
        'used_bytes',
        'warning_threshold_percent',
        'warning_sent_at',
        'is_unlimited',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quota_bytes' => 'integer',
        'used_bytes' => 'integer',
        'warning_threshold_percent' => 'integer',
        'is_unlimited' => 'boolean',
        'warning_sent_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the user this quota belongs to.
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    // ==================== ACCESSORS ====================

    /**
     * Get the usage percentage (used_bytes / quota_bytes * 100).
     *
     * Returns 0 if quota_bytes is 0 to avoid division by zero.
     *
     * @return float
     */
    public function getUsagePercentAttribute(): float {
        if ($this->quota_bytes === 0) {
            return 0.0;
        }

        return round(($this->used_bytes / $this->quota_bytes) * 100, 2);
    }

    /**
     * Check if usage has reached the warning threshold.
     *
     * @return bool
     */
    public function getIsWarningAttribute(): bool {
        return $this->usage_percent >= $this->warning_threshold_percent;
    }
}
