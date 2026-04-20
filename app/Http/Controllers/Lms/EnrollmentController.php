<?php

namespace App\Http\Controllers\Lms;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Models\Klass;
use App\Models\Lms\Course;
use App\Models\Lms\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EnrollmentController extends Controller {
    /**
     * Display enrollments for a course
     */
    public function index(Request $request, Course $course) {
        Gate::authorize('manage-lms-enrollments');

        $query = $course->enrollments()
            ->with(['student' => function ($q) {
                $q->with('currentClassRelation');
            }]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by class
        if ($request->filled('klass_id')) {
            $klassId = $request->klass_id;
            $query->whereHas('student.classes', function ($q) use ($klassId) {
                $q->where('klasses.id', $klassId);
            });
        }

        // Search by student name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->orderBy('enrolled_at', 'desc')->paginate(25);

        // Get classes for filter
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        $classes = Klass::where('term_id', $selectedTermId)
            ->orderBy('name')
            ->get();

        $filters = $request->only(['status', 'klass_id', 'search']);

        return view('lms.enrollments.index', compact('course', 'enrollments', 'classes', 'filters'));
    }

    /**
     * Show bulk enrollment form
     */
    public function create(Course $course) {
        Gate::authorize('manage-lms-enrollments');

        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;

        // Get classes with student counts
        $classes = Klass::where('term_id', $selectedTermId)
            ->withCount('students')
            ->orderBy('name')
            ->get();

        // Get students not yet enrolled
        $enrolledStudentIds = $course->enrollments()->pluck('student_id');
        $availableStudents = Student::where('status', 'Current')
            ->whereNotIn('id', $enrolledStudentIds)
            ->orderBy('first_name')
            ->get();

        return view('lms.enrollments.create', compact('course', 'classes', 'availableStudents'));
    }

    /**
     * Bulk enroll students
     */
    public function store(Request $request, Course $course) {
        Gate::authorize('manage-lms-enrollments');

        $validated = $request->validate([
            'enrollment_type' => 'required|in:individual,class',
            'student_ids' => 'required_if:enrollment_type,individual|array',
            'student_ids.*' => 'exists:students,id',
            'klass_id' => 'required_if:enrollment_type,class|exists:klasses,id',
        ]);

        $enrolledCount = 0;
        $alreadyEnrolled = 0;

        DB::transaction(function () use ($validated, $course, &$enrolledCount, &$alreadyEnrolled) {
            // Lock course row to prevent concurrent capacity changes
            $course = Course::lockForUpdate()->find($course->id);

            $studentIds = [];

            if ($validated['enrollment_type'] === 'class') {
                $klass = Klass::findOrFail($validated['klass_id']);
                $studentIds = $klass->students()->pluck('students.id')->toArray();
            } else {
                $studentIds = $validated['student_ids'];
            }

            // Get current enrollment count once
            $currentCount = Enrollment::where('course_id', $course->id)
                ->where('status', '!=', 'dropped')
                ->count();

            foreach ($studentIds as $studentId) {
                // Check if already enrolled with lock
                $exists = Enrollment::where('course_id', $course->id)
                    ->where('student_id', $studentId)
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    $alreadyEnrolled++;
                    continue;
                }

                // Check max students limit
                if ($course->max_students && ($currentCount + $enrolledCount) >= $course->max_students) {
                    break;
                }

                Enrollment::create([
                    'course_id' => $course->id,
                    'student_id' => $studentId,
                    'enrollment_type' => 'manual',
                    'status' => 'active',
                    'enrolled_at' => now(),
                    'enrolled_by' => Auth::id(),
                ]);

                $enrolledCount++;
            }
        });

        $message = "{$enrolledCount} student(s) enrolled successfully.";
        if ($alreadyEnrolled > 0) {
            $message .= " {$alreadyEnrolled} were already enrolled.";
        }

        return redirect()
            ->route('lms.enrollments.index', $course)
            ->with('success', $message);
    }

    /**
     * Self-enrollment (for students)
     */
    public function selfEnroll(Request $request, Course $course) {
        // Try student guard first, then fall back to finding student by user email
        $student = Auth::guard('student')->user();

        if (!$student) {
            $user = Auth::user();
            if ($user) {
                $student = Student::where('email', $user->email)->first();
            }
        }

        if (!$student) {
            return back()->with('error', 'You must have a student profile to enroll in courses.');
        }

        // Check if self-enrollment is enabled
        if (!$course->self_enrollment) {
            return back()->with('error', 'Self-enrollment is not enabled for this course.');
        }

        // Check enrollment key if required
        if ($course->enrollment_key) {
            $request->validate([
                'enrollment_key' => 'required|string',
            ]);

            if ($request->enrollment_key !== $course->enrollment_key) {
                return back()->with('error', 'Invalid enrollment key.');
            }
        }

        // Check course status
        if ($course->status !== 'published') {
            return back()->with('error', 'This course is not currently accepting enrollments.');
        }

        return DB::transaction(function () use ($course, $student) {
            // Lock course row to prevent concurrent capacity changes
            $course = Course::lockForUpdate()->find($course->id);

            // Check existing enrollment with lock
            $existingEnrollment = Enrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->first();

            if ($existingEnrollment) {
                return redirect()
                    ->route('lms.courses.learn', $course)
                    ->with('info', 'You are already enrolled in this course.');
            }

            // Check capacity atomically
            $currentCount = Enrollment::where('course_id', $course->id)
                ->where('status', '!=', 'dropped')
                ->count();

            if ($course->max_students && $currentCount >= $course->max_students) {
                return back()->with('error', 'This course has reached its maximum enrollment capacity.');
            }

            Enrollment::create([
                'course_id' => $course->id,
                'student_id' => $student->id,
                'enrollment_type' => 'self',
                'status' => 'active',
                'enrolled_at' => now(),
            ]);

            return redirect()
                ->route('lms.courses.learn', $course)
                ->with('success', 'You have successfully enrolled in this course.');
        });
    }

    /**
     * Remove enrollment (admin)
     */
    public function destroy(Enrollment $enrollment) {
        Gate::authorize('manage-lms-enrollments');

        $course = $enrollment->course;
        $studentName = $enrollment->student->full_name;

        $enrollment->delete();

        return redirect()
            ->route('lms.enrollments.index', $course)
            ->with('success', "{$studentName} has been unenrolled from this course.");
    }

    /**
     * Student drops course
     */
    public function drop(Enrollment $enrollment) {
        // Try student guard first, then fall back to finding student by user email
        $student = Auth::guard('student')->user();

        if (!$student) {
            $user = Auth::user();
            if ($user) {
                $student = Student::where('email', $user->email)->first();
            }
        }

        if (!$student) {
            abort(403, 'You must have a student profile to drop courses.');
        }

        // Verify this is the student's enrollment
        if ($enrollment->student_id !== $student->id) {
            abort(403);
        }

        $enrollment->update([
            'status' => 'dropped',
            'dropped_at' => now(),
        ]);

        return redirect()
            ->route('lms.my-courses')
            ->with('success', 'You have dropped this course.');
    }

    /**
     * Student's enrolled courses
     */
    public function myCourses(Request $request) {
        // Try student guard first, then fall back to finding student by user email
        $student = Auth::guard('student')->user();

        if (!$student) {
            // Try to find student by logged-in user's email
            $user = Auth::user();
            if ($user) {
                $student = Student::where('email', $user->email)->first();
            }
        }

        // If still no student found, show empty state
        if (!$student) {
            $enrollments = collect();
            $activeCourses = collect();
            $completedCourses = collect();
            return view('lms.student.my-courses', compact('enrollments', 'activeCourses', 'completedCourses'));
        }

        $query = $student->lmsEnrollments()
            ->with(['course' => function ($q) {
                $q->with(['grade', 'instructor', 'modules']);
            }]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: show active courses
            $query->whereIn('status', ['active', 'completed']);
        }

        $enrollments = $query->orderBy('enrolled_at', 'desc')->get();

        // Group by status
        $activeCourses = $enrollments->where('status', 'active');
        $completedCourses = $enrollments->where('status', 'completed');

        return view('lms.student.my-courses', compact('enrollments', 'activeCourses', 'completedCourses'));
    }

    /**
     * Student learning view for a course
     */
    public function learn(Course $course) {
        // Try student guard first, then fall back to finding student by user email
        $student = Auth::guard('student')->user();

        if (!$student) {
            $user = Auth::user();
            if ($user) {
                $student = Student::where('email', $user->email)->first();
            }
        }

        if (!$student) {
            abort(403, 'You must be enrolled as a student to access this course.');
        }

        $enrollment = Enrollment::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $course->load([
            'instructor',
            'modules' => function ($q) {
                $q->orderBy('sequence');
            },
            'modules.contentItems' => function ($q) {
                $q->orderBy('sequence');
            },
        ]);

        // Get content progress for this enrollment
        $contentProgress = $enrollment->contentProgress()
            ->pluck('progress_percentage', 'content_item_id')
            ->toArray();

        $completedContent = $enrollment->contentProgress()
            ->whereNotNull('completed_at')
            ->pluck('content_item_id')
            ->toArray();

        // Determine next content to continue
        $nextContent = null;
        foreach ($course->modules as $module) {
            foreach ($module->contentItems as $content) {
                if (!in_array($content->id, $completedContent)) {
                    $nextContent = $content;
                    break 2;
                }
            }
        }

        return view('lms.student.learn', compact(
            'course',
            'enrollment',
            'contentProgress',
            'completedContent',
            'nextContent'
        ));
    }
}
