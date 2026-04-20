<?php

namespace App\Http\Controllers\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpTemplate;
use App\Models\User;
use App\Services\Pdp\PdpPlanService;
use App\Services\Pdp\PdpPlanViewService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PdpPlanController extends BasePdpController
{
    public function __construct(
        \App\Services\Pdp\PdpAccessService $accessService,
        private readonly PdpPlanService $planService,
        private readonly PdpPlanViewService $viewService,
        private readonly \App\Services\Pdp\PdpReviewService $reviewService,
        private readonly \App\Services\Pdp\PdpSettingsService $settingsService,
        private readonly \App\Services\Pdp\PdpRolloutService $rolloutService
    ) {
        parent::__construct($accessService);
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $plansQuery = $this->accessiblePlansQuery($user);

        if ($request->filled('status')) {
            $plansQuery->where('status', $request->string('status')->toString());
        }

        if ($request->filled('template_id')) {
            $plansQuery->where('pdp_template_id', (int) $request->integer('template_id'));
        }

        if ($request->filled('user_id')) {
            $plansQuery->where('user_id', (int) $request->integer('user_id'));
        }

        return view('pdp.plans.index', [
            'plans' => $plansQuery->get(),
            'filters' => [
                'status' => $request->string('status')->toString(),
                'template_id' => $request->string('template_id')->toString(),
                'user_id' => $request->string('user_id')->toString(),
            ],
            'activeRollout' => $this->rolloutService->activeRollout(),
            'canCreateManualPlans' => $this->accessService->canCreateManualPlans($user),
            'canManageRollouts' => $this->accessService->canManageRollouts($user),
            'filterTemplates' => PdpTemplate::query()
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(),
            'filterUsers' => $this->availablePlanUsers($user),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $this->authorizeRolloutManage($user);

        return view('pdp.plans.create', [
            'availableUsers' => $this->availablePlanUsers($user),
            'templates' => PdpTemplate::query()
                ->where('status', PdpTemplate::STATUS_PUBLISHED)
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(),
            'suggestedDates' => $this->settingsService->suggestedPlanDatesForYear((int) now()->year),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeRolloutManage($user);
        $availableUsers = $this->availablePlanUsers($user);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'template_id' => ['required', 'integer', 'exists:pdp_templates,id'],
            'plan_period_start' => ['required', 'date'],
            'plan_period_end' => ['required', 'date'],
            'status' => ['required', 'in:draft,active,completed,cancelled'],
            'current_period_key' => ['nullable', 'string'],
        ]);

        abort_unless($availableUsers->pluck('id')->contains((int) $validated['user_id']), 403);

        $employee = User::query()->findOrFail($validated['user_id']);
        $template = PdpTemplate::query()->findOrFail($validated['template_id']);

        $plan = $this->planService->createPlan($employee, [
            'plan_period_start' => $validated['plan_period_start'],
            'plan_period_end' => $validated['plan_period_end'],
            'status' => $validated['status'],
            'current_period_key' => $validated['current_period_key'] ?: null,
            'created_by' => $user->id,
        ], $template);

        return redirect()
            ->route('staff.pdp.plans.show', $plan)
            ->with('message', 'PDP plan created successfully.');
    }

    public function show(Request $request, PdpPlan $plan): View
    {
        $this->authorizePlanRead($plan, $request->user());

        return view('pdp.plans.show', $this->viewService->buildPlanViewModel($plan, $request->user()) + [
            'viewService' => $this->viewService,
            'reviewService' => $this->reviewService,
        ]);
    }

    public function edit(Request $request, PdpPlan $plan): View
    {
        $user = $request->user();
        $this->authorizePlanAdministration($plan, $user);
        $availableUsers = $this->availablePlanUsers($user);

        if ($plan->supervisor && !$availableUsers->pluck('id')->contains($plan->supervisor->id)) {
            $availableUsers = $availableUsers->push($plan->supervisor)->unique('id')->values();
        }

        return view('pdp.plans.edit', [
            'plan' => $plan->loadMissing(['template.periods', 'user', 'supervisor']),
            'availableUsers' => $availableUsers,
        ]);
    }

    public function update(Request $request, PdpPlan $plan): RedirectResponse
    {
        $user = $request->user();
        $this->authorizePlanAdministration($plan, $user);

        $availableUsers = $this->availablePlanUsers($user);

        $validated = $request->validate([
            'supervisor_id' => ['nullable', 'integer', 'exists:users,id'],
            'plan_period_start' => ['required', 'date'],
            'plan_period_end' => ['required', 'date'],
            'status' => ['required', 'in:draft,active,completed,cancelled'],
            'current_period_key' => ['nullable', 'string'],
        ]);

        if (
            !empty($validated['supervisor_id'])
            && !$availableUsers->pluck('id')->contains((int) $validated['supervisor_id'])
            && (int) $validated['supervisor_id'] !== $plan->supervisor_id
        ) {
            abort(403);
        }

        $this->planService->updatePlan($plan, $validated);

        return redirect()
            ->route('staff.pdp.plans.show', $plan)
            ->with('message', 'PDP plan updated successfully.');
    }
}
