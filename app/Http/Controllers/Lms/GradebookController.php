<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\CourseGrade;
use App\Models\Lms\Enrollment;
use App\Models\Lms\Grade;
use App\Models\Lms\GradeCategory;
use App\Models\Lms\GradeItem;
use App\Models\Lms\GradebookSettings;
use App\Models\Lms\GradeScale;
use App\Models\Student;
use App\Services\GradebookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class GradebookController extends Controller {
    protected GradebookService $gradebookService;

    public function __construct(GradebookService $gradebookService) {
        $this->gradebookService = $gradebookService;
    }

    /**
     * View course gradebook (instructor view)
     */
    public function index(Course $course) {
        Gate::authorize('manage-lms-content');

        $data = $this->gradebookService->getGradebook($course);
        $statistics = $this->gradebookService->getCourseStatistics($course);

        return view('lms.gradebook.index', array_merge($data, [
            'course' => $course,
            'statistics' => $statistics,
        ]));
    }

    /**
     * Student's gradebook view
     */
    public function studentView(Course $course) {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $enrollment = Enrollment::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$enrollment) {
            return back()->with('error', 'You are not enrolled in this course.');
        }

        $data = $this->gradebookService->getStudentGradebook($course, $student);

        return view('lms.gradebook.student', array_merge($data, [
            'course' => $course,
            'student' => $student,
            'enrollment' => $enrollment,
        ]));
    }

    /**
     * View all grades for a student
     */
    public function myGrades() {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $enrollments = Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->with('course')
            ->get();

        $courseGrades = [];
        foreach ($enrollments as $enrollment) {
            $courseGrades[$enrollment->course_id] = CourseGrade::where('course_id', $enrollment->course_id)
                ->where('student_id', $student->id)
                ->first();
        }

        return view('lms.gradebook.my-grades', compact('student', 'enrollments', 'courseGrades'));
    }

    /**
     * Gradebook settings
     */
    public function settings(Course $course) {
        Gate::authorize('manage-lms-content');

        $settings = GradebookSettings::getOrCreate($course);
        $gradeScales = GradeScale::active()->get();
        $categories = GradeCategory::where('course_id', $course->id)->ordered()->get();

        return view('lms.gradebook.settings', compact('course', 'settings', 'gradeScales', 'categories'));
    }

    /**
     * Update gradebook settings
     */
    public function updateSettings(Request $request, Course $course) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'grade_scale_id' => 'nullable|exists:lms_grade_scales,id',
            'grading_method' => 'required|in:weighted,points,simple_average',
            'passing_grade' => 'required|numeric|min:0|max:100',
            'show_grade_to_students' => 'boolean',
            'show_rank_to_students' => 'boolean',
            'show_statistics' => 'boolean',
            'drop_lowest' => 'boolean',
            'drop_lowest_count' => 'nullable|integer|min:1',
            'include_incomplete' => 'boolean',
        ]);

        $settings = GradebookSettings::getOrCreate($course);
        $settings->update($validated);

        // Recalculate all grades
        $this->gradebookService->recalculateCourseGrades($course);

        return back()->with('success', 'Gradebook settings updated.');
    }

    // ===== Categories =====

    /**
     * Store grade category
     */
    public function storeCategory(Request $request, Course $course) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:100',
            'color' => 'nullable|string|max:7',
            'drop_lowest' => 'boolean',
            'drop_lowest_count' => 'nullable|integer|min:1',
            'is_extra_credit' => 'boolean',
        ]);

        $position = GradeCategory::where('course_id', $course->id)->max('position') + 1;

        GradeCategory::create([
            'course_id' => $course->id,
            'position' => $position,
            ...$validated,
        ]);

        return back()->with('success', 'Category created.');
    }

    /**
     * Update grade category
     */
    public function updateCategory(Request $request, GradeCategory $category) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:100',
            'color' => 'nullable|string|max:7',
            'drop_lowest' => 'boolean',
            'drop_lowest_count' => 'nullable|integer|min:1',
            'is_extra_credit' => 'boolean',
        ]);

        $category->update($validated);

        // Recalculate grades
        $this->gradebookService->recalculateCourseGrades($category->course);

        return back()->with('success', 'Category updated.');
    }

    /**
     * Delete grade category
     */
    public function destroyCategory(GradeCategory $category) {
        Gate::authorize('manage-lms-content');

        $course = $category->course;
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    // ===== Grade Items =====

    /**
     * Store grade item
     */
    public function storeItem(Request $request, Course $course) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:lms_grade_categories,id',
            'type' => 'required|in:manual,assignment,quiz,attendance,participation',
            'max_points' => 'required|numeric|min:0',
            'weight' => 'nullable|numeric|min:0|max:100',
            'due_date' => 'nullable|date',
            'is_extra_credit' => 'boolean',
            'is_hidden' => 'boolean',
        ]);

        $item = GradeItem::create([
            'course_id' => $course->id,
            ...$validated,
        ]);

        // Create pending grades for enrolled students
        $this->gradebookService->createPendingGrades($item);

        return back()->with('success', 'Grade item created.');
    }

    /**
     * Update grade item
     */
    public function updateItem(Request $request, GradeItem $item) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:lms_grade_categories,id',
            'max_points' => 'required|numeric|min:0',
            'weight' => 'nullable|numeric|min:0|max:100',
            'due_date' => 'nullable|date',
            'is_extra_credit' => 'boolean',
            'is_excluded' => 'boolean',
            'is_hidden' => 'boolean',
        ]);

        $item->update($validated);

        // Recalculate grades
        $this->gradebookService->recalculateCourseGrades($item->course);

        return back()->with('success', 'Grade item updated.');
    }

    /**
     * Delete grade item
     */
    public function destroyItem(GradeItem $item) {
        Gate::authorize('manage-lms-content');

        $course = $item->course;
        $item->delete();

        $this->gradebookService->recalculateCourseGrades($course);

        return back()->with('success', 'Grade item deleted.');
    }

    // ===== Grading =====

    /**
     * Grade entry form for an item
     */
    public function gradeItem(GradeItem $item) {
        Gate::authorize('manage-lms-content');

        $item->load('course');
        $enrollments = Enrollment::where('course_id', $item->course_id)
            ->where('status', 'active')
            ->with('student')
            ->get();

        $grades = Grade::where('grade_item_id', $item->id)
            ->get()
            ->keyBy('student_id');

        $statistics = $item->getStatistics();

        return view('lms.gradebook.grade-item', compact('item', 'enrollments', 'grades', 'statistics'));
    }

    /**
     * Save grades for an item
     */
    public function saveGrades(Request $request, GradeItem $item) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*.score' => 'nullable|numeric|min:0',
            'grades.*.feedback' => 'nullable|string',
            'grades.*.status' => 'nullable|in:pending,graded,excused,incomplete,dropped',
        ]);

        foreach ($validated['grades'] as $studentId => $gradeData) {
            $student = Student::find($studentId);
            if (!$student) continue;

            $enrollment = Enrollment::where('course_id', $item->course_id)
                ->where('student_id', $studentId)
                ->first();
            if (!$enrollment) continue;

            $grade = Grade::getOrCreate($item, $student, $enrollment);

            if (isset($gradeData['status']) && $gradeData['status'] === 'excused') {
                $grade->excuse(Auth::user());
            } elseif (isset($gradeData['score']) && $gradeData['score'] !== null) {
                $grade->setGrade((float) $gradeData['score'], Auth::user(), $gradeData['feedback'] ?? null);
            }
        }

        // Recalculate course grades
        $this->gradebookService->recalculateCourseGrades($item->course);

        return back()->with('success', 'Grades saved successfully.');
    }

    /**
     * Quick grade single student
     */
    public function quickGrade(Request $request) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'grade_item_id' => 'required|exists:lms_grade_items,id',
            'student_id' => 'required|exists:students,id',
            'score' => 'required|numeric|min:0',
        ]);

        $item = GradeItem::findOrFail($validated['grade_item_id']);
        $student = Student::findOrFail($validated['student_id']);

        $grade = $this->gradebookService->gradeItem($item, $student, $validated['score'], Auth::user());

        return response()->json([
            'success' => true,
            'grade' => $grade,
            'percentage' => $grade->percentage,
            'letter_grade' => $grade->letter_grade,
        ]);
    }

    /**
     * Override a grade
     */
    public function overrideGrade(Request $request, Grade $grade) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'score' => 'required|numeric|min:0',
            'reason' => 'nullable|string',
        ]);

        $grade->override($validated['score'], Auth::user(), $validated['reason']);

        $this->gradebookService->recalculateCourseGrades($grade->gradeItem->course);

        return back()->with('success', 'Grade overridden.');
    }

    /**
     * View grade history
     */
    public function gradeHistory(Grade $grade) {
        Gate::authorize('manage-lms-content');

        $grade->load(['history.changedBy', 'gradeItem', 'student']);

        return view('lms.gradebook.grade-history', compact('grade'));
    }

    // ===== Export =====

    /**
     * Export gradebook to CSV
     */
    public function export(Course $course) {
        Gate::authorize('manage-lms-content');

        $data = $this->gradebookService->exportGradebook($course);

        $filename = 'gradebook_' . $course->slug . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Finalize grades
     */
    public function finalizeGrades(Course $course) {
        Gate::authorize('manage-lms-content');

        $courseGrades = CourseGrade::where('course_id', $course->id)
            ->where('is_finalized', false)
            ->get();

        foreach ($courseGrades as $grade) {
            $grade->finalize(Auth::user());
        }

        return back()->with('success', 'All grades finalized.');
    }

    /**
     * Recalculate all grades
     */
    public function recalculate(Course $course) {
        Gate::authorize('manage-lms-content');

        $count = $this->gradebookService->recalculateCourseGrades($course);

        return back()->with('success', "Recalculated grades for {$count} students.");
    }
}
