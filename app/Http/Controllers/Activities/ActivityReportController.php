<?php

namespace App\Http\Controllers\Activities;

use App\Exports\Activities\ActivityReportExport;
use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Models\Activities\Activity;
use App\Models\Term;
use App\Services\Activities\ActivityReportService;
use App\Services\Activities\ActivitySettingsService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ActivityReportController extends Controller
{
    public function __construct(
        private readonly ActivityReportService $activityReportService,
        private readonly ActivitySettingsService $activitySettingsService
    )
    {
    }

    public function index(Request $request)
    {
        $selectedTerm = $this->resolveSelectedTerm();
        $filters = $this->reportFilters($request);
        $payload = $this->activityReportService->reportPayload($request->user(), $selectedTerm, $filters);

        return view('activities.reports', [
            ...$payload,
            'selectedTerm' => $selectedTerm,
            'filters' => $filters,
            'statuses' => Activity::statuses(),
            'categories' => $this->activitySettingsService->activeCategoryOptions(),
            'deliveryModes' => $this->activitySettingsService->activeDeliveryModeOptions(),
        ]);
    }

    public function export(Request $request)
    {
        $selectedTerm = $this->resolveSelectedTerm();
        $filters = $this->reportFilters($request);
        $rows = $this->activityReportService->reportExportRows($request->user(), $selectedTerm, $filters);

        return Excel::download(
            new ActivityReportExport($rows),
            $this->reportFilename($selectedTerm)
        );
    }

    private function resolveSelectedTerm(): ?Term
    {
        $selectedTermId = session('selected_term_id');

        if ($selectedTermId) {
            return Term::query()->find($selectedTermId);
        }

        return TermHelper::getCurrentTerm();
    }

    private function reportFilters(Request $request): array
    {
        return [
            'search' => trim((string) $request->input('search', '')),
            'status' => $request->input('status') ?: null,
            'category' => $request->input('category') ?: null,
            'delivery_mode' => $request->input('delivery_mode') ?: null,
            'activity_id' => $request->filled('activity_id') ? (int) $request->input('activity_id') : null,
        ];
    }

    private function reportFilename(?Term $selectedTerm): string
    {
        if (!$selectedTerm) {
            return 'activities-report.xlsx';
        }

        return sprintf(
            'activities-report-%d-term-%d.xlsx',
            $selectedTerm->year,
            $selectedTerm->term
        );
    }
}
