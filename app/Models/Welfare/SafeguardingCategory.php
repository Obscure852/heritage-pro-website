<?php

namespace App\Models\Welfare;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Lookup model for safeguarding concern categories.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property string|null $guidance_notes
 * @property bool $immediate_action_required
 * @property bool $notify_authorities
 * @property bool $active
 */
class SafeguardingCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'guidance_notes',
        'immediate_action_required',
        'notify_authorities',
        'active',
    ];

    protected $casts = [
        'immediate_action_required' => 'boolean',
        'notify_authorities' => 'boolean',
        'active' => 'boolean',
    ];

    // Category code constants
    public const PHYSICAL_ABUSE = 'PHYS_ABUSE';
    public const EMOTIONAL_ABUSE = 'EMOT_ABUSE';
    public const SEXUAL_ABUSE = 'SEX_ABUSE';
    public const NEGLECT = 'NEGLECT';
    public const SELF_HARM = 'SELF_HARM';
    public const SUICIDAL_IDEATION = 'SUICIDE';

    /**
     * Get all safeguarding concerns of this category.
     */
    public function concerns()
    {
        return $this->hasMany(SafeguardingConcern::class, 'category_id');
    }

    /**
     * Scope to active categories only.
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
     * Scope to categories requiring immediate action.
     */
    public function scopeRequiringImmediateAction(Builder $query): Builder
    {
        return $query->where('immediate_action_required', true);
    }

    /**
     * Scope to categories requiring authority notification.
     */
    public function scopeRequiringAuthorityNotification(Builder $query): Builder
    {
        return $query->where('notify_authorities', true);
    }

    /**
     * Check if this category requires immediate action.
     */
    public function requiresImmediateAction(): bool
    {
        return $this->immediate_action_required;
    }

    /**
     * Check if this category requires notifying authorities.
     */
    public function requiresAuthorityNotification(): bool
    {
        return $this->notify_authorities;
    }

    /**
     * Get urgency level based on flags.
     */
    public function getUrgencyLevelAttribute(): string
    {
        if ($this->immediate_action_required && $this->notify_authorities) {
            return 'critical';
        }
        if ($this->immediate_action_required || $this->notify_authorities) {
            return 'high';
        }
        return 'standard';
    }
}
