<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Models\Lms\ContentItem;
use App\Models\Lms\Course;
use App\Models\Lms\DiscussionAttachment;
use App\Models\Lms\DiscussionForum;
use App\Models\Lms\DiscussionMention;
use App\Models\Lms\DiscussionPost;
use App\Models\Lms\DiscussionThread;
use App\Models\Student;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentDiscussionController extends Controller {
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService) {
        $this->middleware('auth:student');
        $this->notificationService = $notificationService;
    }

    protected function getStudent(): ?Student {
        return Auth::guard('student')->user();
    }

    /**
     * View course discussion forum
     */
    public function forum(Course $course) {
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
            return redirect()->route('student.lms.my-courses')
                ->with('error', 'You must be enrolled in this course to view discussions.');
        }

        $forum = DiscussionForum::getOrCreateForCourse($course);
        $forum->load('categories');

        $threads = DiscussionThread::with(['author', 'category', 'lastReply.author'])
            ->where('forum_id', $forum->id)
            ->whereIn('status', ['open', 'resolved'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_activity_at')
            ->paginate(20);

        return view('students.portal.lms.discussions.index', compact('course', 'forum', 'threads', 'student', 'enrollment'));
    }

    /**
     * View single thread
     */
    public function thread(DiscussionThread $thread) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $forum = $thread->forum;
        $course = $forum->course;

        // Check enrollment
        $enrollment = $student->lmsEnrollments()
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            return redirect()->route('student.lms.my-courses')
                ->with('error', 'You must be enrolled in this course to view discussions.');
        }

        $thread->incrementViews();
        $thread->load(['author', 'category', 'acceptedAnswer.author', 'contentItem']);

        $posts = $thread->posts()
            ->with(['author', 'replies.author', 'attachments'])
            ->whereNull('parent_id')
            ->visible()
            ->orderBy('created_at')
            ->paginate(20);

        $isSubscribed = $thread->isSubscribedBy($student);
        $isLiked = $thread->isLikedBy($student);

        return view('students.portal.lms.discussions.thread', compact(
            'course', 'forum', 'thread', 'posts', 'student', 'enrollment', 'isSubscribed', 'isLiked'
        ));
    }

    /**
     * Create new thread form
     */
    public function createThread(Course $course) {
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
            return redirect()->route('student.lms.my-courses')
                ->with('error', 'You must be enrolled in this course to create discussions.');
        }

        $forum = DiscussionForum::getOrCreateForCourse($course);
        $forum->load(['categories' => fn($q) => $q->where('is_locked', false)]);

        return view('students.portal.lms.discussions.create-thread', compact('course', 'forum', 'student', 'enrollment'));
    }

    /**
     * Store new thread
     */
    public function storeThread(Request $request, Course $course) {
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
            return redirect()->route('student.lms.my-courses')
                ->with('error', 'You must be enrolled in this course.');
        }

        $forum = DiscussionForum::getOrCreateForCourse($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|min:10',
            'category_id' => 'nullable|exists:lms_discussion_categories,id',
            'type' => 'required|in:discussion,question',
            'is_anonymous' => 'boolean',
        ]);

        $thread = DiscussionThread::create([
            'forum_id' => $forum->id,
            'author_id' => $student->id,
            'author_type' => Student::class,
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'type' => $validated['type'],
            'is_anonymous' => $forum->allow_anonymous && $request->boolean('is_anonymous'),
            'status' => $forum->require_approval ? 'pending' : 'open',
        ]);

        // Auto-subscribe author
        $thread->subscribe($student);

        return redirect()->route('student.lms.discussions.thread', $thread)
            ->with('success', 'Discussion thread created successfully.');
    }

    /**
     * Store reply to thread
     */
    public function storePost(Request $request, DiscussionThread $thread) {
        $student = $this->getStudent();

        if (!$student) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('student.login');
        }

        $course = $thread->forum->course;

        // Check enrollment
        $enrollment = $student->lmsEnrollments()
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Not enrolled in this course'], 403);
            }
            return redirect()->route('student.lms.my-courses')
                ->with('error', 'You must be enrolled in this course.');
        }

        if ($thread->is_locked) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Thread is locked'], 403);
            }
            return back()->with('error', 'This thread is locked and cannot accept new replies.');
        }

        $validated = $request->validate([
            'body' => 'required|string|min:2|max:10000',
            'parent_id' => 'nullable|exists:lms_discussion_posts,id',
            'is_anonymous' => 'boolean',
            'attachments.*' => 'file|max:10240',
        ]);

        $post = DiscussionPost::create([
            'thread_id' => $thread->id,
            'author_id' => $student->id,
            'author_type' => Student::class,
            'parent_id' => $validated['parent_id'] ?? null,
            'body' => $validated['body'],
            'is_anonymous' => $thread->forum->allow_anonymous && $request->boolean('is_anonymous'),
            'status' => $thread->forum->require_approval ? 'pending' : 'visible',
        ]);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('lms/discussions/attachments', 'public');
                DiscussionAttachment::create([
                    'post_id' => $post->id,
                    'filename' => basename($path),
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Parse mentions
        $this->processMentions($post);

        // Send notifications
        $this->notificationService->onNewReply($thread, $post);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'post' => $post->load(['author', 'attachments']),
            ]);
        }

        return redirect()->route('student.lms.discussions.thread', $thread)
            ->with('success', 'Reply posted successfully.');
    }

    /**
     * View content-specific discussions
     */
    public function contentDiscussions(ContentItem $contentItem) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $course = $contentItem->module->course;

        // Check enrollment
        $enrollment = $student->lmsEnrollments()
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            return redirect()->route('student.lms.my-courses')
                ->with('error', 'You must be enrolled in this course.');
        }

        $forum = DiscussionForum::getOrCreateForCourse($course);

        $threads = DiscussionThread::with(['author', 'lastReply.author'])
            ->forContent($contentItem->id)
            ->whereIn('status', ['open', 'resolved'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_activity_at')
            ->paginate(20);

        return view('students.portal.lms.discussions.content', compact(
            'course', 'forum', 'contentItem', 'threads', 'student', 'enrollment'
        ));
    }

    /**
     * Create thread for specific content item
     */
    public function storeContentThread(Request $request, ContentItem $contentItem) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $course = $contentItem->module->course;

        // Check enrollment
        $enrollment = $student->lmsEnrollments()
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            return redirect()->route('student.lms.my-courses')
                ->with('error', 'You must be enrolled in this course.');
        }

        $forum = DiscussionForum::getOrCreateForCourse($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|min:10',
            'type' => 'required|in:discussion,question',
            'is_anonymous' => 'boolean',
        ]);

        $thread = DiscussionThread::create([
            'forum_id' => $forum->id,
            'author_id' => $student->id,
            'author_type' => Student::class,
            'content_item_id' => $contentItem->id,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'type' => $validated['type'],
            'is_anonymous' => $forum->allow_anonymous && $request->boolean('is_anonymous'),
            'status' => $forum->require_approval ? 'pending' : 'open',
        ]);

        // Auto-subscribe author
        $thread->subscribe($student);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'thread' => $thread,
                'redirect' => route('student.lms.discussions.thread', $thread),
            ]);
        }

        return redirect()->route('student.lms.discussions.thread', $thread)
            ->with('success', 'Discussion thread created successfully.');
    }

    /**
     * Like/unlike thread or post
     */
    public function toggleLike(Request $request) {
        $student = $this->getStudent();

        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'type' => 'required|in:thread,post',
            'id' => 'required|integer',
        ]);

        if ($validated['type'] === 'thread') {
            $item = DiscussionThread::findOrFail($validated['id']);
        } else {
            $item = DiscussionPost::findOrFail($validated['id']);
        }

        $liked = $item->toggleLike($student);

        return response()->json([
            'liked' => $liked,
            'count' => $item->fresh()->likes_count,
        ]);
    }

    /**
     * Subscribe/unsubscribe to thread
     */
    public function toggleSubscription(DiscussionThread $thread) {
        $student = $this->getStudent();

        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($thread->isSubscribedBy($student)) {
            $thread->unsubscribe($student);
            $subscribed = false;
        } else {
            $thread->subscribe($student);
            $subscribed = true;
        }

        return response()->json(['subscribed' => $subscribed]);
    }

    /**
     * Mark post as answer (for questions)
     */
    public function markAsAnswer(DiscussionPost $post) {
        $student = $this->getStudent();
        $thread = $post->thread;

        // Only thread author can mark answer
        if (!$student || $thread->author_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($thread->type !== 'question') {
            return response()->json(['error' => 'Only questions can have answers'], 400);
        }

        $post->markAsAnswer();

        // Notify answer author
        $this->notificationService->onThreadAnswered($thread, $post);

        return response()->json(['success' => true]);
    }

    protected function processMentions(DiscussionPost $post): void {
        preg_match_all('/@(\w+)/', $post->body, $matches);

        if (empty($matches[1])) {
            return;
        }

        foreach ($matches[1] as $username) {
            $student = Student::where('first_name', 'like', "%{$username}%")
                ->orWhere('last_name', 'like', "%{$username}%")
                ->first();

            if ($student && $student->id !== $post->author_id) {
                DiscussionMention::firstOrCreate([
                    'post_id' => $post->id,
                    'mentioned_student_id' => $student->id,
                ]);

                $this->notificationService->onMention($student, $post);
            }
        }
    }
}
