<?php

namespace App\Services;

use App\Models\Lms\Announcement;
use App\Models\Lms\Course;
use App\Models\Lms\DiscussionPost;
use App\Models\Lms\DiscussionThread;
use App\Models\Lms\Enrollment;
use App\Models\Lms\Notification;
use App\Models\Lms\NotificationEmail;
use App\Models\Lms\NotificationPreference;
use App\Models\Student;
use Illuminate\Support\Facades\Mail;

class NotificationService {
    public function notify(
        Student $student,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $actionText = null,
        $notifiable = null,
        array $data = []
    ): ?Notification {
        // Check if in-app notification is enabled
        if (!NotificationPreference::shouldSendInApp($student->id, $type)) {
            return null;
        }

        $notification = Notification::send(
            $student,
            $type,
            $title,
            $message,
            $actionUrl,
            $actionText,
            $notifiable,
            $data
        );

        // Queue email if enabled
        if (NotificationPreference::shouldSendEmail($student->id, $type)) {
            $this->queueEmail($notification, $student);
        }

        return $notification;
    }

    protected function queueEmail(Notification $notification, Student $student): void {
        if (!$student->email) {
            return;
        }

        NotificationEmail::create([
            'notification_id' => $notification->id,
            'student_id' => $student->id,
            'email' => $student->email,
            'subject' => $notification->title,
            'body' => $this->buildEmailBody($notification),
            'status' => NotificationEmail::STATUS_PENDING,
        ]);
    }

    protected function buildEmailBody(Notification $notification): string {
        return view('emails.lms.notification', [
            'notification' => $notification,
        ])->render();
    }

    // ===== Specific Notification Methods =====

    public function onCourseEnrolled(Enrollment $enrollment): void {
        $this->notify(
            $enrollment->student,
            Notification::TYPE_COURSE_ENROLLED,
            'Enrolled in Course',
            "You have been enrolled in \"{$enrollment->course->title}\"",
            route('lms.courses.learn', $enrollment->course),
            'Start Learning',
            $enrollment->course
        );
    }

    public function onCourseCompleted(Enrollment $enrollment): void {
        $this->notify(
            $enrollment->student,
            Notification::TYPE_COURSE_COMPLETED,
            'Course Completed!',
            "Congratulations! You have completed \"{$enrollment->course->title}\"",
            route('lms.my-courses'),
            'View Courses',
            $enrollment->course
        );
    }

    public function onQuizGraded(Student $student, $quizAttempt): void {
        $quiz = $quizAttempt->quiz;
        $score = $quizAttempt->score_percentage;

        $this->notify(
            $student,
            Notification::TYPE_QUIZ_GRADED,
            'Quiz Graded',
            "Your quiz \"{$quiz->title}\" has been graded. Score: {$score}%",
            route('lms.quizzes.results', [$quiz, $quizAttempt]),
            'View Results',
            $quizAttempt,
            ['score' => $score]
        );
    }

    public function onAssignmentGraded(Student $student, $submission): void {
        $assignment = $submission->assignment;
        $score = $submission->score;

        $this->notify(
            $student,
            Notification::TYPE_ASSIGNMENT_GRADED,
            'Assignment Graded',
            "Your submission for \"{$assignment->title}\" has been graded. Score: {$score}",
            route('lms.assignments.show', $assignment),
            'View Feedback',
            $submission,
            ['score' => $score]
        );
    }

    public function onBadgeEarned(Student $student, $badge): void {
        $this->notify(
            $student,
            Notification::TYPE_BADGE_EARNED,
            'Badge Earned!',
            "You've earned the \"{$badge->name}\" badge!",
            route('lms.gamification.badges.show', $badge),
            'View Badge',
            $badge
        );
    }

    public function onNewReply(DiscussionThread $thread, DiscussionPost $post): void {
        // Notify thread author if not the replier (only if author is a Student)
        $isSameAuthor = $thread->author_type === $post->author_type && $thread->author_id === $post->author_id;

        if (!$isSameAuthor && $thread->isAuthorStudent()) {
            $this->notify(
                $thread->author,
                Notification::TYPE_NEW_REPLY,
                'New Reply',
                "Someone replied to your thread \"{$thread->title}\"",
                route('lms.discussions.thread', [$thread->forum_id, $thread]),
                'View Reply',
                $post
            );
        }

        // Notify subscribers (only students can subscribe)
        $postAuthorId = $post->isAuthorStudent() ? $post->author_id : null;

        $subscriptions = $thread->subscriptions()
            ->when($postAuthorId, fn($q) => $q->where('student_id', '!=', $postAuthorId))
            ->where('frequency', 'instant')
            ->get();

        foreach ($subscriptions as $subscription) {
            $this->notify(
                $subscription->student,
                Notification::TYPE_NEW_REPLY,
                'New Reply',
                "New reply in \"{$thread->title}\"",
                route('lms.discussions.thread', [$thread->forum_id, $thread]),
                'View Reply',
                $post
            );
        }
    }

    public function onMention(Student $mentionedStudent, DiscussionPost $post): void {
        if ($mentionedStudent->id === $post->author_id) {
            return;
        }

        $this->notify(
            $mentionedStudent,
            Notification::TYPE_MENTION,
            'You were mentioned',
            "{$post->display_author} mentioned you in a discussion",
            route('lms.discussions.thread', [$post->thread->forum_id, $post->thread]),
            'View Post',
            $post
        );
    }

    public function onThreadAnswered(DiscussionThread $thread, DiscussionPost $answer): void {
        // Check if same author (comparing both type and id for polymorphic)
        $isSameAuthor = $thread->author_type === $answer->author_type && $thread->author_id === $answer->author_id;

        // Only notify if thread author is a Student (instructors don't get student notifications)
        if ($isSameAuthor || !$thread->isAuthorStudent()) {
            return;
        }

        $this->notify(
            $thread->author,
            Notification::TYPE_THREAD_ANSWERED,
            'Question Answered',
            "Your question \"{$thread->title}\" has been marked as answered!",
            route('lms.discussions.thread', [$thread->forum_id, $thread]),
            'View Answer',
            $answer
        );
    }

    public function onAnnouncement(Announcement $announcement): void {
        // Get target students
        if ($announcement->course_id) {
            $students = Student::whereHas('enrollments', function ($q) use ($announcement) {
                $q->where('course_id', $announcement->course_id);
            })->get();
        } else {
            // Global announcement - all students with active enrollments
            $students = Student::whereHas('enrollments', function ($q) {
                $q->where('status', 'active');
            })->get();
        }

        foreach ($students as $student) {
            $this->notify(
                $student,
                Notification::TYPE_ANNOUNCEMENT,
                $announcement->title,
                \Str::limit(strip_tags($announcement->content), 100),
                $announcement->course_id 
                    ? route('lms.courses.learn', $announcement->course_id)
                    : route('lms.my-courses'),
                'Read More',
                $announcement
            );
        }
    }

    public function onDeadlineReminder(Student $student, $contentItem, int $hoursRemaining): void {
        $this->notify(
            $student,
            Notification::TYPE_DEADLINE_REMINDER,
            'Deadline Approaching',
            "\"{$contentItem->title}\" is due in {$hoursRemaining} hours",
            route('lms.content.show', $contentItem),
            'View',
            $contentItem,
            ['hours_remaining' => $hoursRemaining]
        );
    }

    // ===== Email Processing =====

    public function processPendingEmails(int $limit = 50): int {
        $emails = NotificationEmail::pending()
            ->where('attempts', '<', 3)
            ->limit($limit)
            ->get();

        $sent = 0;

        foreach ($emails as $email) {
            try {
                Mail::raw($email->body, function ($message) use ($email) {
                    $message->to($email->email)
                        ->subject($email->subject);
                });

                $email->markAsSent();
                $sent++;
            } catch (\Exception $e) {
                $email->markAsFailed($e->getMessage());
            }
        }

        return $sent;
    }

    // ===== Bulk Operations =====

    public function markAllAsRead(int $studentId): int {
        return Notification::where('student_id', $studentId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function deleteOldNotifications(int $days = 90): int {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->whereNotNull('read_at')
            ->delete();
    }
}
