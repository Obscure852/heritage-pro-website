<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\StaffDirectConversation;
use App\Models\StaffDirectMessage;
use App\Models\User;
use App\Services\Messaging\StaffMessagingFeatureService;
use App\Services\Messaging\StaffMessagingService;
use App\Services\Messaging\StaffPresenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffDirectMessagingController extends Controller
{
    public function __construct(
        protected StaffMessagingFeatureService $featureService,
        protected StaffMessagingService $messagingService,
        protected StaffPresenceService $presenceService
    ) {
        $this->middleware('auth');
    }

    public function inbox(Request $request): View
    {
        $user = $request->user();
        $showArchived = $request->boolean('archived', false);
        $conversations = $this->messagingService->getInboxFor($user, $showArchived);
        $onlineUsers = $this->presenceService->getOnlineUsersFor($user, null, 12);
        $onlineUsersCount = $this->presenceService->getOnlineUsersCountFor($user);
        $unreadCount = $this->messagingService->unreadConversationCount($user);

        return view('staff.messaging.inbox', [
            'showArchived' => $showArchived,
            'conversations' => $conversations,
            'onlineUsers' => $onlineUsers,
            'onlineUsersCount' => $onlineUsersCount,
            'unreadCount' => $unreadCount,
            'launcherEnabled' => $this->featureService->presenceLauncherEnabled(),
        ]);
    }

    public function launcher(Request $request): JsonResponse
    {
        $user = $request->user();
        $search = trim((string) $request->query('query', ''));
        $onlineUsers = $this->presenceService->getOnlineUsersFor($user, $search, 8);
        $unreadSenders = $this->messagingService->unreadSendersFor($user, 8);

        return response()->json([
            'success' => true,
            'online_count' => $this->presenceService->getOnlineUsersCountFor($user),
            'unread_count' => $this->messagingService->unreadConversationCount($user),
            'unread_senders' => $unreadSenders
                ->map(fn (array $item) => $this->unreadSenderPayloadFor($item))
                ->values(),
            'users' => $onlineUsers->map(function (User $onlineUser) {
                return [
                    'id' => $onlineUser->id,
                    'name' => $onlineUser->full_name,
                    'position' => $onlineUser->position,
                    'department' => $onlineUser->department,
                    'avatar_url' => $this->avatarUrlFor($onlineUser),
                    'last_seen_at' => $onlineUser->presence_last_seen_at?->toIso8601String(),
                    'last_seen_label' => $onlineUser->presence_last_seen_at?->diffForHumans(),
                ];
            })->values(),
            'poll_seconds' => $this->featureService->launcherPollSeconds(),
        ]);
    }

    protected function unreadSenderPayloadFor(array $item): array
    {
        /** @var StaffDirectConversation $conversation */
        $conversation = $item['conversation'];
        /** @var ?User $sender */
        $sender = $item['user'] ?? null;
        /** @var ?StaffDirectMessage $latestMessage */
        $latestMessage = $item['latest_message'] ?? null;

        $rawBody = $latestMessage ? (string) $latestMessage->body : '';
        $normalizedBody = trim(preg_replace('/\s+/u', ' ', $rawBody) ?? '');
        $preview = $normalizedBody !== ''
            ? mb_strimwidth($normalizedBody, 0, 90, '...')
            : '';

        $lastMessageAt = $latestMessage?->created_at ?? $conversation->last_message_at;
        $conversationUrl = route('staff.messages.conversation', $conversation);

        return [
            'conversation_id' => (int) $conversation->id,
            'conversation_url' => $conversationUrl,
            'user_id' => $sender ? (int) $sender->id : 0,
            'name' => $sender ? ($sender->full_name ?: 'Staff user') : 'Unknown staff',
            'position' => $sender?->position,
            'department' => $sender?->department,
            'avatar_url' => $sender
                ? $this->avatarUrlFor($sender)
                : asset('assets/images/users/default-profile.png'),
            'unread_count' => (int) ($item['unread_count'] ?? 0),
            'latest_preview' => $preview,
            'latest_message_at' => $lastMessageAt ? Carbon::parse($lastMessageAt)->toIso8601String() : null,
            'latest_message_label' => $lastMessageAt ? Carbon::parse($lastMessageAt)->diffForHumans() : null,
            'is_online' => (bool) ($item['is_online'] ?? false),
        ];
    }

    public function heartbeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'last_path' => ['nullable', 'string', 'max:255'],
        ]);

        $this->presenceService->heartbeat(
            $request->user(),
            $request->session()->getId(),
            $validated['last_path'] ?? $request->path()
        );

        return response()->json([
            'success' => true,
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $this->messagingService->unreadConversationCount($request->user()),
        ]);
    }

    public function recipients(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('query', ''));
        $users = $this->messagingService->searchRecipients($request->user(), $search, 10);

        return response()->json([
            'success' => true,
            'users' => $users->map(fn (User $recipient) => $this->recipientPayloadFor($recipient))->values(),
        ]);
    }

    public function startConversation(Request $request): JsonResponse|RedirectResponse
    {
        $bodyWasSubmitted = $request->exists('body');
        $validated = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        $body = $bodyWasSubmitted ? ($validated['body'] ?? null) : null;
        if ($bodyWasSubmitted && ($body === null || trim($body) === '')) {
            throw ValidationException::withMessages([
                'body' => ['Message body cannot be empty.'],
            ]);
        }

        $recipient = User::query()->findOrFail($validated['recipient_id']);
        $conversation = $this->messagingService->startConversation($request->user(), $recipient, $body);
        $redirectUrl = route('staff.messages.conversation', $conversation);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'redirect_url' => $redirectUrl,
                'conversation_id' => $conversation->id,
                'message_sent' => $bodyWasSubmitted,
            ]);
        }

        return redirect()->to($redirectUrl);
    }

    public function conversation(StaffDirectConversation $conversation, Request $request): View
    {
        $user = $request->user();

        if (!$conversation->isParticipant($user)) {
            abort(403, 'Unauthorized access to conversation.');
        }

        $messages = $conversation->messages()
            ->with('sender')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(50);

        $messages->setCollection(
            $messages->getCollection()->reverse()->values()
        );

        $lastVisibleMessageId = $messages->getCollection()->max('id');

        if ($messages->currentPage() === 1 && $lastVisibleMessageId) {
            $this->messagingService->markConversationRead($conversation, $user, (int) $lastVisibleMessageId);
        }

        $conversation->load(['userOne', 'userTwo']);
        $otherParticipant = $conversation->otherParticipantFor($user);
        $participantPresence = $this->presenceService->presenceStateFor($otherParticipant);
        $otherParticipantLastReadMessageId = $this->otherParticipantLastReadMessageIdFor($conversation, $user);

        return view('staff.messaging.conversation', [
            'conversation' => $conversation,
            'messages' => $messages,
            'otherParticipant' => $otherParticipant,
            'participantPresence' => $participantPresence,
            'otherParticipantLastReadMessageId' => $otherParticipantLastReadMessageId,
            'isLiveMode' => $messages->currentPage() === 1,
            'lastRenderedMessageId' => $lastVisibleMessageId ? (int) $lastVisibleMessageId : 0,
            'conversationPollSeconds' => $this->featureService->conversationPollSeconds(),
        ]);
    }

    public function updates(StaffDirectConversation $conversation, Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$conversation->isParticipant($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to conversation.',
            ], 403);
        }

        $validated = $request->validate([
            'after_message_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $afterMessageId = (int) ($validated['after_message_id'] ?? 0);
        $messages = $conversation->messages()
            ->with('sender')
            ->when($afterMessageId > 0, function ($query) use ($afterMessageId) {
                $query->where('id', '>', $afterMessageId);
            })
            ->orderBy('id')
            ->get();

        $latestDeliveredIncomingMessageId = $messages
            ->where('sender_id', '!=', $user->id)
            ->max('id');

        if ($latestDeliveredIncomingMessageId) {
            $this->messagingService->markConversationRead($conversation, $user, (int) $latestDeliveredIncomingMessageId);
        }

        $otherParticipant = $conversation->otherParticipantFor($user);
        $participantPresence = $this->presenceService->presenceStateFor($otherParticipant);
        $latestMessageId = $messages->max('id') ?: $afterMessageId;

        return response()->json([
            'success' => true,
            'messages' => $this->messagePayloadCollection($messages, $user),
            'latest_message_id' => (int) $latestMessageId,
            'participant_online' => $participantPresence['is_online'],
            'participant_last_seen_label' => $participantPresence['last_seen_label'],
            'other_participant_last_read_message_id' => $this->otherParticipantLastReadMessageIdFor($conversation, $user),
            'poll_seconds' => $this->featureService->conversationPollSeconds(),
        ]);
    }

    public function reply(StaffDirectConversation $conversation, Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = $this->messagingService->sendMessage($conversation, $request->user(), $validated['body']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $this->messagePayloadFor($message, $request->user()),
                'latest_message_id' => (int) $message->id,
                'other_participant_last_read_message_id' => $this->otherParticipantLastReadMessageIdFor($conversation, $request->user()),
                'notice' => 'Reply sent successfully.',
            ]);
        }

        return redirect()
            ->route('staff.messages.conversation', $conversation)
            ->with('message', 'Reply sent successfully.');
    }

    public function archive(StaffDirectConversation $conversation, Request $request): JsonResponse|RedirectResponse
    {
        $this->messagingService->archiveConversation($conversation, $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Conversation archived.',
            ]);
        }

        return redirect()
            ->route('staff.messages.inbox')
            ->with('message', 'Conversation archived.');
    }

    public function unarchive(StaffDirectConversation $conversation, Request $request): JsonResponse|RedirectResponse
    {
        $this->messagingService->unarchiveConversation($conversation, $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Conversation restored.',
            ]);
        }

        return redirect()
            ->route('staff.messages.inbox', ['archived' => true])
            ->with('message', 'Conversation restored.');
    }

    protected function avatarUrlFor(User $user): string
    {
        if (!$user->avatar) {
            return asset('assets/images/users/default-profile.png');
        }

        if (str_starts_with($user->avatar, 'http://') || str_starts_with($user->avatar, 'https://')) {
            return $user->avatar;
        }

        return asset('storage/' . ltrim($user->avatar, '/'));
    }

    protected function recipientPayloadFor(User $user): array
    {
        $lastSeenAt = $user->presence_last_seen_at ? Carbon::parse($user->presence_last_seen_at) : null;

        return [
            'id' => (int) $user->id,
            'name' => $user->full_name,
            'position' => $user->position,
            'department' => $user->department,
            'avatar_url' => $this->avatarUrlFor($user),
            'is_online' => (bool) $user->is_online,
            'last_seen_label' => $lastSeenAt?->diffForHumans() ?? 'Offline',
            'conversation_id' => $user->existing_conversation_id ? (int) $user->existing_conversation_id : null,
        ];
    }

    protected function messagePayloadCollection(Collection $messages, User $viewer): array
    {
        return $messages
            ->map(fn (StaffDirectMessage $message) => $this->messagePayloadFor($message, $viewer))
            ->values()
            ->all();
    }

    protected function messagePayloadFor(StaffDirectMessage $message, User $viewer): array
    {
        $sender = $message->sender;

        return [
            'id' => (int) $message->id,
            'body' => $message->body,
            'sender_id' => (int) $message->sender_id,
            'sender_name' => $sender?->full_name ?? 'Staff User',
            'sender_avatar_url' => $sender ? $this->avatarUrlFor($sender) : asset('assets/images/users/default-profile.png'),
            'created_at_iso' => optional($message->created_at)->toIso8601String(),
            'created_at_label' => optional($message->created_at)->format('M d, Y H:i'),
            'read_at_iso' => optional($message->read_at)->toIso8601String(),
            'is_sent_by_current_user' => $message->sender_id === $viewer->id,
            'is_seen_by_other_participant' => $message->sender_id === $viewer->id && $message->read_at !== null,
        ];
    }

    protected function otherParticipantLastReadMessageIdFor(StaffDirectConversation $conversation, User $viewer): int
    {
        $otherParticipant = $conversation->otherParticipantFor($viewer);
        $lastReadMessageColumn = $conversation->lastReadMessageColumnFor($otherParticipant->id);

        return (int) ($conversation->{$lastReadMessageColumn} ?? 0);
    }
}
