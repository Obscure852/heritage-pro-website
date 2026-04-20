<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EventAttendee extends Model {
    protected $table = 'lms_event_attendees';

    protected $fillable = [
        'event_id',
        'attendee_type',
        'attendee_id',
        'status',
        'is_required',
        'responded_at',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'responded_at' => 'datetime',
    ];

    public static array $statuses = [
        'pending' => 'Pending',
        'accepted' => 'Accepted',
        'declined' => 'Declined',
        'tentative' => 'Tentative',
    ];

    public function event(): BelongsTo {
        return $this->belongsTo(CalendarEvent::class, 'event_id');
    }

    public function attendee(): MorphTo {
        return $this->morphTo();
    }

    public function respond(string $status): void {
        $this->update([
            'status' => $status,
            'responded_at' => now(),
        ]);
    }

    public function getStatusColorAttribute(): string {
        return match($this->status) {
            'accepted' => 'success',
            'declined' => 'danger',
            'tentative' => 'warning',
            default => 'secondary',
        };
    }
}
