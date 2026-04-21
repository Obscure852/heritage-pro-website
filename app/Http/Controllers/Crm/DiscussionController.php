<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\DiscussionMessageStoreRequest;
use App\Http\Requests\Crm\DiscussionThreadStoreRequest;
use App\Models\DiscussionThread;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DiscussionController extends CrmController
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'channel' => (string) $request->query('channel', ''),
            'delivery_status' => (string) $request->query('delivery_status', ''),
            'user_id' => (string) $request->query('user_id', ''),
        ];

        $threads = $this->discussionQuery()
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($threadQuery) use ($filters) {
                    $threadQuery->where('subject', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('recipient_email', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('recipient_phone', 'like', '%' . $filters['q'] . '%')
                        ->orWhereHas('messages', function ($messageQuery) use ($filters) {
                            $messageQuery->where('body', 'like', '%' . $filters['q'] . '%');
                        });
                });
            })
            ->when($filters['channel'] !== '', function ($query) use ($filters) {
                $query->where('channel', $filters['channel']);
            })
            ->when($filters['delivery_status'] !== '', function ($query) use ($filters) {
                $query->where('delivery_status', $filters['delivery_status']);
            })
            ->when($filters['user_id'] !== '', function ($query) use ($filters) {
                $query->where(function ($threadQuery) use ($filters) {
                    $threadQuery->where('initiated_by_id', (int) $filters['user_id'])
                        ->orWhere('recipient_user_id', (int) $filters['user_id'])
                        ->orWhere('owner_id', (int) $filters['user_id']);
                });
            })
            ->with(['initiatedBy', 'recipientUser', 'integration', 'messages.user'])
            ->orderByDesc('last_message_at')
            ->paginate(12)
            ->withQueryString();

        return view('crm.discussions.index', [
            'threads' => $threads,
            'discussionChannels' => config('heritage_crm.discussion_channels'),
            'deliveryStatuses' => config('heritage_crm.discussion_delivery_statuses'),
            'users' => $this->owners(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('crm.discussions.create', [
            'discussionChannels' => config('heritage_crm.discussion_channels'),
            'users' => $this->owners(),
            'integrations' => $this->availableIntegrations(),
        ]);
    }

    public function store(DiscussionThreadStoreRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $deliveryStatus = $this->resolveDeliveryStatus($payload['channel'], $payload['integration_id'] ?? null);

        $thread = DiscussionThread::query()->create([
            'owner_id' => $this->crmUser()->id,
            'initiated_by_id' => $this->crmUser()->id,
            'recipient_user_id' => $payload['recipient_user_id'] ?? null,
            'integration_id' => $payload['integration_id'] ?? null,
            'subject' => $payload['subject'],
            'channel' => $payload['channel'],
            'recipient_email' => $payload['recipient_email'] ?? null,
            'recipient_phone' => $payload['recipient_phone'] ?? null,
            'delivery_status' => $deliveryStatus,
            'last_message_at' => now(),
            'notes' => $payload['notes'] ?? null,
        ]);

        $message = $thread->messages()->create([
            'user_id' => $this->crmUser()->id,
            'direction' => 'outbound',
            'channel' => $thread->channel,
            'body' => $payload['body'],
            'delivery_status' => $deliveryStatus,
            'sent_at' => now(),
        ]);

        $thread->forceFill([
            'delivery_status' => $this->dispatchExternalMessage($thread, $message),
            'last_message_at' => $message->sent_at,
        ])->save();

        return redirect()
            ->route('crm.discussions.show', $thread)
            ->with('crm_success', 'Discussion created successfully.');
    }

    public function show(DiscussionThread $discussionThread): View
    {
        $this->authorizeDiscussionAccess($discussionThread);

        $discussionThread->load([
            'initiatedBy',
            'recipientUser',
            'integration',
            'messages.user',
        ]);

        return view('crm.discussions.show', [
            'discussionThread' => $discussionThread,
            'deliveryStatuses' => config('heritage_crm.discussion_delivery_statuses'),
        ]);
    }

    public function destroy(DiscussionThread $discussionThread): RedirectResponse
    {
        $this->authorizeDiscussionAccess($discussionThread);

        $discussionThread->forceDelete();

        return redirect()
            ->route('crm.discussions.index')
            ->with('crm_success', 'Discussion deleted permanently.');
    }

    public function storeMessage(DiscussionMessageStoreRequest $request, DiscussionThread $discussionThread): RedirectResponse
    {
        $this->authorizeDiscussionAccess($discussionThread);

        $message = $discussionThread->messages()->create([
            'user_id' => $this->crmUser()->id,
            'direction' => 'outbound',
            'channel' => $discussionThread->channel,
            'body' => $request->validated('body'),
            'delivery_status' => $discussionThread->delivery_status,
            'sent_at' => now(),
        ]);

        $discussionThread->forceFill([
            'delivery_status' => $this->dispatchExternalMessage($discussionThread, $message),
            'last_message_at' => $message->sent_at,
        ])->save();

        return redirect()
            ->route('crm.discussions.show', $discussionThread)
            ->with('crm_success', 'Message sent successfully.');
    }

    private function discussionQuery()
    {
        return DiscussionThread::query()
            ->when(! $this->crmUser()->canManageOperationalRecords(), function ($query) {
                $query->where(function ($threadQuery) {
                    $threadQuery->where('initiated_by_id', $this->crmUser()->id)
                        ->orWhere('recipient_user_id', $this->crmUser()->id)
                        ->orWhere('owner_id', $this->crmUser()->id);
                });
            });
    }

    private function authorizeDiscussionAccess(DiscussionThread $discussionThread): void
    {
        if ($this->crmUser()->canManageOperationalRecords()) {
            return;
        }

        abort_unless(
            in_array($this->crmUser()->id, [
                $discussionThread->initiated_by_id,
                $discussionThread->recipient_user_id,
                $discussionThread->owner_id,
            ], true),
            403
        );
    }

    private function resolveDeliveryStatus(string $channel, ?int $integrationId): string
    {
        if ($channel === 'app') {
            return 'sent';
        }

        if ($channel === 'email') {
            return 'queued';
        }

        return $integrationId ? 'queued' : 'pending_integration';
    }

    private function dispatchExternalMessage(DiscussionThread $thread, $message): string
    {
        if ($thread->channel === 'app') {
            return 'sent';
        }

        if ($thread->channel === 'email' && filled($thread->recipient_email)) {
            try {
                Mail::raw($message->body, function ($mail) use ($thread) {
                    $mail->to($thread->recipient_email)
                        ->subject($thread->subject);
                });

                return 'sent';
            } catch (Throwable) {
                return 'failed';
            }
        }

        return $thread->integration_id ? 'queued' : 'pending_integration';
    }
}
