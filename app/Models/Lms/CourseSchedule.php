<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CourseSchedule extends Model {
    protected $table = 'lms_course_schedules';

    protected $fillable = [
        'course_id',
        'title',
        'day_of_week',
        'start_time',
        'end_time',
        'location',
        'meeting_url',
        'instructor_id',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

    public static array $daysOfWeek = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function scopeActive($query) {
        return $query->where(function ($q) {
            $q->whereNull('effective_from')
              ->orWhere('effective_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('effective_until')
              ->orWhere('effective_until', '>=', now());
        });
    }

    public function getDayNameAttribute(): string {
        return self::$daysOfWeek[$this->day_of_week] ?? 'Unknown';
    }

    public function getFormattedTimeRangeAttribute(): string {
        return date('g:i A', strtotime($this->start_time)) . ' - ' .
               date('g:i A', strtotime($this->end_time));
    }

    public function getNextOccurrence(): ?Carbon {
        $today = Carbon::today();
        $currentDayOfWeek = $today->dayOfWeek;

        $daysUntilNext = ($this->day_of_week - $currentDayOfWeek + 7) % 7;
        if ($daysUntilNext === 0 && Carbon::parse($this->start_time)->isPast()) {
            $daysUntilNext = 7;
        }

        $nextDate = $today->copy()->addDays($daysUntilNext);

        // Check if within effective date range
        if ($this->effective_until && $nextDate->gt($this->effective_until)) {
            return null;
        }
        if ($this->effective_from && $nextDate->lt($this->effective_from)) {
            return null;
        }

        return Carbon::parse($nextDate->toDateString() . ' ' . $this->start_time);
    }

    public function toCalendarEvent(Carbon $date): array {
        return [
            'title' => $this->title ?: ($this->course->title . ' - Class'),
            'start' => Carbon::parse($date->toDateString() . ' ' . $this->start_time)->toIso8601String(),
            'end' => Carbon::parse($date->toDateString() . ' ' . $this->end_time)->toIso8601String(),
            'color' => CalendarEvent::$colors['class'],
            'extendedProps' => [
                'type' => 'class',
                'location' => $this->location,
                'meeting_url' => $this->meeting_url,
                'instructor' => $this->instructor?->name,
                'course_id' => $this->course_id,
            ],
        ];
    }
}
