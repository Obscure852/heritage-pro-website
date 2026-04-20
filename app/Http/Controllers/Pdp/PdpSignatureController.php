<?php

namespace App\Http\Controllers\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpPlanSignature;
use App\Services\Pdp\PdpReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class PdpSignatureController extends BasePdpController
{
    public function __construct(
        \App\Services\Pdp\PdpAccessService $accessService,
        private readonly PdpReviewService $reviewService
    ) {
        parent::__construct($accessService);
        $this->middleware('auth');
    }

    public function sign(Request $request, PdpPlan $plan, PdpPlanSignature $signature): RedirectResponse
    {
        $this->authorizePlanRead($plan, $request->user());
        abort_unless($signature->pdp_plan_id === $plan->id, 404);

        try {
            $this->reviewService->signSignature(
                $plan,
                $signature,
                $request->user(),
                $request->input('comment')
            );
        } catch (HttpExceptionInterface $exception) {
            throw $exception;
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('staff.pdp.plans.show', $plan)
                ->withErrors(['pdp' => $exception->getMessage()]);
        }

        return redirect()
            ->route('staff.pdp.plans.show', $plan)
            ->with('message', 'PDP signature recorded successfully.');
    }
}
