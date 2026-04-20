<?php

namespace App\Models\Lms;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model {
    protected $table = 'lms_grades';

    protected $fillable = [
        'grade_item_id',
        'student_id',
        'enrollment_id',
        'score',
        'max_score',
        'percentage',
        'letter_grade',
        'status',
        'feedback',
        'is_late',
        'late_penalty',
        'is_overridden',
        'original_score',
        'graded_by',
        'graded_at',
        'submitted_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'late_penalty' => 'decimal:2',
        'original_score' => 'decimal:2',
        'is_late' => 'boolean',
        'is_overridden' => 'boolean',
        'graded_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_GRADED = 'graded';
    public const STATUS_EXCUSED = 'excused';
    public const STATUS_INCOMPLETE = 'incomplete';
    public const STATUS_DROPPED = 'dropped';

    public static array $statuses = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_GRADED => 'Graded',
        self::STATUS_EXCUSED => 'Excused',
        self::STATUS_INCOMPLETE => 'Incomplete',
        self::STATUS_DROPPED => 'Dropped',
    ];

    // Relationships
    public function gradeItem(): BelongsTo {
        return $this->belongsTo(GradeItem::class, 'grade_item_id');
    }

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function enrollment(): BelongsTo {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function gradedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function history(): HasMany {
        return $this->hasMany(GradeHistory::class, 'grade_id')->orderByDesc('changed_at');
    }

    public function comments(): HasMany {
        return $this->hasMany(GradeComment::class, 'grade_id');
    }

    public function rubricScores(): HasMany {
        return $this->hasMany(RubricScore::class, 'grade_id');
    }

    // Scopes
    public function scopePending($query) {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeGraded($query) {
        return $query->where('status', self::STATUS_GRADED);
    }

    public function scopeForStudent($query, $studentId) {
        return $query->where('student_id', $studentId);
    }

    // Accessors
    public function getStatusBadgeAttribute(): string {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_GRADED => 'bg-success',
            self::STATUS_EXCUSED => 'bg-info',
            self::STATUS_INCOMPLETE => 'bg-secondary',
            self::STATUS_DROPPED => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    public function getEffectiveScoreAttribute(): float {
        $score = $this->score ?? 0;

        if ($this->is_late && $this->late_penalty > 0) {
            $penalty = $score * ($this->late_penalty / 100);
            return max(0, $score - $penalty);
        }

        return $score;
    }

    public function getMaxPointsAttribute(): float {
        return $this->max_score ?? $this->gradeItem->max_points;
    }

    // Methods
    public function setGrade(float $score, ?User $gradedBy = null, ?string $feedback = null): self {
        $maxPoints = $this->max_score ?? $this->gradeItem->max_points;
        $percentage = $maxPoints > 0 ? ($score / $maxPoints) * 100 : 0;

        // Get letter grade from course settings
        $settings = GradebookSettings::getOrCreate($this->gradeItem->course);
        $letterGrade = $settings->getLetterGrade($percentage);

        $oldScore = $this->score;
        $oldStatus = $this->status;

        $this->update([
            'score' => $score,
            'percentage' => round($percentage, 2),
            'letter_grade' => $letterGrade,
            'status' => self::STATUS_GRADED,
            'feedback' => $feedback ?? $this->feedback,
            'graded_by' => $gradedBy?->id,
            'graded_at' => now(),
        ]);

        // Log history
        GradeHistory::create([
            'grade_id' => $this->id,
            'old_score' => $oldScore,
            'new_score' => $score,
            'old_status' => $oldStatus,
            'new_status' => self::STATUS_GRADED,
            'action' => $oldScore === null ? 'created' : 'updated',
            'changed_by' => $gradedBy?->id,
            'changed_at' => now(),
        ]);

        return $this;
    }

    public function override(float $score, ?User $overriddenBy = null, ?string $reason = null): self {
        $this->update([
            'is_overridden' => true,
            'original_score' => $this->score,
        ]);

        $this->setGrade($score, $overriddenBy);

        GradeHistory::create([
            'grade_id' => $this->id,
            'old_score' => $this->original_score,
            'new_score' => $score,
            'action' => 'overridden',
            'reason' => $reason,
            'changed_by' => $overriddenBy?->id,
            'changed_at' => now(),
        ]);

        return $this;
    }

    public function excuse(?User $excusedBy = null, ?string $reason = null): self {
        $oldStatus = $this->status;

        $this->update(['status' => self::STATUS_EXCUSED]);

        GradeHistory::create([
            'grade_id' => $this->id,
            'old_status' => $oldStatus,
            'new_status' => self::STATUS_EXCUSED,
            'action' => 'excused',
            'reason' => $reason,
            'changed_by' => $excusedBy?->id,
            'changed_at' => now(),
        ]);

        return $this;
    }

    public function drop(?User $droppedBy = null): self {
        $oldStatus = $this->status;

        $this->update(['status' => self::STATUS_DROPPED]);

        GradeHistory::create([
            'grade_id' => $this->id,
            'old_status' => $oldStatus,
            'new_status' => self::STATUS_DROPPED,
            'action' => 'dropped',
            'changed_by' => $droppedBy?->id,
            'changed_at' => now(),
        ]);

        return $this;
    }

    public function applyLatePenalty(float $penaltyPercent): self {
        $this->update([
            'is_late' => true,
            'late_penalty' => $penaltyPercent,
        ]);

        return $this;
    }

    public static function getOrCreate(GradeItem $item, Student $student, Enrollment $enrollment): self {
        return self::firstOrCreate(
            [
                'grade_item_id' => $item->id,
                'student_id' => $student->id,
            ],
            [
                'enrollment_id' => $enrollment->id,
                'status' => self::STATUS_PENDING,
            ]
        );
    }
}
