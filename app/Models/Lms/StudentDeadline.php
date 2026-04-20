<?php

namespace App\Models\Lms;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDeadline extends Model {
    protected $table = 'lms_student_deadlines';

    protected $fillable = [
        'deadline_id',
        'student_id',
        'extended_due_date',
        'extension_reason',
        'extended_by',
        'is_completed',
        'completed_at',
        'reminder_sent',
    ];

    protected $casts = [
        'extended_due_date' => 'datetime',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'reminder_sent' => 'boolean',
    ];

    public function deadline(): BelongsTo {
        return $this->belongsTo(Deadline::class);
    }

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function extendedByUser(): BelongsTo {
        return $this->belongsTo(User::class, 'extended_by');
    }

    public function scopeIncomplete($query) {
        return $query->where('is_completed', false);
    }

    public function scopeCompleted($query) {
        return $query->where('is_completed', true);
    }

    public function markComplete(): void {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function grantExtension(\Carbon\Carbon $newDueDate, int $extendedBy, ?string $reason = null): void {
        $this->update([
            'extended_due_date' => $newDueDate,
            'extended_by' => $extendedBy,
            'extension_reason' => $reason,
        ]);
    }

    public function getEffectiveDueDateAttribute(): \Carbon\Carbon {
        return $this->extended_due_date ?? $this->deadline->due_date;
    }

    public function getIsOverdueAttribute(): bool {
        if ($this->is_completed) {
            return false;
        }

        return now()->gt($this->effective_due_date);
    }

    public function getDaysUntilDueAttribute(): int {
        return now()->diffInDays($this->effective_due_date, false);
    }
}
