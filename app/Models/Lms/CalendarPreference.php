<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarPreference extends Model {
    protected $table = 'lms_calendar_preferences';

    protected $fillable = [
        'user_id',
        'default_view',
        'week_start',
        'working_hours',
        'hidden_event_types',
        'color_overrides',
        'timezone',
        'show_weekends',
    ];

    protected $casts = [
        'working_hours' => 'array',
        'hidden_event_types' => 'array',
        'color_overrides' => 'array',
        'show_weekends' => 'boolean',
    ];

    public static array $views = [
        'day' => 'Day View',
        'week' => 'Week View',
        'month' => 'Month View',
        'agenda' => 'Agenda View',
    ];

    public static array $weekStarts = [
        'sunday' => 'Sunday',
        'monday' => 'Monday',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public static function forUser(int $userId): self {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'default_view' => 'month',
                'week_start' => 'sunday',
                'timezone' => config('app.timezone'),
                'show_weekends' => true,
            ]
        );
    }

    public function getEventColor(string $type): string {
        if ($this->color_overrides && isset($this->color_overrides[$type])) {
            return $this->color_overrides[$type];
        }
        return CalendarEvent::$colors[$type] ?? '#6366f1';
    }

    public function isEventTypeHidden(string $type): bool {
        return in_array($type, $this->hidden_event_types ?? []);
    }
}
