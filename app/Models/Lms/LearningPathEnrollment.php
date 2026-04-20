<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPathEnrollment extends Model {
    protected $table = 'lms_learning_path_enrollments';

    protected $fillable = [
        'learning_path_id',
        'student_id',
        'status',
        'progress_percentage',
        'courses_completed',
        'total_courses',
        'enrolled_at',
        'started_at',
        'completed_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'metadata' => 'array',
        'enrolled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_EXPIRED = 'expired';

    // Relationships
    public function learningPath(): BelongsTo {
        return $this->belongsTo(LearningPath::class, 'learning_path_id');
    }

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function progress(): HasMany {
        return $this->hasMany(LearningPathProgress::class, 'enrollment_id');
    }

    public function milestoneCompletions(): HasMany {
        return $this->hasMany(LearningPathMilestoneCompletion::class, 'enrollment_id');
    }

    // Scopes
    public function scopeActive($query) {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCompleted($query) {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    // Methods
    public static function enroll(LearningPath $path, Student $student): self {
        $enrollment = self::create([
            'learning_path_id' => $path->id,
            'student_id' => $student->id,
            'status' => self::STATUS_ACTIVE,
            'total_courses' => $path->pathCourses()->count(),
            'enrolled_at' => now(),
        ]);

        // Initialize progress for each course
        foreach ($path->pathCourses as $pathCourse) {
            $status = $pathCourse->hasPrerequisites() ? 'locked' : 'available';

            LearningPathProgress::create([
                'enrollment_id' => $enrollment->id,
                'path_course_id' => $pathCourse->id,
                'status' => $status,
                'unlocked_at' => $status === 'available' ? now() : null,
            ]);
        }

        return $enrollment;
    }

    public function start(): void {
        if (!$this->started_at) {
            $this->update(['started_at' => now()]);
        }
    }

    public function complete(): void {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);
    }

    public function pause(): void {
        $this->update(['status' => self::STATUS_PAUSED]);
    }

    public function resume(): void {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function recalculateProgress(): void {
        $completed = $this->progress()->where('status', 'completed')->count();
        $total = $this->total_courses;

        $percentage = $total > 0 ? ($completed / $total) * 100 : 0;

        $this->update([
            'courses_completed' => $completed,
            'progress_percentage' => round($percentage, 2),
        ]);

        // Check if path is completed
        if ($completed >= $total && $this->status !== self::STATUS_COMPLETED) {
            $this->complete();
        }
    }

    public function getCurrentCourse(): ?LearningPathProgress {
        // Get first available or in-progress course
        return $this->progress()
            ->whereIn('status', ['available', 'in_progress'])
            ->orderBy('id')
            ->first();
    }

    public function getNextCourse(): ?LearningPathProgress {
        return $this->progress()
            ->where('status', 'available')
            ->orderBy('id')
            ->first();
    }

    public function unlockNextCourses(): void {
        foreach ($this->progress()->where('status', 'locked')->get() as $courseProgress) {
            if ($courseProgress->pathCourse->isUnlockedFor($this)) {
                $courseProgress->unlock();
            }
        }
    }

    public function isExpired(): bool {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getStatusBadgeAttribute(): string {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'bg-primary',
            self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_PAUSED => 'bg-warning',
            self::STATUS_EXPIRED => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
