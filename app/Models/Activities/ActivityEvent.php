<?php

namespace App\Models\Activities;

use App\Models\User;
use App\Services\Activities\ActivitySettingsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityEvent extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_FIXTURE = 'fixture';
    public const TYPE_SHOWCASE = 'showcase';
    public const TYPE_COMPETITION = 'competition';
    public const TYPE_WORKSHOP = 'workshop';
    public const TYPE_EXHIBITION = 'exhibition';
    public const TYPE_OTHER = 'other';

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_POSTPONED = 'postponed';
    public const STATUS_CANCELLED = 'cancelled';

    public const CALENDAR_NOT_PUBLISHED = 'not_published';
    public const CALENDAR_HELD_LOCALLY = 'held_locally';

    protected $fillable = [
        'activity_id',
        'title',
        'event_type',
        'description',
        'start_datetime',
        'end_datetime',
        'location',
        'opponent_or_partner_name',
        'house_linked',
        'publish_to_calendar',
        'calendar_sync_status',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'house_linked' => 'boolean',
        'publish_to_calendar' => 'boolean',
    ];

    public static function defaultEventTypes(): array
    {
        return [
            self::TYPE_FIXTURE => 'Fixture',
            self::TYPE_SHOWCASE => 'Showcase',
            self::TYPE_COMPETITION => 'Competition',
            self::TYPE_WORKSHOP => 'Workshop',
            self::TYPE_EXHIBITION => 'Exhibition',
            self::TYPE_OTHER => 'Other',
        ];
    }

    public static function eventTypes(): array
    {
        return app(ActivitySettingsService::class)->eventTypeLabels();
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_POSTPONED => 'Postponed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function calendarStatuses(): array
    {
        return [
            self::CALENDAR_NOT_PUBLISHED => 'Not Published',
            self::CALENDAR_HELD_LOCALLY => 'Held Locally',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ActivityResult::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
