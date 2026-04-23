<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\AppDiscussionCampaignRequest;
use App\Http\Requests\Crm\AppDiscussionMessageRequest;
use App\Http\Requests\Crm\AppDiscussionStartRequest;
use App\Http\Requests\Crm\AppDiscussionThreadUpdateRequest;
use App\Models\DiscussionCampaign;
use App\Models\DiscussionMessageAttachment;
use App\Models\DiscussionThread;
use App\Services\Crm\DiscussionDeliveryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppDiscussionController extends CrmController
{
    public function __construct(
        private readonly DiscussionDeliveryService $deliveryService
    ) {
    }

    public function workspace(Request $request, ?DiscussionThread $discussionThread = null): View
    {
        $this->authorizeModuleAccess('discussions', 'edit');

        $selectedThread = $discussionThread;

        if ($selectedThread === null) {
            $selectedThread = $this->deliveryService->companyChatThread($this->crmUser());
        }

        $this->deliveryService->authorizeThreadAccess($this->crmUser(), $selectedThread);
        $this->deliveryService->markThreadRead($selectedThread, $this->crmUser());

        $selectedThread->load([
            'initiatedBy',
            'recipientUser',
            'participants.user',
            'messages.user',
            'messages.attachments',
            'messages.mentions.user',
            'campaigns',
        ]);

        $companyChatThread = $this->deliveryService->companyChatThread($this->crmUser());
        $directThreads = $this->deliveryService->threadQueryFor($this->crmUser())
            ->where('channel', 'app')
            ->where('kind', 'direct')
            ->orderByDesc('last_message_at')
            ->get();
        $groupThreads = $this->deliveryService->threadQueryFor($this->crmUser())
            ->where('channel', 'app')
            ->where('kind', 'group')
            ->orderByDesc('last_message_at')
            ->get();
        $recentFiles = $this->deliveryService->latestAppFiles($this->crmUser());

        return view('crm.discussions.app.workspace', [
            'selectedThread' => $selectedThread,
            'companyChatThread' => $companyChatThread,
            'directThreads' => $directThreads,
            'groupThreads' => $groupThreads,
            'recentFiles' => $recentFiles,
            'crmUsers' => $this->crmUsersForSelect(),
        ]);
    }

    public function poll(DiscussionThread $discussionThread): JsonResponse
    {
        $this->authorizeModuleAccess('discussions', 'edit');
        $this->deliveryService->authorizeThreadAccess($this->crmUser(), $discussionThread);
        $this->deliveryService->markThreadRead($discussionThread, $this->crmUser());

        $discussionThread->load([
            'participants.user',
            'messages.user',
            'messages.attachments',
            'messages.mentions.user',
        ]);

        return response()->json([
            'thread_id' => $discussionThread->id,
            'last_message_at' => optional($discussionThread->last_message_at)->toIso8601String(),
            'html' => view('crm.discussions.app.partials.thread-messages', [
                'selectedThread' => $discussionThread,
            ])->render(),
        ]);
    }

    public function companyChat(): RedirectResponse
    {
        $this->authorizeModuleAccess('discussions', 'edit');

        return redirect()->route('crm.discussions.app.threads.show', $this->deliveryService->companyChatThread($this->crmUser()));
    }

    public function storeCompanyChatMessage(AppDiscussionMessageRequest $request): RedirectResponse
    {
        $this->authorizeModuleAccess('discussions', 'edit');

        $thread = $this->deliveryService->companyChatThread($this->crmUser());

        $this->deliveryService->storeAppMessage(
            $thread,
            $this->crmUser(),
            (string) $request->validated('body', ''),
            $request->file('attachments', []),
            $this->crmUsersForSelect()
                ->pluck('id')
                ->reject(fn ($userId) => (int) $userId === (int) $this->crmUser()->id)
                ->map(fn ($userId) => (int) $userId)
                ->values()
                ->all(),
            $request->validated('mention_user_ids', [])
        );

        return redirect()
            ->route('crm.discussions.app.threads.show', $thread)
            ->with('crm_success', 'Company chat updated successfully.');
    }

    public function createDirect(): View
    {
        $this->authorizeAppEntry(
            (string) request()->query('source_type', ''),
            request()->query('source_id')
        );

        return view('crm.discussions.app.direct-create', [
            'crmUsers' => $this->crmUsersForSelect(),
            'sourceContext' => $this->sourceContext(
                (string) request()->query('source_type', ''),
                request()->query('source_id')
            ),
        ]);
    }

    public function storeDirect(AppDiscussionStartRequest $request): RedirectResponse
    {
        $this->authorizeAppEntry(
            (string) $request->input('source_type', ''),
            $request->input('source_id')
        );

        $recipient = $this->crmUsersForSelect()->firstWhere('id', (int) $request->validated('recipient_user_id'));
        abort_if($recipient === null, 404);

        $thread = $this->deliveryService->startOrResumeDirectThread($this->crmUser(), $recipient, $request->validated());

        if (filled($request->validated('body')) || count($request->file('attachments', [])) > 0) {
            $message = $this->deliveryService->storeAppMessage(
                $thread,
                $this->crmUser(),
                (string) $request->validated('body', ''),
                $request->file('attachments', [])
            );

            $this->deliveryService->attachCommercialSourceToMessage(
                $message,
                $this->crmUser(),
                $request->validated('source_type'),
                $request->validated('source_id')
            );
        }

        return redirect()
            ->route('crm.discussions.app.threads.show', $thread)
            ->with('crm_success', 'Direct conversation ready.');
    }

    public function startDirect(Request $request): RedirectResponse
    {
        $this->authorizeModuleAccess('discussions', 'edit');

        $recipientId = (int) $request->query('recipient_user_id');
        $recipient = $this->crmUsersForSelect()->firstWhere('id', $recipientId);
        abort_if($recipient === null, 404);

        $thread = $this->deliveryService->startOrResumeDirectThread($this->crmUser(), $recipient);

        return redirect()->route('crm.discussions.app.threads.show', $thread);
    }

    public function editDirect(DiscussionThread $discussionThread): View
    {
        $this->authorizeModuleAccess('discussions', 'edit');
        abort_unless($discussionThread->channel === 'app' && $discussionThread->kind === 'direct', 404);
        $this->deliveryService->authorizeThreadAccess($this->crmUser(), $discussionThread);

        return view('crm.discussions.app.direct-edit', [
            'discussionThread' => $discussionThread->load('participants.user'),
        ]);
    }

    public function updateDirect(AppDiscussionThreadUpdateRequest $request, DiscussionThread $discussionThread): RedirectResponse
    {
        $this->authorizeModuleAccess('discussions', 'edit');
        abort_unless($discussionThread->channel === 'app' && $discussionThread->kind === 'direct', 404);
        $this->deliveryService->authorizeThreadAccess($this->crmUser(), $discussionThread);

        $this->deliveryService->updateThreadMetadata($discussionThread, $this->crmUser(), $request->validated());

        return redirect()
            ->route('crm.discussions.app.threads.show', $discussionThread)
            ->with('crm_success', 'Conversation details updated successfully.');
    }

    public function storeDirectMessage(AppDiscussionMessageRequest $request, DiscussionThread $discussionThread): RedirectResponse
    {
        $this->authorizeModuleAccess('discussions', 'edit');
        abort_unless($discussionThread->channel === 'app', 404);
        $this->deliveryService->authorizeThreadAccess($this->crmUser(), $discussionThread);

        $this->deliveryService->storeAppMessage(
            $discussionThread,
            $this->crmUser(),
            (string) $request->validated('body', ''),
            $request->file('attachments', []),
            [],
            $request->validated('mention_user_ids', [])
        );

        return redirect()
            ->route('crm.discussions.app.threads.show', $discussionThread)
            ->with('crm_success', 'Message sent successfully.');
    }

    public function createBulk(): View
    {
        $this->authorizeAppEntry(
            (string) request()->query('source_type', ''),
            request()->query('source_id')
        );

        return view('crm.discussions.app.bulk-create', [
            'crmUsers' => $this->crmUsersForSelect(),
            'departments' => $this->crmDepartmentsForSelect(),
            'sourceContext' => $this->sourceContext(
                (string) request()->query('source_type', ''),
                request()->query('source_id')
            ),
        ]);
    }

    public function storeBulk(AppDiscussionCampaignRequest $request): RedirectResponse
    {
        $this->authorizeAppEntry(
            (string) $request->input('source_type', ''),
            $request->input('source_id')
        );

        $campaign = $this->deliveryService->saveCampaign(
            $this->crmUser(),
            'app',
            [
                'subject' => $request->validated('subject'),
                'body' => $request->validated('body'),
                'notes' => $request->validated('notes'),
                'recipient_user_ids' => $request->validated('recipient_user_ids'),
                'department_ids' => $request->validated('department_ids'),
                'source_type' => $request->validated('source_type'),
                'source_id' => $request->validated('source_id'),
            ]
        );

        if ($request->validated('intent') === 'send') {
            $campaign = $this->deliveryService->sendCampaign($campaign, $this->crmUser(), $request->file('attachments', []));

            return redirect()
                ->route('crm.discussions.app.threads.show', $campaign->thread)
                ->with('crm_success', 'Group chat created successfully.');
        }

        return redirect()
            ->route('crm.discussions.app.bulk.edit', $campaign)
            ->with('crm_success', 'Group chat draft saved successfully.');
    }

    public function editBulk(DiscussionCampaign $discussionCampaign): View
    {
        $this->authorizeModuleAccess('discussions', 'edit');
        abort_unless($discussionCampaign->channel === 'app', 404);
        $this->deliveryService->authorizeCampaignAccess($this->crmUser(), $discussionCampaign);

        return view('crm.discussions.app.bulk-edit', [
            'discussionCampaign' => $discussionCampaign,
            'crmUsers' => $this->crmUsersForSelect(),
            'departments' => $this->crmDepartmentsForSelect(),
            'sourceContext' => $this->sourceContext(
                $discussionCampaign->source_type,
                $discussionCampaign->source_id
            ),
        ]);
    }

    public function updateBulk(AppDiscussionCampaignRequest $request, DiscussionCampaign $discussionCampaign): RedirectResponse
    {
        $this->authorizeModuleAccess('discussions', 'edit');
        abort_unless($discussionCampaign->channel === 'app', 404);
        $this->deliveryService->authorizeCampaignAccess($this->crmUser(), $discussionCampaign);

        $campaign = $this->deliveryService->saveCampaign(
            $this->crmUser(),
            'app',
            [
                'subject' => $request->validated('subject'),
                'body' => $request->validated('body'),
                'notes' => $request->validated('notes'),
                'recipient_user_ids' => $request->validated('recipient_user_ids'),
                'department_ids' => $request->validated('department_ids'),
                'source_type' => $request->validated('source_type'),
                'source_id' => $request->validated('source_id'),
            ],
            $discussionCampaign
        );

        if ($request->validated('intent') === 'send') {
            $campaign = $this->deliveryService->sendCampaign($campaign, $this->crmUser(), $request->file('attachments', []));

            return redirect()
                ->route('crm.discussions.app.threads.show', $campaign->thread)
                ->with('crm_success', 'Group chat created successfully.');
        }

        return redirect()
            ->route('crm.discussions.app.bulk.edit', $campaign)
            ->with('crm_success', 'Group chat draft updated successfully.');
    }

    public function sendBulk(DiscussionCampaign $discussionCampaign): RedirectResponse
    {
        $this->authorizeModuleAccess('discussions', 'edit');
        abort_unless($discussionCampaign->channel === 'app', 404);
        $this->deliveryService->authorizeCampaignAccess($this->crmUser(), $discussionCampaign);

        $campaign = $this->deliveryService->sendCampaign($discussionCampaign, $this->crmUser());

        return redirect()
            ->route('crm.discussions.app.threads.show', $campaign->thread)
            ->with('crm_success', 'Group chat created successfully.');
    }

    public function previewAttachment(DiscussionMessageAttachment $attachment)
    {
        $this->authorizeAttachmentAccess($attachment);

        return response()->file(
            Storage::disk($attachment->disk)->path($attachment->path),
            [
                'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . addslashes($attachment->original_name) . '"',
            ]
        );
    }

    public function openAttachment(DiscussionMessageAttachment $attachment)
    {
        return $this->previewAttachment($attachment);
    }

    public function downloadAttachment(DiscussionMessageAttachment $attachment)
    {
        $this->authorizeAttachmentAccess($attachment);

        return response()->download(
            Storage::disk($attachment->disk)->path($attachment->path),
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
            ]
        );
    }

    private function authorizeAttachmentAccess(DiscussionMessageAttachment $attachment): void
    {
        $attachment->loadMissing('message.thread.participants');
        $thread = $attachment->message?->thread;
        abort_if($thread === null, 404);

        $this->deliveryService->authorizeThreadAccess($this->crmUser(), $thread);
    }

    private function authorizeAppEntry(?string $sourceType = null, mixed $sourceId = null): void
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

    private function sourceContext(?string $sourceType, mixed $sourceId): ?array
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
