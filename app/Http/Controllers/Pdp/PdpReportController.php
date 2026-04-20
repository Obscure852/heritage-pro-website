<?php

namespace App\Http\Controllers\Pdp;

use App\Services\Pdp\PdpReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PdpReportController extends BasePdpController
{
    public function __construct(
        \App\Services\Pdp\PdpAccessService $accessService,
        private readonly PdpReportingService $reportingService
    ) {
        parent::__construct($accessService);
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $this->authorizeReporting($request->user());

        return view('pdp.reports.index', $this->reportingService->buildDashboard());
    }
}
