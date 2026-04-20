<?php

namespace App\Models\Lms;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LearningPath extends Model {
    use SoftDeletes;

    protected $table = 'lms_learning_paths';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'objectives',
        'thumbnail',
        'level',
        'grade_id',
        'estimated_duration_hours',
        'price',
        'is_published',
        'is_featured',
        'enforce_sequence',
        'allow_skip',
        'created_by',
        'published_at',
    ];

    protected $casts = [
        'objectives' => 'array',
        'price' => 'decimal:2',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'enforce_sequence' => 'boolean',
        'allow_skip' => 'boolean',
        'published_at' => 'datetime',
    ];

    public const LEVEL_BEGINNER = 'beginner';
    public const LEVEL_INTERMEDIATE = 'intermediate';
    public const LEVEL_ADVANCED = 'advanced';
    public const LEVEL_EXPERT = 'expert';

    public static array $levels = [
        self::LEVEL_BEGINNER => 'Beginner',
        self::LEVEL_INTERMEDIATE => 'Intermediate',
        self::LEVEL_ADVANCED => 'Advanced',
        self::LEVEL_EXPERT => 'Expert',
    ];

    protected static function boot(): void {
        parent::boot();

        static::creating(function ($path) {
            if (empty($path->slug)) {
                $path->slug = Str::slug($path->title);
            }
        });
    }

    // Relationships
    public function pathCourses(): HasMany {
        return $this->hasMany(LearningPathCourse::class, 'learning_path_id')->orderBy('position');
    }

    public function courses(): BelongsToMany {
        return $this->belongsToMany(Course::class, 'lms_learning_path_courses', 'learning_path_id', 'course_id')
            ->withPivot(['position', 'is_required', 'is_milestone', 'milestone_title', 'unlock_after_days'])
            ->orderBy('position');
    }

    public function enrollments(): HasMany {
        return $this->hasMany(LearningPathEnrollment::class, 'learning_path_id');
    }

    public function milestones(): HasMany {
        return $this->hasMany(LearningPathMilestone::class, 'learning_path_id')->orderBy('position');
    }

    public function categories(): BelongsToMany {
        return $this->belongsToMany(LearningPathCategory::class, 'lms_learning_path_category', 'learning_path_id', 'category_id');
    }

    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function grade(): BelongsTo {
        return $this->belongsTo(Grade::class);
    }

    // Scopes
    public function scopePublished($query) {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query) {
        return $query->where('is_featured', true);
    }

    public function scopeByLevel($query, string $level) {
        return $query->where('level', $level);
    }

    public function scopeByGrade($query, int $gradeId) {
        return $query->where('grade_id', $gradeId);
    }

    // Accessors
    public function getThumbnailUrlAttribute(): ?string {
        return $this->thumbnail ? asset('storage/' . $this->thumbnail) : null;
    }

    public function getLevelLabelAttribute(): string {
        return self::$levels[$this->level] ?? $this->level;
    }

    public function getLevelBadgeAttribute(): string {
        return match ($this->level) {
            self::LEVEL_BEGINNER => 'bg-success',
            self::LEVEL_INTERMEDIATE => 'bg-info',
            self::LEVEL_ADVANCED => 'bg-warning',
            self::LEVEL_EXPERT => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getCoursesCountAttribute(): int {
        return $this->pathCourses()->count();
    }

    public function getEnrollmentsCountAttribute(): int {
        return $this->enrollments()->count();
    }

    public function getEstimatedDurationAttribute(): string {
        $hours = $this->estimated_duration_hours ?? $this->calculateDuration();
        if ($hours < 1) {
            return 'Less than 1 hour';
        }
        return $hours . ' ' . Str::plural('hour', $hours);
    }

    // Methods
    public function calculateDuration(): int {
        return $this->courses->sum('duration_hours') ?? 0;
    }

    public function isEnrolledBy($student): bool {
        if (!$student) return false;
        return $this->enrollments()->where('student_id', $student->id)->exists();
    }

    public function getEnrollment($student): ?LearningPathEnrollment {
        if (!$student) return null;
        return $this->enrollments()->where('student_id', $student->id)->first();
    }

    public function enroll($student): LearningPathEnrollment {
        return LearningPathEnrollment::enroll($this, $student);
    }

    public function publish(): void {
        $this->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function unpublish(): void {
        $this->update([
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function addCourse(Course $course, array $data = []): LearningPathCourse {
        $position = $this->pathCourses()->max('position') + 1;

        return LearningPathCourse::create([
            'learning_path_id' => $this->id,
            'course_id' => $course->id,
            'position' => $data['position'] ?? $position,
            'is_required' => $data['is_required'] ?? true,
            'is_milestone' => $data['is_milestone'] ?? false,
            'milestone_title' => $data['milestone_title'] ?? null,
            'unlock_after_days' => $data['unlock_after_days'] ?? null,
        ]);
    }

    public function removeCourse(Course $course): void {
        $this->pathCourses()->where('course_id', $course->id)->delete();
    }

    public function reorderCourses(array $courseIds): void {
        foreach ($courseIds as $position => $courseId) {
            $this->pathCourses()
                ->where('course_id', $courseId)
                ->update(['position' => $position]);
        }
    }
}
