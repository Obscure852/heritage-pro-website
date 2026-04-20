<?php

namespace App\Models\Welfare;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Lookup model for welfare case types.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property int $confidentiality_level
 * @property bool $requires_approval
 * @property string|null $approval_role
 * @property string|null $icon
 * @property string|null $color
 * @property bool $active
 */
class WelfareType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'confidentiality_level',
        'requires_approval',
        'approval_role',
        'icon',
        'color',
        'active',
    ];

    protected $casts = [
        'confidentiality_level' => 'integer',
        'requires_approval' => 'boolean',
        'active' => 'boolean',
    ];

    // Type code constants
    public const COUNSELING = 'COUNSEL';
    public const DISCIPLINARY = 'DISCIP';
    public const SAFEGUARDING = 'SAFEGUARD';
    public const HEALTH = 'HEALTH';
    public const BULLYING = 'BULLY';
    public const FINANCIAL = 'FINANCE';
    public const INTERVENTION = 'INTERVENE';
    public const PARENT_COMM = 'PARENT_COMM';

    // Confidentiality level constants
    public const LEVEL_PUBLIC = 1;
    public const LEVEL_RESTRICTED = 2;
    public const LEVEL_CONFIDENTIAL = 3;
    public const LEVEL_HIGHLY_CONFIDENTIAL = 4;

    /**
     * Get all welfare cases of this type.
     */
    public function cases()
    {
        return $this->hasMany(WelfareCase::class);
    }

    /**
     * Scope to active types only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope to find by code.
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', $code);
    }

    /**
     * Scope to types requiring approval.
     */
    public function scopeRequiringApproval(Builder $query): Builder
    {
        return $query->where('requires_approval', true);
    }

    /**
     * Scope by confidentiality level.
     */
    public function scopeByConfidentialityLevel(Builder $query, int $level): Builder
    {
        return $query->where('confidentiality_level', $level);
    }

    /**
     * Scope to highly confidential types (Level 4).
     */
    public function scopeHighlyConfidential(Builder $query): Builder
    {
        return $query->where('confidentiality_level', self::LEVEL_HIGHLY_CONFIDENTIAL);
    }

    /**
     * Check if this type is highly confidential (Level 4).
     */
    public function isHighlyConfidential(): bool
    {
        return $this->confidentiality_level === self::LEVEL_HIGHLY_CONFIDENTIAL;
    }

    /**
     * Check if this type requires approval.
     */
    public function requiresApproval(): bool
    {
        return $this->requires_approval;
    }

    /**
     * Get the badge color for UI display.
     */
    public function getBadgeColorAttribute(): string
    {
        return $this->color ?? 'gray';
    }

    /**
     * Get confidentiality level label.
     */
    public function getConfidentialityLabelAttribute(): string
    {
        return match ($this->confidentiality_level) {
            self::LEVEL_PUBLIC => 'Public',
            self::LEVEL_RESTRICTED => 'Restricted',
            self::LEVEL_CONFIDENTIAL => 'Confidential',
            self::LEVEL_HIGHLY_CONFIDENTIAL => 'Highly Confidential',
            default => 'Unknown',
        };
    }
}
