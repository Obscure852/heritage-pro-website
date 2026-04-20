<?php

namespace App\Models\Lms;

use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lms_courses';

    protected $fillable = [
        'code',
        'title',
        'slug',
        'description',
        'learning_objectives',
        'prerequisites_text',
        'thumbnail_path',
        'grade_id',
        'grade_subject_id',
        'term_id',
        'instructor_id',
        'status',
        'visibility',
        'start_date',
        'end_date',
        'max_students',
        'estimated_duration_minutes',
        'passing_grade',
        'self_enrollment',
        'enrollment_key',
        'max_attempts',
        'sequential_content',
        'adaptive_learning_enabled',
        'created_by',
        'published_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'learning_objectives' => 'array',
        'passing_grade' => 'decimal:2',
        'self_enrollment' => 'boolean',
        'sequential_content' => 'boolean',
        'adaptive_learning_enabled' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
                $count = static::where('slug', 'like', $course->slug . '%')->count();
                if ($count > 0) {
                    $course->slug = $course->slug . '-' . ($count + 1);
                }
            }
        });
    }

    // Relationships
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function gradeSubject(): BelongsTo
    {
        return $this->belongsTo(GradeSubject::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('sequence');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function contentItems(): HasManyThrough
    {
        return $this->hasManyThrough(ContentItem::class, Module::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeForGrade($query, $gradeId)
    {
        return $query->where('grade_id', $gradeId);
    }

    public function scopeForTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    public function scopeByInstructor($query, $userId)
    {
        return $query->where('instructor_id', $userId);
    }

    // Accessors
    public function getEnrollmentCountAttribute(): int
    {
        return $this->enrollments()->count();
    }

    public function getActiveEnrollmentCountAttribute(): int
    {
        return $this->enrollments()->where('status', 'active')->count();
    }

    public function getCompletedEnrollmentCountAttribute(): int
    {
        return $this->enrollments()->where('status', 'completed')->count();
    }

    public function getModuleCountAttribute(): int
    {
        return $this->modules()->count();
    }

    public function getContentCountAttribute(): int
    {
        return $this->contentItems()->count();
    }

    public function getTotalDurationAttribute(): int
    {
        return $this->contentItems()->sum('duration_minutes') ?? 0;
    }

    // Helper Methods
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function isAvailable(): bool
    {
        if (!$this->isPublished()) {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    public function canEnroll(): bool
    {
        return $this->isAvailable() && $this->self_enrollment;
    }

    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update([
            'status' => 'draft',
        ]);
    }

    public function archive(): void
    {
        $this->update([
            'status' => 'archived',
        ]);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
