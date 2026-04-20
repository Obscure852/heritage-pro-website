<?php

namespace App\Http\Controllers\Pdp;

use App\Http\Controllers\Controller;
use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpTemplate;
use App\Models\User;
use App\Services\Pdp\PdpAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

abstract class BasePdpController extends Controller
{
    public function __construct(
        protected readonly PdpAccessService $accessService
    ) {
    }

    protected function accessiblePlansQuery(User $user): Builder
    {
        $query = PdpPlan::query()
            ->with(['template', 'user', 'supervisor'])
            ->orderByDesc('created_at');

        if ($this->accessService->hasElevatedAccess($user)) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($user): void {
            $builder->where('user_id', $user->id)
                ->orWhere('supervisor_id', $user->id)
                ->orWhere('created_by', $user->id);
        });
    }

    protected function availablePlanUsers(User $user): Collection
    {
        if ($this->accessService->hasElevatedAccess($user)) {
            return User::query()
                ->where('status', 'Current')
                ->orderBy('firstname')
                ->orderBy('lastname')
                ->get();
        }

        return User::query()
            ->where(function (Builder $builder) use ($user): void {
                $builder->where('id', $user->id)
                    ->orWhere('reporting_to', $user->id);
            })
            ->where('status', 'Current')
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();
    }

    protected function authorizePlanRead(PdpPlan $plan, User $user): void
    {
        if ($this->accessService->canReadPlan($plan, $user)) {
            return;
        }

        abort(403);
    }

    protected function authorizePlanManage(PdpPlan $plan, User $user): void
    {
        if ($this->accessService->canManagePlan($plan, $user)) {
            return;
        }

        abort(403);
    }

    protected function authorizePlanAdministration(PdpPlan $plan, User $user): void
    {
        if ($this->accessService->canAdministerPlan($plan, $user)) {
            return;
        }

        abort(403);
    }

    protected function authorizeTemplateManage(User $user): void
    {
        if ($this->accessService->canManageTemplates($user)) {
            return;
        }

        abort(403);
    }

    protected function authorizeReporting(User $user): void
    {
        if ($this->accessService->canViewReports($user)) {
            return;
        }

        abort(403);
    }

    protected function authorizeRolloutManage(User $user): void
    {
        if ($this->accessService->canManageRollouts($user)) {
            return;
        }

        abort(403);
    }

    protected function loadTemplateDefinition(PdpTemplate $template): PdpTemplate
    {
        return $template->loadMissing([
            'sections.fields.childFields',
            'sections.rows',
            'periods',
            'ratingSchemes',
            'approvalSteps',
            'createdBy',
        ]);
    }
}
