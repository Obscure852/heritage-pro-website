<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Deadline extends Model {
    protected $table = 'lms_deadlines';

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'type',
        'deadlineable_type',
        'deadlineable_id',
        'due_date',
        'grace_period_minutes',
        'allows_late',
        'late_penalty_percent',
        'is_active',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'allows_late' => 'boolean',
        'late_penalty_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public static array $types = [
        'assignment' => 'Assignment',
        'quiz' => 'Quiz',
        'discussion' => 'Discussion',
        'custom' => 'Custom',
    ];

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function deadlineable(): MorphTo {
        return $this->morphTo();
    }

    public function studentDeadlines(): HasMany {
        return $this->hasMany(StudentDeadline::class);
    }

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public function scopeUpcoming($query) {
        return $query->where('due_date', '>', now())
                     ->orderBy('due_date');
    }

    public function scopePast($query) {
        return $query->where('due_date', '<', now())
                     ->orderByDesc('due_date');
    }

    public function getEffectiveDueDateForStudent(int $studentId): ?\Carbon\Carbon {
        $studentDeadline = $this->studentDeadlines()
            ->where('student_id', $studentId)
            ->first();

        if ($studentDeadline?->extended_due_date) {
            return $studentDeadline->extended_due_date;
        }

        return $this->due_date;
    }

    public function isLateSubmission(\Carbon\Carbon $submittedAt, int $studentId): bool {
        $effectiveDueDate = $this->getEffectiveDueDateForStudent($studentId);

        if ($this->grace_period_minutes > 0) {
            $effectiveDueDate = $effectiveDueDate->copy()->addMinutes($this->grace_period_minutes);
        }

        return $submittedAt->gt($effectiveDueDate);
    }

    public function getLatePenalty(\Carbon\Carbon $submittedAt, int $studentId): float {
        if (!$this->allows_late || $this->late_penalty_percent <= 0) {
            return 0;
        }

        if (!$this->isLateSubmission($submittedAt, $studentId)) {
            return 0;
        }

        return $this->late_penalty_percent;
    }

    public function toCalendarEvent(): array {
        return [
            'id' => 'deadline_' . $this->id,
            'title' => $this->title,
            'start' => $this->due_date->toIso8601String(),
            'allDay' => false,
            'color' => CalendarEvent::$colors[$this->type] ?? CalendarEvent::$colors['deadline'],
            'extendedProps' => [
                'type' => $this->type,
                'description' => $this->description,
                'course_id' => $this->course_id,
                'allows_late' => $this->allows_late,
            ],
        ];
    }
}
