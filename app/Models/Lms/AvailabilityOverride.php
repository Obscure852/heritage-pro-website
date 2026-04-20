<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityOverride extends Model {
    protected $table = 'lms_availability_overrides';

    protected $fillable = [
        'schedule_id',
        'date',
        'is_available',
        'start_time',
        'end_time',
        'reason',
    ];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function schedule(): BelongsTo {
        return $this->belongsTo(AvailabilitySchedule::class, 'schedule_id');
    }

    public function getStatusTextAttribute(): string {
        if (!$this->is_available) {
            return 'Blocked' . ($this->reason ? ': ' . $this->reason : '');
        }

        if ($this->start_time && $this->end_time) {
            return 'Available: ' . $this->formatted_time_range;
        }

        return 'Available (custom hours)';
    }

    public function getFormattedTimeRangeAttribute(): string {
        if (!$this->start_time || !$this->end_time) {
            return '';
        }

        return date('g:i A', strtotime($this->start_time)) . ' - ' .
               date('g:i A', strtotime($this->end_time));
    }
}
