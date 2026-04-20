<?php

namespace App\Http\Controllers\Pdp;

use App\Models\Pdp\PdpRollout;
use App\Services\Pdp\PdpRolloutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use RuntimeException;

class PdpRolloutController extends BasePdpController
{
    public function __construct(
        \App\Services\Pdp\PdpAccessService $accessService,
        private readonly PdpRolloutService $rolloutService
    ) {
        parent::__construct($accessService);
        $this->middleware('auth');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeRolloutManage($request->user());

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'cycle_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'plan_period_start' => ['required', 'date'],
            'plan_period_end' => ['required', 'date'],
            'auto_provision_new_staff' => ['nullable', 'boolean'],
            'fallback_supervisor_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {
            $rollout = $this->rolloutService->launch([
                'label' => $validated['label'],
                'cycle_year' => $validated['cycle_year'],
                'plan_period_start' => $validated['plan_period_start'],
                'plan_period_end' => $validated['plan_period_end'],
                'auto_provision_new_staff' => (bool) ($validated['auto_provision_new_staff'] ?? false),
                'fallback_supervisor_user_id' => $validated['fallback_supervisor_user_id'],
            ], $request->user());
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return redirect()
                ->route('staff.pdp.settings.index', ['tab' => 'rollouts'])
                ->withInput()
                ->withErrors(['rollout' => $exception->getMessage()]);
        }

        return redirect()
            ->route('staff.pdp.rollouts.show', $rollout)
            ->with('message', 'PDP rollout launched successfully.');
    }

    public function show(Request $request, PdpRollout $rollout): View
    {
        $this->authorizeRolloutManage($request->user());

        $rollout = $this->rolloutService->activeRollout()?->id === $rollout->id
            ? $this->rolloutService->activeRollout()
            : $rollout->load([
                'template',
                'fallbackSupervisor',
                'launcher',
                'plans.user',
                'plans.supervisor',
            ]);

        return view('pdp.rollouts.show', [
            'rollout' => $rollout,
        ]);
    }
}
