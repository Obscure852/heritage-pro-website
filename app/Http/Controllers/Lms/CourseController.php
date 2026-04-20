<?php

namespace App\Http\Controllers\Lms;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Lms\Course;
use App\Models\Term;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller {
    public function index(Request $request) {
        $query = Course::with(['grade', 'term', 'instructor', 'modules', 'gradeSubject.subject'])
            ->withCount(['enrollments', 'modules']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by grade
        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }

        // Filter by term
        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        // Filter by instructor (for teachers, show only their courses)
        if (!Gate::allows('manage-lms-courses')) {
            $query->where('instructor_id', Auth::id());
        } elseif ($request->filled('instructor_id')) {
            $query->where('instructor_id', $request->instructor_id);
        }

        // Search by title or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $courses = $query->orderBy('created_at', 'desc')->paginate(15);
        $grades = Grade::where('active', true)->orderBy('name')->get();
        $terms = Term::orderBy('year', 'desc')->orderBy('term', 'desc')->get();
        $instructors = User::where('status', 'Current')
            ->orderBy('firstname')
            ->get();

        $filters = $request->only(['status', 'grade_id', 'term_id', 'instructor_id', 'search']);

        return view('lms.courses.index', compact('courses', 'grades', 'terms', 'instructors', 'filters'));
    }

    public function create() {
        Gate::authorize('manage-lms-courses');

        $currentTerm = TermHelper::getCurrentTerm();
        $grades = Grade::where('active', true)->orderBy('sequence')->get();
        $terms = Term::orderBy('year', 'desc')->orderBy('term', 'desc')->get();
        $instructors = User::where('status', 'Current')
            ->orderBy('firstname')
            ->get();

        return view('lms.courses.create', compact('grades', 'terms', 'instructors', 'currentTerm'));
    }

    public function store(Request $request) {
        Gate::authorize('manage-lms-courses');

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:lms_courses,code',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grade_id' => 'required|exists:grades,id',
            'grade_subject_id' => 'required|exists:grade_subject,id',
            'term_id' => 'required|exists:terms,id',
            'instructor_id' => 'required|exists:users,id',
            'thumbnail' => 'nullable|image|max:2048',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'max_students' => 'nullable|integer|min:1',
            'self_enrollment' => 'boolean',
            'enrollment_key' => 'nullable|string|max:50',
            'passing_grade' => 'nullable|numeric|min:0|max:100',
            'learning_objectives' => 'nullable|string',
            'prerequisites_text' => 'nullable|string',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail_path'] = $request->file('thumbnail')
                ->store('lms/courses/thumbnails', 'public');
        }

        // Parse learning objectives into array
        if (!empty($validated['learning_objectives'])) {
            $objectives = array_filter(array_map('trim', explode("\n", $validated['learning_objectives'])));
            $validated['learning_objectives'] = $objectives;
        }

        $validated['self_enrollment'] = $request->boolean('self_enrollment');
        $validated['created_by'] = Auth::id();
        $validated['status'] = 'draft';

        $course = Course::create($validated);

        return redirect()
            ->route('lms.courses.edit', $course)
            ->with('success', 'Course created successfully. You can now add modules and content.');
    }

    public function show(Course $course) {
        $course->load([
            'grade',
            'term',
            'instructor',
            'gradeSubject.subject',
            'modules' => function ($query) {
                $query->orderBy('sequence');
            },
            'modules.contentItems' => function ($query) {
                $query->orderBy('sequence');
            },
            'enrollments' => function ($query) {
                $query->with('student')->latest()->take(10);
            },
        ]);

        $enrollmentStats = [
            'total' => $course->enrollments()->count(),
            'active' => $course->enrollments()->where('status', 'active')->count(),
            'completed' => $course->enrollments()->where('status', 'completed')->count(),
            'average_progress' => round($course->enrollments()->avg('progress_percentage') ?? 0, 1),
        ];

        return view('lms.courses.show', compact('course', 'enrollmentStats'));
    }

    public function edit(Course $course) {
        Gate::authorize('manage-lms-courses');

        $course->load([
            'modules' => function ($query) {
                $query->orderBy('sequence');
            },
            'modules.contentItems' => function ($query) {
                $query->orderBy('sequence');
            },
            'modules.contentItems.quiz.questions',
            'gradeSubject.subject',
        ]);

        $currentTerm = TermHelper::getCurrentTerm();
        $grades = Grade::where('active', true)->orderBy('sequence')->get();
        $terms = Term::orderBy('year', 'desc')->orderBy('term', 'desc')->get();
        $instructors = User::where('status', 'Current')
            ->orderBy('firstname')
            ->get();

        // Get subjects for the course's grade in current term
        $subjects = GradeSubject::with('subject')
            ->where('grade_id', $course->grade_id)
            ->where('term_id', $currentTerm->id)
            ->where('active', true)
            ->get();

        return view('lms.courses.edit', compact('course', 'grades', 'terms', 'instructors', 'currentTerm', 'subjects'));
    }

    public function update(Request $request, Course $course) {
        Gate::authorize('manage-lms-courses');

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:lms_courses,code,' . $course->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grade_id' => 'required|exists:grades,id',
            'grade_subject_id' => 'required|exists:grade_subject,id',
            'term_id' => 'required|exists:terms,id',
            'instructor_id' => 'required|exists:users,id',
            'thumbnail' => 'nullable|image|max:2048',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'max_students' => 'nullable|integer|min:1',
            'self_enrollment' => 'boolean',
            'enrollment_key' => 'nullable|string|max:50',
            'passing_grade' => 'nullable|numeric|min:0|max:100',
            'learning_objectives' => 'nullable|string',
            'prerequisites_text' => 'nullable|string',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($course->thumbnail_path) {
                \Storage::disk('public')->delete($course->thumbnail_path);
            }
            $validated['thumbnail_path'] = $request->file('thumbnail')
                ->store('lms/courses/thumbnails', 'public');
        }

        // Parse learning objectives into array
        if (!empty($validated['learning_objectives'])) {
            $objectives = array_filter(array_map('trim', explode("\n", $validated['learning_objectives'])));
            $validated['learning_objectives'] = $objectives;
        }

        $validated['self_enrollment'] = $request->boolean('self_enrollment');

        $course->update($validated);

        return redirect()
            ->route('lms.courses.edit', $course)
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course) {
        Gate::authorize('manage-lms-courses');

        // Check if course has enrollments
        if ($course->enrollments()->exists()) {
            return back()->with('error', 'Cannot delete course with active enrollments. Archive it instead.');
        }

        DB::transaction(function () use ($course) {
            // Delete related records in order
            $course->modules()->each(function ($module) {
                $module->contentItems()->delete();
            });
            $course->modules()->delete();

            // Delete thumbnail
            if ($course->thumbnail_path) {
                try {
                    \Storage::disk('public')->delete($course->thumbnail_path);
                } catch (\Exception $e) {
                    \Log::warning('Failed to delete course thumbnail', [
                        'course_id' => $course->id,
                        'path' => $course->thumbnail_path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $course->delete();
        });

        return redirect()
            ->route('lms.courses.index')
            ->with('success', 'Course deleted successfully.');
    }

    public function publish(Course $course) {
        Gate::authorize('manage-lms-courses');

        return DB::transaction(function () use ($course) {
            // Lock the course to prevent concurrent status changes
            $course = Course::lockForUpdate()->find($course->id);

            // Validate course has at least one module with content
            if ($course->modules()->count() === 0) {
                return back()->with('error', 'Course must have at least one module before publishing.');
            }

            // Check if already published
            if ($course->status === 'published') {
                return back()->with('info', 'Course is already published.');
            }

            $course->publish();

            return back()->with('success', 'Course published successfully. Students can now enroll.');
        });
    }

    public function unpublish(Course $course) {
        Gate::authorize('manage-lms-courses');

        $course->unpublish();

        return back()->with('success', 'Course unpublished. No new enrollments allowed.');
    }

    public function archive(Course $course) {
        Gate::authorize('manage-lms-courses');

        $course->archive();

        return back()->with('success', 'Course archived successfully.');
    }

    public function duplicate(Course $course) {
        Gate::authorize('manage-lms-courses');

        $newCourse = $course->replicate();
        $newCourse->code = $course->code . '-copy-' . time();
        $newCourse->title = $course->title . ' (Copy)';
        $newCourse->status = 'draft';
        $newCourse->published_at = null;
        $newCourse->created_by = Auth::id();
        $newCourse->save();

        // Duplicate modules and content
        foreach ($course->modules as $module) {
            $newModule = $module->replicate();
            $newModule->course_id = $newCourse->id;
            $newModule->save();

            foreach ($module->contentItems as $content) {
                $newContent = $content->replicate();
                $newContent->module_id = $newModule->id;
                $newContent->save();
            }
        }

        return redirect()
            ->route('lms.courses.edit', $newCourse)
            ->with('success', 'Course duplicated successfully. You can now modify the copy.');
    }

    /**
     * Get subjects for a specific grade in the current term (API endpoint)
     */
    public function getSubjectsByGrade(Request $request) {
        $gradeId = $request->input('grade_id');
        $currentTerm = TermHelper::getCurrentTerm();

        $subjects = GradeSubject::with('subject')
            ->where('grade_id', $gradeId)
            ->where('term_id', $currentTerm->id)
            ->where('active', true)
            ->get()
            ->map(function ($gradeSubject) {
                return [
                    'id' => $gradeSubject->id,
                    'name' => $gradeSubject->subject->name ?? 'Unknown Subject',
                ];
            });

        return response()->json($subjects);
    }

    /**
     * Get courses as card partial for AJAX loading
     */
    public function partial(Request $request) {
        $query = Course::with(['grade', 'gradeSubject.subject', 'instructor', 'modules'])
            ->withCount(['enrollments', 'modules']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by grade
        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }

        // Filter by term
        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        // Filter by instructor (for teachers, show only their courses)
        if (!Gate::allows('manage-lms-courses')) {
            $query->where('instructor_id', Auth::id());
        }

        // Search by title or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $courses = $query->orderBy('created_at', 'desc')->get();

        return view('lms.courses.partials.courses-cards-partial', compact('courses'));
    }
}
