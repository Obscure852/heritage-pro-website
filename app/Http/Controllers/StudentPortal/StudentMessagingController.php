<?php

namespace App\Http\Controllers\StudentPortal;

use App\Http\Controllers\Controller;
use App\Models\Lms\Conversation;
use App\Models\Lms\Course;
use App\Models\Lms\DirectMessage;
use App\Models\Lms\MessageAttachment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentMessagingController extends Controller {
    public function __construct() {
        $this->middleware('auth:student');
    }

    protected function getStudent(): ?Student {
        return Auth::guard('student')->user();
    }

    /**
     * Display student's message inbox
     */
    public function inbox(Request $request) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $query = $student->conversations()
            ->with(['instructor', 'course', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }]);

        // Filter by archived status
        $showArchived = $request->boolean('archived', false);
        if ($showArchived) {
            $query->where('is_archived_by_student', true);
        } else {
            $query->where('is_archived_by_student', false);
        }

        $conversations = $query->orderByDesc('last_message_at')
            ->paginate(20);

        // Calculate unread status for each conversation
        $conversations->each(function ($conversation) {
            $conversation->has_unread = $conversation->hasUnreadForStudent();
        });

        // Get stats for the header
        $totalCount = $student->conversations()
            ->where('is_archived_by_student', false)
            ->count();

        $unreadCount = $student->unreadConversationsCount();

        $archivedCount = $student->conversations()
            ->where('is_archived_by_student', true)
            ->count();

        return view('students.portal.lms.messaging.inbox', compact(
            'conversations',
            'showArchived',
            'student',
            'totalCount',
            'unreadCount',
            'archivedCount'
        ));
    }

    /**
     * Show compose new message form
     */
    public function compose(Request $request) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        // Get instructors from student's enrolled courses
        $enrolledCourses = $student->lmsEnrollments()
            ->with(['course.instructor'])
            ->where('status', 'active')
            ->get()
            ->pluck('course')
            ->filter();

        $instructors = $enrolledCourses
            ->pluck('instructor')
            ->filter()
            ->unique('id')
            ->values();

        // Pre-selected instructor if passed via query param
        $selectedInstructorId = $request->input('instructor_id');
        $selectedCourseId = $request->input('course_id');

        return view('students.portal.lms.messaging.compose', compact(
            'student',
            'enrolledCourses',
            'instructors',
            'selectedInstructorId',
            'selectedCourseId'
        ));
    }

    /**
     * Send a new message (create conversation if needed)
     */
    public function send(Request $request) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $validated = $request->validate([
            'instructor_id' => 'required|exists:users,id',
            'course_id' => 'nullable|exists:lms_courses,id',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string|min:2|max:10000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240',
        ]);

        // Verify instructor teaches a course the student is enrolled in
        $instructor = User::findOrFail($validated['instructor_id']);
        $enrolledCourseIds = $student->enrolledCourseIds();

        $instructorCourseIds = Course::where('instructor_id', $instructor->id)
            ->whereIn('id', $enrolledCourseIds)
            ->pluck('id')
            ->toArray();

        if (empty($instructorCourseIds)) {
            return back()->with('error', 'You can only message instructors of your enrolled courses.');
        }

        // Validate course_id if provided
        if (!empty($validated['course_id']) && !in_array($validated['course_id'], $instructorCourseIds)) {
            return back()->with('error', 'Invalid course selected.');
        }

        DB::transaction(function () use ($student, $validated, &$conversation) {
            // Find or create conversation
            $conversation = Conversation::findOrCreateForStudentAndInstructor(
                $student->id,
                $validated['instructor_id'],
                $validated['course_id'] ?? null,
                $validated['subject'] ?? null
            );

            // Update subject if provided and conversation is new or has no subject
            if (!empty($validated['subject']) && empty($conversation->subject)) {
                $conversation->update(['subject' => $validated['subject']]);
            }

            // Unarchive if archived
            if ($conversation->is_archived_by_student) {
                $conversation->unarchiveForStudent();
            }

            // Create message
            $message = DirectMessage::create([
                'conversation_id' => $conversation->id,
                'sender_type' => Student::class,
                'sender_id' => $student->id,
                'body' => $validated['body'],
            ]);

            // Handle attachments
            if (isset($validated['attachments'])) {
                foreach ($validated['attachments'] as $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('lms/messages/attachments', $filename, 'public');

                    MessageAttachment::create([
                        'message_id' => $message->id,
                        'filename' => $filename,
                        'original_filename' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            // Mark as read by student (since they're sending it)
            $conversation->markAsReadByStudent();
        });

        return redirect()->route('student.lms.messages.conversation', $conversation)
            ->with('success', 'Message sent successfully.');
    }

    /**
     * View a conversation thread
     */
    public function conversation(Conversation $conversation) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        // Verify ownership
        if ($conversation->student_id !== $student->id) {
            abort(403, 'Unauthorized access to conversation.');
        }

        // Load messages with attachments
        $messages = $conversation->messages()
            ->with(['sender', 'attachments'])
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        // Mark conversation as read
        $conversation->markAsReadByStudent();

        // Load related data
        $conversation->load(['instructor', 'course']);

        return view('students.portal.lms.messaging.conversation', compact('conversation', 'messages', 'student'));
    }

    /**
     * Reply to a conversation
     */
    public function reply(Request $request, Conversation $conversation) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        // Verify ownership
        if ($conversation->student_id !== $student->id) {
            abort(403, 'Unauthorized access to conversation.');
        }

        $validated = $request->validate([
            'body' => 'required|string|min:2|max:10000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240',
        ]);

        DB::transaction(function () use ($student, $conversation, $validated) {
            // Create message
            $message = DirectMessage::create([
                'conversation_id' => $conversation->id,
                'sender_type' => Student::class,
                'sender_id' => $student->id,
                'body' => $validated['body'],
            ]);

            // Handle attachments
            if (isset($validated['attachments'])) {
                foreach ($validated['attachments'] as $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('lms/messages/attachments', $filename, 'public');

                    MessageAttachment::create([
                        'message_id' => $message->id,
                        'filename' => $filename,
                        'original_filename' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            // Mark as read by student
            $conversation->markAsReadByStudent();
        });

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Reply sent successfully.']);
        }

        return redirect()->route('student.lms.messages.conversation', $conversation)
            ->with('success', 'Reply sent successfully.');
    }

    /**
     * Archive a conversation
     */
    public function archive(Request $request, Conversation $conversation) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        // Verify ownership
        if ($conversation->student_id !== $student->id) {
            abort(403, 'Unauthorized access to conversation.');
        }

        $conversation->archiveForStudent();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Conversation archived.']);
        }

        return redirect()->route('student.lms.messages.inbox')
            ->with('success', 'Conversation archived.');
    }

    /**
     * Unarchive a conversation
     */
    public function unarchive(Request $request, Conversation $conversation) {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('student.login');
        }

        // Verify ownership
        if ($conversation->student_id !== $student->id) {
            abort(403, 'Unauthorized access to conversation.');
        }

        $conversation->unarchiveForStudent();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Conversation restored.']);
        }

        return redirect()->route('student.lms.messages.inbox', ['archived' => true])
            ->with('success', 'Conversation restored.');
    }

    /**
     * Get unread message count (AJAX endpoint)
     */
    public function unreadCount(Request $request) {
        $student = $this->getStudent();

        if (!$student) {
            return response()->json(['count' => 0]);
        }

        $count = $student->unreadConversationsCount();

        return response()->json(['count' => $count]);
    }
}
