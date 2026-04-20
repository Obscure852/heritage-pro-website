<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Jobs\SendCalendarEventNotificationJob;
use App\Models\Grade;
use App\Models\Klass;
use App\Models\Lms\Appointment;
use App\Models\Lms\AvailabilityOverride;
use App\Models\Lms\AvailabilitySchedule;
use App\Models\Lms\AvailabilityWindow;
use App\Models\Lms\CalendarEvent;
use App\Models\Lms\CalendarPreference;
use App\Models\Lms\Course;
use App\Models\Lms\CourseSchedule;
use App\Models\Lms\Deadline;
use App\Models\Lms\Enrollment;
use App\Models\Lms\EventReminder;
use App\Models\SMSApiSetting;
use App\Models\Student;
use App\Models\StudentTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class CalendarController extends Controller {
    // Main Calendar View
    public function index() {
        $preferences = CalendarPreference::forUser(Auth::id());

        // Get event counts for stats
        $student = Auth::user()->student;
        $courseIds = [];

        if ($student) {
            $courseIds = Enrollment::where('student_id', $student->id)
                ->where('status', 'active')
                ->pluck('course_id')
                ->toArray();
        }

        // Count events by type
        $eventCounts = CalendarEvent::published()
            ->where(function ($q) use ($courseIds) {
                $q->whereIn('course_id', $courseIds)
                  ->orWhereNull('course_id');
            })
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Count deadlines
        $deadlineCount = Deadline::whereIn('course_id', $courseIds)
            ->active()
            ->count();

        // Count appointments
        $appointmentCount = 0;
        if ($student) {
            $appointmentCount = Appointment::where('student_id', $student->id)
                ->where('status', '!=', 'cancelled')
                ->where('start_time', '>=', now())
                ->count();
        }

        $totalEvents = array_sum($eventCounts) + $deadlineCount + $appointmentCount;

        // Get available audiences for event targeting
        $grades = Grade::where('active', true)->orderBy('name')->get(['id', 'name']);
        $classes = Klass::with('grade')->orderBy('name')->get(['id', 'name', 'grade_id']);
        $courses = Course::where('status', 'published')->orderBy('title')->get(['id', 'title']);

        return view('lms.calendar.index', compact(
            'preferences', 'eventCounts', 'deadlineCount', 'appointmentCount', 'totalEvents',
            'grades', 'classes', 'courses'
        ));
    }

    // Get Events (AJAX)
    public function events(Request $request) {
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $events = collect();

        // Get enrolled courses
        $student = Auth::user()->student;
        $courseIds = [];

        if ($student) {
            $courseIds = Enrollment::where('student_id', $student->id)
                ->pluck('course_id')
                ->toArray();
        }

        // Get calendar events
        $calendarEvents = CalendarEvent::published()
            ->where(function ($q) use ($courseIds) {
                $q->whereIn('course_id', $courseIds)
                  ->orWhereNull('course_id');
            })
            ->inDateRange($start, $end)
            ->get();

        foreach ($calendarEvents as $event) {
            $events->push($event->toFullCalendarEvent());
        }

        // Get course schedules (class sessions)
        $courseSchedules = CourseSchedule::whereIn('course_id', $courseIds)
            ->active()
            ->get();

        foreach ($courseSchedules as $schedule) {
            $period = CarbonPeriod::create($start, '1 day', $end);

            foreach ($period as $date) {
                if ($date->dayOfWeek === $schedule->day_of_week) {
                    $events->push($schedule->toCalendarEvent($date));
                }
            }
        }

        // Get deadlines
        $deadlines = Deadline::whereIn('course_id', $courseIds)
            ->active()
            ->whereBetween('due_date', [$start, $end])
            ->get();

        foreach ($deadlines as $deadline) {
            $events->push($deadline->toCalendarEvent());
        }

        // Get appointments
        if ($student) {
            $appointments = Appointment::where('student_id', $student->id)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('start_time', [$start, $end])
                ->with('schedule.user')
                ->get();

            foreach ($appointments as $appointment) {
                $events->push([
                    'id' => 'appointment_' . $appointment->id,
                    'title' => $appointment->schedule->title,
                    'start' => $appointment->start_time->toIso8601String(),
                    'end' => $appointment->end_time->toIso8601String(),
                    'color' => CalendarEvent::$colors['meeting'],
                    'extendedProps' => [
                        'type' => 'appointment',
                        'instructor' => $appointment->schedule->user->name,
                        'location' => $appointment->schedule->location,
                        'meeting_url' => $appointment->meeting_url,
                    ],
                ]);
            }
        }

        return response()->json($events->values());
    }

    // Create Event
    public function store(Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(CalendarEvent::$eventTypes)),
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'meeting_url' => 'nullable|url|max:255',
            'course_id' => 'nullable|exists:lms_courses,id',
            'is_published' => 'boolean',
            'notify_students' => 'boolean',
            'audience_scope' => 'required|in:all,course,grade,class,mixed',
            'audience_courses' => 'nullable|array',
            'audience_courses.*' => 'exists:lms_courses,id',
            'audience_grades' => 'nullable|array',
            'audience_grades.*' => 'exists:grades,id',
            'audience_classes' => 'nullable|array',
            'audience_classes.*' => 'exists:klasses,id',
        ]);

        $validated['color'] = CalendarEvent::$colors[$validated['type']] ?? '#6366f1';
        $validated['created_by'] = Auth::id();
        $validated['notify_students'] = $validated['notify_students'] ?? false;

        $event = CalendarEvent::create($validated);

        // Sync audiences based on scope
        $audiences = $this->buildAudienceArray($validated);
        $event->syncAudiences($audiences);

        // Dispatch notifications if enabled
        if ($validated['notify_students'] && ($validated['is_published'] ?? false)) {
            $this->dispatchEventNotifications($event, $validated);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'event' => $event->toFullCalendarEvent(),
            ]);
        }

        return back()->with('success', 'Event created.');
    }

    /**
     * Build audience array from validated request data.
     */
    private function buildAudienceArray(array $validated): array {
        $audiences = [];

        if (!empty($validated['audience_courses'])) {
            foreach ($validated['audience_courses'] as $id) {
                $audiences[] = ['type' => Course::class, 'id' => $id];
            }
        }

        if (!empty($validated['audience_grades'])) {
            foreach ($validated['audience_grades'] as $id) {
                $audiences[] = ['type' => Grade::class, 'id' => $id];
            }
        }

        if (!empty($validated['audience_classes'])) {
            foreach ($validated['audience_classes'] as $id) {
                $audiences[] = ['type' => Klass::class, 'id' => $id];
            }
        }

        return $audiences;
    }

    /**
     * Dispatch calendar event notifications to targeted students.
     */
    protected function dispatchEventNotifications(CalendarEvent $event, array $validated): void {
        $enabled = SMSApiSetting::where('key', 'lms_calendar_notifications_enabled')
            ->value('value');

        if ($enabled !== 'true') {
            return;
        }

        $studentIds = $this->resolveTargetedStudents($event, $validated);

        if (empty($studentIds)) {
            return;
        }

        $batchSize = (int) (SMSApiSetting::where('key', 'lms_calendar_notification_batch_size')
            ->value('value') ?? 100);

        $batches = array_chunk($studentIds, $batchSize);

        foreach ($batches as $batch) {
            SendCalendarEventNotificationJob::dispatch($event, $batch);
        }
    }

    /**
     * Resolve student IDs based on audience scope.
     */
    protected function resolveTargetedStudents(CalendarEvent $event, array $validated): array {
        $studentIds = collect();
        $scope = $validated['audience_scope'] ?? 'all';

        // Get current term for student filtering
        $currentTerm = \App\Helpers\TermHelper::getCurrentTerm();
        if (!$currentTerm) {
            return [];
        }

        // Scope: all - all current students
        if ($scope === 'all') {
            $studentIds = Student::where('status', 'current')
                ->pluck('id');
        }

        // Scope: course - students enrolled in target courses
        if ($scope === 'course' && !empty($validated['audience_courses'])) {
            $studentIds = Enrollment::whereIn('course_id', $validated['audience_courses'])
                ->where('status', 'active')
                ->pluck('student_id');
        }

        // Scope: grade - students in target grades
        if ($scope === 'grade' && !empty($validated['audience_grades'])) {
            $studentIds = StudentTerm::where('term_id', $currentTerm->id)
                ->whereHas('klass', function ($q) use ($validated) {
                    $q->whereIn('grade_id', $validated['audience_grades']);
                })
                ->pluck('student_id');
        }

        // Scope: class - students in target classes
        if ($scope === 'class' && !empty($validated['audience_classes'])) {
            $studentIds = StudentTerm::where('term_id', $currentTerm->id)
                ->whereIn('klass_id', $validated['audience_classes'])
                ->pluck('student_id');
        }

        // Scope: mixed - combine all audience types
        if ($scope === 'mixed') {
            $combined = collect();

            if (!empty($validated['audience_courses'])) {
                $courseStudents = Enrollment::whereIn('course_id', $validated['audience_courses'])
                    ->where('status', 'active')
                    ->pluck('student_id');
                $combined = $combined->merge($courseStudents);
            }

            if (!empty($validated['audience_grades'])) {
                $gradeStudents = StudentTerm::where('term_id', $currentTerm->id)
                    ->whereHas('klass', function ($q) use ($validated) {
                        $q->whereIn('grade_id', $validated['audience_grades']);
                    })
                    ->pluck('student_id');
                $combined = $combined->merge($gradeStudents);
            }

            if (!empty($validated['audience_classes'])) {
                $classStudents = StudentTerm::where('term_id', $currentTerm->id)
                    ->whereIn('klass_id', $validated['audience_classes'])
                    ->pluck('student_id');
                $combined = $combined->merge($classStudents);
            }

            $studentIds = $combined;
        }

        return $studentIds->unique()->values()->toArray();
    }

    // Update Event
    public function update(Request $request, CalendarEvent $event) {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'type' => 'in:' . implode(',', array_keys(CalendarEvent::$eventTypes)),
            'start_date' => 'date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'meeting_url' => 'nullable|url|max:255',
            'is_published' => 'boolean',
            'audience_scope' => 'nullable|in:all,course,grade,class,mixed',
            'audience_courses' => 'nullable|array',
            'audience_courses.*' => 'exists:lms_courses,id',
            'audience_grades' => 'nullable|array',
            'audience_grades.*' => 'exists:grades,id',
            'audience_classes' => 'nullable|array',
            'audience_classes.*' => 'exists:klasses,id',
        ]);

        if (isset($validated['type'])) {
            $validated['color'] = CalendarEvent::$colors[$validated['type']] ?? $event->color;
        }

        $event->update($validated);

        // Sync audiences if audience_scope is provided
        if (isset($validated['audience_scope'])) {
            $audiences = $this->buildAudienceArray($validated);
            $event->syncAudiences($audiences);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'event' => $event->fresh()->toFullCalendarEvent(),
            ]);
        }

        return back()->with('success', 'Event updated.');
    }

    // Delete Event
    public function destroy(CalendarEvent $event) {
        $event->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Event deleted.');
    }

    // Calendar Preferences
    public function preferences() {
        $preferences = CalendarPreference::forUser(Auth::id());
        return view('lms.calendar.preferences', compact('preferences'));
    }

    public function updatePreferences(Request $request) {
        $validated = $request->validate([
            'default_view' => 'in:day,week,month,agenda',
            'week_start' => 'in:sunday,monday',
            'timezone' => 'nullable|string|max:50',
            'show_weekends' => 'boolean',
            'hidden_event_types' => 'nullable|array',
        ]);

        $preferences = CalendarPreference::forUser(Auth::id());
        $preferences->update($validated);

        return back()->with('success', 'Preferences updated.');
    }

    // Course Calendar (Course-specific view)
    public function courseCalendar(Course $course) {
        $schedules = CourseSchedule::where('course_id', $course->id)
            ->active()
            ->with('instructor')
            ->get();

        $deadlines = Deadline::where('course_id', $course->id)
            ->active()
            ->upcoming()
            ->take(10)
            ->get();

        return view('lms.calendar.course', compact('course', 'schedules', 'deadlines'));
    }

    // Scheduling: View Available Slots
    public function availableSlots(AvailabilitySchedule $schedule, Request $request) {
        $date = $request->date ? Carbon::parse($request->date) : now();

        // Get slots for next 14 days
        $dates = [];
        for ($i = 0; $i < 14; $i++) {
            $checkDate = $date->copy()->addDays($i);
            $slots = $schedule->getAvailableSlotsForDate($checkDate);

            if (!empty($slots)) {
                $dates[$checkDate->toDateString()] = $slots;
            }
        }

        if ($request->ajax()) {
            return response()->json($dates);
        }

        return view('lms.calendar.available-slots', compact('schedule', 'dates', 'date'));
    }

    // Book Appointment
    public function bookAppointment(Request $request, AvailabilitySchedule $schedule) {
        $validated = $request->validate([
            'start_time' => 'required|date',
            'student_notes' => 'nullable|string|max:500',
        ]);

        $student = Auth::user()->student;
        if (!$student) {
            return back()->with('error', 'Student profile not found.');
        }

        $startTime = Carbon::parse($validated['start_time']);
        $endTime = $startTime->copy()->addMinutes($schedule->slot_duration);

        // Verify slot is still available
        $existingCount = Appointment::where('schedule_id', $schedule->id)
            ->where('start_time', $startTime)
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($existingCount >= $schedule->max_bookings_per_slot) {
            return back()->with('error', 'This slot is no longer available.');
        }

        $appointment = Appointment::create([
            'schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'student_notes' => $validated['student_notes'] ?? null,
            'meeting_url' => $schedule->meeting_url,
            'status' => 'confirmed',
        ]);

        return redirect()->route('lms.calendar.my-appointments')
            ->with('success', 'Appointment booked successfully.');
    }

    // My Appointments (Student view)
    public function myAppointments() {
        $student = Auth::user()->student;

        if (!$student) {
            return redirect()->route('lms.calendar.index')
                ->with('error', 'Student profile not found.');
        }

        $upcomingAppointments = Appointment::where('student_id', $student->id)
            ->upcoming()
            ->with(['schedule.user', 'schedule.course'])
            ->get();

        $pastAppointments = Appointment::where('student_id', $student->id)
            ->where(function ($q) {
                $q->where('start_time', '<', now())
                  ->orWhere('status', 'cancelled');
            })
            ->with(['schedule.user', 'schedule.course'])
            ->orderByDesc('start_time')
            ->take(20)
            ->get();

        return view('lms.calendar.my-appointments', compact('upcomingAppointments', 'pastAppointments'));
    }

    // Cancel Appointment
    public function cancelAppointment(Appointment $appointment, Request $request) {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $appointment->cancel(Auth::id(), $validated['reason'] ?? null);

        return back()->with('success', 'Appointment cancelled.');
    }

    // Instructor: Manage Availability
    public function manageAvailability() {
        $schedules = AvailabilitySchedule::where('user_id', Auth::id())
            ->with(['windows', 'course'])
            ->get();

        return view('lms.calendar.manage-availability', compact('schedules'));
    }

    // Create Availability Schedule
    public function storeAvailability(Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'nullable|exists:lms_courses,id',
            'location' => 'nullable|string|max:255',
            'meeting_url' => 'nullable|url|max:255',
            'slot_duration' => 'required|integer|min:15|max:120',
            'buffer_time' => 'integer|min:0|max:60',
            'max_bookings_per_slot' => 'integer|min:1|max:20',
            'windows' => 'required|array|min:1',
            'windows.*.day_of_week' => 'required|integer|min:0|max:6',
            'windows.*.start_time' => 'required|date_format:H:i',
            'windows.*.end_time' => 'required|date_format:H:i|after:windows.*.start_time',
        ]);

        $schedule = AvailabilitySchedule::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'course_id' => $validated['course_id'] ?? null,
            'location' => $validated['location'] ?? null,
            'meeting_url' => $validated['meeting_url'] ?? null,
            'slot_duration' => $validated['slot_duration'],
            'buffer_time' => $validated['buffer_time'] ?? 0,
            'max_bookings_per_slot' => $validated['max_bookings_per_slot'] ?? 1,
        ]);

        foreach ($validated['windows'] as $window) {
            $schedule->windows()->create($window);
        }

        return redirect()->route('lms.calendar.availability')
            ->with('success', 'Availability schedule created.');
    }

    // Add Override (block or custom hours for specific date)
    public function storeOverride(Request $request, AvailabilitySchedule $schedule) {
        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'is_available' => 'boolean',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:255',
        ]);

        AvailabilityOverride::updateOrCreate(
            ['schedule_id' => $schedule->id, 'date' => $validated['date']],
            [
                'is_available' => $validated['is_available'] ?? true,
                'start_time' => $validated['start_time'] ?? null,
                'end_time' => $validated['end_time'] ?? null,
                'reason' => $validated['reason'] ?? null,
            ]
        );

        return back()->with('success', 'Override saved.');
    }

    // Upcoming Deadlines Widget
    public function upcomingDeadlines() {
        $student = Auth::user()->student;
        $courseIds = [];

        if ($student) {
            $courseIds = Enrollment::where('student_id', $student->id)
                ->where('status', 'active')
                ->pluck('course_id')
                ->toArray();
        }

        $deadlines = Deadline::whereIn('course_id', $courseIds)
            ->active()
            ->upcoming()
            ->with('course')
            ->take(10)
            ->get();

        if (request()->ajax()) {
            return response()->json($deadlines);
        }

        return view('lms.calendar.upcoming-deadlines', compact('deadlines'));
    }

    // Create Event Reminder
    public function createReminder(Request $request, CalendarEvent $event) {
        $validated = $request->validate([
            'minutes_before' => 'required|integer|in:' . implode(',', array_keys(EventReminder::$presets)),
            'method' => 'required|in:' . implode(',', array_keys(EventReminder::$methods)),
        ]);

        EventReminder::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'minutes_before' => $validated['minutes_before'],
            'method' => $validated['method'],
        ]);

        return back()->with('success', 'Reminder set.');
    }
}
