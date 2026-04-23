<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\ExternalDiscussionCampaignRequest;
use App\Http\Requests\Crm\ExternalDiscussionDirectRequest;
use App\Http\Requests\Crm\ExternalDiscussionReplyRequest;
use App\Models\DiscussionCampaign;
use App\Models\DiscussionThread;
use App\Services\Crm\DiscussionDeliveryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class ExternalDiscussionChannelController extends CrmController
{
    public function __construct(
        protected readonly DiscussionDeliveryService $deliveryService
    ) {
    }

    abstract protected function channelKey(): string;

    abstract protected function viewBase(): string;

    abstract protected function routeBase(): string;

    abstract protected function channelLabel(): string;

    public function index(): View
    {
        $this->authorizeModuleAccess('discussions', 'edit');

        $threads = $this->deliveryService->threadQueryFor($this->crmUser())
            ->where('channel', $this->channelKey())
            ->orderByDesc('last_message_at')
            ->paginate(12, ['*'], $this->channelKey() . '_threads')
            ->withQueryString();

        $campaigns = $this->deliveryService->campaignQueryFor($this->crmUser(), $this->channelKey())
            ->latest('updated_at')
            ->limit(8)
            ->get();

        return view($this->viewBase() . '.index', [
            'threads' => $threads,
            'campaigns' => $campaigns,
            'channelLabel' => $this->channelLabel(),
            'deliveryStatuses' => config('heritage_crm.discussion_delivery_statuses'),
        ]);
    }

    public function createDirect(Request $request): View
    {
        $this->authorizeChannelEntry(
            (string) $request->query('source_type', ''),
            $request->query('source_id')
        );

        return view($this->viewBase() . '.direct-create', $this->directViewData($request));
    }

    public function storeDirect(ExternalDiscussionDirectRequest $request): RedirectResponse
    {
        $this->authorizeChannelEntry(
            (string) $request->input('source_type', ''),
            $request->input('source_id')
        );

        $thread = $this->deliveryService->saveExternalDirectDraft(
            $this->crmUser(),
            $this->channelKey(),
            array_merge($request->validated(), [
                'attachments' => $request->file('attachments', []),
            ])
        );

        if ($request->validated('intent') === 'send') {
            $thread = $this->deliveryService->sendExternalDraft($thread, $this->crmUser());
        }

        return redirect()
            ->to($this->deliveryService->threadRoute($thread))
            ->with('crm_success', $request->validated('intent') === 'send'
                ? $this->channelLabel() . ' message sent successfully.'
                : $this->channelLabel() . ' draft saved successfully.');
    }

    public function showDirect(DiscussionThread $discussionThread): View
    {
        $this->ensureChannelThread($discussionThread);
        $this->deliveryService->authorizeThreadAccess($this->crmUser(), $discussionThread);
        $this->deliveryService->markThreadRead($discussionThread, $this->crmUser());

        $discussionThread->load([
            'initiatedBy',
            'recipientUser',
            'integration',
            'participants.user',
            'messages.user',
            'messages.attachments',
            'messages.mentions.user',
        ]);

        return view($this->viewBase() . '.direct-show', [
            'discussionThread' => $discussionThread,
            'channelLabel' => $this->channelLabel(),
            'deliveryStatuses' => config('heritage_crm.discussion_delivery_statuses'),
        ]);
    }

    public function editDirect(DiscussionThread $discussionThread): View
    {
        $this->ensureChannelThread($discussionThread);
        $this->deliveryService->authorizeThreadAccess($this->crmUser(), $discussionThread);

        if ($discussionThread->status !== 'draft') {
            return $this->showDirect($discussionThread);
        }

        return view($this->viewBase() . '.direct-edit', $this->directViewData(
            request(),
            $discussionThread
        ));
    }

    public function updateDirect(ExternalDiscussionDirectRequest $request, DiscussionThread $discussionThread): RedirectResponse
    {
        $this->ensureChannelThread($discussionThread);
        $this->deliveryService->authorizeThreadAccess($this->crmUser(), $discussionThread);

        $thread = $this->deliveryService->saveExternalDirectDraft(
            $this->crmUser(),
            $this->channelKey(),
            array_merge($request->validated(), [
                'attachments' => $request->file('attachments', []),
            ]),
            $discussionThread
        );

        if ($request->validated('intent') === 'send') {
            $thread = $this->deliveryService->sendExternalDraft($thread, $this->crmUser());
        }

        return redirect()
            ->to($this->deliveryService->threadRoute($thread))
            ->with('crm_success', $request->validated('intent') === 'send'
                ? $this->channelLabel() . ' message sent successfully.'
                : $this->channelLabel() . ' draft updated successfully.');
    }

    public function sendDirect(DiscussionThread $discussionThread): RedirectResponse
    {
        $this->ensureChannelThread($discussionThread);
        $this->deliveryService->authorizeThreadAccess($this->crmUser(), $discussionThread);

        $thread = $this->deliveryService->sendExternalDraft($discussionThread, $this->crmUser());

        return redirect()
            ->to($this->deliveryService->threadRoute($thread))
            ->with('crm_success', $this->channelLabel() . ' message sent successfully.');
    }

    public function replyDirect(ExternalDiscussionReplyRequest $request, DiscussionThread $discussionThread): RedirectResponse
    {
        $this->ensureChannelThread($discussionThread);
        $this->deliveryService->authorizeThreadAccess($this->crmUser(), $discussionThread);

        $this->deliveryService->replyExternalThread(
            $discussionThread,
            $this->crmUser(),
            $request->validated('body'),
            $request->file('attachments', [])
        );

        return redirect()
            ->route($this->routeBase() . '.direct.show', $discussionThread)
            ->with('crm_success', $this->channelLabel() . ' reply queued successfully.');
    }

    public function createBulk(Request $request): View
    {
        $this->authorizeModuleAccess('discussions', 'edit');

        return view($this->viewBase() . '.bulk-create', $this->campaignViewData($request));
    }

    public function storeBulk(ExternalDiscussionCampaignRequest $request): RedirectResponse
    {
        $this->authorizeModuleAccess('discussions', 'edit');

        $campaign = $this->deliveryService->saveCampaign(
            $this->crmUser(),
            $this->channelKey(),
            $request->validated()
        );

        if ($request->validated('intent') === 'send') {
            $campaign = $this->deliveryService->sendCampaign(
                $campaign,
                $this->crmUser(),
                $request->file('attachments', [])
            );
        }

        return redirect()
            ->route($this->routeBase() . '.bulk.edit', $campaign)
            ->with('crm_success', $request->validated('intent') === 'send'
                ? $this->channelLabel() . ' bulk message sent successfully.'
                : $this->channelLabel() . ' bulk draft saved successfully.');
    }

    public function editBulk(DiscussionCampaign $discussionCampaign): View
    {
        $this->ensureChannelCampaign($discussionCampaign);
        $this->deliveryService->authorizeCampaignAccess($this->crmUser(), $discussionCampaign);

        return view($this->viewBase() . '.bulk-edit', $this->campaignViewData(
            request(),
            $discussionCampaign
        ));
    }

    public function updateBulk(ExternalDiscussionCampaignRequest $request, DiscussionCampaign $discussionCampaign): RedirectResponse
    {
        $this->ensureChannelCampaign($discussionCampaign);
        $this->deliveryService->authorizeCampaignAccess($this->crmUser(), $discussionCampaign);

        $campaign = $this->deliveryService->saveCampaign(
            $this->crmUser(),
            $this->channelKey(),
            $request->validated(),
            $discussionCampaign
        );

        if ($request->validated('intent') === 'send') {
            $campaign = $this->deliveryService->sendCampaign(
                $campaign,
                $this->crmUser(),
                $request->file('attachments', [])
            );
        }

        return redirect()
            ->route($this->routeBase() . '.bulk.edit', $campaign)
            ->with('crm_success', $request->validated('intent') === 'send'
                ? $this->channelLabel() . ' bulk message sent successfully.'
                : $this->channelLabel() . ' bulk draft updated successfully.');
    }

    public function sendBulk(DiscussionCampaign $discussionCampaign): RedirectResponse
    {
        $this->ensureChannelCampaign($discussionCampaign);
        $this->deliveryService->authorizeCampaignAccess($this->crmUser(), $discussionCampaign);

        $campaign = $this->deliveryService->sendCampaign($discussionCampaign, $this->crmUser());

        return redirect()
            ->route($this->routeBase() . '.bulk.edit', $campaign)
            ->with('crm_success', $this->channelLabel() . ' bulk message sent successfully.');
    }

    protected function directViewData(Request $request, ?DiscussionThread $discussionThread = null): array
    {
        if ($discussionThread) {
            $discussionThread->load([
                'initiatedBy',
                'recipientUser',
                'integration',
                'messages.user',
                'messages.attachments',
            ]);
        }

        $sourceContext = $this->sourceContext(
            (string) ($request->query('source_type', $discussionThread?->source_type ?? $request->old('source_type'))),
            $request->query('source_id', $discussionThread?->source_id ?? $request->old('source_id'))
        );

        return [
            'discussionThread' => $discussionThread,
            'channelKey' => $this->channelKey(),
            'channelLabel' => $this->channelLabel(),
            'routeBase' => $this->routeBase(),
            'users' => $this->crmUsersForSelect(),
            'leads' => $this->leadsForSelect(),
            'customers' => $this->customersForSelect(),
            'contacts' => $this->contactsForSelect(),
            'integrations' => $this->availableIntegrations(),
            'sourceContext' => $sourceContext,
        ];
    }

    protected function campaignViewData(Request $request, ?DiscussionCampaign $discussionCampaign = null): array
    {
        if ($discussionCampaign) {
            $discussionCampaign->load(['initiatedBy', 'integration', 'recipients']);
        }

        $sourceContext = $this->sourceContext(
            (string) ($request->query('source_type', $discussionCampaign?->source_type ?? $request->old('source_type'))),
            $request->query('source_id', $discussionCampaign?->source_id ?? $request->old('source_id'))
        );

        return [
            'discussionCampaign' => $discussionCampaign,
            'channelKey' => $this->channelKey(),
            'channelLabel' => $this->channelLabel(),
            'routeBase' => $this->routeBase(),
            'users' => $this->crmUsersForSelect(),
            'leads' => $this->leadsForSelect(),
            'customers' => $this->customersForSelect(),
            'contacts' => $this->contactsForSelect(),
            'integrations' => $this->availableIntegrations(),
            'sourceContext' => $sourceContext,
        ];
    }

    protected function authorizeChannelEntry(?string $sourceType = null, mixed $sourceId = null): void
    {
        if ($this->crmUser()->canAccessCrmModule('discussions', 'edit')) {
            return;
        }

        if (filled($sourceType) && filled($sourceId)) {
            $this->sourceContext((string) $sourceType, $sourceId);

            return;
        }

        abort(403);
    }

    protected function ensureChannelThread(DiscussionThread $discussionThread): void
    {
        abort_unless($discussionThread->channel === $this->channelKey(), 404);
    }

    protected function ensureChannelCampaign(DiscussionCampaign $discussionCampaign): void
    {
        abort_unless($discussionCampaign->channel === $this->channelKey(), 404);
    }

    protected function sourceContext(?string $sourceType, mixed $sourceId): ?array
    {
        if (! filled($sourceType) || ! filled($sourceId)) {
            return null;
        }

        return $this->deliveryService->commercialSourceContext(
            $this->crmUser(),
            (string) $sourceType,
            (int) $sourceId
        );
    }
}
