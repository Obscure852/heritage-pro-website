<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class AvailabilitySchedule extends Model {
    protected $table = 'lms_availability_schedules';

    protected $fillable = [
        'user_id',
        'course_id',
        'title',
        'description',
        'location',
        'meeting_url',
        'slot_duration',
        'buffer_time',
        'max_bookings_per_slot',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function windows(): HasMany {
        return $this->hasMany(AvailabilityWindow::class, 'schedule_id');
    }

    public function overrides(): HasMany {
        return $this->hasMany(AvailabilityOverride::class, 'schedule_id');
    }

    public function appointments(): HasMany {
        return $this->hasMany(Appointment::class, 'schedule_id');
    }

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public function getAvailableSlotsForDate(Carbon $date): array {
        // Check for override on this date
        $override = $this->overrides()->where('date', $date->toDateString())->first();

        if ($override && !$override->is_available) {
            return []; // Blocked day
        }

        // Get windows for this day of week
        $dayOfWeek = $date->dayOfWeek;
        $windows = $this->windows()->where('day_of_week', $dayOfWeek)->get();

        if ($windows->isEmpty() && !$override) {
            return [];
        }

        // Use override times if available
        if ($override && $override->start_time && $override->end_time) {
            $windows = collect([
                (object) [
                    'start_time' => $override->start_time,
                    'end_time' => $override->end_time,
                ]
            ]);
        }

        $slots = [];
        $existingBookings = $this->appointments()
            ->whereDate('start_time', $date)
            ->where('status', '!=', 'cancelled')
            ->get();

        foreach ($windows as $window) {
            $startTime = Carbon::parse($date->toDateString() . ' ' . $window->start_time);
            $endTime = Carbon::parse($date->toDateString() . ' ' . $window->end_time);

            while ($startTime->copy()->addMinutes($this->slot_duration) <= $endTime) {
                $slotEnd = $startTime->copy()->addMinutes($this->slot_duration);

                // Count existing bookings for this slot
                $bookingsCount = $existingBookings->filter(function ($booking) use ($startTime, $slotEnd) {
                    return $booking->start_time < $slotEnd && $booking->end_time > $startTime;
                })->count();

                if ($bookingsCount < $this->max_bookings_per_slot) {
                    $slots[] = [
                        'start' => $startTime->copy(),
                        'end' => $slotEnd->copy(),
                        'available' => $this->max_bookings_per_slot - $bookingsCount,
                    ];
                }

                $startTime->addMinutes($this->slot_duration + $this->buffer_time);
            }
        }

        return $slots;
    }
}
