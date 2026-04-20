<?php

namespace App\Models\Lms;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'lms_enrollments';

    protected $fillable = [
        'course_id',
        'student_id',
        'enrolled_by',
        'enrollment_type',
        'role',
        'status',
        'enrolled_at',
        'started_at',
        'completed_at',
        'progress_percentage',
        'current_module_id',
        'current_content_id',
        'grade',
        'grade_letter',
        'last_activity_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'progress_percentage' => 'decimal:2',
        'grade' => 'decimal:2',
    ];

    // Relationships
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    public function currentModule(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'current_module_id');
    }

    public function currentContent(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class, 'current_content_id');
    }

    public function contentProgress(): HasMany
    {
        return $this->hasMany(ContentProgress::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    // Helper Methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function markAsStarted(): void
    {
        if (!$this->started_at) {
            $this->update([
                'started_at' => now(),
                'status' => 'active',
            ]);
        }
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function updateProgress(): void
    {
        $totalMandatory = $this->course->contentItems()
            ->where('is_mandatory', true)
            ->count();

        if ($totalMandatory === 0) {
            $this->update(['progress_percentage' => 100]);
            return;
        }

        $completed = $this->contentProgress()
            ->whereHas('contentItem', fn($q) => $q->where('is_mandatory', true))
            ->where('status', 'completed')
            ->count();

        $percentage = round(($completed / $totalMandatory) * 100, 2);

        $this->update([
            'progress_percentage' => $percentage,
            'last_activity_at' => now(),
        ]);

        if ($percentage >= 100) {
            $this->calculateFinalGrade();
            $this->markAsCompleted();
        }
    }

    public function calculateFinalGrade(): void
    {
        $scoredProgress = $this->contentProgress()
            ->whereNotNull('score_percentage')
            ->get();

        if ($scoredProgress->isEmpty()) {
            return;
        }

        $totalScore = $scoredProgress->sum('score_percentage');
        $averageGrade = $totalScore / $scoredProgress->count();

        $this->update([
            'grade' => round($averageGrade, 2),
            'grade_letter' => $this->calculateGradeLetter($averageGrade),
        ]);
    }

    protected function calculateGradeLetter(float $percentage): string
    {
        return match (true) {
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B',
            $percentage >= 60 => 'C',
            $percentage >= 50 => 'D',
            default => 'F',
        };
    }

    public function recordActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }
}
