<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Models\Lms\Appointment;
use App\Models\Lms\AvailabilitySchedule;
use App\Models\Lms\CalendarEvent;
use App\Models\Lms\ContentItem;
use App\Models\Lms\ContentProgress;
use App\Models\Lms\Course;
use App\Models\Lms\CourseSchedule;
use App\Models\Lms\Deadline;
use App\Models\Lms\Enrollment;
use App\Models\Lms\LearningPath;
use App\Models\Lms\LearningPathEnrollment;
use App\Models\Lms\LearningPathProgress;
use App\Models\Lms\ScormPackage;
use App\Models\Student;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentLmsController extends Controller {
    public function __construct() {
        $this->middleware('auth:student');
    }

    /**
     * Get the authenticated student
     */
    protected function getStudent(): ?Student {
        return Auth::guard('student')->user();
    }

    /**
     * Student's enrolled courses
     */
    public function myCourses(Request $request) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
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

        // Separate active and completed
        $activeCourses = $enrollments->where('status', 'active');
        $completedCourses = $enrollments->where('status', 'completed');

        return view('students.portal.lms.my-courses', compact('enrollments', 'activeCourses', 'completedCourses', 'student'));
    }

    /**
     * Course learning interface
     */
    public function learn(Course $course) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        // Check enrollment
        $enrollment = $student->lmsEnrollments()
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            return redirect()->route('student.lms.courses')
                ->with('error', 'You are not enrolled in this course.');
        }

        // Check if this course is part of a learning path and if it's locked
        $pathProgress = $this->getPathProgressForCourse($student, $course);
        if ($pathProgress && !$pathProgress->isAccessible()) {
            return redirect()->route('student.lms.my-learning-paths')
                ->with('error', 'This course is locked. Please complete the prerequisite courses first.');
        }

        // Mark enrollment as started if first access
        $enrollment->markAsStarted();

        // If part of a learning path, update progress to in_progress
        if ($pathProgress && $pathProgress->status === 'available') {
            $pathProgress->start($enrollment);
        }

        // Load course with modules and content
        $course->load([
            'modules' => function ($q) {
                $q->orderBy('sequence')->with(['contentItems' => function ($c) {
                    $c->orderBy('sequence');
                }]);
            },
            'instructor',
        ]);

        // Get student's progress through enrollment
        $progress = ContentProgress::where('enrollment_id', $enrollment->id)
            ->whereIn('content_item_id', $course->modules->pluck('contentItems')->flatten()->pluck('id'))
            ->get()
            ->keyBy('content_item_id');

        // Record activity
        $enrollment->recordActivity();

        return view('students.portal.lms.learn', compact('course', 'enrollment', 'progress', 'student', 'pathProgress'));
    }

    /**
     * Get learning path progress for a course if student is enrolled in any path containing it
     */
    protected function getPathProgressForCourse(Student $student, Course $course): ?LearningPathProgress {
        $pathEnrollment = $student->learningPathEnrollments()
            ->where('status', 'active')
            ->whereHas('learningPath.pathCourses', function ($q) use ($course) {
                $q->where('course_id', $course->id);
            })
            ->with(['progress.pathCourse'])
            ->first();

        if (!$pathEnrollment) {
            return null;
        }

        return $pathEnrollment->progress
            ->first(fn($p) => $p->pathCourse->course_id === $course->id);
    }

    /**
     * Browse available courses
     */
    public function courses(Request $request) {
        $student = $this->getStudent();

        $query = Course::where('status', 'published')
            ->with(['grade', 'instructor', 'enrollments']);

        // Filter by grade if student has a current class
        if ($student && $student->currentClass) {
            $gradeId = $student->currentClass->grade_id ?? null;
            if ($gradeId && !$request->filled('show_all')) {
                $query->where(function ($q) use ($gradeId) {
                    $q->where('grade_id', $gradeId)
                        ->orWhereNull('grade_id');
                });
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $courses = $query->orderBy('title')->paginate(12);

        // Get student's enrollments
        $enrolledCourseIds = $student ? $student->lmsEnrollments()->pluck('course_id')->toArray() : [];

        return view('students.portal.lms.courses', compact('courses', 'enrolledCourseIds', 'student'));
    }

    /**
     * View course details
     */
    public function showCourse(Course $course) {
        $student = $this->getStudent();

        // Check if course is published or student is enrolled
        if ($course->status !== 'published') {
            return redirect()->route('student.lms.courses')
                ->with('error', 'This course is not available.');
        }

        $course->load(['modules.contentItems', 'instructor', 'grade']);

        $enrollment = null;
        if ($student) {
            $enrollment = $student->lmsEnrollments()
                ->where('course_id', $course->id)
                ->first();
        }

        return view('students.portal.lms.course-detail', compact('course', 'enrollment', 'student'));
    }

    /**
     * Self-enroll in a course
     */
    public function selfEnroll(Request $request, Course $course) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
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

        // Check if already enrolled
        if ($student->lmsEnrollments()->where('course_id', $course->id)->exists()) {
            return redirect()
                ->route('student.lms.learn', $course)
                ->with('info', 'You are already enrolled in this course.');
        }

        // Check max students
        if ($course->max_students && $course->enrollments()->count() >= $course->max_students) {
            return back()->with('error', 'This course has reached its maximum enrollment capacity.');
        }

        // Check course status
        if ($course->status !== 'published') {
            return back()->with('error', 'This course is not currently accepting enrollments.');
        }

        Enrollment::create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        return redirect()
            ->route('student.lms.learn', $course)
            ->with('success', 'You have successfully enrolled in this course.');
    }

    /**
     * Drop a course
     */
    public function dropCourse(Enrollment $enrollment) {
        $student = $this->getStudent();

        if (!$student || $enrollment->student_id !== $student->id) {
            abort(403);
        }

        $enrollment->update([
            'status' => 'dropped',
            'dropped_at' => now(),
        ]);

        return redirect()
            ->route('student.lms.my-courses')
            ->with('success', 'You have dropped this course.');
    }

    /**
     * Browse learning paths
     */
    public function learningPaths(Request $request) {
        $student = $this->getStudent();

        $query = LearningPath::published()
            ->with(['pathCourses.course', 'categories', 'creator']);

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('lms_learning_path_categories.id', $request->category);
            });
        }

        // Filter by level
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $learningPaths = $query->orderBy('title')->paginate(12);

        // Get student's enrolled paths
        $enrolledPathIds = $student ? $student->learningPathEnrollments()->pluck('learning_path_id')->toArray() : [];

        return view('students.portal.lms.learning-paths', compact('learningPaths', 'enrolledPathIds', 'student'));
    }

    /**
     * My learning paths
     */
    public function myLearningPaths() {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $enrollments = $student->learningPathEnrollments()
            ->with(['learningPath.pathCourses.course', 'learningPath.categories'])
            ->orderBy('enrolled_at', 'desc')
            ->get();

        // Separate in-progress and completed
        $inProgress = $enrollments->where('status', 'active');
        $completed = $enrollments->where('status', 'completed');

        return view('students.portal.lms.my-learning-paths', compact('enrollments', 'inProgress', 'completed', 'student'));
    }

    /**
     * View learning path details
     */
    public function showLearningPath(LearningPath $learningPath) {
        $student = $this->getStudent();

        if (!$learningPath->is_published) {
            return redirect()->route('student.lms.learning-paths')
                ->with('error', 'This learning path is not available.');
        }

        $learningPath->load(['pathCourses.course.modules', 'categories', 'creator']);

        $enrollment = null;
        $courseProgress = [];
        if ($student) {
            $enrollment = $student->learningPathEnrollments()
                ->where('learning_path_id', $learningPath->id)
                ->first();

            // Get course completion status
            foreach ($learningPath->pathCourses as $pathCourse) {
                $courseEnrollment = $student->lmsEnrollments()
                    ->where('course_id', $pathCourse->course_id)
                    ->first();
                $courseProgress[$pathCourse->course_id] = $courseEnrollment?->status ?? 'not_started';
            }
        }

        return view('students.portal.lms.learning-path-detail', compact('learningPath', 'enrollment', 'courseProgress', 'student'));
    }

    /**
     * Enroll in learning path
     */
    public function enrollInPath(LearningPath $learningPath) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        if (!$learningPath->is_published) {
            return back()->with('error', 'This learning path is not available.');
        }

        // Check if already enrolled
        if ($student->learningPathEnrollments()->where('learning_path_id', $learningPath->id)->exists()) {
            return redirect()
                ->route('student.lms.learning-path.learn', $learningPath)
                ->with('info', 'You are already enrolled in this learning path.');
        }

        // Load path courses for initialization
        $learningPath->load('pathCourses');

        // Use the model's enroll method which properly initializes course progress records
        // This sets up locked/available status based on prerequisites
        LearningPathEnrollment::enroll($learningPath, $student);

        return redirect()
            ->route('student.lms.learning-path.learn', $learningPath)
            ->with('success', 'You have successfully enrolled in this learning path.');
    }

    /**
     * Learning path learning interface
     */
    public function learnPath(LearningPath $learningPath) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $enrollment = $student->learningPathEnrollments()
            ->where('learning_path_id', $learningPath->id)
            ->with(['progress.pathCourse'])
            ->first();

        if (!$enrollment) {
            return redirect()->route('student.lms.learning-path', $learningPath)
                ->with('error', 'You need to enroll first.');
        }

        // Mark path as started if first access
        $enrollment->start();

        $learningPath->load(['pathCourses.course.modules.contentItems', 'categories']);

        // Get course enrollments for progress display
        $courseEnrollments = $student->lmsEnrollments()
            ->whereIn('course_id', $learningPath->pathCourses->pluck('course_id'))
            ->get()
            ->keyBy('course_id');

        // Build path progress map keyed by course_id for easy lookup in view
        $pathProgress = $enrollment->progress->keyBy(fn($p) => $p->pathCourse->course_id);

        // Calculate completion from path progress (more accurate than course enrollments)
        $completedCourses = $enrollment->progress->where('status', 'completed')->count();
        $totalCourses = $learningPath->pathCourses->count();

        // Check if path enforces sequence and update display accordingly
        $enforceSequence = $learningPath->enforce_sequence;

        return view('students.portal.lms.learn-path', compact(
            'learningPath',
            'enrollment',
            'courseEnrollments',
            'pathProgress',
            'completedCourses',
            'totalCourses',
            'student',
            'enforceSequence'
        ));
    }

    /**
     * Play SCORM content
     */
    public function playScorm(Course $course, ContentItem $contentItem) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        // Check enrollment
        $enrollment = $student->lmsEnrollments()
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            return redirect()->route('student.lms.courses')
                ->with('error', 'You are not enrolled in this course.');
        }

        // Verify content item belongs to this course
        if ($contentItem->module->course_id !== $course->id) {
            return redirect()->route('student.lms.learn', $course)
                ->with('error', 'Invalid content item.');
        }

        // Verify content type is SCORM
        if ($contentItem->type !== 'scorm' || !$contentItem->contentable instanceof ScormPackage) {
            return redirect()->route('student.lms.learn', $course)
                ->with('error', 'This content is not a SCORM package.');
        }

        $package = $contentItem->contentable;

        // Check if student can attempt
        if (!$package->canStudentAttempt($student->id)) {
            return redirect()->route('student.lms.learn', $course)
                ->with('error', 'You have reached the maximum number of attempts for this content.');
        }

        // Get or create attempt
        $attempt = $package->getOrCreateAttempt($student->id, $contentItem->id);

        return view('students.portal.lms.scorm-player', compact('package', 'attempt', 'contentItem', 'course'));
    }

    /**
     * Mark content as complete
     */
    public function markContentComplete(Request $request, Course $course, ContentItem $contentItem) {
        $student = $this->getStudent();

        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Verify content item belongs to this course
        if ($contentItem->module->course_id !== $course->id) {
            return response()->json(['error' => 'Invalid content item'], 400);
        }

        // Get enrollment
        $enrollment = $student->lmsEnrollments()
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'Not enrolled'], 403);
        }

        // Get or create progress record
        $progress = ContentProgress::firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'content_item_id' => $contentItem->id,
            ],
            [
                'status' => 'not_started',
                'started_at' => now(),
            ]
        );

        // Use the model's markAsCompleted which properly updates enrollment progress
        // This handles mandatory content tracking and final grade calculation
        $score = $request->input('score');
        $scorePercentage = $request->input('score_percentage');
        $progress->markAsCompleted($score, $scorePercentage);

        // Refresh enrollment to get updated progress
        $enrollment->refresh();

        // Sync with learning path if this course is part of one
        $this->syncLearningPathProgress($student, $enrollment);

        // Get counts for response
        $totalMandatory = $course->contentItems()->where('is_mandatory', true)->count();
        $completedMandatory = $enrollment->contentProgress()
            ->whereHas('contentItem', fn($q) => $q->where('is_mandatory', true))
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'success' => true,
            'completed_count' => $completedMandatory,
            'total_count' => $totalMandatory,
            'progress_percent' => $enrollment->progress_percentage,
            'course_completed' => $enrollment->status === 'completed',
        ]);
    }

    /**
     * Sync learning path progress when a course is completed
     */
    protected function syncLearningPathProgress(Student $student, Enrollment $enrollment): void {
        // Check if this course is part of any learning path the student is enrolled in
        $pathEnrollments = $student->learningPathEnrollments()
            ->where('status', 'active')
            ->whereHas('learningPath.pathCourses', function ($q) use ($enrollment) {
                $q->where('course_id', $enrollment->course_id);
            })
            ->with(['progress.pathCourse'])
            ->get();

        foreach ($pathEnrollments as $pathEnrollment) {
            // Find the progress record for this course
            $courseProgress = $pathEnrollment->progress
                ->first(fn($p) => $p->pathCourse->course_id === $enrollment->course_id);

            if ($courseProgress) {
                // Link the course enrollment if not already linked
                if (!$courseProgress->course_enrollment_id) {
                    $courseProgress->update(['course_enrollment_id' => $enrollment->id]);
                }

                // Sync progress with course enrollment (handles completion, unlocking next courses)
                $courseProgress->syncWithCourseEnrollment();
            }
        }
    }

    /**
     * Student calendar view
     */
    public function calendar() {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        // Get enrolled course IDs
        $enrolledCourseIds = $student->lmsEnrollments()
            ->where('status', 'active')
            ->pluck('course_id')
            ->toArray();

        // Get stats
        $schedulesCount = CourseSchedule::whereIn('course_id', $enrolledCourseIds)
            ->active()
            ->count();

        $deadlinesCount = Deadline::whereIn('course_id', $enrolledCourseIds)
            ->where('is_active', true)
            ->where('due_date', '>=', now())
            ->count();

        $eventsCount = CalendarEvent::visibleToStudent($student)
            ->where('start_date', '>=', now())
            ->count();

        $appointmentsCount = Appointment::where('student_id', $student->id)
            ->where('status', 'confirmed')
            ->where('start_time', '>=', now())
            ->count();

        return view('students.portal.lms.calendar', compact(
            'student',
            'schedulesCount',
            'deadlinesCount',
            'eventsCount',
            'appointmentsCount'
        ));
    }

    /**
     * Get calendar events for FullCalendar (AJAX)
     */
    public function calendarEvents(Request $request) {
        $student = $this->getStudent();

        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $start = Carbon::parse($request->input('start', now()->startOfMonth()));
        $end = Carbon::parse($request->input('end', now()->endOfMonth()));

        // Get enrolled course IDs
        $enrolledCourseIds = $student->lmsEnrollments()
            ->where('status', 'active')
            ->pluck('course_id')
            ->toArray();

        $events = [];

        // 1. Calendar Events (using audience targeting)
        $calendarEvents = CalendarEvent::visibleToStudent($student)
            ->where('start_date', '>=', $start)
            ->where('start_date', '<=', $end)
            ->get();

        foreach ($calendarEvents as $event) {
            $events[] = [
                'id' => 'event-' . $event->id,
                'title' => $event->title,
                'start' => $event->start_date->toIso8601String(),
                'end' => $event->end_date?->toIso8601String(),
                'allDay' => $event->all_day,
                'color' => $event->color ?? '#4e73df',
                'extendedProps' => [
                    'type' => 'event',
                    'description' => $event->description,
                    'location' => $event->location,
                    'meeting_url' => $event->meeting_url,
                ],
            ];
        }

        // 2. Course Schedules (generate recurring occurrences)
        $schedules = CourseSchedule::whereIn('course_id', $enrolledCourseIds)
            ->active()
            ->with('course')
            ->get();

        foreach ($schedules as $schedule) {
            $period = CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                if ($date->dayOfWeek === $schedule->day_of_week) {
                    // Check effective dates
                    if ($schedule->effective_from && $date->lt($schedule->effective_from)) {
                        continue;
                    }
                    if ($schedule->effective_until && $date->gt($schedule->effective_until)) {
                        continue;
                    }

                    $startTime = is_string($schedule->start_time) ? $schedule->start_time : $schedule->start_time->format('H:i:s');
                    $endTime = is_string($schedule->end_time) ? $schedule->end_time : $schedule->end_time->format('H:i:s');

                    $startDateTime = $date->copy()->setTimeFromTimeString($startTime);
                    $endDateTime = $date->copy()->setTimeFromTimeString($endTime);

                    $events[] = [
                        'id' => 'schedule-' . $schedule->id . '-' . $date->format('Y-m-d'),
                        'title' => $schedule->title ?? ($schedule->course->title ?? 'Class'),
                        'start' => $startDateTime->toIso8601String(),
                        'end' => $endDateTime->toIso8601String(),
                        'color' => '#36b9cc',
                        'extendedProps' => [
                            'type' => 'schedule',
                            'location' => $schedule->location,
                            'meeting_url' => $schedule->meeting_url,
                        ],
                    ];
                }
            }
        }

        // 3. Deadlines
        $deadlines = Deadline::whereIn('course_id', $enrolledCourseIds)
            ->where('is_active', true)
            ->where('due_date', '>=', $start)
            ->where('due_date', '<=', $end)
            ->with('course')
            ->get();

        foreach ($deadlines as $deadline) {
            // Check for student-specific extension
            $studentDeadline = $deadline->studentDeadlines()
                ->where('student_id', $student->id)
                ->first();

            $dueDate = $studentDeadline?->extended_due_date ?? $deadline->due_date;

            $events[] = [
                'id' => 'deadline-' . $deadline->id,
                'title' => $deadline->title,
                'start' => $dueDate->toIso8601String(),
                'allDay' => false,
                'color' => '#e74a3b',
                'extendedProps' => [
                    'type' => 'deadline',
                    'deadline_type' => $deadline->type,
                    'description' => $deadline->description,
                    'has_extension' => $studentDeadline !== null,
                ],
            ];
        }

        // 4. Appointments
        $appointments = Appointment::where('student_id', $student->id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->where('start_time', '>=', $start)
            ->where('start_time', '<=', $end)
            ->with('schedule.user')
            ->get();

        foreach ($appointments as $appointment) {
            $events[] = [
                'id' => 'appointment-' . $appointment->id,
                'title' => ($appointment->schedule->user->name ?? 'Appointment'),
                'start' => $appointment->start_time->toIso8601String(),
                'end' => $appointment->end_time?->toIso8601String(),
                'color' => '#6f42c1',
                'extendedProps' => [
                    'type' => 'appointment',
                    'status' => $appointment->status,
                    'location' => $appointment->schedule->location ?? null,
                    'meeting_url' => $appointment->meeting_url,
                ],
            ];
        }

        return response()->json($events);
    }

    /**
     * Get upcoming deadlines for widget
     */
    public function upcomingDeadlines() {
        $student = $this->getStudent();

        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $enrolledCourseIds = $student->lmsEnrollments()
            ->where('status', 'active')
            ->pluck('course_id')
            ->toArray();

        $deadlines = Deadline::whereIn('course_id', $enrolledCourseIds)
            ->where('is_active', true)
            ->where('due_date', '>=', now())
            ->orderBy('due_date')
            ->limit(10)
            ->with('course')
            ->get()
            ->map(function ($deadline) use ($student) {
                $studentDeadline = $deadline->studentDeadlines()
                    ->where('student_id', $student->id)
                    ->first();

                return [
                    'id' => $deadline->id,
                    'title' => $deadline->title,
                    'course' => $deadline->course?->title ?? 'Course',
                    'due_datetime' => $studentDeadline?->extended_due_date ?? $deadline->due_date,
                    'deadline_type' => $deadline->type,
                    'has_extension' => $studentDeadline !== null,
                ];
            });

        return response()->json($deadlines);
    }

    /**
     * View student's appointments
     */
    public function myAppointments() {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        // Upcoming appointments
        $upcomingAppointments = Appointment::where('student_id', $student->id)
            ->where('status', 'confirmed')
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->with(['schedule.user'])
            ->get();

        // Past appointments
        $pastAppointments = Appointment::where('student_id', $student->id)
            ->where(function ($q) {
                $q->where('status', 'completed')
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'confirmed')
                            ->where('start_time', '<', now());
                    });
            })
            ->orderBy('start_time', 'desc')
            ->limit(20)
            ->with(['schedule.user'])
            ->get();

        // Cancelled appointments
        $cancelledAppointments = Appointment::where('student_id', $student->id)
            ->where('status', 'cancelled')
            ->orderBy('cancelled_at', 'desc')
            ->limit(10)
            ->with(['schedule.user'])
            ->get();

        // Available schedules for booking
        $enrolledCourseIds = $student->lmsEnrollments()
            ->where('status', 'active')
            ->pluck('course_id')
            ->toArray();

        $availableSchedules = AvailabilitySchedule::where('is_active', true)
            ->where(function ($q) use ($enrolledCourseIds) {
                $q->whereIn('course_id', $enrolledCourseIds)
                    ->orWhereNull('course_id');
            })
            ->with(['user', 'windows'])
            ->get();

        return view('students.portal.lms.my-appointments', compact(
            'student',
            'upcomingAppointments',
            'pastAppointments',
            'cancelledAppointments',
            'availableSchedules'
        ));
    }

    /**
     * View available slots for a schedule
     */
    public function availableSlots(AvailabilitySchedule $schedule) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        // Load schedule with windows and user
        $schedule->load(['windows', 'user']);

        // Generate available slots for the next 14 days
        $slots = [];
        $today = Carbon::today();
        $endDate = $today->copy()->addDays(14);

        $period = CarbonPeriod::create($today, $endDate);

        foreach ($period as $date) {
            // Use the model's built-in method to get available slots
            $daySlots = $schedule->getAvailableSlotsForDate($date);

            // Filter out past slots
            $daySlots = array_filter($daySlots, function ($slot) {
                return $slot['start']->gt(now());
            });

            if (count($daySlots) > 0) {
                $slots[$date->format('Y-m-d')] = [
                    'date' => $date->copy(),
                    'formatted' => $date->format('l, F j, Y'),
                    'slots' => array_values($daySlots),
                ];
            }
        }

        return view('students.portal.lms.available-slots', compact('schedule', 'slots', 'student'));
    }

    /**
     * Book an appointment
     */
    public function bookAppointment(Request $request, AvailabilitySchedule $schedule) {
        $student = $this->getStudent();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'datetime' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $datetime = Carbon::parse($request->datetime);

        if ($datetime->lt(now())) {
            return response()->json(['success' => false, 'message' => 'Cannot book a slot in the past.']);
        }

        // Check availability using a transaction with lock
        try {
            $appointment = DB::transaction(function () use ($schedule, $student, $datetime, $request) {
                // Lock the schedule row
                $schedule = AvailabilitySchedule::where('id', $schedule->id)
                    ->lockForUpdate()
                    ->first();

                $endTime = $datetime->copy()->addMinutes($schedule->slot_duration);

                // Count existing bookings for this slot
                $bookingsCount = Appointment::where('schedule_id', $schedule->id)
                    ->where('start_time', $datetime)
                    ->where('status', '!=', 'cancelled')
                    ->count();

                if ($bookingsCount >= $schedule->max_bookings_per_slot) {
                    throw new \Exception('This slot is no longer available.');
                }

                // Check if student already has appointment at this time
                $existingAppointment = Appointment::where('student_id', $student->id)
                    ->where('start_time', $datetime)
                    ->where('status', 'confirmed')
                    ->exists();

                if ($existingAppointment) {
                    throw new \Exception('You already have an appointment at this time.');
                }

                // Create the appointment
                return Appointment::create([
                    'schedule_id' => $schedule->id,
                    'student_id' => $student->id,
                    'start_time' => $datetime,
                    'end_time' => $endTime,
                    'status' => 'confirmed',
                    'student_notes' => $request->notes,
                    'meeting_url' => $schedule->meeting_url,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully!',
                'appointment_id' => $appointment->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Cancel an appointment
     */
    public function cancelAppointment(Request $request, Appointment $appointment) {
        $student = $this->getStudent();

        if (!$student || $appointment->student_id !== $student->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($appointment->status !== 'confirmed') {
            return response()->json(['success' => false, 'message' => 'This appointment cannot be cancelled.']);
        }

        // Don't allow cancellation if appointment is in less than 2 hours
        if ($appointment->start_time->lt(now()->addHours(2))) {
            return response()->json(['success' => false, 'message' => 'Appointments must be cancelled at least 2 hours in advance.']);
        }

        $appointment->cancel($student->id, $request->input('reason', 'Cancelled by student'));

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled successfully.',
        ]);
    }
}
