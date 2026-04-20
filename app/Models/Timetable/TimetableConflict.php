<?php

namespace App\Models\Timetable;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Timetable conflict model.
 *
 * Represents a detected scheduling conflict within a timetable.
 *
 * @property int $id
 * @property int $timetable_id
 * @property int|null $slot_id
 * @property string $type
 * @property string|null $constraint_type
 * @property string $description
 * @property \Carbon\Carbon|null $resolved_at
 * @property int|null $resolved_by
 * @property \Carbon\Carbon $created_at
 */
class TimetableConflict extends Model {
    use HasFactory;

    public $timestamps = false;

    // ==================== ATTRIBUTES ====================

    protected $fillable = [
        'timetable_id',
        'slot_id',
        'type',
        'constraint_type',
        'description',
        'resolved_at',
        'resolved_by',
        'created_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void {
        parent::boot();

        static::creating(function (self $model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function timetable(): BelongsTo {
        return $this->belongsTo(Timetable::class);
    }

    public function slot(): BelongsTo {
        return $this->belongsTo(TimetableSlot::class, 'slot_id');
    }

    public function resolvedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // ==================== SCOPES ====================

    public function scopeUnresolved($query) {
        return $query->whereNull('resolved_at');
    }

    public function scopeHard($query) {
        return $query->where('type', 'hard');
    }
}
