<?php

namespace App\Models\Welfare;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Lookup model for disciplinary actions.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $severity_level
 * @property bool $requires_approval
 * @property bool $requires_parent_notification
 * @property int|null $max_duration_days
 * @property string $school_level
 * @property bool $active
 */
class DisciplinaryAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'severity_level',
        'requires_approval',
        'requires_parent_notification',
        'max_duration_days',
        'school_level',
        'active',
    ];

    protected $casts = [
        'severity_level' => 'integer',
        'requires_approval' => 'boolean',
        'requires_parent_notification' => 'boolean',
        'max_duration_days' => 'integer',
        'active' => 'boolean',
    ];

    // Action code constants
    public const VERBAL_WARNING = 'VERBAL_WARN';
    public const WRITTEN_WARNING = 'WRITTEN_WARN';
    public const DETENTION = 'DETENTION';
    public const IN_SCHOOL_SUSPENSION = 'ISS';
    public const OUT_OF_SCHOOL_SUSPENSION = 'OSS';
    public const EXTENDED_SUSPENSION = 'EXT_SUSP';
    public const EXPULSION_RECOMMENDATION = 'EXPEL_REC';

    /**
     * Get all disciplinary records using this action.
     */
    public function disciplinaryRecords()
    {
        return $this->hasMany(DisciplinaryRecord::class, 'action_id');
    }

    /**
     * Get incident types that default to this action.
     */
    public function defaultForIncidentTypes()
    {
        return $this->hasMany(DisciplinaryIncidentType::class, 'default_action_id');
    }

    /**
     * Scope to active actions only.
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
     * Scope by school level.
     */
    public function scopeForSchoolLevel(Builder $query, string $level): Builder
    {
        return $query->whereIn('school_level', [$level, 'all']);
    }

    /**
     * Scope by severity level.
     */
    public function scopeBySeverity(Builder $query, int $level): Builder
    {
        return $query->where('severity_level', $level);
    }

    /**
     * Check if this action is a suspension.
     */
    public function isSuspension(): bool
    {
        return in_array($this->code, [
            self::IN_SCHOOL_SUSPENSION,
            self::OUT_OF_SCHOOL_SUSPENSION,
            self::EXTENDED_SUSPENSION,
        ]);
    }

    /**
     * Check if this action requires approval.
     */
    public function requiresApproval(): bool
    {
        return $this->requires_approval;
    }

    /**
     * Get severity label.
     */
    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity_level) {
            1 => 'Minor',
            2 => 'Low',
            3 => 'Moderate',
            4 => 'Serious',
            5 => 'Severe',
            default => 'Unknown',
        };
    }
}
