<?php

namespace App\Http\Controllers\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Services\Pdp\PdpPdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PdpPdfController extends BasePdpController
{
    public function __construct(
        \App\Services\Pdp\PdpAccessService $accessService,
        private readonly PdpPdfService $pdfService
    ) {
        parent::__construct($accessService);
        $this->middleware('auth');
    }

    public function preview(Request $request, PdpPlan $plan): View
    {
        $this->authorizePlanRead($plan, $request->user());

        return view('pdp.pdf.plan', $this->pdfService->buildViewData($plan, $request->user()) + [
            'isPreview' => true,
        ]);
    }

    public function download(Request $request, PdpPlan $plan): Response
    {
        $this->authorizePlanRead($plan, $request->user());

        return $this->pdfService->download($plan, $request->user());
    }
}
