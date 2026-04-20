<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\DiscussionAttachment;
use App\Models\Lms\DiscussionCategory;
use App\Models\Lms\DiscussionForum;
use App\Models\Lms\DiscussionMention;
use App\Models\Lms\DiscussionPost;
use App\Models\Lms\DiscussionThread;
use App\Models\Lms\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DiscussionController extends Controller {
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService) {
        $this->notificationService = $notificationService;
    }

    /**
     * Get the current authenticated user (student or instructor)
     * Returns array with 'user' and 'type' keys
     */
    protected function getAuthUser(): ?array {
        $student = Auth::guard('student')->user();
        if ($student) {
            return ['user' => $student, 'type' => Student::class];
        }

        $instructor = Auth::user();
        if ($instructor) {
            return ['user' => $instructor, 'type' => User::class];
        }

        return null;
    }

    /**
     * Check if the current user is an instructor
     */
    protected function isInstructor(): bool {
        return Auth::check() && !Auth::guard('student')->check();
    }

    /**
     * View course forum
     */
    public function forum(Course $course) {
        $authUser = $this->getAuthUser();
        $student = Auth::guard('student')->user();
        $instructor = $this->isInstructor() ? Auth::user() : null;

        // Check enrollment for students
        if ($student) {
            $enrollment = Enrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->first();

            if (!$enrollment) {
                return back()->with('error', 'You must be enrolled in this course.');
            }
        }

        $forum = DiscussionForum::getOrCreateForCourse($course);
        $forum->load('categories');

        $threads = DiscussionThread::with(['author', 'category', 'lastReply.author'])
            ->where('forum_id', $forum->id)
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_activity_at')
            ->paginate(20);

        $isInstructor = $this->isInstructor();

        return view('lms.discussions.forum', compact('course', 'forum', 'threads', 'student', 'instructor', 'isInstructor'));
    }

    /**
     * View single thread
     */
    public function thread(DiscussionForum $forum, DiscussionThread $thread) {
        $student = Auth::guard('student')->user();
        $instructor = $this->isInstructor() ? Auth::user() : null;

        $thread->incrementViews();
        $thread->load(['author', 'category', 'acceptedAnswer.author']);

        $posts = $thread->posts()
            ->with(['author', 'replies.author', 'attachments'])
            ->whereNull('parent_id')
            ->visible()
            ->orderBy('created_at')
            ->paginate(20);

        $isSubscribed = $student ? $thread->isSubscribedBy($student) : false;
        $isInstructor = $this->isInstructor();
        $course = $forum->course;

        return view('lms.discussions.thread', compact('forum', 'thread', 'posts', 'student', 'instructor', 'isSubscribed', 'isInstructor', 'course'));
    }

    /**
     * Create new thread form
     */
    public function createThread(DiscussionForum $forum) {
        $authUser = $this->getAuthUser();

        if (!$authUser) {
            return redirect()->route('student.login');
        }

        $forum->load(['categories' => fn($q) => $q->where('is_locked', false)]);

        $student = Auth::guard('student')->user();
        $instructor = $this->isInstructor() ? Auth::user() : null;
        $isInstructor = $this->isInstructor();
        $course = $forum->course;

        return view('lms.discussions.create-thread', compact('forum', 'student', 'instructor', 'isInstructor', 'course'));
    }

    /**
     * Store new thread
     */
    public function storeThread(Request $request, DiscussionForum $forum) {
        $authUser = $this->getAuthUser();

        if (!$authUser) {
            return redirect()->route('student.login');
        }

        $isInstructor = $this->isInstructor();

        // Allow announcement type only for instructors
        $allowedTypes = $isInstructor ? 'discussion,question,announcement' : 'discussion,question';

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|min:10',
            'category_id' => 'nullable|exists:lms_discussion_categories,id',
            'type' => 'required|in:' . $allowedTypes,
            'is_anonymous' => 'boolean',
            'is_pinned' => 'boolean',
        ]);

        $thread = DiscussionThread::create([
            'forum_id' => $forum->id,
            'author_id' => $authUser['user']->id,
            'author_type' => $authUser['type'],
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'type' => $validated['type'],
            'is_anonymous' => !$isInstructor && $forum->allow_anonymous && $request->boolean('is_anonymous'),
            'is_pinned' => $isInstructor && $request->boolean('is_pinned'),
            'status' => $forum->require_approval && !$isInstructor ? 'pending' : 'open',
        ]);

        // Auto-subscribe author (only for students)
        $student = Auth::guard('student')->user();
        if ($student) {
            $thread->subscribe($student);
        }

        return redirect()->route('lms.discussions.thread', [$forum, $thread])
            ->with('success', 'Thread created successfully.');
    }

    /**
     * Store reply to thread
     */
    public function storePost(Request $request, DiscussionThread $thread) {
        $authUser = $this->getAuthUser();
        $isInstructor = $this->isInstructor();

        if (!$authUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Instructors can reply to locked threads
        if ($thread->is_locked && !$isInstructor) {
            return response()->json(['error' => 'Thread is locked'], 403);
        }

        $validated = $request->validate([
            'body' => 'required|string|min:2',
            'parent_id' => 'nullable|exists:lms_discussion_posts,id',
            'is_anonymous' => 'boolean',
            'attachments.*' => 'file|max:10240',
        ]);

        $post = DB::transaction(function () use ($request, $thread, $authUser, $isInstructor, $validated) {
            // Lock thread to update reply count atomically
            $thread = DiscussionThread::lockForUpdate()->find($thread->id);

            $post = DiscussionPost::create([
                'thread_id' => $thread->id,
                'author_id' => $authUser['user']->id,
                'author_type' => $authUser['type'],
                'parent_id' => $validated['parent_id'] ?? null,
                'body' => $validated['body'],
                'is_anonymous' => !$isInstructor && $thread->forum->allow_anonymous && $request->boolean('is_anonymous'),
                'status' => $thread->forum->require_approval && !$isInstructor ? 'pending' : 'visible',
            ]);

            // Update thread activity atomically
            $thread->update(['last_activity_at' => now()]);

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

            return $post;
        });

        // Parse mentions (@username)
        $this->processMentions($post);

        // Send notifications (outside transaction to avoid blocking)
        $this->notificationService->onNewReply($thread->fresh(), $post);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'post' => $post->load(['author', 'attachments']),
            ]);
        }

        return back()->with('success', 'Reply posted.');
    }

    /**
     * Like/unlike thread or post
     */
    public function toggleLike(Request $request) {
        $student = Auth::guard('student')->user();

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
        $student = Auth::guard('student')->user();

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
        $authUser = $this->getAuthUser();
        $thread = $post->thread;

        if (!$authUser) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Thread author or instructors can mark answer
        $canMark = $this->isInstructor() ||
            ($thread->author_type === $authUser['type'] && $thread->author_id === $authUser['user']->id);

        if (!$canMark) {
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

    /**
     * Update post (author can edit within 30 minutes, admin anytime)
     */
    public function updatePost(Request $request, DiscussionPost $post) {
        $student = Auth::guard('student')->user();

        // Only author can edit within 30 min window, or admin anytime
        $canEdit = $student && $post->author_id === $student->id
            && $post->created_at->addMinutes(30)->isFuture();

        if (!$canEdit && !Gate::allows('manage-lms-content')) {
            return response()->json(['error' => 'Unauthorized or edit window expired'], 403);
        }

        $validated = $request->validate([
            'body' => 'required|string|min:2|max:10000',
        ]);

        $post->update(['body' => $validated['body']]);

        // Re-process mentions in case @mentions changed
        $this->processMentions($post);

        return response()->json([
            'success' => true,
            'post' => $post->fresh(['author', 'attachments']),
        ]);
    }

    /**
     * Delete post
     */
    public function deletePost(DiscussionPost $post) {
        $student = Auth::guard('student')->user();

        // Only author or admin can delete
        if (!$student || ($post->author_id !== $student->id && !Gate::allows('manage-lms-content'))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Admin: Lock/unlock thread
     */
    public function toggleLock(DiscussionThread $thread) {
        Gate::authorize('manage-lms-content');

        $thread->update(['is_locked' => !$thread->is_locked]);

        return back()->with('success', $thread->is_locked ? 'Thread locked.' : 'Thread unlocked.');
    }

    /**
     * Admin: Pin/unpin thread
     */
    public function togglePin(DiscussionThread $thread) {
        Gate::authorize('manage-lms-content');

        $thread->update(['is_pinned' => !$thread->is_pinned]);

        return back()->with('success', $thread->is_pinned ? 'Thread pinned.' : 'Thread unpinned.');
    }

    protected function processMentions(DiscussionPost $post): void {
        // Find @mentions in the post body
        preg_match_all('/@(\w+)/', $post->body, $matches);

        if (empty($matches[1])) {
            return;
        }

        foreach ($matches[1] as $username) {
            $student = Student::where('name', 'like', "%{$username}%")->first();

            if ($student && $student->id !== $post->author_id) {
                DiscussionMention::create([
                    'post_id' => $post->id,
                    'mentioned_student_id' => $student->id,
                ]);

                $this->notificationService->onMention($student, $post);
            }
        }
    }
}
