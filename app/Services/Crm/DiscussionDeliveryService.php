<?php

namespace App\Services\Crm;

use App\Models\Contact;
use App\Models\CrmCommercialDocumentArtifact;
use App\Models\CrmInvoice;
use App\Models\CrmQuote;
use App\Models\CrmUserDepartment;
use App\Models\Customer;
use App\Models\DiscussionCampaign;
use App\Models\DiscussionCampaignRecipient;
use App\Models\DiscussionMessage;
use App\Models\DiscussionMessageAttachment;
use App\Models\DiscussionMessageMention;
use App\Models\DiscussionThread;
use App\Models\DiscussionThreadParticipant;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class DiscussionDeliveryService
{
    public function __construct(
        private readonly CommercialDocumentPdfService $documentPdfService
    ) {
    }

    public function threadQueryFor(User $user): Builder
    {
        return DiscussionThread::query()
            ->with([
                'initiatedBy',
                'recipientUser',
                'integration',
                'participants.user',
                'latestMessage.attachments',
                'latestMessage.mentions.user',
            ])
            ->when(! $user->canManageOperationalRecords(), function (Builder $query) use ($user): void {
                $query->where(function (Builder $threadQuery) use ($user): void {
                    $threadQuery->where('owner_id', $user->id)
                        ->orWhere('initiated_by_id', $user->id)
                        ->orWhere('recipient_user_id', $user->id)
                        ->orWhereHas('participants', function (Builder $participantQuery) use ($user): void {
                            $participantQuery->where('user_id', $user->id);
                        });
                });
            });
    }

    public function campaignQueryFor(User $user, string $channel): Builder
    {
        return DiscussionCampaign::query()
            ->where('channel', $channel)
            ->with(['initiatedBy', 'integration', 'recipients'])
            ->when(! $user->canManageOperationalRecords(), function (Builder $query) use ($user): void {
                $query->where('owner_id', $user->id);
            });
    }

    public function authorizeThreadAccess(User $user, DiscussionThread $thread): void
    {
        if ($user->canManageOperationalRecords()) {
            return;
        }

        if ($thread->isAppThread()) {
            $participantIds = $thread->participants()
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            abort_unless(
                in_array($user->id, $participantIds, true)
                || in_array($user->id, [
                    (int) $thread->owner_id,
                    (int) $thread->initiated_by_id,
                    (int) $thread->recipient_user_id,
                ], true),
                403
            );

            return;
        }

        abort_unless(
            in_array($user->id, [
                (int) $thread->owner_id,
                (int) $thread->initiated_by_id,
                (int) $thread->recipient_user_id,
            ], true),
            403
        );
    }

    public function authorizeCampaignAccess(User $user, DiscussionCampaign $campaign): void
    {
        if ($user->canManageOperationalRecords()) {
            return;
        }

        abort_unless((int) $campaign->owner_id === (int) $user->id, 403);
    }

    public function threadRoute(DiscussionThread $thread): string
    {
        if ($thread->channel === 'app') {
            return $thread->isCompanyChat()
                ? route('crm.discussions.app.company-chat')
                : route('crm.discussions.app.threads.show', $thread);
        }

        $channelPrefix = $thread->channel === 'whatsapp' ? 'crm.discussions.whatsapp' : 'crm.discussions.email';

        return $thread->status === 'draft'
            ? route($channelPrefix . '.direct.edit', $thread)
            : route($channelPrefix . '.direct.show', $thread);
    }

    public function startOrResumeDirectThread(User $sender, User $recipient, array $attributes = []): DiscussionThread
    {
        if ((int) $sender->id === (int) $recipient->id) {
            throw ValidationException::withMessages([
                'recipient_user_id' => 'You cannot start a direct message with yourself.',
            ]);
        }

        $participantIds = collect([(int) $sender->id, (int) $recipient->id])->sort()->values();
        $pairKey = $participantIds->implode(':');

        return DB::transaction(function () use ($attributes, $pairKey, $participantIds, $recipient, $sender): DiscussionThread {
            $thread = DiscussionThread::query()
                ->where('channel', 'app')
                ->where('kind', 'direct')
                ->where('direct_participant_key', $pairKey)
                ->first();

            if (! $thread) {
                $thread = DiscussionThread::query()->create([
                    'owner_id' => $sender->id,
                    'initiated_by_id' => $sender->id,
                    'recipient_user_id' => $recipient->id,
                    'direct_participant_key' => $pairKey,
                    'subject' => $attributes['subject'] ?? $this->defaultDirectSubject($sender, $recipient),
                    'channel' => 'app',
                    'kind' => 'direct',
                    'delivery_status' => 'sent',
                    'status' => 'sent',
                    'notes' => $attributes['notes'] ?? null,
                    'metadata_updated_at' => now(),
                    'edited_by_id' => $sender->id,
                ]);
            } elseif (filled($attributes['subject'] ?? null) || array_key_exists('notes', $attributes)) {
                $thread->forceFill([
                    'subject' => $attributes['subject'] ?? $thread->subject,
                    'notes' => $attributes['notes'] ?? $thread->notes,
                    'metadata_updated_at' => now(),
                    'edited_by_id' => $sender->id,
                ])->save();
            }

            foreach ($participantIds as $participantId) {
                DiscussionThreadParticipant::query()->updateOrCreate(
                    [
                        'thread_id' => $thread->id,
                        'user_id' => (int) $participantId,
                    ],
                    [
                        'role' => (int) $participantId === (int) $sender->id ? 'owner' : 'member',
                        'last_read_at' => (int) $participantId === (int) $sender->id
                            ? now()
                            : DiscussionThreadParticipant::query()
                                ->where('thread_id', $thread->id)
                                ->where('user_id', (int) $participantId)
                                ->value('last_read_at'),
                    ]
                );
            }

            return $thread->fresh([
                'initiatedBy',
                'recipientUser',
                'participants.user',
                'messages.user',
                'messages.attachments',
            ]);
        });
    }

    public function companyChatThread(User $user): DiscussionThread
    {
        $thread = DiscussionThread::query()
            ->where('channel', 'app')
            ->where('kind', 'company_chat')
            ->first();

        if (! $thread) {
            $thread = DiscussionThread::query()->create([
                'owner_id' => $user->id,
                'initiated_by_id' => $user->id,
                'subject' => 'Company Chat',
                'channel' => 'app',
                'kind' => 'company_chat',
                'delivery_status' => 'sent',
                'status' => 'sent',
                'metadata_updated_at' => now(),
                'edited_by_id' => $user->id,
                'notes' => 'Shared company-wide CRM chat room.',
            ]);
        }

        DiscussionThreadParticipant::query()->updateOrCreate(
            [
                'thread_id' => $thread->id,
                'user_id' => $user->id,
            ],
            [
                'role' => 'member',
                'last_read_at' => now(),
            ]
        );

        return $thread;
    }

    public function markThreadRead(DiscussionThread $thread, User $user): void
    {
        DiscussionThreadParticipant::query()->updateOrCreate(
            [
                'thread_id' => $thread->id,
                'user_id' => $user->id,
            ],
            [
                'role' => (int) $thread->initiated_by_id === (int) $user->id ? 'owner' : 'member',
                'last_read_at' => now(),
                'archived_at' => null,
            ]
        );
    }

    public function storeAppMessage(
        DiscussionThread $thread,
        User $sender,
        string $body = '',
        array $files = [],
        array $audienceUserIds = [],
        array $mentionUserIds = []
    ): DiscussionMessage {
        if (! $thread->isAppThread()) {
            throw ValidationException::withMessages([
                'thread' => 'App messages can only be added to in-app threads.',
            ]);
        }

        if (trim($body) === '' && $files === []) {
            throw ValidationException::withMessages([
                'body' => 'Write a message or attach at least one file.',
            ]);
        }

        return DB::transaction(function () use ($audienceUserIds, $body, $files, $mentionUserIds, $sender, $thread): DiscussionMessage {
            $mentionedUsers = $this->resolveMentionUsers($thread, $sender, $body, $mentionUserIds);
            $message = $thread->messages()->create([
                'user_id' => $sender->id,
                'direction' => 'outbound',
                'channel' => 'app',
                'body' => trim($body),
                'delivery_status' => 'sent',
                'sent_at' => now(),
            ]);

            $this->storeUploadedAttachments($message, $sender, $files);
            $this->storeMessageMentions($message, $mentionedUsers);

            $thread->forceFill([
                'last_message_at' => $message->sent_at,
                'delivery_status' => 'sent',
                'status' => 'sent',
            ])->save();

            $participantIds = $thread->participants()->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if ($thread->isCompanyChat()) {
                $participantIds = array_values(array_unique(array_merge($participantIds, array_map('intval', $audienceUserIds))));
            }

            if ($thread->isCompanyChat() || $thread->isGroupChat()) {
                $participantIds = array_values(array_unique(array_merge(
                    $participantIds,
                    $mentionedUsers->pluck('id')->map(fn ($value) => (int) $value)->all()
                )));
            }

            foreach ($participantIds as $participantId) {
                $existingReadAt = DiscussionThreadParticipant::query()
                    ->where('thread_id', $thread->id)
                    ->where('user_id', $participantId)
                    ->value('last_read_at');

                DiscussionThreadParticipant::query()->updateOrCreate(
                    [
                        'thread_id' => $thread->id,
                        'user_id' => $participantId,
                    ],
                    [
                        'role' => $participantId === (int) $sender->id ? 'owner' : 'member',
                        'last_read_at' => $participantId === (int) $sender->id ? now() : $existingReadAt,
                        'archived_at' => null,
                    ]
                );
            }

            return $message->fresh(['user', 'attachments', 'mentions.user']);
        });
    }

    public function updateThreadMetadata(DiscussionThread $thread, User $editor, array $payload): DiscussionThread
    {
        $thread->forceFill([
            'subject' => $payload['subject'],
            'notes' => $payload['notes'] ?? null,
            'metadata_updated_at' => now(),
            'edited_by_id' => $editor->id,
        ])->save();

        return $thread->fresh(['participants.user', 'latestMessage']);
    }

    public function saveExternalDirectDraft(
        User $sender,
        string $channel,
        array $payload,
        ?DiscussionThread $thread = null
    ): DiscussionThread {
        $recipient = $this->resolveDirectRecipient($sender, $channel, $payload);

        return DB::transaction(function () use ($channel, $payload, $recipient, $sender, $thread): DiscussionThread {
            if ($thread && $thread->status !== 'draft') {
                throw ValidationException::withMessages([
                    'thread' => 'Only draft conversations can be edited.',
                ]);
            }

            $deliveryStatus = $this->resolveDeliveryStatus($channel, $payload['integration_id'] ?? null);

            $thread = $thread ?: new DiscussionThread();
            $thread->forceFill([
                'owner_id' => $thread->owner_id ?: $sender->id,
                'initiated_by_id' => $thread->initiated_by_id ?: $sender->id,
                'recipient_user_id' => $recipient['user_id'] ?? null,
                'integration_id' => $payload['integration_id'] ?? null,
                'subject' => $payload['subject'],
                'channel' => $channel,
                'kind' => 'external_direct',
                'recipient_email' => $recipient['email'] ?? null,
                'recipient_phone' => $recipient['phone'] ?? null,
                'delivery_status' => $deliveryStatus,
                'status' => 'draft',
                'notes' => $payload['notes'] ?? null,
                'source_type' => $payload['source_type'] ?? null,
                'source_id' => $payload['source_id'] ?? null,
                'target_type' => $recipient['recipient_type'] ?? null,
                'target_id' => $recipient['recipient_id'] ?? null,
                'metadata_updated_at' => now(),
                'edited_by_id' => $sender->id,
            ])->save();

            $message = $thread->messages()->latest('id')->first();

            if (! $message || $message->sent_at !== null) {
                $message = $thread->messages()->create([
                    'user_id' => $sender->id,
                    'direction' => 'outbound',
                    'channel' => $channel,
                    'body' => $payload['body'],
                    'delivery_status' => $deliveryStatus,
                ]);
            } else {
                $message->forceFill([
                    'body' => $payload['body'],
                    'delivery_status' => $deliveryStatus,
                ])->save();
            }

            $thread->forceFill([
                'last_message_at' => $message->created_at,
            ])->save();

            $this->storeUploadedAttachments($message, $sender, $payload['attachments'] ?? []);

            $artifact = $this->resolveCommercialArtifact(
                $thread->source_type,
                $thread->source_id,
                $sender
            );

            if ($artifact) {
                $this->storeArtifactAttachment($message, $artifact, $sender);
            }

            return $thread->fresh([
                'initiatedBy',
                'recipientUser',
                'integration',
                'messages.user',
                'messages.attachments',
            ]);
        });
    }

    public function sendExternalDraft(DiscussionThread $thread, User $sender): DiscussionThread
    {
        if ($thread->status !== 'draft') {
            return $thread->fresh(['messages.user', 'messages.attachments']);
        }

        return DB::transaction(function () use ($sender, $thread): DiscussionThread {
            $message = $thread->messages()->latest('id')->first();

            if (! $message) {
                throw ValidationException::withMessages([
                    'body' => 'Draft conversations require a message before they can be sent.',
                ]);
            }

            $artifact = $this->resolveCommercialArtifact(
                $thread->source_type,
                $thread->source_id,
                $sender
            );

            if ($artifact) {
                $this->storeArtifactAttachment($message, $artifact, $sender);
                $artifact->forceFill([
                    'shared_discussion_thread_id' => $thread->id,
                ])->save();
            }

            $message->forceFill([
                'sent_at' => now(),
            ])->save();

            $deliveryStatus = $this->dispatchExternalMessage(
                $thread->fresh('integration'),
                $message->fresh('attachments'),
                $artifact
            );

            $message->forceFill([
                'delivery_status' => $deliveryStatus,
            ])->save();

            $thread->forceFill([
                'delivery_status' => $deliveryStatus,
                'status' => $this->highLevelStatus($deliveryStatus),
                'last_message_at' => $message->sent_at ?: now(),
            ])->save();

            $this->syncThreadParticipants($thread, $sender);

            return $thread->fresh([
                'initiatedBy',
                'recipientUser',
                'participants.user',
                'integration',
                'messages.user',
                'messages.attachments',
            ]);
        });
    }

    public function replyExternalThread(
        DiscussionThread $thread,
        User $sender,
        string $body,
        array $files = []
    ): DiscussionMessage {
        return DB::transaction(function () use ($body, $files, $sender, $thread): DiscussionMessage {
            $deliveryStatus = $this->resolveDeliveryStatus($thread->channel, $thread->integration_id);
            $message = $thread->messages()->create([
                'user_id' => $sender->id,
                'direction' => 'outbound',
                'channel' => $thread->channel,
                'body' => trim($body),
                'delivery_status' => $deliveryStatus,
                'sent_at' => now(),
            ]);

            $this->storeUploadedAttachments($message, $sender, $files);

            $artifact = $this->resolveCommercialArtifact(
                $thread->source_type,
                $thread->source_id,
                $sender
            );

            if ($artifact) {
                $this->storeArtifactAttachment($message, $artifact, $sender);
                $artifact->forceFill([
                    'shared_discussion_thread_id' => $thread->id,
                ])->save();
            }

            $dispatchStatus = $this->dispatchExternalMessage(
                $thread->fresh('integration'),
                $message->fresh('attachments'),
                $artifact
            );

            $message->forceFill([
                'delivery_status' => $dispatchStatus,
            ])->save();

            $thread->forceFill([
                'delivery_status' => $dispatchStatus,
                'status' => $this->highLevelStatus($dispatchStatus),
                'last_message_at' => $message->sent_at,
            ])->save();

            $this->syncThreadParticipants($thread, $sender);

            return $message->fresh(['user', 'attachments']);
        });
    }

    public function saveCampaign(
        User $sender,
        string $channel,
        array $payload,
        ?DiscussionCampaign $campaign = null
    ): DiscussionCampaign {
        if ($campaign && $campaign->status !== 'draft') {
            throw ValidationException::withMessages([
                'campaign' => 'Only draft campaigns can be edited.',
            ]);
        }

        $snapshot = $this->buildAudienceSnapshot($sender, $channel, $payload);

        $campaign = $campaign ?: new DiscussionCampaign();
        $campaign->forceFill([
            'owner_id' => $campaign->owner_id ?: $sender->id,
            'initiated_by_id' => $campaign->initiated_by_id ?: $sender->id,
            'integration_id' => $payload['integration_id'] ?? null,
            'channel' => $channel,
            'status' => 'draft',
            'subject' => $payload['subject'],
            'body' => $payload['body'],
            'notes' => $payload['notes'] ?? null,
            'audience_snapshot' => $snapshot,
            'source_type' => $payload['source_type'] ?? null,
            'source_id' => $payload['source_id'] ?? null,
        ])->save();

        return $campaign->fresh(['initiatedBy', 'integration', 'recipients']);
    }

    public function sendCampaign(DiscussionCampaign $campaign, User $sender, array $files = []): DiscussionCampaign
    {
        $snapshot = $campaign->audience_snapshot ?? [];
        $resolvedRecipients = collect($snapshot['resolved'] ?? []);

        if ($resolvedRecipients->isEmpty()) {
            throw ValidationException::withMessages([
                'recipient_user_ids' => 'Bulk campaigns require at least one resolved recipient.',
            ]);
        }

        return DB::transaction(function () use ($campaign, $files, $resolvedRecipients, $sender): DiscussionCampaign {
            $campaign->recipients()->delete();
            $artifact = $this->resolveCommercialArtifact(
                $campaign->source_type,
                $campaign->source_id,
                $sender
            );

            if ($campaign->channel === 'app') {
                $thread = $this->createAppGroupThread($campaign, $sender, $resolvedRecipients);
                $campaign->forceFill([
                    'thread_id' => $thread->id,
                ])->save();

                $message = $this->storeAppMessage(
                    $thread,
                    $sender,
                    $campaign->body,
                    $files
                );

                if ($artifact) {
                    $this->storeArtifactAttachment($message, $artifact, $sender);
                    $artifact->forceFill([
                        'shared_discussion_thread_id' => $thread->id,
                    ])->save();
                }

                foreach ($resolvedRecipients as $recipient) {
                    $campaign->recipients()->create([
                        'thread_id' => $thread->id,
                        'message_id' => $message->id,
                        'recipient_user_id' => $recipient['user_id'] ?? null,
                        'recipient_type' => $recipient['recipient_type'] ?? 'user',
                        'recipient_id' => $recipient['recipient_id'] ?? null,
                        'recipient_label' => $recipient['label'] ?? null,
                        'recipient_address' => null,
                        'delivery_status' => 'sent',
                    ]);
                }

                $campaign->forceFill([
                    'status' => 'sent',
                    'last_sent_at' => now(),
                ])->save();

                return $campaign->fresh(['thread', 'recipients']);
            }

            $campaignStatuses = collect();

            foreach ($resolvedRecipients as $recipient) {
                $thread = DiscussionThread::query()->create([
                    'owner_id' => $sender->id,
                    'initiated_by_id' => $sender->id,
                    'recipient_user_id' => $recipient['user_id'] ?? null,
                    'integration_id' => $campaign->integration_id,
                    'subject' => $campaign->subject,
                    'channel' => $campaign->channel,
                    'kind' => 'external_direct',
                    'recipient_email' => $recipient['email'] ?? null,
                    'recipient_phone' => $recipient['phone'] ?? null,
                    'delivery_status' => $this->resolveDeliveryStatus($campaign->channel, $campaign->integration_id),
                    'status' => 'sent',
                    'last_message_at' => now(),
                    'notes' => $campaign->notes,
                    'source_type' => $campaign->source_type,
                    'source_id' => $campaign->source_id,
                    'metadata_updated_at' => now(),
                    'edited_by_id' => $sender->id,
                ]);

                $message = $thread->messages()->create([
                    'user_id' => $sender->id,
                    'direction' => 'outbound',
                    'channel' => $campaign->channel,
                    'body' => $campaign->body,
                    'delivery_status' => $this->resolveDeliveryStatus($campaign->channel, $campaign->integration_id),
                    'sent_at' => now(),
                ]);

                $this->storeUploadedAttachments($message, $sender, $files);

                if ($artifact) {
                    $this->storeArtifactAttachment($message, $artifact, $sender);
                }

                $deliveryStatus = $this->dispatchExternalMessage(
                    $thread->fresh('integration'),
                    $message->fresh('attachments'),
                    $artifact
                );

                $message->forceFill([
                    'delivery_status' => $deliveryStatus,
                ])->save();

                $thread->forceFill([
                    'delivery_status' => $deliveryStatus,
                    'status' => $this->highLevelStatus($deliveryStatus),
                    'last_message_at' => $message->sent_at,
                ])->save();

                $this->syncThreadParticipants($thread, $sender);

                $campaign->recipients()->create([
                    'thread_id' => $thread->id,
                    'message_id' => $message->id,
                    'recipient_user_id' => $recipient['user_id'] ?? null,
                    'recipient_type' => $recipient['recipient_type'] ?? null,
                    'recipient_id' => $recipient['recipient_id'] ?? null,
                    'recipient_label' => $recipient['label'] ?? null,
                    'recipient_address' => $recipient['address'] ?? null,
                    'delivery_status' => $deliveryStatus,
                ]);

                $campaignStatuses->push($deliveryStatus);
            }

            if ($artifact) {
                $artifact->forceFill([
                    'shared_discussion_thread_id' => $campaign->recipients()->latest('id')->value('thread_id'),
                ])->save();
            }

            $campaign->forceFill([
                'status' => $campaignStatuses->every(fn ($status) => $status === 'failed') ? 'failed' : 'sent',
                'last_sent_at' => now(),
            ])->save();

            return $campaign->fresh(['recipients.thread', 'recipients.message']);
        });
    }

    public function latestAppFiles(User $user, int $limit = 8): Collection
    {
        return DiscussionMessageAttachment::query()
            ->with(['message.thread'])
            ->whereHas('message.thread', function (Builder $query) use ($user): void {
                $query->where('channel', 'app');

                if ($user->canManageOperationalRecords()) {
                    return;
                }

                $query->where(function (Builder $threadQuery) use ($user): void {
                    $threadQuery->where('owner_id', $user->id)
                        ->orWhere('initiated_by_id', $user->id)
                        ->orWhere('recipient_user_id', $user->id)
                        ->orWhereHas('participants', function (Builder $participantQuery) use ($user): void {
                            $participantQuery->where('user_id', $user->id);
                        });
                });
            })
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function unreadPayload(User $user): array
    {
        $unreadThreads = $this->unreadThreadsQueryFor($user);
        $count = (clone $unreadThreads)->count();
        $channelCounts = (clone $unreadThreads)
            ->selectRaw('channel, COUNT(*) as aggregate')
            ->groupBy('channel')
            ->pluck('aggregate', 'channel');

        $threads = $unreadThreads
            ->with([
                'initiatedBy',
                'recipientUser',
                'participants.user',
                'latestMessage.user',
                'latestMessage.attachments',
                'latestMessage.mentions.user',
            ])
            ->orderByDesc('last_message_at')
            ->limit(12)
            ->get();

        return [
            'count' => $count,
            'channel_counts' => [
                'app' => (int) ($channelCounts['app'] ?? 0),
                'email' => (int) ($channelCounts['email'] ?? 0),
                'whatsapp' => (int) ($channelCounts['whatsapp'] ?? 0),
            ],
            'threads' => $threads->map(fn (DiscussionThread $thread): array => $this->mapUnreadThread($thread, $user))->all(),
        ];
    }

    public function commercialSourceContext(User $user, string $sourceType, int $sourceId): array
    {
        if ($sourceType === 'quote') {
            $quote = CrmQuote::query()->with(['contact', 'customer', 'lead'])->findOrFail($sourceId);
            abort_unless($user->canAccessCommercialDocumentRecord($quote->owner_id), 403);

            return [
                'type' => 'quote',
                'id' => $quote->id,
                'label' => $quote->quote_number,
                'title' => 'Quote ' . $quote->quote_number,
                'subject' => 'Share ' . $quote->quote_number,
                'body' => 'Please review the latest commercial quote.',
                'recipient_email' => $quote->contact?->email ?: $quote->customer?->email ?: $quote->lead?->email,
                'recipient_phone' => $quote->contact?->phone ?: $quote->customer?->phone ?: $quote->lead?->phone,
                'recipient_label' => $quote->contact?->name ?: $quote->customer?->company_name ?: $quote->lead?->company_name,
            ];
        }

        if ($sourceType === 'invoice') {
            $invoice = CrmInvoice::query()->with(['contact', 'customer', 'lead'])->findOrFail($sourceId);
            abort_unless($user->canAccessCommercialDocumentRecord($invoice->owner_id), 403);

            return [
                'type' => 'invoice',
                'id' => $invoice->id,
                'label' => $invoice->invoice_number,
                'title' => 'Invoice ' . $invoice->invoice_number,
                'subject' => 'Share ' . $invoice->invoice_number,
                'body' => 'Please review the latest commercial invoice.',
                'recipient_email' => $invoice->contact?->email ?: $invoice->customer?->email ?: $invoice->lead?->email,
                'recipient_phone' => $invoice->contact?->phone ?: $invoice->customer?->phone ?: $invoice->lead?->phone,
                'recipient_label' => $invoice->contact?->name ?: $invoice->customer?->company_name ?: $invoice->lead?->company_name,
            ];
        }

        abort(404);
    }

    public function attachCommercialSourceToMessage(
        DiscussionMessage $message,
        User $user,
        ?string $sourceType,
        ?int $sourceId
    ): ?CrmCommercialDocumentArtifact {
        $artifact = $this->resolveCommercialArtifact($sourceType, $sourceId, $user);

        if (! $artifact) {
            return null;
        }

        $this->storeArtifactAttachment($message, $artifact, $user);

        $artifact->forceFill([
            'shared_discussion_thread_id' => $message->thread_id,
        ])->save();

        return $artifact;
    }

    private function buildAudienceSnapshot(User $sender, string $channel, array $payload): array
    {
        $resolved = collect();
        $skipped = collect();
        $seenKeys = [];
        $selectedDepartments = collect();

        $appendRecipient = function (array $recipient) use (&$resolved, &$seenKeys): void {
            $key = implode(':', [
                $recipient['recipient_type'] ?? 'manual',
                $recipient['recipient_id'] ?? 0,
                $recipient['address'] ?? '',
            ]);

            if (in_array($key, $seenKeys, true)) {
                return;
            }

            $seenKeys[] = $key;
            $resolved->push($recipient);
        };

        $userIds = collect($payload['recipient_user_ids'] ?? [])->map(fn ($value) => (int) $value)->filter();
        $departmentIds = collect($payload['department_ids'] ?? [])->map(fn ($value) => (int) $value)->filter();
        $leadIds = collect($payload['lead_ids'] ?? [])->map(fn ($value) => (int) $value)->filter();
        $customerIds = collect($payload['customer_ids'] ?? [])->map(fn ($value) => (int) $value)->filter();
        $contactIds = collect($payload['contact_ids'] ?? [])->map(fn ($value) => (int) $value)->filter();

        foreach ($userIds as $userId) {
            $target = User::query()
                ->where('active', true)
                ->whereIn('role', array_keys(config('heritage_crm.roles', [])))
                ->find($userId);

            if (! $target) {
                continue;
            }

            if ($channel === 'app' && (int) $target->id === (int) $sender->id) {
                continue;
            }

            $recipient = [
                'recipient_type' => 'user',
                'recipient_id' => $target->id,
                'user_id' => $target->id,
                'label' => $target->name,
                'email' => $target->email,
                'phone' => $target->phone,
                'address' => $channel === 'email' ? $target->email : $target->phone,
            ];

            if (blank($recipient['address']) && $channel !== 'app') {
                $skipped->push($recipient + ['error' => 'No channel address available.']);
                continue;
            }

            $appendRecipient($recipient);
        }

        if ($channel === 'app' && $departmentIds->isNotEmpty()) {
            $departments = CrmUserDepartment::query()
                ->where('is_active', true)
                ->whereIn('id', $departmentIds)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            $departmentMembers = User::query()
                ->where('active', true)
                ->whereIn('role', array_keys(config('heritage_crm.roles', [])))
                ->whereIn('department_id', $departments->pluck('id'))
                ->get();

            $selectedDepartments = $departments->map(function (CrmUserDepartment $department) use ($departmentMembers, $sender): array {
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'member_count' => $departmentMembers
                        ->where('department_id', $department->id)
                        ->reject(fn (User $member) => (int) $member->id === (int) $sender->id)
                        ->count(),
                ];
            });

            foreach ($departmentMembers as $member) {
                if ((int) $member->id === (int) $sender->id) {
                    continue;
                }

                $appendRecipient([
                    'recipient_type' => 'user',
                    'recipient_id' => $member->id,
                    'user_id' => $member->id,
                    'label' => $member->name,
                    'email' => $member->email,
                    'phone' => $member->phone,
                    'address' => null,
                ]);
            }
        }

        foreach ($leadIds as $leadId) {
            $lead = Lead::query()->find($leadId);

            if (! $lead) {
                continue;
            }

            $this->authorizeOwnedModel($sender, $lead);
            $primaryContact = $this->primaryContactForLead($lead);
            $recipient = [
                'recipient_type' => 'lead',
                'recipient_id' => $lead->id,
                'user_id' => null,
                'label' => $lead->company_name,
                'email' => $primaryContact?->email ?: $lead->email,
                'phone' => $primaryContact?->phone ?: $lead->phone,
                'address' => $channel === 'email'
                    ? ($primaryContact?->email ?: $lead->email)
                    : ($primaryContact?->phone ?: $lead->phone),
            ];

            if (blank($recipient['address'])) {
                $skipped->push($recipient + ['error' => 'No channel address available.']);
                continue;
            }

            $appendRecipient($recipient);
        }

        foreach ($customerIds as $customerId) {
            $customer = Customer::query()->find($customerId);

            if (! $customer) {
                continue;
            }

            $this->authorizeOwnedModel($sender, $customer);
            $primaryContact = $this->primaryContactForCustomer($customer);
            $recipient = [
                'recipient_type' => 'customer',
                'recipient_id' => $customer->id,
                'user_id' => null,
                'label' => $customer->company_name,
                'email' => $primaryContact?->email ?: $customer->email,
                'phone' => $primaryContact?->phone ?: $customer->phone,
                'address' => $channel === 'email'
                    ? ($primaryContact?->email ?: $customer->email)
                    : ($primaryContact?->phone ?: $customer->phone),
            ];

            if (blank($recipient['address'])) {
                $skipped->push($recipient + ['error' => 'No channel address available.']);
                continue;
            }

            $appendRecipient($recipient);
        }

        foreach ($contactIds as $contactId) {
            $contact = Contact::query()->find($contactId);

            if (! $contact) {
                continue;
            }

            $this->authorizeOwnedModel($sender, $contact);
            $recipient = [
                'recipient_type' => 'contact',
                'recipient_id' => $contact->id,
                'user_id' => null,
                'label' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'address' => $channel === 'email' ? $contact->email : $contact->phone,
            ];

            if (blank($recipient['address'])) {
                $skipped->push($recipient + ['error' => 'No channel address available.']);
                continue;
            }

            $appendRecipient($recipient);
        }

        return [
            'requested' => [
                'recipient_user_ids' => $userIds->values()->all(),
                'department_ids' => $departmentIds->values()->all(),
                'lead_ids' => $leadIds->values()->all(),
                'customer_ids' => $customerIds->values()->all(),
                'contact_ids' => $contactIds->values()->all(),
            ],
            'departments' => $selectedDepartments->values()->all(),
            'resolved' => $resolved->values()->all(),
            'skipped' => $skipped->values()->all(),
        ];
    }

    private function createAppGroupThread(
        DiscussionCampaign $campaign,
        User $sender,
        Collection $resolvedRecipients
    ): DiscussionThread {
        $participantIds = $resolvedRecipients
            ->pluck('user_id')
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->reject(fn (int $userId) => $userId === (int) $sender->id)
            ->unique()
            ->values();

        if ($participantIds->isEmpty()) {
            throw ValidationException::withMessages([
                'recipient_user_ids' => 'App group chats require at least one internal recipient.',
            ]);
        }

        return DB::transaction(function () use ($campaign, $participantIds, $sender): DiscussionThread {
            $thread = DiscussionThread::query()->create([
                'owner_id' => $sender->id,
                'initiated_by_id' => $sender->id,
                'subject' => $campaign->subject,
                'channel' => 'app',
                'kind' => 'group',
                'delivery_status' => 'sent',
                'status' => 'sent',
                'notes' => $campaign->notes,
                'source_type' => $campaign->source_type,
                'source_id' => $campaign->source_id,
                'target_type' => 'group',
                'metadata_updated_at' => now(),
                'edited_by_id' => $sender->id,
            ]);

            $allParticipantIds = $participantIds
                ->push((int) $sender->id)
                ->unique()
                ->values();

            foreach ($allParticipantIds as $participantId) {
                DiscussionThreadParticipant::query()->updateOrCreate(
                    [
                        'thread_id' => $thread->id,
                        'user_id' => (int) $participantId,
                    ],
                    [
                        'role' => (int) $participantId === (int) $sender->id ? 'owner' : 'member',
                        'last_read_at' => (int) $participantId === (int) $sender->id ? now() : null,
                        'archived_at' => null,
                    ]
                );
            }

            return $thread->fresh([
                'initiatedBy',
                'recipientUser',
                'participants.user',
                'messages.user',
                'messages.attachments',
            ]);
        });
    }

    private function resolveDirectRecipient(User $sender, string $channel, array $payload): array
    {
        $recipientType = (string) ($payload['recipient_type'] ?? 'manual');

        if ($recipientType === 'user') {
            $target = User::query()->findOrFail($payload['recipient_user_id']);

            return [
                'recipient_type' => 'user',
                'recipient_id' => $target->id,
                'user_id' => $target->id,
                'label' => $target->name,
                'email' => $target->email,
                'phone' => $target->phone,
            ];
        }

        if ($recipientType === 'lead') {
            $lead = Lead::query()->findOrFail($payload['lead_id']);
            $this->authorizeOwnedModel($sender, $lead);
            $primaryContact = $this->primaryContactForLead($lead);

            return [
                'recipient_type' => 'lead',
                'recipient_id' => $lead->id,
                'user_id' => null,
                'label' => $lead->company_name,
                'email' => $primaryContact?->email ?: $lead->email,
                'phone' => $primaryContact?->phone ?: $lead->phone,
            ];
        }

        if ($recipientType === 'customer') {
            $customer = Customer::query()->findOrFail($payload['customer_id']);
            $this->authorizeOwnedModel($sender, $customer);
            $primaryContact = $this->primaryContactForCustomer($customer);

            return [
                'recipient_type' => 'customer',
                'recipient_id' => $customer->id,
                'user_id' => null,
                'label' => $customer->company_name,
                'email' => $primaryContact?->email ?: $customer->email,
                'phone' => $primaryContact?->phone ?: $customer->phone,
            ];
        }

        if ($recipientType === 'contact') {
            $contact = Contact::query()->findOrFail($payload['contact_id']);
            $this->authorizeOwnedModel($sender, $contact);

            return [
                'recipient_type' => 'contact',
                'recipient_id' => $contact->id,
                'user_id' => null,
                'label' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
            ];
        }

        $recipient = [
            'recipient_type' => 'manual',
            'recipient_id' => null,
            'user_id' => null,
            'label' => ($payload['recipient_label'] ?? null) ?: (($payload['recipient_email'] ?? null) ?: ($payload['recipient_phone'] ?? null)),
            'email' => $payload['recipient_email'] ?? null,
            'phone' => $payload['recipient_phone'] ?? null,
        ];

        $address = $channel === 'email' ? $recipient['email'] : $recipient['phone'];

        if (blank($address)) {
            throw ValidationException::withMessages([
                $channel === 'email' ? 'recipient_email' : 'recipient_phone' => 'This channel requires a valid recipient address.',
            ]);
        }

        return $recipient;
    }

    private function unreadThreadsQueryFor(User $user): Builder
    {
        return DiscussionThread::query()
            ->whereNotNull('last_message_at')
            ->where('status', '!=', 'draft')
            ->where(function (Builder $threadQuery) use ($user): void {
                $threadQuery->whereHas('participants', function (Builder $participantQuery) use ($user): void {
                    $participantQuery->where('user_id', $user->id)
                        ->whereNull('archived_at')
                        ->where(function (Builder $unreadQuery): void {
                            $unreadQuery->whereNull('last_read_at')
                                ->orWhereColumn('crm_discussion_threads.last_message_at', '>', 'crm_discussion_thread_participants.last_read_at');
                        });
                })->orWhere(function (Builder $legacyQuery) use ($user): void {
                    $legacyQuery->where('recipient_user_id', $user->id)
                        ->where('channel', '!=', 'app')
                        ->whereDoesntHave('participants', function (Builder $participantQuery) use ($user): void {
                            $participantQuery->where('user_id', $user->id);
                        });
                });
            });
    }

    private function mapUnreadThread(DiscussionThread $thread, User $user): array
    {
        $message = $thread->latestMessage;
        $activityAt = $message?->sent_at ?: $thread->last_message_at;
        $mentionedUser = $message?->mentionsUser($user) ?? false;

        return [
            'id' => $thread->id,
            'thread_id' => $thread->id,
            'message_id' => $message?->id,
            'label' => $this->unreadThreadLabel($thread, $user),
            'channel' => $thread->channel,
            'channel_label' => config('heritage_crm.discussion_channels.' . $thread->channel, ucfirst($thread->channel)),
            'icon' => $this->unreadThreadIcon($thread),
            'sender_label' => $message?->user?->name ?: ($thread->initiatedBy?->name ?: 'CRM user'),
            'preview' => $this->unreadThreadPreview($thread),
            'mentioned' => $mentionedUser,
            'activity_reason' => $mentionedUser ? 'mentioned_you' : 'unread_message',
            'activity_reason_label' => $mentionedUser ? 'Mentioned you' : null,
            'activity_at' => $activityAt?->toIso8601String(),
            'activity_label' => $activityAt?->diffForHumans(),
            'url' => $this->threadRoute($thread),
        ];
    }

    private function unreadThreadLabel(DiscussionThread $thread, User $user): string
    {
        if ($thread->isCompanyChat()) {
            return 'Company Chat';
        }

        if ($thread->isGroupChat()) {
            return $thread->subject ?: 'Group chat';
        }

        if ($thread->channel === 'app') {
            return $thread->counterpartFor($user)?->name ?: ($thread->subject ?: 'Direct message');
        }

        return $thread->subject ?: ($thread->initiatedBy?->name ?: config('heritage_crm.discussion_channels.' . $thread->channel, 'Discussion'));
    }

    private function unreadThreadPreview(DiscussionThread $thread): string
    {
        $message = $thread->latestMessage;

        if (! $message) {
            return 'Open this discussion to review the latest activity.';
        }

        $body = trim(preg_replace('/\s+/', ' ', (string) $message->body));

        if ($body !== '') {
            return Str::limit($body, 110);
        }

        return $message->attachments->isNotEmpty()
            ? 'Attachment shared in this discussion.'
            : 'Open this discussion to review the latest activity.';
    }

    private function unreadThreadIcon(DiscussionThread $thread): string
    {
        return match ($thread->channel) {
            'email' => 'bx bx-envelope',
            'whatsapp' => 'bx bxl-whatsapp',
            default => 'bx bx-chat',
        };
    }

    private function syncThreadParticipants(DiscussionThread $thread, User $actor): void
    {
        $participantIds = collect([
            $thread->owner_id,
            $thread->initiated_by_id,
            $thread->recipient_user_id,
            $actor->id,
        ])
            ->merge(
                DiscussionThreadParticipant::query()
                    ->where('thread_id', $thread->id)
                    ->pluck('user_id')
            )
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        foreach ($participantIds as $participantId) {
            $existingReadAt = DiscussionThreadParticipant::query()
                ->where('thread_id', $thread->id)
                ->where('user_id', $participantId)
                ->value('last_read_at');

            DiscussionThreadParticipant::query()->updateOrCreate(
                [
                    'thread_id' => $thread->id,
                    'user_id' => $participantId,
                ],
                [
                    'role' => in_array($participantId, [(int) $thread->owner_id, (int) $thread->initiated_by_id], true) ? 'owner' : 'member',
                    'last_read_at' => $participantId === (int) $actor->id ? now() : $existingReadAt,
                    'archived_at' => null,
                ]
            );
        }
    }

    private function resolveMentionUsers(DiscussionThread $thread, User $sender, string $body, array $mentionUserIds): Collection
    {
        $mentionIds = collect($mentionUserIds)
            ->map(fn ($value) => (int) $value)
            ->filter()
            ->unique()
            ->values();

        if ($mentionIds->isEmpty()) {
            return collect();
        }

        if (! ($thread->isCompanyChat() || $thread->isGroupChat())) {
            throw ValidationException::withMessages([
                'mention_user_ids' => 'Mentions are only available in company chat and group chats.',
            ]);
        }

        $users = User::query()
            ->where('active', true)
            ->whereIn('role', array_keys(config('heritage_crm.roles', [])))
            ->whereIn('id', $mentionIds)
            ->get();

        if ($users->count() !== $mentionIds->count()) {
            throw ValidationException::withMessages([
                'mention_user_ids' => 'One or more mentioned users are no longer available in CRM.',
            ]);
        }

        $trimmedBody = trim($body);

        return $users
            ->reject(fn (User $user) => (int) $user->id === (int) $sender->id)
            ->filter(function (User $user) use ($trimmedBody): bool {
                return $trimmedBody !== '' && Str::contains($trimmedBody, $this->mentionTokensForUser($user));
            })
            ->values();
    }

    private function storeMessageMentions(DiscussionMessage $message, Collection $mentionedUsers): void
    {
        if ($mentionedUsers->isEmpty()) {
            return;
        }

        DiscussionMessageMention::query()
            ->where('message_id', $message->id)
            ->delete();

        $now = now();

        DiscussionMessageMention::query()->insert(
            $mentionedUsers
                ->map(fn (User $user): array => [
                    'message_id' => $message->id,
                    'user_id' => $user->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all()
        );
    }

    private function mentionLabelForUser(User $user): string
    {
        return trim((string) ($user->name ?: ($user->email ?: ('User #' . $user->id))));
    }

    private function mentionTokensForUser(User $user): array
    {
        $label = $this->mentionLabelForUser($user);

        return [
            '@' . $label,
            '@[' . $label . ']',
        ];
    }

    private function dispatchExternalMessage(
        DiscussionThread $thread,
        DiscussionMessage $message,
        ?CrmCommercialDocumentArtifact $artifact = null
    ): string {
        if ($thread->channel === 'email' && filled($thread->recipient_email)) {
            try {
                Mail::html(DiscussionMessage::emailBodyAsHtml((string) $message->body), function ($mail) use ($artifact, $message, $thread): void {
                    $mail->to($thread->recipient_email)
                        ->subject($thread->subject);

                    $message->loadMissing('attachments');

                    foreach ($message->attachments as $attachment) {
                        $mail->attach(
                            Storage::disk($attachment->disk)->path($attachment->path),
                            [
                                'as' => $attachment->original_name,
                                'mime' => $attachment->mime_type ?: 'application/octet-stream',
                            ]
                        );
                    }

                    if ($artifact && $message->attachments->every(fn (DiscussionMessageAttachment $attachment) => $attachment->path !== $artifact->path)) {
                        $mail->attach(
                            Storage::disk($artifact->disk ?: 'documents')->path($artifact->path),
                            [
                                'as' => $artifact->original_name,
                                'mime' => $artifact->mime_type ?: 'application/pdf',
                            ]
                        );
                    }
                });

                return 'sent';
            } catch (Throwable $exception) {
                Log::error('CRM email discussion dispatch failed.', [
                    'thread_id' => $thread->id,
                    'message_id' => $message->id,
                    'recipient_email' => $thread->recipient_email,
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ]);

                return 'failed';
            }
        }

        return $thread->integration_id ? 'queued' : 'pending_integration';
    }

    private function resolveDeliveryStatus(string $channel, ?int $integrationId): string
    {
        if ($channel === 'email') {
            return 'queued';
        }

        if ($channel === 'whatsapp') {
            return $integrationId ? 'queued' : 'pending_integration';
        }

        return 'sent';
    }

    private function highLevelStatus(string $deliveryStatus): string
    {
        return match ($deliveryStatus) {
            'failed' => 'failed',
            'queued', 'pending_integration' => 'queued',
            default => 'sent',
        };
    }

    private function storeUploadedAttachments(DiscussionMessage $message, User $uploadedBy, array $files): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('crm/discussion-attachments/' . $message->id, 'documents');

            $message->attachments()->create([
                'uploaded_by_id' => $uploadedBy->id,
                'disk' => 'documents',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'extension' => strtolower((string) $file->getClientOriginalExtension()),
                'size' => (int) $file->getSize(),
            ]);
        }
    }

    private function storeArtifactAttachment(
        DiscussionMessage $message,
        CrmCommercialDocumentArtifact $artifact,
        User $uploadedBy
    ): DiscussionMessageAttachment {
        return DiscussionMessageAttachment::query()->firstOrCreate(
            [
                'message_id' => $message->id,
                'path' => $artifact->path,
            ],
            [
                'uploaded_by_id' => $uploadedBy->id,
                'disk' => $artifact->disk ?: 'documents',
                'original_name' => $artifact->original_name,
                'mime_type' => $artifact->mime_type,
                'extension' => $artifact->extension,
                'size' => (int) $artifact->size,
            ]
        );
    }

    private function resolveCommercialArtifact(?string $sourceType, ?int $sourceId, User $user): ?CrmCommercialDocumentArtifact
    {
        if (blank($sourceType) || blank($sourceId)) {
            return null;
        }

        if ($sourceType === 'quote') {
            $quote = CrmQuote::query()->findOrFail($sourceId);
            abort_unless($user->canAccessCommercialDocumentRecord($quote->owner_id), 403);

            return $this->documentPdfService->ensureQuoteArtifact($quote, $user);
        }

        if ($sourceType === 'invoice') {
            $invoice = CrmInvoice::query()->findOrFail($sourceId);
            abort_unless($user->canAccessCommercialDocumentRecord($invoice->owner_id), 403);

            return $this->documentPdfService->ensureInvoiceArtifact($invoice, $user);
        }

        return null;
    }

    private function authorizeOwnedModel(User $user, Model $record): void
    {
        $ownerId = (int) ($record->owner_id ?? 0);

        abort_unless(
            $user->canAccessOwnedRecord($ownerId) || $user->canAccessCommercialContextRecord($ownerId),
            403
        );
    }

    private function primaryContactForLead(Lead $lead): ?Contact
    {
        return Contact::query()
            ->where('lead_id', $lead->id)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->first();
    }

    private function primaryContactForCustomer(Customer $customer): ?Contact
    {
        return Contact::query()
            ->where('customer_id', $customer->id)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->first();
    }

    private function defaultDirectSubject(User $sender, User $recipient): string
    {
        return $sender->name . ' and ' . $recipient->name;
    }
}
