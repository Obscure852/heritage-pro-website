<?php

namespace App\Http\Controllers\Crm;

use App\Services\Crm\CrmGlobalSearchService;
use App\Services\Crm\CrmPresenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkspaceController extends CrmController
{
    public function __construct(
        private readonly CrmGlobalSearchService $searchService,
        private readonly CrmPresenceService $presenceService
    ) {
    }

    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        return response()->json([
            'query' => $query,
            'sections' => $this->searchService->search($this->crmUser(), $query),
        ]);
    }

    public function presenceLauncher(Request $request): JsonResponse
    {
        return response()->json(
            $this->presenceService->launcherPayload(
                $this->crmUser(),
                (string) $request->query('q', '')
            )
        );
    }

    public function presenceHeartbeat(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'path' => ['nullable', 'string', 'max:255'],
        ]);

        $this->presenceService->heartbeat($this->crmUser(), $payload['path'] ?? null);

        return response()->json(['ok' => true]);
    }

    public function presenceUnreadCount(): JsonResponse
    {
        return response()->json($this->presenceService->unreadPayload($this->crmUser()));
    }

    public function updateDiscussionSoundPreference(Request $request): JsonResponse
    {
        abort_unless($this->crmUser()->canAccessCrm(), 403);

        $payload = $request->validate([
            'crm_discussion_sound_enabled' => ['required', 'boolean'],
        ]);

        return response()->json(
            $this->presenceService->updateDiscussionSoundPreference(
                $this->crmUser(),
                (bool) $payload['crm_discussion_sound_enabled']
            )
        );
    }
}
