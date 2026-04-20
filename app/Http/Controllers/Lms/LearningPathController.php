<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\LearningPath;
use App\Models\Lms\LearningPathCategory;
use App\Models\Lms\LearningPathCourse;
use App\Models\Lms\LearningPathEnrollment;
use App\Models\Lms\LearningPathProgress;
use App\Services\GamificationService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class LearningPathController extends Controller {
    protected GamificationService $gamificationService;
    protected NotificationService $notificationService;

    public function __construct(GamificationService $gamificationService, NotificationService $notificationService) {
        $this->gamificationService = $gamificationService;
        $this->notificationService = $notificationService;
    }

    /**
     * Browse all learning paths
     */
    public function index(Request $request) {
        $student = Auth::guard('student')->user();
        $categorySlug = $request->query('category');
        $gradeId = $request->query('grade_id');
        $search = $request->query('search');

        $paths = LearningPath::published()
            ->with(['pathCourses.course', 'categories', 'grade'])
            ->when($categorySlug, function ($q) use ($categorySlug) {
                $q->whereHas('categories', fn($q) => $q->where('slug', $categorySlug));
            })
            ->when($gradeId, fn($q) => $q->byGrade($gradeId))
            ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%"))
            ->orderByDesc('is_featured')
            ->orderBy('title')
            ->paginate(12);

        $categories = LearningPathCategory::active()->ordered()->get();
        $featuredPaths = LearningPath::published()->featured()->limit(3)->get();

        // Get user's enrolled paths
        $enrolledPathIds = $student
            ? LearningPathEnrollment::where('student_id', $student->id)->pluck('learning_path_id')
            : collect();

        return view('lms.learning-paths.index', compact(
            'paths',
            'categories',
            'featuredPaths',
            'enrolledPathIds',
            'student',
            'categorySlug',
            'gradeId',
            'search'
        ));
    }

    /**
     * View learning path details
     */
    public function show(LearningPath $learningPath) {
        $student = Auth::guard('student')->user();

        if (!$learningPath->is_published && !Gate::allows('manage-lms-content')) {
            abort(404);
        }

        $learningPath->load(['pathCourses.course', 'milestones', 'categories']);

        $enrollment = $student ? $learningPath->getEnrollment($student) : null;
        $progress = $enrollment ? $enrollment->progress()->with('pathCourse.course')->get() : collect();

        // Get related paths
        $relatedPaths = LearningPath::published()
            ->where('id', '!=', $learningPath->id)
            ->where('level', $learningPath->level)
            ->limit(4)
            ->get();

        return view('lms.learning-paths.show', compact(
            'learningPath',
            'student',
            'enrollment',
            'progress',
            'relatedPaths'
        ));
    }

    /**
     * Enroll in learning path
     */
    public function enroll(LearningPath $learningPath) {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('student.login');
        }

        return DB::transaction(function () use ($learningPath, $student) {
            // Lock learning path row
            $learningPath = LearningPath::lockForUpdate()->find($learningPath->id);

            // Check if already enrolled with lock
            $existingEnrollment = LearningPathEnrollment::where('learning_path_id', $learningPath->id)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->first();

            if ($existingEnrollment) {
                return back()->with('info', 'You are already enrolled in this learning path.');
            }

            $enrollment = $learningPath->enroll($student);

            // Award points for enrollment (outside transaction would be ideal, but keeping for simplicity)
            $this->gamificationService->awardPoints($student, 10, 'enrollment', "Enrolled in learning path: {$learningPath->title}");

            return redirect()->route('lms.learning-paths.learn', $learningPath)
                ->with('success', 'Successfully enrolled in the learning path!');
        });
    }

    /**
     * Learning path learning interface
     */
    public function learn(LearningPath $learningPath) {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $enrollment = $learningPath->getEnrollment($student);

        if (!$enrollment) {
            return redirect()->route('lms.learning-paths.show', $learningPath)
                ->with('error', 'Please enroll first to access this learning path.');
        }

        $learningPath->load(['pathCourses.course', 'milestones']);
        $progress = $enrollment->progress()->with('pathCourse.course', 'courseEnrollment')->get()->keyBy('path_course_id');

        // Check for milestone completions
        foreach ($learningPath->milestones as $milestone) {
            $milestone->checkCompletion($enrollment);
        }

        $milestoneCompletions = $enrollment->milestoneCompletions()->pluck('milestone_id');
        $currentCourse = $enrollment->getCurrentCourse();

        return view('lms.learning-paths.learn', compact(
            'learningPath',
            'enrollment',
            'progress',
            'milestoneCompletions',
            'currentCourse',
            'student'
        ));
    }

    /**
     * Start a course within the learning path
     */
    public function startCourse(LearningPath $learningPath, LearningPathCourse $pathCourse) {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $enrollment = $learningPath->getEnrollment($student);

        if (!$enrollment) {
            return redirect()->route('lms.learning-paths.show', $learningPath);
        }

        $courseProgress = LearningPathProgress::where('enrollment_id', $enrollment->id)
            ->where('path_course_id', $pathCourse->id)
            ->first();

        if (!$courseProgress || !$courseProgress->isAccessible()) {
            return back()->with('error', 'This course is not available yet.');
        }

        // Check if already enrolled in the course
        $course = $pathCourse->course;
        $courseEnrollment = $course->getEnrollment($student);

        if (!$courseEnrollment) {
            $courseEnrollment = $course->enroll($student);
        }

        // Update path progress
        if ($courseProgress->status === LearningPathProgress::STATUS_AVAILABLE) {
            $courseProgress->start($courseEnrollment);
        }

        return redirect()->route('lms.courses.learn', $course);
    }

    /**
     * My learning paths
     */
    public function myPaths() {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $enrollments = LearningPathEnrollment::where('student_id', $student->id)
            ->with(['learningPath.pathCourses.course', 'progress'])
            ->orderByDesc('updated_at')
            ->get();

        $activeEnrollments = $enrollments->where('status', 'active');
        $completedEnrollments = $enrollments->where('status', 'completed');

        return view('lms.learning-paths.my-paths', compact(
            'enrollments',
            'activeEnrollments',
            'completedEnrollments',
            'student'
        ));
    }

    // ===== Admin Methods =====

    /**
     * Admin: Create learning path form
     */
    public function create() {
        Gate::authorize('manage-lms-content');

        $courses = Course::orderBy('title')->get();
        $categories = LearningPathCategory::active()->ordered()->get();

        return view('lms.learning-paths.admin.create', compact('courses', 'categories'));
    }

    /**
     * Admin: Store learning path
     */
    public function store(Request $request) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'objectives' => 'nullable|array',
            'grade_id' => 'required|exists:grades,id',
            'thumbnail' => 'nullable|image|max:2048',
            'enforce_sequence' => 'boolean',
            'allow_skip' => 'boolean',
            'courses' => 'required|array|min:1',
            'courses.*' => 'exists:lms_courses,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:lms_learning_path_categories,id',
        ]);

        $path = LearningPath::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'objectives' => $validated['objectives'] ?? [],
            'grade_id' => $validated['grade_id'],
            'enforce_sequence' => $request->boolean('enforce_sequence'),
            'allow_skip' => $request->boolean('allow_skip'),
            'created_by' => Auth::id(),
        ]);

        if ($request->hasFile('thumbnail')) {
            $path->update([
                'thumbnail' => $request->file('thumbnail')->store('lms/learning-paths', 'public'),
            ]);
        }

        // Add courses
        foreach ($validated['courses'] as $position => $courseId) {
            $path->addCourse(Course::find($courseId), ['position' => $position]);
        }

        // Add categories
        if (!empty($validated['categories'])) {
            $path->categories()->sync($validated['categories']);
        }

        return redirect()->route('lms.learning-paths.show', $path)
            ->with('success', 'Learning path created successfully.');
    }

    /**
     * Admin: Edit learning path
     */
    public function edit(LearningPath $learningPath) {
        Gate::authorize('manage-lms-content');

        $learningPath->load(['pathCourses.course', 'categories']);
        $courses = Course::orderBy('title')->get();
        $categories = LearningPathCategory::active()->ordered()->get();

        return view('lms.learning-paths.admin.edit', compact('learningPath', 'courses', 'categories'));
    }

    /**
     * Admin: Update learning path
     */
    public function update(Request $request, LearningPath $learningPath) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'objectives' => 'nullable|array',
            'grade_id' => 'required|exists:grades,id',
            'thumbnail' => 'nullable|image|max:2048',
            'enforce_sequence' => 'boolean',
            'allow_skip' => 'boolean',
            'courses' => 'required|array|min:1',
            'courses.*' => 'exists:lms_courses,id',
            'categories' => 'nullable|array',
        ]);

        $learningPath->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'objectives' => $validated['objectives'] ?? [],
            'grade_id' => $validated['grade_id'],
            'enforce_sequence' => $request->boolean('enforce_sequence'),
            'allow_skip' => $request->boolean('allow_skip'),
        ]);

        if ($request->hasFile('thumbnail')) {
            $learningPath->update([
                'thumbnail' => $request->file('thumbnail')->store('lms/learning-paths', 'public'),
            ]);
        }

        // Update courses
        $learningPath->pathCourses()->delete();
        foreach ($validated['courses'] as $position => $courseId) {
            $learningPath->addCourse(Course::find($courseId), ['position' => $position]);
        }

        // Update categories
        $learningPath->categories()->sync($validated['categories'] ?? []);

        return redirect()->route('lms.learning-paths.show', $learningPath)
            ->with('success', 'Learning path updated successfully.');
    }

    /**
     * Admin: Publish/unpublish
     */
    public function togglePublish(LearningPath $learningPath) {
        Gate::authorize('manage-lms-content');

        if ($learningPath->is_published) {
            $learningPath->unpublish();
            $message = 'Learning path unpublished.';
        } else {
            $learningPath->publish();
            $message = 'Learning path published.';
        }

        return back()->with('success', $message);
    }

    /**
     * Admin: Delete learning path
     */
    public function destroy(LearningPath $learningPath) {
        Gate::authorize('manage-lms-content');

        $learningPath->delete();

        return redirect()->route('lms.learning-paths.index')
            ->with('success', 'Learning path deleted.');
    }
}
