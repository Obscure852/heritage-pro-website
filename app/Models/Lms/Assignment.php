<?php

namespace App\Models\Lms;

use App\Models\GradeSubject;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lms_assignments';

    protected $fillable = [
        'title',
        'description',
        'instructions',
        'submission_type',
        'allowed_file_types',
        'max_file_size_mb',
        'max_files',
        'available_from',
        'due_date',
        'cutoff_date',
        'max_points',
        'rubric_id',
        'allow_late_submissions',
        'late_penalty_percent',
        'max_attempts',
        'allow_resubmission',
        'require_submission_text',
        'anonymous_grading',
        'peer_review_enabled',
        'peer_reviews_per_student',
        'grade_subject_id',
        'sync_to_gradebook',
        'status',
    ];

    protected $casts = [
        'allowed_file_types' => 'array',
        'available_from' => 'datetime',
        'due_date' => 'datetime',
        'cutoff_date' => 'datetime',
        'max_points' => 'decimal:2',
        'late_penalty_percent' => 'decimal:2',
        'allow_late_submissions' => 'boolean',
        'allow_resubmission' => 'boolean',
        'require_submission_text' => 'boolean',
        'anonymous_grading' => 'boolean',
        'peer_review_enabled' => 'boolean',
        'sync_to_gradebook' => 'boolean',
    ];

    public const SUBMISSION_TYPES = [
        'file' => 'File Upload',
        'text' => 'Text Entry',
        'both' => 'File Upload & Text',
    ];

    public const DEFAULT_FILE_TYPES = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip'];

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (Assignment $assignment) {
            foreach ($assignment->attachments as $attachment) {
                if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $attachment->delete();
            }
        });
    }

    // Relationships
    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    public function gradeSubject(): BelongsTo
    {
        return $this->belongsTo(GradeSubject::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AssignmentAttachment::class)->orderBy('sort_order');
    }

    public function contentItem(): MorphOne
    {
        return $this->morphOne(ContentItem::class, 'contentable');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeAvailable($query)
    {
        return $query->published()
            ->where(function ($q) {
                $q->whereNull('available_from')
                    ->orWhere('available_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('cutoff_date')
                    ->orWhere('cutoff_date', '>=', now());
            });
    }

    // Accessors
    public function getIsAvailableAttribute(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->available_from && $this->available_from->isFuture()) {
            return false;
        }

        if ($this->cutoff_date && $this->cutoff_date->isPast()) {
            return false;
        }

        return true;
    }

    public function getIsPastDueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->is_past_due;
    }

    public function getAcceptsLateAttribute(): bool
    {
        if (!$this->allow_late_submissions) {
            return false;
        }

        if ($this->cutoff_date && $this->cutoff_date->isPast()) {
            return false;
        }

        return true;
    }

    public function getAllowedFileTypesListAttribute(): string
    {
        return implode(', ', $this->allowed_file_types ?? self::DEFAULT_FILE_TYPES);
    }

    // Methods
    public function getSubmissionForStudent(int $studentId): ?AssignmentSubmission
    {
        return $this->submissions()
            ->where('student_id', $studentId)
            ->latest('attempt_number')
            ->first();
    }

    public function canStudentSubmit(int $studentId): bool
    {
        if (!$this->is_available && !$this->accepts_late) {
            return false;
        }

        $submission = $this->getSubmissionForStudent($studentId);

        if (!$submission) {
            return true;
        }

        // Check if resubmission is allowed
        if (!$this->allow_resubmission && $submission->status === 'submitted') {
            return false;
        }

        // Check max attempts
        if ($this->max_attempts && $submission->attempt_number >= $this->max_attempts) {
            return false;
        }

        return true;
    }

    public function calculateLatePenalty(int $daysLate): float
    {
        if ($daysLate <= 0 || $this->late_penalty_percent <= 0) {
            return 0;
        }

        $penalty = $daysLate * $this->late_penalty_percent;
        return min($penalty, 100); // Cap at 100%
    }

    public function publish(): void
    {
        $this->update(['status' => 'published']);
    }

    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }
}
