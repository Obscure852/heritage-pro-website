<?php

namespace App\Models\Timetable;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Timetable audit log model.
 *
 * Tracks all changes made to a timetable for audit purposes.
 *
 * @property int $id
 * @property int $timetable_id
 * @property int $user_id
 * @property string $action
 * @property string|null $description
 * @property array|null $old_values
 * @property array|null $new_values
 * @property \Carbon\Carbon $created_at
 */
class TimetableAuditLog extends Model {
    public $timestamps = false;

    // ==================== ATTRIBUTES ====================

    protected $fillable = [
        'timetable_id',
        'user_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function timetable(): BelongsTo {
        return $this->belongsTo(Timetable::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    // ==================== STATIC HELPERS ====================

    /**
     * Log an audit entry for a timetable.
     */
    public static function log(
        Timetable $timetable,
        string $action,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): self {
        $resolvedUserId = $userId ?? Auth::id() ?? $timetable->created_by;

        return self::create([
            'timetable_id' => $timetable->id,
            'user_id' => $resolvedUserId,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'created_at' => now(),
        ]);
    }
}
