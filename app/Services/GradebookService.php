<?php

namespace App\Services;

use App\Models\Lms\Assignment;
use App\Models\Lms\Course;
use App\Models\Lms\CourseGrade;
use App\Models\Lms\Enrollment;
use App\Models\Lms\Grade;
use App\Models\Lms\GradeCategory;
use App\Models\Lms\GradeItem;
use App\Models\Lms\GradebookSettings;
use App\Models\Lms\Quiz;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;

class GradebookService {
    protected ?NotificationService $notificationService;

    public function __construct(?NotificationService $notificationService = null) {
        $this->notificationService = $notificationService;
    }

    /**
     * Get complete gradebook data for a course
     */
    public function getGradebook(Course $course): array {
        $settings = GradebookSettings::getOrCreate($course);
        $categories = GradeCategory::where('course_id', $course->id)->ordered()->with('items')->get();
        $items = GradeItem::where('course_id', $course->id)->ordered()->get();
        $enrollments = Enrollment::where('course_id', $course->id)->where('status', 'active')->with('student')->get();

        $grades = [];
        foreach ($enrollments as $enrollment) {
            $studentGrades = [];
            foreach ($items as $item) {
                $grade = $item->getGradeFor($enrollment->student);
                $studentGrades[$item->id] = $grade;
            }
            $grades[$enrollment->student_id] = $studentGrades;
        }

        // Calculate course grades
        $courseGrades = [];
        foreach ($enrollments as $enrollment) {
            $courseGrades[$enrollment->student_id] = CourseGrade::calculate($course, $enrollment->student);
        }

        return [
            'settings' => $settings,
            'categories' => $categories,
            'items' => $items,
            'enrollments' => $enrollments,
            'grades' => $grades,
            'course_grades' => $courseGrades,
        ];
    }

    /**
     * Get gradebook data for a single student
     */
    public function getStudentGradebook(Course $course, Student $student): array {
        $settings = GradebookSettings::getOrCreate($course);
        $categories = GradeCategory::where('course_id', $course->id)->ordered()->with('items.grades')->get();

        $grades = [];
        $categoryGrades = [];

        foreach ($categories as $category) {
            $categoryResult = $category->calculateStudentGrade($student->id);
            $categoryGrades[$category->id] = $categoryResult;

            foreach ($category->items as $item) {
                if ($settings->show_grade_to_students || !$item->is_hidden) {
                    $grades[$item->id] = $item->getGradeFor($student);
                }
            }
        }

        // Uncategorized items
        $uncategorizedItems = GradeItem::where('course_id', $course->id)
            ->whereNull('category_id')
            ->visible()
            ->get();

        foreach ($uncategorizedItems as $item) {
            $grades[$item->id] = $item->getGradeFor($student);
        }

        $courseGrade = CourseGrade::calculate($course, $student);

        return [
            'settings' => $settings,
            'categories' => $categories,
            'uncategorized_items' => $uncategorizedItems,
            'grades' => $grades,
            'category_grades' => $categoryGrades,
            'course_grade' => $courseGrade,
        ];
    }

    /**
     * Grade a single item for a student
     */
    public function gradeItem(GradeItem $item, Student $student, float $score, ?User $gradedBy = null, ?string $feedback = null): Grade {
        $enrollment = Enrollment::where('course_id', $item->course_id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $grade = Grade::getOrCreate($item, $student, $enrollment);
        $grade->setGrade($score, $gradedBy, $feedback);

        // Recalculate course grade
        CourseGrade::calculate($item->course, $student);

        // Send notification
        $this->notificationService?->onAssignmentGraded($student, $grade);

        return $grade;
    }

    /**
     * Bulk grade an item for multiple students
     */
    public function bulkGradeItem(GradeItem $item, array $studentScores, ?User $gradedBy = null): int {
        $count = 0;

        foreach ($studentScores as $studentId => $score) {
            $student = Student::find($studentId);
            if (!$student) continue;

            $this->gradeItem($item, $student, $score, $gradedBy);
            $count++;
        }

        return $count;
    }

    /**
     * Create grade item from assignment
     */
    public function createGradeItemFromAssignment(Assignment $assignment): GradeItem {
        $item = GradeItem::createFromAssignment($assignment);

        // Create pending grades for all enrolled students
        $this->createPendingGrades($item);

        return $item;
    }

    /**
     * Create grade item from quiz
     */
    public function createGradeItemFromQuiz(Quiz $quiz): GradeItem {
        $item = GradeItem::createFromQuiz($quiz);

        // Create pending grades for all enrolled students
        $this->createPendingGrades($item);

        return $item;
    }

    /**
     * Create pending grades for all enrolled students
     */
    public function createPendingGrades(GradeItem $item): int {
        $enrollments = Enrollment::where('course_id', $item->course_id)
            ->where('status', 'active')
            ->get();

        $count = 0;
        foreach ($enrollments as $enrollment) {
            Grade::getOrCreate($item, $enrollment->student, $enrollment);
            $count++;
        }

        return $count;
    }

    /**
     * Sync grades from assignment submissions
     */
    public function syncAssignmentGrades(Assignment $assignment): int {
        $item = GradeItem::where('gradeable_type', Assignment::class)
            ->where('gradeable_id', $assignment->id)
            ->first();

        if (!$item) {
            $item = $this->createGradeItemFromAssignment($assignment);
        }

        $submissions = $assignment->submissions()->whereNotNull('score')->get();
        $count = 0;

        foreach ($submissions as $submission) {
            $enrollment = Enrollment::where('course_id', $assignment->course_id)
                ->where('student_id', $submission->student_id)
                ->first();

            if (!$enrollment) continue;

            $grade = Grade::getOrCreate($item, $submission->student, $enrollment);

            if ($grade->status !== Grade::STATUS_GRADED || $grade->score !== $submission->score) {
                $grade->update([
                    'score' => $submission->score,
                    'percentage' => ($submission->score / $item->max_points) * 100,
                    'status' => Grade::STATUS_GRADED,
                    'feedback' => $submission->feedback,
                    'is_late' => $submission->is_late ?? false,
                    'submitted_at' => $submission->submitted_at,
                    'graded_at' => $submission->graded_at,
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Sync grades from quiz attempts
     */
    public function syncQuizGrades(Quiz $quiz): int {
        $item = GradeItem::where('gradeable_type', Quiz::class)
            ->where('gradeable_id', $quiz->id)
            ->first();

        if (!$item) {
            $item = $this->createGradeItemFromQuiz($quiz);
        }

        $attempts = $quiz->attempts()->where('status', 'completed')->get();
        $count = 0;

        foreach ($attempts as $attempt) {
            $enrollment = Enrollment::where('course_id', $quiz->course_id)
                ->where('student_id', $attempt->student_id)
                ->first();

            if (!$enrollment) continue;

            $grade = Grade::getOrCreate($item, $attempt->student, $enrollment);

            $score = $attempt->score ?? 0;
            if ($grade->status !== Grade::STATUS_GRADED || $grade->score !== $score) {
                $grade->update([
                    'score' => $score,
                    'percentage' => $attempt->score_percentage ?? 0,
                    'status' => Grade::STATUS_GRADED,
                    'graded_at' => $attempt->completed_at,
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Recalculate all course grades
     */
    public function recalculateCourseGrades(Course $course): int {
        $enrollments = Enrollment::where('course_id', $course->id)
            ->where('status', 'active')
            ->get();

        foreach ($enrollments as $enrollment) {
            CourseGrade::calculate($course, $enrollment->student);
        }

        CourseGrade::calculateRanks($course);

        return $enrollments->count();
    }

    /**
     * Export gradebook to array (for CSV/Excel export)
     */
    public function exportGradebook(Course $course): array {
        $data = $this->getGradebook($course);
        $rows = [];

        // Header row
        $header = ['Student ID', 'Student Name'];
        foreach ($data['items'] as $item) {
            $header[] = $item->name . ' (' . $item->max_points . ')';
        }
        $header[] = 'Total Points';
        $header[] = 'Percentage';
        $header[] = 'Letter Grade';
        $rows[] = $header;

        // Data rows
        foreach ($data['enrollments'] as $enrollment) {
            $row = [
                $enrollment->student->id,
                $enrollment->student->name,
            ];

            foreach ($data['items'] as $item) {
                $grade = $data['grades'][$enrollment->student_id][$item->id] ?? null;
                $row[] = $grade?->score ?? '-';
            }

            $courseGrade = $data['course_grades'][$enrollment->student_id] ?? null;
            $row[] = $courseGrade?->total_points_earned ?? 0;
            $row[] = $courseGrade?->percentage ?? 0;
            $row[] = $courseGrade?->letter_grade ?? '-';

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Get grade statistics for a course
     */
    public function getCourseStatistics(Course $course): array {
        $grades = CourseGrade::where('course_id', $course->id)->get();

        if ($grades->isEmpty()) {
            return [
                'count' => 0,
                'average' => null,
                'median' => null,
                'highest' => null,
                'lowest' => null,
                'passing_rate' => null,
                'grade_distribution' => [],
            ];
        }

        $percentages = $grades->pluck('percentage')->sort()->values();

        // Grade distribution
        $distribution = [
            'A' => $grades->filter(fn($g) => $g->percentage >= 90)->count(),
            'B' => $grades->filter(fn($g) => $g->percentage >= 80 && $g->percentage < 90)->count(),
            'C' => $grades->filter(fn($g) => $g->percentage >= 70 && $g->percentage < 80)->count(),
            'D' => $grades->filter(fn($g) => $g->percentage >= 60 && $g->percentage < 70)->count(),
            'F' => $grades->filter(fn($g) => $g->percentage < 60)->count(),
        ];

        return [
            'count' => $grades->count(),
            'average' => round($percentages->avg(), 2),
            'median' => round($percentages->median(), 2),
            'highest' => round($percentages->max(), 2),
            'lowest' => round($percentages->min(), 2),
            'passing_rate' => round($grades->where('is_passing', true)->count() / $grades->count() * 100, 1),
            'grade_distribution' => $distribution,
        ];
    }
}
