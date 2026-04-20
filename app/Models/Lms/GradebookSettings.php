<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradebookSettings extends Model {
    protected $table = 'lms_gradebook_settings';

    protected $fillable = [
        'course_id',
        'grade_scale_id',
        'grading_method',
        'passing_grade',
        'show_grade_to_students',
        'show_rank_to_students',
        'show_statistics',
        'drop_lowest',
        'drop_lowest_count',
        'include_incomplete',
        'settings',
    ];

    protected $casts = [
        'passing_grade' => 'decimal:2',
        'show_grade_to_students' => 'boolean',
        'show_rank_to_students' => 'boolean',
        'show_statistics' => 'boolean',
        'drop_lowest' => 'boolean',
        'include_incomplete' => 'boolean',
        'settings' => 'array',
    ];

    public const METHOD_WEIGHTED = 'weighted';
    public const METHOD_POINTS = 'points';
    public const METHOD_SIMPLE_AVERAGE = 'simple_average';

    public static array $gradingMethods = [
        self::METHOD_WEIGHTED => 'Weighted Categories',
        self::METHOD_POINTS => 'Total Points',
        self::METHOD_SIMPLE_AVERAGE => 'Simple Average',
    ];

    // Relationships
    public function course(): BelongsTo {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function gradeScale(): BelongsTo {
        return $this->belongsTo(GradeScale::class, 'grade_scale_id');
    }

    // Methods
    public static function getOrCreate(Course $course): self {
        return self::firstOrCreate(
            ['course_id' => $course->id],
            [
                'grade_scale_id' => GradeScale::getDefault()?->id,
                'grading_method' => self::METHOD_WEIGHTED,
                'passing_grade' => 50,
            ]
        );
    }

    public function isPassing(float $percentage): bool {
        return $percentage >= $this->passing_grade;
    }

    public function getLetterGrade(float $percentage): ?string {
        return $this->gradeScale?->getLetterGrade($percentage);
    }
}
