<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\Announcement;
use App\Models\Lms\Course;
use App\Models\Lms\Notification;
use App\Models\Lms\NotificationPreference;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller {
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService) {
        $this->notificationService = $notificationService;
    }

    /**
     * List all notifications
     */
    public function index(Request $request) {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $filter = $request->query('filter', 'all');

        $notifications = Notification::where('student_id', $student->id)
            ->when($filter === 'unread', fn($q) => $q->unread())
            ->orderByDesc('created_at')
            ->paginate(20);

        $unreadCount = Notification::getUnreadCount($student->id);

        return view('lms.notifications.index', compact('notifications', 'filter', 'unreadCount'));
    }

    /**
     * Get notifications for dropdown (AJAX)
     */
    public function dropdown() {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notifications = Notification::where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $unreadCount = Notification::getUnreadCount($student->id);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification) {
        $student = Auth::guard('student')->user();

        if (!$student || $notification->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        // Redirect to action URL if present
        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return back();
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead() {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $count = $this->notificationService->markAllAsRead($student->id);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'count' => $count]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete notification
     */
    public function destroy(Notification $notification) {
        $student = Auth::guard('student')->user();

        if (!$student || $notification->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification deleted.');
    }

    /**
     * Notification preferences
     */
    public function preferences() {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $preferences = NotificationPreference::where('student_id', $student->id)->get();

        $notificationTypes = [
            'course_announcement' => 'Course Announcements',
            'assignment_due' => 'Assignment Due Reminders',
            'assignment_graded' => 'Assignment Graded',
            'quiz_available' => 'Quiz Available',
            'quiz_graded' => 'Quiz Graded',
            'discussion_reply' => 'Discussion Replies',
            'discussion_mention' => 'Discussion Mentions',
            'badge_earned' => 'Badges Earned',
            'achievement_unlocked' => 'Achievements Unlocked',
            'course_completed' => 'Course Completed',
            'enrollment' => 'Enrollment Updates',
        ];

        $typeDescriptions = [
            'course_announcement' => 'Notifications when instructors post announcements',
            'assignment_due' => 'Reminders before assignment deadlines',
            'assignment_graded' => 'When your assignments are graded',
            'quiz_available' => 'When new quizzes become available',
            'quiz_graded' => 'When your quiz results are ready',
            'discussion_reply' => 'When someone replies to your posts',
            'discussion_mention' => 'When you are mentioned in discussions',
            'badge_earned' => 'When you earn badges',
            'achievement_unlocked' => 'When you unlock achievements',
            'course_completed' => 'When you complete a course',
            'enrollment' => 'Enrollment confirmations and updates',
        ];

        $emailFrequency = $student->notification_email_frequency ?? 'immediate';
        $quietHoursEnabled = $student->quiet_hours_enabled ?? false;
        $quietHoursStart = $student->quiet_hours_start ?? '22:00';
        $quietHoursEnd = $student->quiet_hours_end ?? '07:00';

        return view('lms.notifications.preferences', compact(
            'preferences',
            'notificationTypes',
            'typeDescriptions',
            'emailFrequency',
            'quietHoursEnabled',
            'quietHoursStart',
            'quietHoursEnd'
        ));
    }

    /**
     * Save preferences
     */
    public function savePreferences(Request $request) {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $preferences = $request->input('preferences', []);

        foreach ($preferences as $type => $settings) {
            NotificationPreference::updateOrCreate(
                ['student_id' => $student->id, 'type' => $type],
                [
                    'in_app' => isset($settings['in_app']),
                    'email' => isset($settings['email']),
                ]
            );
        }

        return back()->with('success', 'Preferences saved.');
    }

    // ===== Announcements =====

    /**
     * List announcements
     */
    public function announcements(Request $request) {
        $student = Auth::guard('student')->user();
        $courseId = $request->query('course_id');

        $announcements = Announcement::active()
            ->when($courseId, fn($q) => $q->forCourse($courseId), fn($q) => $q->global())
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(15);

        $courses = $student 
            ? Course::whereHas('enrollments', fn($q) => $q->where('student_id', $student->id))->get()
            : collect();

        return view('lms.notifications.announcements', compact('announcements', 'courses', 'courseId', 'student'));
    }

    /**
     * View announcement
     */
    public function showAnnouncement(Announcement $announcement) {
        $student = Auth::guard('student')->user();

        if ($student) {
            $announcement->markAsRead($student);
        }

        // Get related announcements from same course or global
        $relatedAnnouncements = Announcement::active()
            ->where('id', '!=', $announcement->id)
            ->when($announcement->course_id, fn($q) => $q->where('course_id', $announcement->course_id))
            ->when(!$announcement->course_id, fn($q) => $q->whereNull('course_id'))
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        return view('lms.notifications.announcement-show', compact('announcement', 'student', 'relatedAnnouncements'));
    }

    // ===== Admin Methods =====

    /**
     * Admin: Create announcement form
     */
    public function createAnnouncement(Request $request) {
        Gate::authorize('manage-lms-content');

        $courseId = $request->query('course_id');
        $course = $courseId ? Course::find($courseId) : null;
        $courses = Course::orderBy('title')->get();

        return view('lms.notifications.announcement-create', compact('course', 'courses'));
    }

    /**
     * Admin: Store announcement
     */
    public function storeAnnouncement(Request $request) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'course_id' => 'nullable|exists:lms_courses,id',
            'priority' => 'required|in:low,normal,high,urgent',
            'send_email' => 'boolean',
            'is_pinned' => 'boolean',
            'publish_now' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $announcement = Announcement::create([
            'course_id' => $validated['course_id'],
            'author_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'priority' => $validated['priority'],
            'send_email' => $request->boolean('send_email'),
            'is_pinned' => $request->boolean('is_pinned'),
            'published_at' => $request->boolean('publish_now') ? now() : null,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        // Send notifications if publishing now
        if ($announcement->is_published) {
            $this->notificationService->onAnnouncement($announcement);
        }

        return redirect()->route('lms.announcements')
            ->with('success', 'Announcement created.');
    }

    /**
     * Admin: Publish announcement
     */
    public function publishAnnouncement(Announcement $announcement) {
        Gate::authorize('manage-lms-content');

        $announcement->publish();
        $this->notificationService->onAnnouncement($announcement);

        return back()->with('success', 'Announcement published.');
    }

    /**
     * Admin: Delete announcement
     */
    public function deleteAnnouncement(Announcement $announcement) {
        Gate::authorize('manage-lms-content');

        $announcement->delete();

        return back()->with('success', 'Announcement deleted.');
    }
}
