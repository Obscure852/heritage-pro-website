<?php

namespace App\Http\Controllers\Fee;

use App\Exports\Fee\AgingReportExport;
use App\Exports\Fee\CollectionSummaryExport;
use App\Exports\Fee\CollectorPerformanceExport;
use App\Exports\Fee\DailyCollectionsExport;
use App\Exports\Fee\DebtorsListExport;
use App\Exports\Fee\OutstandingByGradeExport;
use App\Exports\Fee\StudentStatementExport;
use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\SchoolSetup;
use App\Models\Student;
use App\Models\Term;
use App\Services\Fee\ReportingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FeeReportController extends Controller
{
    protected ReportingService $reportingService;

    public function __construct(ReportingService $reportingService)
    {
        $this->middleware('auth');
        $this->reportingService = $reportingService;
    }

    // ========================================
    // Dashboard
    // ========================================

    /**
     * Display the fee reports dashboard with key statistics.
     */
    public function dashboard(Request $request): View
    {
        Gate::authorize('view-fee-reports');

        // Default to current term's year
        $currentTermYear = TermHelper::getCurrentTerm()?->year ?? (int) date('Y');

        // Get year filter - handle "All Years" vs default to current term year
        $year = null;
        if ($request->has('year') && $request->year === '') {
            // "All Years" explicitly selected - use null
            $year = null;
        } elseif ($request->filled('year')) {
            // Specific year selected
            $year = (int) $request->year;
        } else {
            // First page load - default to current term year
            $year = $currentTermYear;
        }

        // Get dashboard statistics
        $stats = $this->reportingService->getDashboardStats($year);

        // Get recent payments
        $recentPayments = $this->reportingService->getRecentPayments(10);

        // Get top debtors
        $topDebtors = $year
            ? $this->reportingService->getTopDebtors($year, 5)
            : collect([]);

        // Get payment trends for chart
        $paymentTrends = $year
            ? $this->reportingService->getPaymentTrends($year, 'week')
            : [];

        // Get collections by method
        $collectionsByMethod = $this->reportingService->getCollectionsByMethod($year);

        return view('fees.reports.dashboard', [
            'stats' => $stats,
            'recentPayments' => $recentPayments,
            'topDebtors' => $topDebtors,
            'paymentTrends' => $paymentTrends,
            'collectionsByMethod' => $collectionsByMethod,
            'years' => $this->getAvailableYears(),
            'selectedYear' => $year,
        ]);
    }

    // ========================================
    // Collection Reports
    // ========================================

    /**
     * Display collection summary report with date range and year filters.
     */
    public function collectionSummary(Request $request): View
    {
        Gate::authorize('view-fee-reports');

        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'year' => ['nullable', 'integer'],
        ]);

        $year = $request->filled('year') ? (int) $request->year : null;

        // Default to current month if no dates provided
        $startDate = $request->filled('start_date')
            ? $request->start_date
            : now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date')
            ? $request->end_date
            : now()->endOfMonth()->format('Y-m-d');

        $summary = $this->reportingService->getCollectionSummary($startDate, $endDate, $year);
        $collectionsByMethod = $this->reportingService->getCollectionsByMethod($year, $startDate, $endDate);

        return view('fees.reports.collection-summary', [
            'summary' => $summary,
            'collectionsByMethod' => $collectionsByMethod,
            'years' => $this->getAvailableYears(),
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'year' => $year,
            ],
        ]);
    }

    // ========================================
    // Student Statement
    // ========================================

    /**
     * Display student statement with search and year filter.
     */
    public function studentStatement(Request $request): View
    {
        Gate::authorize('view-fee-reports');

        $student = null;
        $statement = null;
        $year = $request->filled('year') ? (int) $request->year : null;

        if ($request->filled('student_id')) {
            $student = Student::with(['currentGrade', 'sponsor'])->find($request->student_id);

            if ($student) {
                $statement = $this->reportingService->getStudentStatement($student->id, $year);
            }
        }

        return view('fees.reports.student-statement', [
            'student' => $student,
            'statement' => $statement,
            'years' => $this->getAvailableYears(),
            'filters' => [
                'student_id' => $request->student_id,
                'year' => $year,
            ],
        ]);
    }

    /**
     * Generate PDF statement for a student.
     */
    public function studentStatementPdf(Request $request, Student $student): Response
    {
        Gate::authorize('view-fee-reports');

        $year = $request->filled('year') ? (int) $request->year : null;

        $student->load(['currentGrade', 'sponsor']);
        $statement = $this->reportingService->getStudentStatement($student->id, $year);
        $school = SchoolSetup::first();

        $pdf = Pdf::loadView('fees.reports.student-statement-pdf', [
            'student' => $student,
            'statement' => $statement,
            'school' => $school,
            'year' => $year,
        ]);

        $pdf->setPaper('A4', 'portrait');

        // Set font for DejaVu Sans (broad character support)
        $pdf->setOption('defaultFont', 'DejaVu Sans');

        $filename = "statement-{$student->student_number}";
        if ($year) {
            $filename .= "-{$year}";
        }
        $filename .= '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Search for students (AJAX autocomplete for statement page).
     */
    public function searchStudent(Request $request): JsonResponse
    {
        Gate::authorize('view-fee-reports');

        $request->validate([
            'search' => ['required', 'string', 'min:2'],
        ]);

        $search = $request->search;

        $students = Student::with(['currentGrade'])
            ->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "{$search}%");
            })
            ->where('status', Student::STATUS_CURRENT)
            ->limit(20)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->full_name,
                    'student_number' => $student->student_number,
                    'grade_name' => $student->currentGrade?->name ?? 'N/A',
                ];
            });

        return response()->json($students);
    }

    // ========================================
    // Outstanding Balances Reports
    // ========================================

    /**
     * Display outstanding balances grouped by grade.
     */
    public function outstandingByGrade(Request $request): View
    {
        Gate::authorize('view-fee-reports');

        $request->validate([
            'year' => ['nullable', 'integer'],
        ]);

        $year = $request->filled('year')
            ? (int) $request->year
            : (int) date('Y');

        $outstandingByGrade = $this->reportingService->getOutstandingByGrade($year);

        // Calculate totals
        $totalStudents = 0;
        $totalOutstanding = '0.00';
        foreach ($outstandingByGrade as $grade) {
            $totalStudents += $grade['student_count'];
            $totalOutstanding = bcadd($totalOutstanding, $grade['total_outstanding'], 2);
        }

        return view('fees.reports.outstanding-by-grade', [
            'outstandingByGrade' => $outstandingByGrade,
            'totalStudents' => $totalStudents,
            'totalOutstanding' => $totalOutstanding,
            'years' => $this->getAvailableYears(),
            'filters' => [
                'year' => $year,
            ],
        ]);
    }

    /**
     * Display aging report with 30/60/90 day buckets.
     */
    public function agingReport(Request $request): View
    {
        Gate::authorize('view-fee-reports');

        $request->validate([
            'year' => ['nullable', 'integer'],
        ]);

        $year = $request->filled('year')
            ? (int) $request->year
            : (int) date('Y');

        $agingReport = $this->reportingService->getAgingReport($year);

        return view('fees.reports.aging-report', [
            'agingReport' => $agingReport,
            'agingSummary' => $agingReport['summary'] ?? [],
            'agingDetails' => $agingReport['details'] ?? [],
            'years' => $this->getAvailableYears(),
            'filters' => [
                'year' => $year,
            ],
        ]);
    }

    /**
     * Display list of debtors with filters.
     */
    public function debtorsList(Request $request): View
    {
        Gate::authorize('view-fee-reports');

        $request->validate([
            'year' => ['nullable', 'integer'],
            'grade_id' => ['nullable', 'integer', 'exists:grades,id'],
            'min_balance' => ['nullable', 'numeric', 'min:0'],
        ]);

        $year = $request->filled('year')
            ? (int) $request->year
            : (int) date('Y');
        $gradeId = $request->filled('grade_id') ? $request->grade_id : null;
        $minBalance = $request->filled('min_balance') ? (float) $request->min_balance : null;

        $debtors = $this->reportingService->getDebtorsList($year, $gradeId, $minBalance);

        // Calculate totals
        $totalBalance = '0.00';
        foreach ($debtors as $debtor) {
            $totalBalance = bcadd($totalBalance, $debtor['balance'], 2);
        }

        return view('fees.reports.debtors-list', [
            'debtors' => $debtors,
            'totalBalance' => $totalBalance,
            'years' => $this->getAvailableYears(),
            'grades' => Grade::where('active', true)->orderBy('name')->get(),
            'filters' => [
                'year' => $year,
                'grade_id' => $gradeId,
                'min_balance' => $minBalance,
            ],
        ]);
    }

    // ========================================
    // Analytics Reports
    // ========================================

    /**
     * Display collector performance report.
     */
    public function collectorPerformance(Request $request): View
    {
        Gate::authorize('view-fee-reports');

        $request->validate([
            'year' => ['nullable', 'integer'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $year = $request->filled('year')
            ? (int) $request->year
            : (int) date('Y');
        $startDate = $request->filled('start_date') ? $request->start_date : null;
        $endDate = $request->filled('end_date') ? $request->end_date : null;

        $performance = $this->reportingService->getCollectorPerformance($year, $startDate, $endDate);

        // Calculate totals
        $totalCollected = '0.00';
        $totalPayments = 0;
        foreach ($performance as $collector) {
            $totalCollected = bcadd($totalCollected, $collector['total_collected'], 2);
            $totalPayments += $collector['payment_count'];
        }

        return view('fees.reports.collector-performance', [
            'collectors' => $performance,
            'totalCollected' => $totalCollected,
            'totalPayments' => $totalPayments,
            'years' => $this->getAvailableYears(),
            'filters' => [
                'year' => $year,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Display payment trends report.
     */
    public function paymentTrends(Request $request): View
    {
        Gate::authorize('view-fee-reports');

        $request->validate([
            'year' => ['nullable', 'integer'],
            'group_by' => ['nullable', 'string', 'in:day,week,month'],
        ]);

        $year = $request->filled('year')
            ? (int) $request->year
            : (int) date('Y');
        $groupBy = $request->filled('group_by') ? $request->group_by : 'week';

        $trends = $this->reportingService->getPaymentTrends($year, $groupBy);

        // Calculate summary stats
        $totalCollected = collect($trends)->sum('total_amount');
        $highestDay = collect($trends)->max('total_amount');
        $daysWithData = count($trends) > 0 ? count($trends) : 1;
        $averageDaily = $totalCollected / $daysWithData;

        // Get grade comparison if year is specified
        $gradeComparison = $year ? $this->reportingService->getGradeComparison($year) : [];

        return view('fees.reports.payment-trends', [
            'paymentTrends' => $trends,
            'gradeComparison' => $gradeComparison,
            'summary' => [
                'total_collected' => $totalCollected,
                'highest_day' => $highestDay,
                'average_daily' => $averageDaily,
            ],
            'years' => $this->getAvailableYears(),
            'filters' => [
                'year' => $year,
                'group_by' => $groupBy,
            ],
        ]);
    }

    // ========================================
    // Excel Export Methods
    // ========================================

    /**
     * Export collection summary to Excel.
     */
    public function exportCollectionSummary(Request $request): BinaryFileResponse
    {
        Gate::authorize('export-fee-reports');

        $year = $request->filled('year') ? (int) $request->year : null;

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date')
            ? $request->end_date
            : now()->endOfMonth()->format('Y-m-d');

        $summary = $this->reportingService->getCollectionSummary($startDate, $endDate, $year);
        $byMethod = $this->reportingService->getCollectionsByMethod($year, $startDate, $endDate);

        $filename = "collection-summary-{$startDate}-to-{$endDate}.xlsx";

        return Excel::download(
            new CollectionSummaryExport($summary, $byMethod, $startDate, $endDate),
            $filename
        );
    }

    /**
     * Export outstanding by grade to Excel.
     */
    public function exportOutstandingByGrade(Request $request): BinaryFileResponse
    {
        Gate::authorize('export-fee-reports');

        $year = $request->filled('year')
            ? (int) $request->year
            : (int) date('Y');

        $data = $this->reportingService->getOutstandingByGrade($year);

        $filename = "outstanding-by-grade-{$year}.xlsx";

        return Excel::download(new OutstandingByGradeExport($data, (string) $year), $filename);
    }

    /**
     * Export aging report to Excel.
     */
    public function exportAgingReport(Request $request): BinaryFileResponse
    {
        Gate::authorize('export-fee-reports');

        $year = $request->filled('year')
            ? (int) $request->year
            : (int) date('Y');

        $data = $this->reportingService->getAgingReport($year);

        $filename = "aging-report-{$year}.xlsx";

        return Excel::download(new AgingReportExport($data, (string) $year), $filename);
    }

    /**
     * Export debtors list to Excel.
     */
    public function exportDebtorsList(Request $request): BinaryFileResponse
    {
        Gate::authorize('export-fee-reports');

        $year = $request->filled('year')
            ? (int) $request->year
            : (int) date('Y');
        $gradeId = $request->filled('grade_id') ? $request->grade_id : null;
        $minBalance = $request->filled('min_balance') ? (float) $request->min_balance : null;

        $debtors = $this->reportingService->getDebtorsList($year, $gradeId, $minBalance);

        $filename = "debtors-list-{$year}.xlsx";

        return Excel::download(new DebtorsListExport($debtors->toArray(), (string) $year), $filename);
    }

    /**
     * Export collector performance to Excel.
     */
    public function exportCollectorPerformance(Request $request): BinaryFileResponse
    {
        Gate::authorize('export-fee-reports');

        $year = $request->filled('year')
            ? (int) $request->year
            : (int) date('Y');
        $startDate = $request->filled('start_date') ? $request->start_date : null;
        $endDate = $request->filled('end_date') ? $request->end_date : null;

        $performance = $this->reportingService->getCollectorPerformance($year, $startDate, $endDate);

        $filename = "collector-performance-{$year}.xlsx";

        return Excel::download(new CollectorPerformanceExport($performance, (string) $year), $filename);
    }

    /**
     * Export student statement to Excel.
     */
    public function exportStudentStatement(Student $student, Request $request): BinaryFileResponse
    {
        Gate::authorize('export-fee-reports');

        $year = $request->filled('year') ? (int) $request->year : null;

        $statement = $this->reportingService->getStudentStatement($student->id, $year);

        $studentName = $student->full_name ?? ($student->first_name . ' ' . $student->last_name);
        $filename = "statement-{$student->student_number}.xlsx";

        return Excel::download(new StudentStatementExport($statement, $studentName), $filename);
    }

    // ========================================
    // Daily Operations Reports
    // ========================================

    /**
     * Display daily collections report.
     */
    public function dailyCollections(Request $request): View
    {
        Gate::authorize('view-fee-reports');

        $request->validate([
            'date' => ['nullable', 'date'],
            'year' => ['nullable', 'integer'],
        ]);

        $date = $request->filled('date') ? $request->date : now()->format('Y-m-d');
        $year = $request->filled('year') ? (int) $request->year : null;

        $collections = $this->reportingService->getDailyCollections($date, $year);

        return view('fees.reports.daily-collections', [
            'collections' => $collections,
            'years' => $this->getAvailableYears(),
            'filters' => [
                'date' => $date,
                'year' => $year,
            ],
        ]);
    }

    /**
     * Display end-of-day report.
     */
    public function endOfDayReport(Request $request): View
    {
        Gate::authorize('view-fee-reports');

        $request->validate([
            'date' => ['nullable', 'date'],
            'year' => ['nullable', 'integer'],
        ]);

        $date = $request->filled('date') ? $request->date : now()->format('Y-m-d');
        $year = $request->filled('year') ? (int) $request->year : null;

        $report = $this->reportingService->getEndOfDayReport($date, $year);

        return view('fees.reports.end-of-day-report', [
            'report' => $report,
            'years' => $this->getAvailableYears(),
            'filters' => [
                'date' => $date,
                'year' => $year,
            ],
        ]);
    }

    /**
     * Generate end-of-day report as PDF.
     */
    public function endOfDayReportPdf(Request $request): Response
    {
        Gate::authorize('view-fee-reports');

        $date = $request->filled('date') ? $request->date : now()->format('Y-m-d');
        $year = $request->filled('year') ? (int) $request->year : null;

        $report = $this->reportingService->getEndOfDayReport($date, $year);
        $school = SchoolSetup::first();

        $pdf = Pdf::loadView('fees.reports.end-of-day-pdf', [
            'report' => $report,
            'school' => $school,
            'year' => $year,
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('defaultFont', 'DejaVu Sans');

        $filename = "end-of-day-report-{$date}.pdf";

        return $pdf->stream($filename);
    }

    /**
     * Export daily collections to Excel.
     */
    public function exportDailyCollections(Request $request): BinaryFileResponse
    {
        Gate::authorize('export-fee-reports');

        $date = $request->filled('date') ? $request->date : now()->format('Y-m-d');
        $year = $request->filled('year') ? (int) $request->year : null;

        $collections = $this->reportingService->getDailyCollections($date, $year);

        $filename = "daily-collections-{$date}.xlsx";

        return Excel::download(new DailyCollectionsExport($collections), $filename);
    }

    /**
     * Get available years.
     *
     * @return \Illuminate\Support\Collection
     */
    /**
     * Get available years from the terms table.
     */
    protected function getAvailableYears()
    {
        return Term::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
    }
}
