<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningPathProgress extends Model {
    protected $table = 'lms_learning_path_progress';

    protected $fillable = [
        'enrollment_id',
        'path_course_id',
        'course_enrollment_id',
        'status',
        'progress_percentage',
        'grade',
        'unlocked_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'grade' => 'decimal:2',
        'unlocked_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const STATUS_LOCKED = 'locked';
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    // Relationships
    public function enrollment(): BelongsTo {
        return $this->belongsTo(LearningPathEnrollment::class, 'enrollment_id');
    }

    public function pathCourse(): BelongsTo {
        return $this->belongsTo(LearningPathCourse::class, 'path_course_id');
    }

    public function courseEnrollment(): BelongsTo {
        return $this->belongsTo(Enrollment::class, 'course_enrollment_id');
    }

    // Scopes
    public function scopeLocked($query) {
        return $query->where('status', self::STATUS_LOCKED);
    }

    public function scopeAvailable($query) {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeInProgress($query) {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query) {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    // Accessors
    public function getStatusBadgeAttribute(): string {
        return match ($this->status) {
            self::STATUS_LOCKED => 'bg-secondary',
            self::STATUS_AVAILABLE => 'bg-info',
            self::STATUS_IN_PROGRESS => 'bg-primary',
            self::STATUS_COMPLETED => 'bg-success',
            default => 'bg-secondary',
        };
    }

    public function getStatusIconAttribute(): string {
        return match ($this->status) {
            self::STATUS_LOCKED => 'fas fa-lock',
            self::STATUS_AVAILABLE => 'fas fa-unlock',
            self::STATUS_IN_PROGRESS => 'fas fa-spinner fa-spin',
            self::STATUS_COMPLETED => 'fas fa-check-circle',
            default => 'fas fa-circle',
        };
    }

    // Methods
    public function unlock(): void {
        if ($this->status === self::STATUS_LOCKED) {
            $this->update([
                'status' => self::STATUS_AVAILABLE,
                'unlocked_at' => now(),
            ]);
        }
    }

    public function start(Enrollment $courseEnrollment): void {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'course_enrollment_id' => $courseEnrollment->id,
            'started_at' => now(),
        ]);

        $this->enrollment->start();
    }

    public function complete(?float $grade = null): void {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'grade' => $grade,
            'progress_percentage' => 100,
            'completed_at' => now(),
        ]);

        // Recalculate path progress
        $this->enrollment->recalculateProgress();

        // Unlock dependent courses
        $this->enrollment->unlockNextCourses();
    }

    public function syncWithCourseEnrollment(): void {
        if (!$this->courseEnrollment) return;

        $courseEnrollment = $this->courseEnrollment;

        $this->update([
            'progress_percentage' => $courseEnrollment->progress_percentage,
        ]);

        if ($courseEnrollment->status === 'completed' && $this->status !== self::STATUS_COMPLETED) {
            $this->complete($courseEnrollment->grade);
        }
    }

    public function isAccessible(): bool {
        return in_array($this->status, [self::STATUS_AVAILABLE, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED]);
    }
}
