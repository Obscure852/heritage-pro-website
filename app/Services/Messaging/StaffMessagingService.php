<?php

namespace App\Services\Messaging;

use App\Models\StaffDirectConversation;
use App\Models\StaffDirectMessage;
use App\Models\StaffUserPresence;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StaffMessagingService
{
    public function __construct(
        protected StaffMessagingFeatureService $featureService
    ) {
    }

    public function startConversation(User $sender, User $recipient, ?string $body = null): StaffDirectConversation
    {
        return DB::transaction(function () use ($sender, $recipient, $body) {
            $this->ensureRecipientCanBeMessaged($sender, $recipient);

            [$userOneId, $userTwoId] = $this->normalizePair($sender->id, $recipient->id);

            $conversation = $this->findOrCreateConversation([
                'user_one_id' => $userOneId,
                'user_two_id' => $userTwoId,
            ]);

            $conversation->unarchiveFor($sender);

            if ($body !== null) {
                $this->sendMessage($conversation, $sender, $body);
            }

            return $conversation->fresh(['userOne', 'userTwo']);
        });
    }

    public function searchRecipients(User $currentUser, string $search, int $limit = 10): Collection
    {
        $search = trim($search);

        if (mb_strlen($search) < 2) {
            return collect();
        }

        $limit = max(1, min($limit, 25));
        $threshold = now()->subMinutes($this->featureService->onlineWindowMinutes());
        $currentUserId = $currentUser->id;

        $latestPresence = StaffUserPresence::query()
            ->selectRaw('user_id, MAX(last_seen_at) as last_seen_at')
            ->where('last_seen_at', '>=', $threshold)
            ->groupBy('user_id');

        $existingConversationQuery = StaffDirectConversation::query()
            ->select([
                'staff_direct_conversations.id',
                'staff_direct_conversations.user_one_id',
                'staff_direct_conversations.user_two_id',
            ])
            ->whereHas('messages');

        return User::query()
            ->select([
                'users.id',
                'users.firstname',
                'users.lastname',
                'users.avatar',
                'users.position',
                'users.department',
            ])
            ->selectRaw('presence_latest.last_seen_at as presence_last_seen_at')
            ->selectRaw('existing_conversation.id as existing_conversation_id')
            ->selectRaw('CASE WHEN presence_latest.last_seen_at IS NULL THEN 0 ELSE 1 END as is_online')
            ->leftJoinSub($latestPresence, 'presence_latest', function ($join) {
                $join->on('presence_latest.user_id', '=', 'users.id');
            })
            ->leftJoinSub($existingConversationQuery, 'existing_conversation', function ($join) use ($currentUserId) {
                $join->where(function ($pairQuery) use ($currentUserId) {
                    $pairQuery
                        ->where(function ($forwardPairQuery) use ($currentUserId) {
                            $forwardPairQuery
                                ->where('existing_conversation.user_one_id', '=', $currentUserId)
                                ->whereColumn('existing_conversation.user_two_id', 'users.id');
                        })
                        ->orWhere(function ($reversePairQuery) use ($currentUserId) {
                            $reversePairQuery
                                ->whereColumn('existing_conversation.user_one_id', 'users.id')
                                ->where('existing_conversation.user_two_id', '=', $currentUserId);
                        });
                });
            })
            ->where('users.id', '!=', $currentUserId)
            ->where('users.active', true)
            ->whereNull('users.deleted_at')
            ->where(function (Builder $searchQuery) use ($search) {
                $searchQuery
                    ->whereRaw("CONCAT(COALESCE(users.firstname, ''), ' ', COALESCE(users.lastname, '')) LIKE ?", ["%{$search}%"])
                    ->orWhere('users.position', 'like', "%{$search}%")
                    ->orWhere('users.department', 'like', "%{$search}%");
            })
            ->orderByRaw('CASE WHEN existing_conversation.id IS NULL THEN 1 ELSE 0 END')
            ->orderByRaw('CASE WHEN presence_latest.last_seen_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('users.firstname')
            ->orderBy('users.lastname')
            ->limit($limit)
            ->get();
    }

    public function getInboxFor(User $user, bool $archived = false, int $perPage = 20): LengthAwarePaginator
    {
        $conversations = $this->withUnreadCount(
                $this->inboxQueryFor($user, $archived),
                $user
            )
            ->with(['userOne', 'userTwo', 'latestMessage.sender'])
            ->paginate($perPage);

        $conversations->getCollection()->transform(function (StaffDirectConversation $conversation) use ($user) {
            $conversation->setRelation('otherParticipant', $conversation->otherParticipantFor($user));
            $conversation->unread_count = (int) $conversation->unread_count;
            $conversation->has_unread = $conversation->unread_count > 0;

            return $conversation;
        });

        return $conversations;
    }

    public function unreadConversationCount(User $user): int
    {
        return $this->withUnreadCount(
                $this->inboxQueryFor($user, false),
                $user
            )
            ->get()
            ->where('unread_count', '>', 0)
            ->count();
    }

    /**
     * Return a collection describing the staff members who have sent the given
     * user unread messages. Each entry contains the raw conversation model,
     * the other participant (User model, may be null if trashed), the unread
     * count, the latest message model and a resolved online/offline flag.
     *
     * The collection is ordered by most recent message first and capped to
     * the requested limit. Consumers (e.g. the launcher endpoint) shape the
     * entries into a JSON payload.
     */
    public function unreadSendersFor(User $user, int $limit = 8): Collection
    {
        $limit = max(1, min($limit, 25));

        $conversations = $this->withUnreadCount(
                $this->inboxQueryFor($user, false),
                $user
            )
            ->with(['userOne', 'userTwo', 'latestMessage'])
            ->get()
            ->filter(fn (StaffDirectConversation $conversation) => (int) $conversation->unread_count > 0)
            ->values()
            ->take($limit);

        if ($conversations->isEmpty()) {
            return collect();
        }

        $otherParticipantIds = $conversations
            ->map(fn (StaffDirectConversation $conversation) => optional($conversation->otherParticipantFor($user))->id)
            ->filter()
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $onlineIds = collect();
        if (!empty($otherParticipantIds)) {
            $threshold = now()->subMinutes($this->featureService->onlineWindowMinutes());
            $onlineIds = StaffUserPresence::query()
                ->whereIn('user_id', $otherParticipantIds)
                ->where('last_seen_at', '>=', $threshold)
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->flip();
        }

        return $conversations->map(function (StaffDirectConversation $conversation) use ($user, $onlineIds) {
            $otherParticipant = $conversation->otherParticipantFor($user);
            $otherParticipantId = $otherParticipant ? (int) $otherParticipant->id : null;

            return [
                'conversation' => $conversation,
                'user' => $otherParticipant,
                'unread_count' => (int) $conversation->unread_count,
                'latest_message' => $conversation->latestMessage,
                'is_online' => $otherParticipantId !== null && $onlineIds->has($otherParticipantId),
            ];
        })->values();
    }

    public function sendMessage(StaffDirectConversation $conversation, User $sender, string $body): StaffDirectMessage
    {
        if (!$conversation->isParticipant($sender)) {
            abort(403, 'Unauthorized access to conversation.');
        }

        $trimmedBody = trim($body);
        if ($trimmedBody === '') {
            throw ValidationException::withMessages([
                'body' => ['Message body cannot be empty.'],
            ]);
        }

        $message = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => $trimmedBody,
        ]);

        $conversation->unarchiveFor($sender);
        $conversation->unarchiveFor($conversation->otherParticipantFor($sender));
        $conversation->markAsReadFor($sender, $message->id);

        return $message->load('sender');
    }

    public function markConversationRead(StaffDirectConversation $conversation, User $user, ?int $upToMessageId = null): void
    {
        if (!$conversation->isParticipant($user)) {
            abort(403, 'Unauthorized access to conversation.');
        }

        $conversation->markAsReadFor($user, $upToMessageId);
    }

    public function archiveConversation(StaffDirectConversation $conversation, User $user): void
    {
        if (!$conversation->isParticipant($user)) {
            abort(403, 'Unauthorized access to conversation.');
        }

        $conversation->archiveFor($user);
    }

    public function unarchiveConversation(StaffDirectConversation $conversation, User $user): void
    {
        if (!$conversation->isParticipant($user)) {
            abort(403, 'Unauthorized access to conversation.');
        }

        $conversation->unarchiveFor($user);
    }

    public function normalizePair(int $firstUserId, int $secondUserId): array
    {
        return $firstUserId < $secondUserId
            ? [$firstUserId, $secondUserId]
            : [$secondUserId, $firstUserId];
    }

    protected function inboxQueryFor(User $user, bool $archived): Builder
    {
        return StaffDirectConversation::query()
            ->select('staff_direct_conversations.*')
            ->forUser($user)
            ->whereHas('messages')
            ->where(function (Builder $archiveQuery) use ($user, $archived) {
                $archiveQuery
                    ->where(function (Builder $userOneQuery) use ($user, $archived) {
                        $userOneQuery->where('user_one_id', $user->id)
                            ->where('is_archived_by_user_one', $archived);
                    })
                    ->orWhere(function (Builder $userTwoQuery) use ($user, $archived) {
                        $userTwoQuery->where('user_two_id', $user->id)
                            ->where('is_archived_by_user_two', $archived);
                    });
            })
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at');
    }

    protected function withUnreadCount(Builder $query, User $user): Builder
    {
        $userId = $user->id;

        return $query->withCount([
            'messages as unread_count' => function (Builder $messageQuery) use ($userId) {
                $messageQuery
                    ->where('sender_id', '!=', $userId)
                    ->where(function (Builder $participantQuery) use ($userId) {
                        $participantQuery
                            ->where(function (Builder $userOneQuery) use ($userId) {
                                $userOneQuery
                                    ->whereRaw('staff_direct_conversations.user_one_id = ?', [$userId])
                                    ->where(function (Builder $readWindowQuery) {
                                        $readWindowQuery
                                            ->whereNull('staff_direct_conversations.user_one_last_read_message_id')
                                            ->orWhereColumn(
                                                'staff_direct_messages.id',
                                                '>',
                                                'staff_direct_conversations.user_one_last_read_message_id'
                                            );
                                    });
                            })
                            ->orWhere(function (Builder $userTwoQuery) use ($userId) {
                                $userTwoQuery
                                    ->whereRaw('staff_direct_conversations.user_two_id = ?', [$userId])
                                    ->where(function (Builder $readWindowQuery) {
                                        $readWindowQuery
                                            ->whereNull('staff_direct_conversations.user_two_last_read_message_id')
                                            ->orWhereColumn(
                                                'staff_direct_messages.id',
                                                '>',
                                                'staff_direct_conversations.user_two_last_read_message_id'
                                            );
                                    });
                            });
                    });
            },
        ]);
    }

    protected function findOrCreateConversation(array $attributes, array $values = []): StaffDirectConversation
    {
        try {
            return StaffDirectConversation::firstOrCreate($attributes, $values);
        } catch (QueryException $exception) {
            if (!$this->isDuplicateConversationPairException($exception)) {
                throw $exception;
            }

            return StaffDirectConversation::query()
                ->where($attributes)
                ->firstOrFail();
        }
    }

    protected function isDuplicateConversationPairException(QueryException $exception): bool
    {
        $message = $exception->getMessage();
        $sqlState = $exception->errorInfo[0] ?? null;
        $driverCode = $exception->errorInfo[1] ?? null;

        return str_contains($message, 'staff_direct_unique_pair')
            || str_contains($message, 'UNIQUE constraint failed: staff_direct_conversations.user_one_id, staff_direct_conversations.user_two_id')
            || ($sqlState === '23000' && $driverCode === 1062);
    }

    protected function ensureRecipientCanBeMessaged(User $sender, User $recipient): void
    {
        if ($sender->id === $recipient->id) {
            throw ValidationException::withMessages([
                'recipient_id' => ['You cannot message yourself.'],
            ]);
        }

        if (!$recipient->active || $recipient->trashed()) {
            throw ValidationException::withMessages([
                'recipient_id' => ['Selected staff member is not available for messaging.'],
            ]);
        }
    }
}
