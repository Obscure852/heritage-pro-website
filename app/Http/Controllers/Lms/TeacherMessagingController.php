<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\Conversation;
use App\Models\Lms\DirectMessage;
use App\Models\Lms\MessageAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherMessagingController extends Controller {
    /**
     * Display instructor's message inbox
     */
    public function inbox(Request $request) {
        $instructor = Auth::user();

        $query = Conversation::with(['student', 'course', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->where('instructor_id', $instructor->id);

        // Filter by archived status
        $showArchived = $request->boolean('archived', false);
        if ($showArchived) {
            $query->where('is_archived_by_instructor', true);
        } else {
            $query->where('is_archived_by_instructor', false);
        }

        $conversations = $query->orderByDesc('last_message_at')
            ->paginate(20);

        // Calculate unread status for each conversation
        $conversations->each(function ($conversation) {
            $conversation->has_unread = $conversation->hasUnreadForInstructor();
            $conversation->unread_count = $conversation->unreadCountForInstructor();
        });

        // Get total unread count
        $totalUnread = Conversation::where('instructor_id', $instructor->id)
            ->where('is_archived_by_instructor', false)
            ->get()
            ->sum(fn($c) => $c->unreadCountForInstructor());

        return view('lms.messaging.inbox', compact('conversations', 'showArchived', 'instructor', 'totalUnread'));
    }

    /**
     * View a conversation thread
     */
    public function conversation(Conversation $conversation) {
        $instructor = Auth::user();

        // Verify ownership
        if ($conversation->instructor_id !== $instructor->id) {
            abort(403, 'Unauthorized access to conversation.');
        }

        // Load messages with attachments
        $messages = $conversation->messages()
            ->with(['sender', 'attachments'])
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        // Mark conversation as read
        $conversation->markAsReadByInstructor();

        // Load related data
        $conversation->load(['student', 'course']);

        return view('lms.messaging.conversation', compact('conversation', 'messages', 'instructor'));
    }

    /**
     * Reply to a conversation
     */
    public function reply(Request $request, Conversation $conversation) {
        $instructor = Auth::user();

        // Verify ownership
        if ($conversation->instructor_id !== $instructor->id) {
            abort(403, 'Unauthorized access to conversation.');
        }

        $validated = $request->validate([
            'body' => 'required|string|min:2|max:10000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240',
        ]);

        DB::transaction(function () use ($instructor, $conversation, $validated, $request) {
            // Create message
            $message = DirectMessage::create([
                'conversation_id' => $conversation->id,
                'sender_type' => User::class,
                'sender_id' => $instructor->id,
                'body' => $validated['body'],
            ]);

            // Handle attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
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

            // Mark as read by instructor
            $conversation->markAsReadByInstructor();
        });

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Reply sent successfully.']);
        }

        return redirect()->route('lms.messaging.conversation', $conversation)
            ->with('success', 'Reply sent successfully.');
    }

    /**
     * Archive a conversation
     */
    public function archive(Request $request, Conversation $conversation) {
        $instructor = Auth::user();

        // Verify ownership
        if ($conversation->instructor_id !== $instructor->id) {
            abort(403, 'Unauthorized access to conversation.');
        }

        $conversation->archiveForInstructor();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Conversation archived.']);
        }

        return redirect()->route('lms.messaging.inbox')
            ->with('success', 'Conversation archived.');
    }

    /**
     * Unarchive a conversation
     */
    public function unarchive(Request $request, Conversation $conversation) {
        $instructor = Auth::user();

        // Verify ownership
        if ($conversation->instructor_id !== $instructor->id) {
            abort(403, 'Unauthorized access to conversation.');
        }

        $conversation->unarchiveForInstructor();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Conversation restored.']);
        }

        return redirect()->route('lms.messaging.inbox', ['archived' => true])
            ->with('success', 'Conversation restored.');
    }

    /**
     * Get unread message count (AJAX endpoint)
     */
    public function unreadCount(Request $request) {
        $instructor = Auth::user();

        $count = Conversation::where('instructor_id', $instructor->id)
            ->where('is_archived_by_instructor', false)
            ->get()
            ->sum(fn($c) => $c->unreadCountForInstructor());

        return response()->json(['count' => $count]);
    }
}
