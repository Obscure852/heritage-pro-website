<?php

namespace App\Models\Lms;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseGrade extends Model {
    protected $table = 'lms_course_grades';

    protected $fillable = [
        'course_id',
        'student_id',
        'enrollment_id',
        'total_points_earned',
        'total_points_possible',
        'percentage',
        'weighted_percentage',
        'letter_grade',
        'gpa_points',
        'rank',
        'items_graded',
        'items_total',
        'is_passing',
        'is_finalized',
        'finalized_at',
        'finalized_by',
        'category_grades',
        'calculated_at',
    ];

    protected $casts = [
        'total_points_earned' => 'decimal:2',
        'total_points_possible' => 'decimal:2',
        'percentage' => 'decimal:2',
        'weighted_percentage' => 'decimal:2',
        'gpa_points' => 'decimal:2',
        'is_passing' => 'boolean',
        'is_finalized' => 'boolean',
        'category_grades' => 'array',
        'finalized_at' => 'datetime',
        'calculated_at' => 'datetime',
    ];

    // Relationships
    public function course(): BelongsTo {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function enrollment(): BelongsTo {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function finalizedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    // Scopes
    public function scopePassing($query) {
        return $query->where('is_passing', true);
    }

    public function scopeFinalized($query) {
        return $query->where('is_finalized', true);
    }

    // Accessors
    public function getStatusAttribute(): string {
        if ($this->is_finalized) {
            return 'Finalized';
        }
        return $this->is_passing ? 'Passing' : 'Not Passing';
    }

    public function getStatusBadgeAttribute(): string {
        if ($this->is_finalized) {
            return 'bg-dark';
        }
        return $this->is_passing ? 'bg-success' : 'bg-danger';
    }

    // Methods
    public static function calculate(Course $course, Student $student): self {
        $settings = GradebookSettings::getOrCreate($course);
        $enrollment = Enrollment::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$enrollment) {
            throw new \Exception('Student is not enrolled in this course');
        }

        $courseGrade = self::firstOrNew([
            'course_id' => $course->id,
            'student_id' => $student->id,
        ]);

        $courseGrade->enrollment_id = $enrollment->id;

        // Calculate based on grading method
        switch ($settings->grading_method) {
            case GradebookSettings::METHOD_WEIGHTED:
                $result = self::calculateWeighted($course, $student, $settings);
                break;
            case GradebookSettings::METHOD_POINTS:
                $result = self::calculatePoints($course, $student, $settings);
                break;
            default:
                $result = self::calculateSimpleAverage($course, $student, $settings);
        }

        $courseGrade->fill($result);
        $courseGrade->is_passing = $settings->isPassing($courseGrade->percentage);
        $courseGrade->letter_grade = $settings->getLetterGrade($courseGrade->percentage);
        $courseGrade->gpa_points = $settings->gradeScale?->getGpaPoints($courseGrade->percentage);
        $courseGrade->calculated_at = now();
        $courseGrade->save();

        return $courseGrade;
    }

    protected static function calculateWeighted(Course $course, Student $student, GradebookSettings $settings): array {
        $categories = GradeCategory::where('course_id', $course->id)->get();
        $categoryGrades = [];
        $totalWeighted = 0;
        $totalWeight = 0;
        $totalEarned = 0;
        $totalPossible = 0;
        $itemsGraded = 0;
        $itemsTotal = 0;

        foreach ($categories as $category) {
            $categoryResult = $category->calculateStudentGrade($student->id);
            $categoryGrades[$category->name] = $categoryResult;

            if ($categoryResult['possible'] > 0) {
                $totalWeighted += $categoryResult['weighted'];
                $totalWeight += $category->is_extra_credit ? 0 : $category->weight;
            }

            $totalEarned += $categoryResult['earned'];
            $totalPossible += $categoryResult['possible'];
            $itemsGraded += $category->items()->whereHas('grades', fn($q) => $q->where('student_id', $student->id)->where('status', 'graded'))->count();
            $itemsTotal += $category->items()->count();
        }

        // Normalize if total weight is not 100
        $percentage = $totalWeight > 0 ? ($totalWeighted / $totalWeight) * 100 : 0;

        return [
            'total_points_earned' => $totalEarned,
            'total_points_possible' => $totalPossible,
            'percentage' => round($percentage, 2),
            'weighted_percentage' => round($totalWeighted, 2),
            'items_graded' => $itemsGraded,
            'items_total' => $itemsTotal,
            'category_grades' => $categoryGrades,
        ];
    }

    protected static function calculatePoints(Course $course, Student $student, GradebookSettings $settings): array {
        $items = GradeItem::where('course_id', $course->id)
            ->where('is_excluded', false)
            ->get();

        $totalEarned = 0;
        $totalPossible = 0;
        $itemsGraded = 0;

        foreach ($items as $item) {
            $grade = $item->getGradeFor($student);

            if ($grade && $grade->status === 'graded') {
                $totalEarned += $grade->effective_score;
                $totalPossible += $item->max_points;
                $itemsGraded++;
            } elseif ($settings->include_incomplete) {
                $totalPossible += $item->max_points;
            }
        }

        $percentage = $totalPossible > 0 ? ($totalEarned / $totalPossible) * 100 : 0;

        return [
            'total_points_earned' => $totalEarned,
            'total_points_possible' => $totalPossible,
            'percentage' => round($percentage, 2),
            'weighted_percentage' => round($percentage, 2),
            'items_graded' => $itemsGraded,
            'items_total' => $items->count(),
            'category_grades' => [],
        ];
    }

    protected static function calculateSimpleAverage(Course $course, Student $student, GradebookSettings $settings): array {
        $grades = Grade::whereHas('gradeItem', fn($q) => $q->where('course_id', $course->id)->where('is_excluded', false))
            ->where('student_id', $student->id)
            ->where('status', 'graded')
            ->get();

        $totalPercentage = $grades->sum('percentage');
        $count = $grades->count();

        $percentage = $count > 0 ? $totalPercentage / $count : 0;

        return [
            'total_points_earned' => $grades->sum('score'),
            'total_points_possible' => $grades->sum(fn($g) => $g->max_score ?? $g->gradeItem->max_points),
            'percentage' => round($percentage, 2),
            'weighted_percentage' => round($percentage, 2),
            'items_graded' => $count,
            'items_total' => GradeItem::where('course_id', $course->id)->where('is_excluded', false)->count(),
            'category_grades' => [],
        ];
    }

    public static function calculateRanks(Course $course): void {
        $grades = self::where('course_id', $course->id)
            ->orderByDesc('percentage')
            ->get();

        $rank = 1;
        $prevPercentage = null;
        $sameRankCount = 0;

        foreach ($grades as $grade) {
            if ($prevPercentage !== null && $grade->percentage < $prevPercentage) {
                $rank += $sameRankCount;
                $sameRankCount = 1;
            } else {
                $sameRankCount++;
            }

            $grade->update(['rank' => $rank]);
            $prevPercentage = $grade->percentage;
        }
    }

    public function finalize(?User $finalizedBy = null): self {
        $this->update([
            'is_finalized' => true,
            'finalized_at' => now(),
            'finalized_by' => $finalizedBy?->id,
        ]);

        return $this;
    }
}
