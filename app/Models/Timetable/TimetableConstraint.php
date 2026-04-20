<?php

namespace App\Models\Timetable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Timetable constraint model.
 *
 * Represents a scheduling constraint applied to a timetable.
 *
 * @property int $id
 * @property int $timetable_id
 * @property string $constraint_type
 * @property array $constraint_config
 * @property bool $is_hard
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class TimetableConstraint extends Model {
    use HasFactory;

    // ==================== TYPE CONSTANTS ====================

    public const TYPE_TEACHER_AVAILABILITY = 'teacher_availability';
    public const TYPE_TEACHER_PREFERENCE = 'teacher_preference';
    public const TYPE_ROOM_REQUIREMENT = 'room_requirement';
    public const TYPE_ROOM_CAPACITY = 'room_capacity';
    public const TYPE_SUBJECT_SPREAD = 'subject_spread';
    public const TYPE_CONSECUTIVE_LIMIT = 'consecutive_limit';
    public const TYPE_ELECTIVE_COUPLING = 'elective_coupling';
    public const TYPE_SUBJECT_PAIR = 'subject_pair';
    public const TYPE_PERIOD_RESTRICTION = 'period_restriction';
    public const TYPE_TEACHER_ROOM_ASSIGNMENT = 'teacher_room_assignment';

    // ==================== ATTRIBUTES ====================

    protected $fillable = [
        'timetable_id',
        'constraint_type',
        'constraint_config',
        'is_hard',
        'is_active',
    ];

    protected $casts = [
        'constraint_config' => 'array',
        'is_hard' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    public function timetable(): BelongsTo {
        return $this->belongsTo(Timetable::class);
    }

    // ==================== SCOPES ====================

    public function scopeHard($query) {
        return $query->where('is_hard', true);
    }

    public function scopeSoft($query) {
        return $query->where('is_hard', false);
    }

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type) {
        return $query->where('constraint_type', $type);
    }
}
