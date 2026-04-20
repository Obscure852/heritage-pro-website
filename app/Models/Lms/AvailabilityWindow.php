<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityWindow extends Model {
    protected $table = 'lms_availability_windows';

    protected $fillable = [
        'schedule_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
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

    public function schedule(): BelongsTo {
        return $this->belongsTo(AvailabilitySchedule::class, 'schedule_id');
    }

    public function getDayNameAttribute(): string {
        return self::$daysOfWeek[$this->day_of_week] ?? 'Unknown';
    }

    public function getFormattedTimeRangeAttribute(): string {
        return date('g:i A', strtotime($this->start_time)) . ' - ' .
               date('g:i A', strtotime($this->end_time));
    }
}
