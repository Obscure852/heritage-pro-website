<?php

namespace App\Models\Lms;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model {
    protected $table = 'lms_appointments';

    protected $fillable = [
        'schedule_id',
        'student_id',
        'start_time',
        'end_time',
        'status',
        'student_notes',
        'instructor_notes',
        'meeting_url',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public static array $statuses = [
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled',
        'completed' => 'Completed',
        'no_show' => 'No Show',
    ];

    public function schedule(): BelongsTo {
        return $this->belongsTo(AvailabilitySchedule::class, 'schedule_id');
    }

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function cancelledByUser(): BelongsTo {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function scopeUpcoming($query) {
        return $query->where('start_time', '>', now())
                     ->where('status', 'confirmed')
                     ->orderBy('start_time');
    }

    public function scopeForStudent($query, int $studentId) {
        return $query->where('student_id', $studentId);
    }

    public function scopeConfirmed($query) {
        return $query->where('status', 'confirmed');
    }

    public function cancel(int $userId, ?string $reason = null): void {
        $this->update([
            'status' => 'cancelled',
            'cancelled_by' => $userId,
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);
    }

    public function complete(): void {
        $this->update(['status' => 'completed']);
    }

    public function markNoShow(): void {
        $this->update(['status' => 'no_show']);
    }

    public function getStatusColorAttribute(): string {
        return match($this->status) {
            'confirmed' => 'success',
            'completed' => 'info',
            'cancelled' => 'secondary',
            'no_show' => 'danger',
            default => 'secondary',
        };
    }

    public function getDurationMinutesAttribute(): int {
        return $this->start_time->diffInMinutes($this->end_time);
    }
}
