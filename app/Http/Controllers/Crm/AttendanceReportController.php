<?php

namespace App\Http\Controllers\Crm;

use App\Exports\AttendanceMonthlyExport;
use App\Exports\AttendanceReportExport;
use App\Models\CrmAttendanceDevice;
use App\Models\CrmUserDepartment;
use App\Services\Crm\AttendanceReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttendanceReportController extends CrmController
{
    public function __construct(
        private readonly AttendanceReportService $reportService
    ) {
    }

    public function index(): View
    {
        $this->authorizeModuleAccess('attendance', 'view');

        $crmUser = $this->crmUser();
        abort_if($crmUser->isRep(), 403);

        $todayStats = $this->reportService->todayStats();

        return view('crm.attendance.reports.index', compact('todayStats'));
    }

    public function show(Request $request, string $type): View
    {
        $this->authorizeModuleAccess('attendance', 'view');
        abort_if($this->crmUser()->isRep(), 403);

        $departments = CrmUserDepartment::where('is_active', true)->orderBy('name')->get();
        $devices = CrmAttendanceDevice::where('is_active', true)->orderBy('name')->get();

        $filters = [
            'department_id' => $request->query('department_id'),
            'date' => $request->query('date', now()->toDateString()),
            'month' => $request->query('month', now()->format('Y-m')),
            'from' => $request->query('from', now()->startOfMonth()->toDateString()),
            'to' => $request->query('to', now()->toDateString()),
            'device_id' => $request->query('device_id'),
        ];

        $data = $this->generateReport($type, $filters);

        return view('crm.attendance.reports.show', compact('type', 'data', 'filters', 'departments', 'devices'));
    }

    public function export(Request $request, string $type): BinaryFileResponse
    {
        $this->authorizeModuleAccess('attendance', 'view');
        abort_if($this->crmUser()->isRep(), 403);

        $filters = [
            'department_id' => $request->query('department_id'),
            'date' => $request->query('date', now()->toDateString()),
            'month' => $request->query('month', now()->format('Y-m')),
            'from' => $request->query('from', now()->startOfMonth()->toDateString()),
            'to' => $request->query('to', now()->toDateString()),
            'device_id' => $request->query('device_id'),
        ];

        $filename = 'attendance-' . $type . '-' . now()->format('Y-m-d-His') . '.xlsx';

        return match ($type) {
            'daily-summary' => $this->exportDailySummary($filters, $filename),
            'monthly-register' => $this->exportMonthlyRegister($filters, $filename),
            'hours-worked' => $this->exportHoursWorked($filters, $filename),
            'late-arrivals' => $this->exportLateArrivals($filters, $filename),
            'absenteeism' => $this->exportAbsenteeism($filters, $filename),
            'biometric-audit' => $this->exportBiometricAudit($filters, $filename),
            default => abort(404, 'Unknown report type.'),
        };
    }

    private function generateReport(string $type, array $filters): array
    {
        return match ($type) {
            'daily-summary' => [
                'title' => 'Daily Attendance Summary',
                'rows' => $this->reportService->dailySummary(Carbon::parse($filters['date']), $filters),
            ],
            'monthly-register' => [
                'title' => 'Monthly Attendance Register',
                'register' => $this->reportService->monthlyRegister(
                    Carbon::parse($filters['month'] . '-01'),
                    $filters['department_id'] ? (int) $filters['department_id'] : null
                ),
            ],
            'hours-worked' => [
                'title' => 'Hours Worked Summary',
                'rows' => $this->reportService->hoursWorked(Carbon::parse($filters['from']), Carbon::parse($filters['to']), $filters),
            ],
            'late-arrivals' => [
                'title' => 'Late Arrivals Report',
                'rows' => $this->reportService->lateArrivals(Carbon::parse($filters['from']), Carbon::parse($filters['to']), $filters),
            ],
            'absenteeism' => [
                'title' => 'Absenteeism Report',
                'rows' => $this->reportService->absenteeism(Carbon::parse($filters['from']), Carbon::parse($filters['to']), $filters),
            ],
            'biometric-audit' => [
                'title' => 'Biometric Audit Log',
                'rows' => $this->reportService->biometricAudit(
                    Carbon::parse($filters['from']),
                    Carbon::parse($filters['to']),
                    $filters['device_id'] ? (int) $filters['device_id'] : null
                ),
            ],
            default => abort(404, 'Unknown report type.'),
        };
    }

    private function exportDailySummary(array $filters, string $filename): BinaryFileResponse
    {
        $rows = $this->reportService->dailySummary(Carbon::parse($filters['date']), $filters);

        return Excel::download(
            new AttendanceReportExport(
                ['Name', 'Department', 'Code', 'Code Label', 'Clock In', 'Clock Out', 'Hours', 'Source', 'Late', 'Early Out'],
                $rows->map(fn ($r) => [
                    $r['user_name'], $r['department'], $r['code'], $r['code_label'],
                    $r['clocked_in'], $r['clocked_out'], $r['total_hours'], $r['source'],
                    $r['is_late'] ? 'Yes' : 'No', $r['is_early_out'] ? 'Yes' : 'No',
                ])
            ),
            $filename
        );
    }

    private function exportMonthlyRegister(array $filters, string $filename): BinaryFileResponse
    {
        $register = $this->reportService->monthlyRegister(
            Carbon::parse($filters['month'] . '-01'),
            $filters['department_id'] ? (int) $filters['department_id'] : null
        );

        return Excel::download(new AttendanceMonthlyExport($register), $filename);
    }

    private function exportHoursWorked(array $filters, string $filename): BinaryFileResponse
    {
        $rows = $this->reportService->hoursWorked(Carbon::parse($filters['from']), Carbon::parse($filters['to']), $filters);

        return Excel::download(
            new AttendanceReportExport(
                ['Name', 'Department', 'Working Days', 'Total Hours', 'Overtime Hours', 'Avg Daily Hours'],
                $rows->map(fn ($r) => [$r['user_name'], $r['department'], $r['working_days'], $r['total_hours'], $r['overtime_hours'], $r['average_daily_hours']])
            ),
            $filename
        );
    }

    private function exportLateArrivals(array $filters, string $filename): BinaryFileResponse
    {
        $rows = $this->reportService->lateArrivals(Carbon::parse($filters['from']), Carbon::parse($filters['to']), $filters);

        return Excel::download(
            new AttendanceReportExport(
                ['Name', 'Department', 'Date', 'Clock In', 'Code'],
                $rows->map(fn ($r) => [$r['user_name'], $r['department'], $r['date'], $r['clocked_in'], $r['code']])
            ),
            $filename
        );
    }

    private function exportAbsenteeism(array $filters, string $filename): BinaryFileResponse
    {
        $rows = $this->reportService->absenteeism(Carbon::parse($filters['from']), Carbon::parse($filters['to']), $filters);

        return Excel::download(
            new AttendanceReportExport(
                ['Name', 'Department', 'Absent Days', 'Dates'],
                $rows->map(fn ($r) => [$r['user_name'], $r['department'], $r['absent_days'], $r['dates']])
            ),
            $filename
        );
    }

    private function exportBiometricAudit(array $filters, string $filename): BinaryFileResponse
    {
        $rows = $this->reportService->biometricAudit(
            Carbon::parse($filters['from']),
            Carbon::parse($filters['to']),
            $filters['device_id'] ? (int) $filters['device_id'] : null
        );

        return Excel::download(
            new AttendanceReportExport(
                ['Device', 'Employee ID', 'Event', 'Captured At', 'Method', 'Confidence', 'Status', 'Matched User', 'Error'],
                $rows->map(fn ($r) => [
                    $r['device_name'], $r['employee_identifier'], $r['event_type'], $r['captured_at'],
                    $r['verification_method'], $r['confidence'], $r['status'], $r['matched_user'], $r['error'],
                ])
            ),
            $filename
        );
    }
}
