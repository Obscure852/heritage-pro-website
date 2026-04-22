<?php

namespace App\Http\Middleware;

use App\Models\DiscussionCampaign;
use App\Models\DiscussionMessageAttachment;
use App\Models\DiscussionThread;
use App\Services\Crm\CrmModulePermissionService;
use App\Services\Crm\DiscussionDeliveryService;
use Closure;
use Illuminate\Http\Request;
use Throwable;

class EnsureCrmAccess
{
    public function __construct(
        private readonly CrmModulePermissionService $permissionService,
        private readonly DiscussionDeliveryService $discussionDeliveryService
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        abort_unless($user && $user->canAccessCrm(), 403);

        $routeName = $request->route()?->getName();
        $module = $this->permissionService->moduleForRoute($routeName);

        if ($module !== null) {
            $requiredLevel = $this->permissionService->requiredPermissionLevelForRoute($routeName, $request->method()) ?? 'view';
            $hasAccess = $this->permissionService->hasAccess($user, $module['key'], $requiredLevel);

            if (! $hasAccess && $module['key'] === 'discussions' && $this->canAccessSourceBackedDiscussion($request, $user)) {
                return $next($request);
            }

            abort_unless(
                $hasAccess,
                403
            );
        }

        return $next($request);
    }

    private function canAccessSourceBackedDiscussion(Request $request, $user): bool
    {
        $sourceType = (string) ($request->input('source_type') ?: $request->query('source_type'));
        $sourceId = (int) ($request->input('source_id') ?: $request->query('source_id'));

        if ($sourceType !== '' && $sourceId > 0) {
            return $this->canAccessCommercialSource($user, $sourceType, $sourceId);
        }

        $thread = $request->route('discussionThread');

        if ($thread instanceof DiscussionThread && filled($thread->source_type) && filled($thread->source_id)) {
            try {
                $this->discussionDeliveryService->authorizeThreadAccess($user, $thread);
            } catch (Throwable) {
                return false;
            }

            return $this->canAccessCommercialSource($user, (string) $thread->source_type, (int) $thread->source_id);
        }

        $campaign = $request->route('discussionCampaign');

        if ($campaign instanceof DiscussionCampaign && filled($campaign->source_type) && filled($campaign->source_id)) {
            try {
                $this->discussionDeliveryService->authorizeCampaignAccess($user, $campaign);
            } catch (Throwable) {
                return false;
            }

            return $this->canAccessCommercialSource($user, (string) $campaign->source_type, (int) $campaign->source_id);
        }

        $attachment = $request->route('attachment');

        if ($attachment instanceof DiscussionMessageAttachment) {
            $thread = $attachment->message?->thread;

            if (! $thread || blank($thread->source_type) || blank($thread->source_id)) {
                return false;
            }

            try {
                $this->discussionDeliveryService->authorizeThreadAccess($user, $thread);
            } catch (Throwable) {
                return false;
            }

            return $this->canAccessCommercialSource($user, (string) $thread->source_type, (int) $thread->source_id);
        }

        return false;
    }

    private function canAccessCommercialSource($user, string $sourceType, int $sourceId): bool
    {
        try {
            $this->discussionDeliveryService->commercialSourceContext($user, $sourceType, $sourceId);

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
