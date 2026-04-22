<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\DiscussionMessageStoreRequest;
use App\Http\Requests\Crm\DiscussionThreadStoreRequest;
use App\Models\DiscussionCampaign;
use App\Models\DiscussionThread;
use App\Services\Crm\DiscussionDeliveryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DiscussionController extends CrmController
{
    public function __construct(
        private readonly DiscussionDeliveryService $deliveryService
    ) {
    }

    public function index(): View
    {
        $this->authorizeModuleAccess('discussions', 'edit');

        $recentThreads = $this->deliveryService->threadQueryFor($this->crmUser())
            ->latest('last_message_at')
            ->limit(8)
            ->get();

        $recentCampaigns = DiscussionCampaign::query()
            ->with(['initiatedBy', 'recipients'])
            ->when(! $this->crmUser()->canManageOperationalRecords(), function ($query) {
                $query->where('owner_id', $this->crmUser()->id);
            })
            ->latest('updated_at')
            ->limit(6)
            ->get();

        return view('crm.discussions.index', [
            'recentThreads' => $recentThreads,
            'recentCampaigns' => $recentCampaigns,
            'discussionChannels' => config('heritage_crm.discussion_channels'),
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $this->authorizeModuleAccess('discussions', 'edit');

        $channel = (string) $request->query('channel', 'app');
        $query = $request->query();

        return match ($channel) {
            'email' => redirect()->route('crm.discussions.email.direct.create', $query),
            'whatsapp' => redirect()->route('crm.discussions.whatsapp.direct.create', $query),
            default => redirect()->route('crm.discussions.app.direct.create', $query),
        };
    }

    public function store(DiscussionThreadStoreRequest $request): RedirectResponse
    {
        $this->authorizeModuleAccess('discussions', 'edit');

        $payload = $request->validated();

        if ($payload['channel'] === 'app') {
            $recipient = $this->crmUsersForSelect()->firstWhere('id', (int) ($payload['recipient_user_id'] ?? 0));
            abort_if($recipient === null, 404);

            $thread = $this->deliveryService->startOrResumeDirectThread($this->crmUser(), $recipient, [
                'subject' => $payload['subject'] ?? null,
                'notes' => $payload['notes'] ?? null,
            ]);

            $this->deliveryService->storeAppMessage(
                $thread,
                $this->crmUser(),
                $payload['body'],
                $request->file('attachments', [])
            );

            return redirect()
                ->route('crm.discussions.app.threads.show', $thread)
                ->with('crm_success', 'Discussion created successfully.');
        }

        $recipientType = filled($payload['recipient_user_id'] ?? null) ? 'user' : 'manual';
        $thread = $this->deliveryService->saveExternalDirectDraft(
            $this->crmUser(),
            $payload['channel'],
            array_merge($payload, [
                'recipient_type' => $recipientType,
                'intent' => 'send',
                'attachments' => $request->file('attachments', []),
            ])
        );
        $thread = $this->deliveryService->sendExternalDraft($thread, $this->crmUser());

        return redirect()
            ->to($this->deliveryService->threadRoute($thread))
            ->with('crm_success', 'Discussion created successfully.');
    }

    public function show(DiscussionThread $discussionThread): RedirectResponse
    {
        $this->deliveryService->authorizeThreadAccess($this->crmUser(), $discussionThread);

        return redirect()->to($this->deliveryService->threadRoute($discussionThread));
    }

    public function destroy(DiscussionThread $discussionThread): RedirectResponse
    {
        $this->deliveryService->authorizeThreadAccess($this->crmUser(), $discussionThread);

        $discussionThread->forceDelete();

        return redirect()
            ->route('crm.discussions.index')
            ->with('crm_success', 'Discussion deleted permanently.');
    }

    public function storeMessage(DiscussionMessageStoreRequest $request, DiscussionThread $discussionThread): RedirectResponse
    {
        $this->deliveryService->authorizeThreadAccess($this->crmUser(), $discussionThread);

        if ($discussionThread->channel === 'app') {
            $this->deliveryService->storeAppMessage(
                $discussionThread,
                $this->crmUser(),
                (string) $request->validated('body', ''),
                $request->file('attachments', [])
            );

            return redirect()
                ->route('crm.discussions.app.threads.show', $discussionThread)
                ->with('crm_success', 'Message sent successfully.');
        }

        $this->deliveryService->replyExternalThread(
            $discussionThread,
            $this->crmUser(),
            $request->validated('body'),
            $request->file('attachments', [])
        );

        return redirect()
            ->to($this->deliveryService->threadRoute($discussionThread))
            ->with('crm_success', 'Message sent successfully.');
    }
}
