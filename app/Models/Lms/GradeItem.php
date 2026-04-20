<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GradeItem extends Model {
    protected $table = 'lms_grade_items';

    protected $fillable = [
        'course_id',
        'category_id',
        'name',
        'type',
        'gradeable_type',
        'gradeable_id',
        'max_points',
        'weight',
        'is_extra_credit',
        'is_excluded',
        'is_hidden',
        'due_date',
        'position',
    ];

    protected $casts = [
        'max_points' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_extra_credit' => 'boolean',
        'is_excluded' => 'boolean',
        'is_hidden' => 'boolean',
        'due_date' => 'date',
    ];

    public const TYPE_MANUAL = 'manual';
    public const TYPE_ASSIGNMENT = 'assignment';
    public const TYPE_QUIZ = 'quiz';
    public const TYPE_ATTENDANCE = 'attendance';
    public const TYPE_PARTICIPATION = 'participation';

    public static array $types = [
        self::TYPE_MANUAL => 'Manual Grade',
        self::TYPE_ASSIGNMENT => 'Assignment',
        self::TYPE_QUIZ => 'Quiz',
        self::TYPE_ATTENDANCE => 'Attendance',
        self::TYPE_PARTICIPATION => 'Participation',
    ];

    // Relationships
    public function course(): BelongsTo {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function category(): BelongsTo {
        return $this->belongsTo(GradeCategory::class, 'category_id');
    }

    public function gradeable(): MorphTo {
        return $this->morphTo();
    }

    public function grades(): HasMany {
        return $this->hasMany(Grade::class, 'grade_item_id');
    }

    public function rubrics() {
        return $this->belongsToMany(Rubric::class, 'lms_grade_item_rubric', 'grade_item_id', 'rubric_id');
    }

    // Scopes
    public function scopeVisible($query) {
        return $query->where('is_hidden', false);
    }

    public function scopeIncludedInGrade($query) {
        return $query->where('is_excluded', false);
    }

    public function scopeOrdered($query) {
        return $query->orderBy('position');
    }

    public function scopeByType($query, string $type) {
        return $query->where('type', $type);
    }

    // Accessors
    public function getTypeLabelAttribute(): string {
        return self::$types[$this->type] ?? $this->type;
    }

    public function getEffectiveWeightAttribute(): ?float {
        if ($this->weight !== null) {
            return $this->weight;
        }
        return $this->category?->weight;
    }

    // Methods
    public function getGradeFor($student): ?Grade {
        return $this->grades()->where('student_id', $student->id)->first();
    }

    public function hasRubric(): bool {
        return $this->rubrics()->exists();
    }

    public function getClassAverage(): ?float {
        $grades = $this->grades()
            ->whereIn('status', ['graded'])
            ->get();

        if ($grades->isEmpty()) {
            return null;
        }

        return round($grades->avg('percentage'), 2);
    }

    public function getStatistics(): array {
        $grades = $this->grades()
            ->where('status', 'graded')
            ->get();

        if ($grades->isEmpty()) {
            return [
                'count' => 0,
                'average' => null,
                'median' => null,
                'highest' => null,
                'lowest' => null,
                'std_dev' => null,
            ];
        }

        $scores = $grades->pluck('percentage')->sort()->values();

        return [
            'count' => $scores->count(),
            'average' => round($scores->avg(), 2),
            'median' => round($scores->median(), 2),
            'highest' => round($scores->max(), 2),
            'lowest' => round($scores->min(), 2),
            'std_dev' => round($this->calculateStdDev($scores), 2),
        ];
    }

    protected function calculateStdDev($scores): float {
        $mean = $scores->avg();
        $squaredDiffs = $scores->map(fn($s) => pow($s - $mean, 2));
        return sqrt($squaredDiffs->avg());
    }

    public static function createFromAssignment(Assignment $assignment): self {
        return self::create([
            'course_id' => $assignment->course_id,
            'name' => $assignment->title,
            'type' => self::TYPE_ASSIGNMENT,
            'gradeable_type' => Assignment::class,
            'gradeable_id' => $assignment->id,
            'max_points' => $assignment->max_score,
            'due_date' => $assignment->due_date,
        ]);
    }

    public static function createFromQuiz(Quiz $quiz): self {
        return self::create([
            'course_id' => $quiz->course_id,
            'name' => $quiz->title,
            'type' => self::TYPE_QUIZ,
            'gradeable_type' => Quiz::class,
            'gradeable_id' => $quiz->id,
            'max_points' => $quiz->total_points,
        ]);
    }
}
