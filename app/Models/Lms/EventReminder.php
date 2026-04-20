<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventReminder extends Model {
    protected $table = 'lms_event_reminders';

    protected $fillable = [
        'event_id',
        'user_id',
        'minutes_before',
        'method',
        'is_sent',
        'sent_at',
    ];

    protected $casts = [
        'is_sent' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public static array $methods = [
        'notification' => 'In-App Notification',
        'email' => 'Email',
        'both' => 'Both',
    ];

    public static array $presets = [
        5 => '5 minutes before',
        15 => '15 minutes before',
        30 => '30 minutes before',
        60 => '1 hour before',
        1440 => '1 day before',
        10080 => '1 week before',
    ];

    public function event(): BelongsTo {
        return $this->belongsTo(CalendarEvent::class, 'event_id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query) {
        return $query->where('is_sent', false);
    }

    public function scopeDue($query) {
        return $query->whereHas('event', function ($q) {
            $q->whereRaw('start_date <= DATE_ADD(NOW(), INTERVAL minutes_before MINUTE)');
        })->where('is_sent', false);
    }

    public function markSent(): void {
        $this->update([
            'is_sent' => true,
            'sent_at' => now(),
        ]);
    }
}
