<?php

namespace App\Models\Timetable;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Timetable version model.
 *
 * Stores a JSON snapshot of all timetable slots at the moment of publishing,
 * enabling version history browsing and rollback.
 *
 * @property int $id
 * @property int $timetable_id
 * @property int $version_number
 * @property array $snapshot_data
 * @property int $slot_count
 * @property string|null $notes
 * @property \Carbon\Carbon $published_at
 * @property int $published_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class TimetableVersion extends Model {
    // ==================== ATTRIBUTES ====================

    protected $fillable = [
        'timetable_id',
        'version_number',
        'snapshot_data',
        'slot_count',
        'notes',
        'published_at',
        'published_by',
    ];

    protected $casts = [
        'snapshot_data' => 'array',
        'slot_count' => 'integer',
        'published_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function timetable(): BelongsTo {
        return $this->belongsTo(Timetable::class);
    }

    public function publisher(): BelongsTo {
        return $this->belongsTo(User::class, 'published_by');
    }
}
