<?php

namespace App\Models\Lms;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lms_assignment_submissions';

    protected $fillable = [
        'assignment_id',
        'student_id',
        'attempt_number',
        'submission_text',
        'files',
        'submitted_at',
        'is_late',
        'days_late',
        'ip_address',
        'status',
        'score',
        'score_after_penalty',
        'feedback',
        'rubric_scores',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'files' => 'array',
        'rubric_scores' => 'array',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'is_late' => 'boolean',
        'score' => 'decimal:2',
        'score_after_penalty' => 'decimal:2',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'grading' => 'Grading',
        'graded' => 'Graded',
        'returned' => 'Returned',
    ];

    // Relationships
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function attachedFiles(): HasMany
    {
        return $this->hasMany(SubmissionFile::class, 'submission_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(SubmissionComment::class, 'commentable');
    }

    // Scopes
    public function scopeSubmitted($query)
    {
        return $query->whereNotNull('submitted_at');
    }

    public function scopeNeedsGrading($query)
    {
        return $query->submitted()->where('status', 'submitted');
    }

    public function scopeGraded($query)
    {
        return $query->whereIn('status', ['graded', 'returned']);
    }

    // Accessors
    public function getIsSubmittedAttribute(): bool
    {
        return $this->submitted_at !== null;
    }

    public function getIsGradedAttribute(): bool
    {
        return in_array($this->status, ['graded', 'returned']);
    }

    public function getPercentageAttribute(): ?float
    {
        if (!$this->score_after_penalty || !$this->assignment->max_points) {
            return null;
        }

        return round(($this->score_after_penalty / $this->assignment->max_points) * 100, 2);
    }

    public function getFinalScoreAttribute(): ?float
    {
        return $this->score_after_penalty !== null ? (float) $this->score_after_penalty : null;
    }

    public function getLatePenaltyAppliedAttribute(): ?float
    {
        if ($this->score === null || $this->score_after_penalty === null) {
            return null;
        }
        return round((float) $this->score - (float) $this->score_after_penalty, 2);
    }

    // Methods
    public function submit(): void
    {
        $assignment = $this->assignment;
        $now = now();
        $isLate = false;
        $daysLate = 0;

        if ($assignment->due_date && $now->isAfter($assignment->due_date)) {
            $isLate = true;
            $daysLate = (int) $assignment->due_date->diffInDays($now);
        }

        $this->update([
            'submitted_at' => $now,
            'is_late' => $isLate,
            'days_late' => $daysLate,
            'status' => 'submitted',
            'ip_address' => request()->ip(),
        ]);
    }

    public function grade(float $score, ?string $feedback, int $graderId, ?array $rubricScores = null): void
    {
        $assignment = $this->assignment;
        $scoreAfterPenalty = $score;

        // Apply late penalty
        if ($this->is_late && $this->days_late > 0) {
            $penaltyPercent = $assignment->calculateLatePenalty($this->days_late);
            $scoreAfterPenalty = $score * (1 - ($penaltyPercent / 100));
        }

        $this->update([
            'score' => $score,
            'score_after_penalty' => max(0, $scoreAfterPenalty),
            'feedback' => $feedback,
            'rubric_scores' => $rubricScores,
            'graded_by' => $graderId,
            'graded_at' => now(),
            'status' => 'graded',
        ]);

        // Update content progress if linked to a content item
        $this->updateContentProgress();
    }

    public function returnToStudent(?string $feedback = null): void
    {
        $this->update([
            'status' => 'returned',
            'feedback' => $feedback ?? $this->feedback,
        ]);
    }

    protected function updateContentProgress(): void
    {
        $contentItem = $this->assignment->contentItem;
        if (!$contentItem) {
            return;
        }

        $enrollment = Enrollment::where('course_id', $contentItem->module->course_id)
            ->where('student_id', $this->student_id)
            ->first();

        if (!$enrollment) {
            return;
        }

        $progress = ContentProgress::firstOrCreate([
            'enrollment_id' => $enrollment->id,
            'content_item_id' => $contentItem->id,
        ]);

        $progress->markAsCompleted($this->score_after_penalty, $this->percentage);
    }

    public function addFile(string $path, string $originalName, string $mimeType, int $size): SubmissionFile
    {
        return $this->attachedFiles()->create([
            'file_path' => $path,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'file_size' => $size,
        ]);
    }

    public function deleteFiles(): void
    {
        foreach ($this->attachedFiles as $file) {
            Storage::disk('public')->delete($file->file_path);
            $file->delete();
        }
    }
}
