<?php

namespace App\Models\Welfare;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Lookup model for health incident types.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $category
 * @property string $severity
 * @property bool $requires_parent_notification
 * @property bool $active
 */
class HealthIncidentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'category',
        'severity',
        'requires_parent_notification',
        'active',
    ];

    protected $casts = [
        'requires_parent_notification' => 'boolean',
        'active' => 'boolean',
    ];

    // Category constants
    public const CATEGORY_ILLNESS = 'illness';
    public const CATEGORY_INJURY = 'injury';
    public const CATEGORY_MENTAL_HEALTH = 'mental_health';
    public const CATEGORY_MEDICATION = 'medication';
    public const CATEGORY_EMERGENCY = 'emergency';
    public const CATEGORY_OTHER = 'other';

    // Severity constants
    public const SEVERITY_MINOR = 'minor';
    public const SEVERITY_MODERATE = 'moderate';
    public const SEVERITY_SERIOUS = 'serious';
    public const SEVERITY_EMERGENCY = 'emergency';

    /**
     * Get all health incidents of this type.
     */
    public function incidents()
    {
        return $this->hasMany(HealthIncident::class, 'incident_type_id');
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
     * Scope by category.
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by severity.
     */
    public function scopeBySeverity(Builder $query, string $severity): Builder
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope to emergency types.
     */
    public function scopeEmergency(Builder $query): Builder
    {
        return $query->where('severity', self::SEVERITY_EMERGENCY);
    }

    /**
     * Check if this type is an emergency.
     */
    public function isEmergency(): bool
    {
        return $this->severity === self::SEVERITY_EMERGENCY;
    }

    /**
     * Check if parent notification is required.
     */
    public function requiresParentNotification(): bool
    {
        return $this->requires_parent_notification;
    }

    /**
     * Get severity badge color.
     */
    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            self::SEVERITY_MINOR => 'green',
            self::SEVERITY_MODERATE => 'yellow',
            self::SEVERITY_SERIOUS => 'orange',
            self::SEVERITY_EMERGENCY => 'red',
            default => 'gray',
        };
    }
}
