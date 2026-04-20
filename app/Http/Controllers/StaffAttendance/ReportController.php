<?php

namespace App\Http\Controllers\StaffAttendance;

use App\Exports\StaffAttendance\AbsenteeismExport;
use App\Exports\StaffAttendance\DailyAttendanceExport;
use App\Exports\StaffAttendance\DepartmentAttendanceExport;
use App\Exports\StaffAttendance\HoursWorkedExport;
use App\Exports\StaffAttendance\MonthlyAttendanceExport;
use App\Exports\StaffAttendance\PunctualityExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StaffAttendance\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Controller for staff attendance reports.
 *
 * Provides HR interface for comprehensive attendance reports including daily,
 * monthly, department comparison, punctuality, absenteeism, and hours worked.
 */
class ReportController extends Controller {
    /**
     * The report service instance.
     *
     * @var ReportService
     */
    protected ReportService $reportService;

    /**
     * Create a new controller instance.
     *
     * @param ReportService $reportService
     */
    public function __construct(ReportService $reportService) {
        $this->middleware('auth');
        $this->reportService = $reportService;
    }

    /**
     * Get distinct departments for filter dropdown.
     *
     * @return array
     */
    protected function getDepartments(): array {
        return User::where('status', 'Current')
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department')
            ->toArray();
    }

    /**
     * Get current staff for filter dropdown.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getStaff() {
        return User::where('status', 'Current')
            ->orderBy('firstname')
            ->get(['id', 'firstname', 'lastname']);
    }

    /**
     * Display the daily attendance report.
     *
     * @param Request $request
     * @return View
     */
    public function daily(Request $request): View {
        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();
        $department = $request->input('department');
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;

        $records = $this->reportService->getDailyReport($date, $department, $userId);
        $departments = $this->getDepartments();
        $staff = $this->getStaff();

        return view('staff-attendance.reports.daily', compact(
            'records', 'departments', 'staff', 'date', 'department', 'userId'
        ));
    }

    /**
     * Display the monthly summary report.
     *
     * @param Request $request
     * @return View
     */
    public function monthly(Request $request): View {
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (int) $request->input('month', Carbon::now()->month);
        $department = $request->input('department');
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;

        $records = $this->reportService->getMonthlyReport($year, $month, $department, $userId);
        $departments = $this->getDepartments();
        $staff = $this->getStaff();

        // Summary totals
        $totals = [
            'days_present' => $records->sum('days_present'),
            'days_absent' => $records->sum('days_absent'),
            'days_late' => $records->sum('days_late'),
            'days_on_leave' => $records->sum('days_on_leave'),
            'total_hours' => $records->sum('total_hours'),
        ];

        return view('staff-attendance.reports.monthly', compact(
            'records', 'departments', 'staff', 'year', 'month', 'department', 'userId', 'totals'
        ));
    }

    /**
     * Display the department comparison report.
     *
     * @param Request $request
     * @return View
     */
    public function department(Request $request): View {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::now()->endOfMonth();

        $records = $this->reportService->getDepartmentReport($startDate, $endDate);

        return view('staff-attendance.reports.department', compact(
            'records', 'startDate', 'endDate'
        ));
    }

    /**
     * Display the punctuality analysis report.
     *
     * @param Request $request
     * @return View
     */
    public function punctuality(Request $request): View {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::now()->endOfMonth();
        $department = $request->input('department');
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;

        $records = $this->reportService->getPunctualityReport($startDate, $endDate, $department, $userId);
        $departments = $this->getDepartments();
        $staff = $this->getStaff();

        return view('staff-attendance.reports.punctuality', compact(
            'records', 'departments', 'staff', 'startDate', 'endDate', 'department', 'userId'
        ));
    }

    /**
     * Display the absenteeism patterns report.
     *
     * @param Request $request
     * @return View
     */
    public function absenteeism(Request $request): View {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::now()->endOfMonth();
        $department = $request->input('department');
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;

        $records = $this->reportService->getAbsenteeismReport($startDate, $endDate, $department, $userId);
        $departments = $this->getDepartments();
        $staff = $this->getStaff();

        return view('staff-attendance.reports.absenteeism', compact(
            'records', 'departments', 'staff', 'startDate', 'endDate', 'department', 'userId'
        ));
    }

    /**
     * Display the hours worked summary report.
     *
     * @param Request $request
     * @return View
     */
    public function hoursWorked(Request $request): View {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::now()->endOfMonth();
        $department = $request->input('department');
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;

        $records = $this->reportService->getHoursWorkedReport($startDate, $endDate, $department, $userId);
        $departments = $this->getDepartments();
        $staff = $this->getStaff();

        // Summary totals
        $totals = [
            'total_hours' => $records->sum('total_hours'),
            'overtime_hours' => $records->sum('overtime_hours'),
        ];

        return view('staff-attendance.reports.hours-worked', compact(
            'records', 'departments', 'staff', 'startDate', 'endDate', 'department', 'userId', 'totals'
        ));
    }

    // ==================== EXPORT METHODS ====================

    /**
     * Export daily attendance report to Excel.
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportDaily(Request $request): BinaryFileResponse {
        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();
        $department = $request->input('department');
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;

        $records = $this->reportService->getDailyReport($date, $department, $userId);
        $filename = 'daily-attendance-' . $date->format('Y-m-d') . '.xlsx';

        return Excel::download(new DailyAttendanceExport($records, $date), $filename);
    }

    /**
     * Export monthly summary report to Excel.
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportMonthly(Request $request): BinaryFileResponse {
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (int) $request->input('month', Carbon::now()->month);
        $department = $request->input('department');
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;

        $records = $this->reportService->getMonthlyReport($year, $month, $department, $userId);
        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $filename = 'monthly-attendance-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.xlsx';

        return Excel::download(new MonthlyAttendanceExport($records, $year, $month), $filename);
    }

    /**
     * Export department comparison report to Excel.
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportDepartment(Request $request): BinaryFileResponse {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::now()->endOfMonth();

        $records = $this->reportService->getDepartmentReport($startDate, $endDate);
        $filename = 'department-attendance-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.xlsx';

        return Excel::download(new DepartmentAttendanceExport($records, $startDate, $endDate), $filename);
    }

    /**
     * Export punctuality report to Excel.
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportPunctuality(Request $request): BinaryFileResponse {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::now()->endOfMonth();
        $department = $request->input('department');
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;

        $records = $this->reportService->getPunctualityReport($startDate, $endDate, $department, $userId);
        $filename = 'punctuality-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.xlsx';

        return Excel::download(new PunctualityExport($records, $startDate, $endDate), $filename);
    }

    /**
     * Export absenteeism report to Excel.
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportAbsenteeism(Request $request): BinaryFileResponse {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::now()->endOfMonth();
        $department = $request->input('department');
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;

        $records = $this->reportService->getAbsenteeismReport($startDate, $endDate, $department, $userId);
        $filename = 'absenteeism-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.xlsx';

        return Excel::download(new AbsenteeismExport($records, $startDate, $endDate), $filename);
    }

    /**
     * Export hours worked report to Excel.
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportHoursWorked(Request $request): BinaryFileResponse {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::now()->endOfMonth();
        $department = $request->input('department');
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;

        $records = $this->reportService->getHoursWorkedReport($startDate, $endDate, $department, $userId);
        $filename = 'hours-worked-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.xlsx';

        return Excel::download(new HoursWorkedExport($records, $startDate, $endDate), $filename);
    }
}
