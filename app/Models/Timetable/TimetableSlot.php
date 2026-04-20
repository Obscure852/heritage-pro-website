<?php

namespace App\Models\Timetable;

use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Timetable slot model.
 *
 * Represents a single scheduled period in a timetable.
 *
 * @property int $id
 * @property int $timetable_id
 * @property int|null $klass_subject_id
 * @property int|null $optional_subject_id
 * @property int|null $teacher_id
 * @property int|null $venue_id
 * @property int|null $assistant_teacher_id
 * @property int $day_of_cycle
 * @property int $period_number
 * @property int $duration
 * @property bool $is_locked
 * @property string|null $block_id
 * @property string|null $coupling_group_key
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class TimetableSlot extends Model {
    use HasFactory;

    // ==================== ATTRIBUTES ====================

    protected $fillable = [
        'timetable_id',
        'klass_subject_id',
        'optional_subject_id',
        'teacher_id',
        'venue_id',
        'assistant_teacher_id',
        'day_of_cycle',
        'period_number',
        'duration',
        'is_locked',
        'block_id',
        'coupling_group_key',
    ];

    protected $casts = [
        'day_of_cycle' => 'integer',
        'period_number' => 'integer',
        'duration' => 'integer',
        'is_locked' => 'boolean',
        'teacher_id' => 'integer',
        'klass_subject_id' => 'integer',
        'optional_subject_id' => 'integer',
        'venue_id' => 'integer',
        'assistant_teacher_id' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    public function timetable(): BelongsTo {
        return $this->belongsTo(Timetable::class);
    }

    public function klassSubject(): BelongsTo {
        return $this->belongsTo(KlassSubject::class);
    }

    public function optionalSubject(): BelongsTo {
        return $this->belongsTo(OptionalSubject::class);
    }

    public function teacher(): BelongsTo {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function venue(): BelongsTo {
        return $this->belongsTo(Venue::class);
    }

    public function assistantTeacher(): BelongsTo {
        return $this->belongsTo(User::class, 'assistant_teacher_id');
    }

    // ==================== SCOPES ====================

    public function scopeForBlock(Builder $query, string $blockId): Builder {
        return $query->where('block_id', $blockId);
    }
}
