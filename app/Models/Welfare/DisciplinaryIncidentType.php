<?php

namespace App\Models\Welfare;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Lookup model for disciplinary incident types.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $severity
 * @property int|null $default_action_id
 * @property bool $requires_approval
 * @property string $school_level
 * @property bool $active
 */
class DisciplinaryIncidentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'severity',
        'default_action_id',
        'requires_approval',
        'school_level',
        'active',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'active' => 'boolean',
    ];

    // Severity constants
    public const SEVERITY_MINOR = 'minor';
    public const SEVERITY_MODERATE = 'moderate';
    public const SEVERITY_MAJOR = 'major';
    public const SEVERITY_SEVERE = 'severe';

    /**
     * Get the default action for this incident type.
     */
    public function defaultAction()
    {
        return $this->belongsTo(DisciplinaryAction::class, 'default_action_id');
    }

    /**
     * Get all disciplinary records of this type.
     */
    public function disciplinaryRecords()
    {
        return $this->hasMany(DisciplinaryRecord::class, 'incident_type_id');
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
     * Scope by severity.
     */
    public function scopeBySeverity(Builder $query, string $severity): Builder
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope by school level.
     */
    public function scopeForSchoolLevel(Builder $query, string $level): Builder
    {
        return $query->whereIn('school_level', [$level, 'all']);
    }

    /**
     * Check if this incident type requires approval.
     */
    public function requiresApproval(): bool
    {
        return $this->requires_approval;
    }

    /**
     * Get severity badge color.
     */
    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            self::SEVERITY_MINOR => 'yellow',
            self::SEVERITY_MODERATE => 'orange',
            self::SEVERITY_MAJOR => 'red',
            self::SEVERITY_SEVERE => 'purple',
            default => 'gray',
        };
    }
}
