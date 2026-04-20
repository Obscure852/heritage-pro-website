<?php

namespace App\Http\Controllers\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Services\Pdp\PdpReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class PdpReviewController extends BasePdpController
{
    public function __construct(
        \App\Services\Pdp\PdpAccessService $accessService,
        private readonly PdpReviewService $reviewService
    ) {
        parent::__construct($accessService);
        $this->middleware('auth');
    }

    public function open(Request $request, PdpPlan $plan, string $periodKey): RedirectResponse
    {
        $this->authorizePlanManage($plan, $request->user());

        try {
            $this->reviewService->openReview($plan, $periodKey, $request->user());
        } catch (HttpExceptionInterface $exception) {
            throw $exception;
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('staff.pdp.plans.show', $plan)
                ->withErrors(['pdp' => $exception->getMessage()]);
        }

        return redirect()
            ->route('staff.pdp.plans.show', $plan)
            ->with('message', 'PDP review opened successfully.');
    }

    public function close(Request $request, PdpPlan $plan, string $periodKey): RedirectResponse
    {
        $this->authorizePlanManage($plan, $request->user());

        try {
            $this->reviewService->closeReview(
                $plan,
                $periodKey,
                $request->user(),
                $request->input('narrative_summary')
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
            ->with('message', 'PDP review closed and scored successfully.');
    }
}
