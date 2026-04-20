<?php

namespace App\Models\Timetable;

use App\Models\KlassSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Timetable block allocation model.
 *
 * Defines how many singles, doubles, and triples a class-subject gets per timetable cycle.
 *
 * @property int $id
 * @property int $timetable_id
 * @property int $klass_subject_id
 * @property int $singles
 * @property int $doubles
 * @property int $triples
 * @property int $total_periods
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class TimetableBlockAllocation extends Model {
    use HasFactory;

    // ==================== ATTRIBUTES ====================

    protected $fillable = [
        'timetable_id',
        'klass_subject_id',
        'singles',
        'doubles',
        'triples',
    ];

    protected $casts = [
        'singles' => 'integer',
        'doubles' => 'integer',
        'triples' => 'integer',
        'total_periods' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    public function timetable(): BelongsTo {
        return $this->belongsTo(Timetable::class);
    }

    public function klassSubject(): BelongsTo {
        return $this->belongsTo(KlassSubject::class);
    }
}
