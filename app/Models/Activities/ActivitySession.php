<?php

namespace App\Models\Activities;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivitySession extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_SCHEDULED = 'scheduled';
    public const TYPE_MANUAL = 'manual';

    public const STATUS_PLANNED = 'planned';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_POSTPONED = 'postponed';

    protected $fillable = [
        'activity_id',
        'activity_schedule_id',
        'session_type',
        'session_date',
        'start_datetime',
        'end_datetime',
        'location',
        'status',
        'attendance_locked',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'attendance_locked' => 'boolean',
    ];

    public static function sessionTypes(): array
    {
        return [
            self::TYPE_SCHEDULED => 'Scheduled',
            self::TYPE_MANUAL => 'Manual',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PLANNED => 'Planned',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_POSTPONED => 'Postponed',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ActivitySchedule::class, 'activity_schedule_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(ActivitySessionAttendance::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeUnlocked($query)
    {
        return $query->where('attendance_locked', false);
    }

    protected function sessionDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value) : null,
            set: fn ($value) => $value ? Carbon::parse($value)->toDateString() : null,
        );
    }
}
