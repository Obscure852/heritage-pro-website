<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Services\Library\LibraryReportService;
use App\Exports\Library\CirculationReportExport;
use App\Exports\Library\OverdueReportExport;
use App\Exports\Library\MostBorrowedExport;
use App\Exports\Library\BorrowerActivityExport;
use App\Exports\Library\CollectionDevelopmentExport;
use App\Exports\Library\FineCollectionExport;
use App\Models\Grade;
use App\Models\Klass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller {
    protected LibraryReportService $reportService;

    public function __construct(LibraryReportService $reportService) {
        $this->middleware('auth');
        $this->reportService = $reportService;
    }

    // ==================== HELPERS ====================

    protected function getDateRange(Request $request): array {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::now()->endOfMonth();
        return [$startDate, $endDate];
    }

    protected function getFilters(Request $request): array {
        return [
            'borrower_type' => $request->input('borrower_type') ?: null,
            'grade_id' => $request->filled('grade_id') ? (int) $request->input('grade_id') : null,
            'klass_id' => $request->filled('klass_id') ? (int) $request->input('klass_id') : null,
        ];
    }

    protected function getFilterData(): array {
        return [
            'grades' => Grade::where('active', 1)->orderBy('name')->get(),
            'klasses' => Klass::orderBy('name')->get(),
        ];
    }

    // ==================== CIRCULATION REPORT ====================

    public function circulation(Request $request): View {
        Gate::authorize('manage-library');

        [$startDate, $endDate] = $this->getDateRange($request);
        $filters = $this->getFilters($request);

        $records = $this->reportService->getCirculationReport(
            $startDate, $endDate,
            $filters['borrower_type'], $filters['grade_id'], $filters['klass_id']
        );

        $totalReturns = $records->where('status', 'Returned')->count();

        return view('library.reports.circulation', array_merge(
            compact('records', 'startDate', 'endDate', 'filters', 'totalReturns'),
            $this->getFilterData()
        ));
    }

    public function exportCirculation(Request $request): BinaryFileResponse {
        Gate::authorize('manage-library');

        [$startDate, $endDate] = $this->getDateRange($request);
        $filters = $this->getFilters($request);

        $records = $this->reportService->getCirculationReport(
            $startDate, $endDate,
            $filters['borrower_type'], $filters['grade_id'], $filters['klass_id']
        );

        $filename = 'circulation-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.xlsx';

        return Excel::download(new CirculationReportExport($records, $startDate, $endDate), $filename);
    }

    // ==================== OVERDUE REPORT ====================

    public function overdue(Request $request): View {
        Gate::authorize('manage-library');

        $filters = $this->getFilters($request);

        $records = $this->reportService->getOverdueReport(
            $filters['borrower_type'], $filters['grade_id'], $filters['klass_id']
        );

        $totalFineAmount = number_format($records->sum(fn($r) => (float) str_replace(',', '', $r['fine_amount'])), 2);

        return view('library.reports.overdue', array_merge(
            compact('records', 'filters', 'totalFineAmount'),
            $this->getFilterData()
        ));
    }

    public function exportOverdue(Request $request): BinaryFileResponse {
        Gate::authorize('manage-library');

        $filters = $this->getFilters($request);

        $records = $this->reportService->getOverdueReport(
            $filters['borrower_type'], $filters['grade_id'], $filters['klass_id']
        );

        $filename = 'overdue-report-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new OverdueReportExport($records), $filename);
    }

    // ==================== MOST BORROWED REPORT ====================

    public function mostBorrowed(Request $request): View {
        Gate::authorize('manage-library');

        [$startDate, $endDate] = $this->getDateRange($request);
        $gradeId = $request->filled('grade_id') ? (int) $request->input('grade_id') : null;

        $records = $this->reportService->getMostBorrowedReport($startDate, $endDate, $gradeId);

        $grades = Grade::where('active', 1)->orderBy('name')->get();

        return view('library.reports.most-borrowed', compact(
            'records', 'startDate', 'endDate', 'gradeId', 'grades'
        ));
    }

    public function exportMostBorrowed(Request $request): BinaryFileResponse {
        Gate::authorize('manage-library');

        [$startDate, $endDate] = $this->getDateRange($request);
        $gradeId = $request->filled('grade_id') ? (int) $request->input('grade_id') : null;

        $records = $this->reportService->getMostBorrowedReport($startDate, $endDate, $gradeId);

        $filename = 'most-borrowed-' . $startDate->format('Y-m-d') . '.xlsx';

        return Excel::download(new MostBorrowedExport($records, $startDate, $endDate), $filename);
    }

    // ==================== BORROWER ACTIVITY REPORT ====================

    public function borrowerActivity(Request $request): View {
        Gate::authorize('manage-library');

        [$startDate, $endDate] = $this->getDateRange($request);
        $filters = $this->getFilters($request);

        // Individual mode when both borrower_type and borrower_id are provided
        if ($request->filled('borrower_type') && $request->filled('borrower_id')) {
            $individualData = $this->reportService->getIndividualBorrowerReport(
                $request->input('borrower_type'),
                (int) $request->input('borrower_id'),
                $startDate,
                $endDate
            );
            $mode = 'individual';

            return view('library.reports.borrower-activity', array_merge(
                compact('mode', 'individualData', 'startDate', 'endDate', 'filters'),
                $this->getFilterData()
            ));
        }

        // Aggregate mode
        $records = $this->reportService->getBorrowerActivityReport(
            $startDate, $endDate,
            $filters['borrower_type'], $filters['grade_id'], $filters['klass_id']
        );
        $mode = 'aggregate';

        return view('library.reports.borrower-activity', array_merge(
            compact('mode', 'records', 'startDate', 'endDate', 'filters'),
            $this->getFilterData()
        ));
    }

    public function exportBorrowerActivity(Request $request): BinaryFileResponse {
        Gate::authorize('manage-library');

        [$startDate, $endDate] = $this->getDateRange($request);
        $filters = $this->getFilters($request);

        if ($request->filled('borrower_type') && $request->filled('borrower_id')) {
            $data = $this->reportService->getIndividualBorrowerReport(
                $request->input('borrower_type'),
                (int) $request->input('borrower_id'),
                $startDate,
                $endDate
            );
            $mode = 'individual';
        } else {
            $data = $this->reportService->getBorrowerActivityReport(
                $startDate, $endDate,
                $filters['borrower_type'], $filters['grade_id'], $filters['klass_id']
            );
            $mode = 'aggregate';
        }

        $filename = 'borrower-activity-' . $startDate->format('Y-m-d') . '.xlsx';

        return Excel::download(new BorrowerActivityExport($data, $mode, $startDate, $endDate), $filename);
    }

    // ==================== COLLECTION DEVELOPMENT REPORT ====================

    public function collectionDevelopment(Request $request): View {
        Gate::authorize('manage-library');

        $gradeId = $request->filled('grade_id') ? (int) $request->input('grade_id') : null;

        $records = $this->reportService->getCollectionDevelopmentReport($gradeId);

        $totalTitles = $records->sum('total_titles');
        $totalCopies = $records->sum('total_copies');
        $avgUtilization = $records->count() > 0 ? round($records->avg('utilization_rate'), 1) : 0;

        $grades = Grade::where('active', 1)->orderBy('name')->get();

        return view('library.reports.collection-development', compact(
            'records', 'gradeId', 'grades', 'totalTitles', 'totalCopies', 'avgUtilization'
        ));
    }

    public function exportCollectionDevelopment(Request $request): BinaryFileResponse {
        Gate::authorize('manage-library');

        $gradeId = $request->filled('grade_id') ? (int) $request->input('grade_id') : null;

        $records = $this->reportService->getCollectionDevelopmentReport($gradeId);

        $filename = 'collection-development-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new CollectionDevelopmentExport($records), $filename);
    }

    // ==================== FINE COLLECTION REPORT ====================

    public function fineCollection(Request $request): View {
        Gate::authorize('manage-library');

        [$startDate, $endDate] = $this->getDateRange($request);
        $borrowerType = $request->input('borrower_type') ?: null;

        $data = $this->reportService->getFineCollectionReport($startDate, $endDate, $borrowerType);

        $filters = ['borrower_type' => $borrowerType];

        return view('library.reports.fine-collection', compact(
            'startDate', 'endDate', 'filters'
        ) + [
            'records' => $data['records'],
            'summary' => $data['summary'],
        ]);
    }

    public function exportFineCollection(Request $request): BinaryFileResponse {
        Gate::authorize('manage-library');

        [$startDate, $endDate] = $this->getDateRange($request);
        $borrowerType = $request->input('borrower_type') ?: null;

        $data = $this->reportService->getFineCollectionReport($startDate, $endDate, $borrowerType);

        $filename = 'fine-collection-' . $startDate->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new FineCollectionExport($data['records'], $data['summary'], $startDate, $endDate),
            $filename
        );
    }
}
