<?php

namespace App\Http\Controllers\StaffAttendance;

use App\Http\Controllers\Controller;
use App\Models\Leave\PublicHoliday;
use App\Models\StaffAttendance\StaffAttendanceCode;
use App\Models\StaffAttendance\StaffAttendanceRecord;
use App\Models\User;
use App\Services\StaffAttendance\ManualAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Controller for manual attendance register.
 *
 * Provides a weekly grid view for HR to mark attendance manually.
 * Supports batch saving of multiple entries at once.
 */
class ManualRegisterController extends Controller
{
    /**
     * The manual attendance service instance.
     *
     * @var ManualAttendanceService
     */
    protected ManualAttendanceService $service;

    /**
     * Create a new controller instance.
     *
     * @param ManualAttendanceService $service
     */
    public function __construct(ManualAttendanceService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    /**
     * Display the manual attendance register.
     *
     * Shows a weekly grid with staff in rows and days in columns.
     * Allows filtering by department and navigating between weeks.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        Gate::authorize('manage-staff-attendance-register');

        // Get unique departments from current staff
        $departments = User::whereNotNull('department')
            ->where('status', 'Current')
            ->distinct()
            ->pluck('department')
            ->sort()
            ->values();

        // Get active attendance codes for the dropdown
        $codes = StaffAttendanceCode::active()->ordered()->get();

        // Parse week start from request or default to current week
        $weekStart = $request->input('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek()
            : Carbon::now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        // Department filter
        $departmentFilter = $request->input('department');

        // Build staff query
        $staffQuery = User::where('status', 'Current')
            ->where('active', true)
            ->where('position', '!=', 'External Support') // Exclude external support staff
            ->orderBy('lastname')
            ->orderBy('firstname');

        // Apply department filter if provided
        if ($departmentFilter) {
            $staffQuery->where('department', $departmentFilter);
        }

        $staff = $staffQuery->get();

        // Load attendance records for the week
        $attendances = StaffAttendanceRecord::with('attendanceCode')
            ->betweenDates($weekStart, $weekEnd)
            ->get()
            ->keyBy(fn($r) => $r->user_id . '_' . $r->date->format('Y-m-d'));

        // Identify leave-protected cells (leave-synced records that should not be manually edited)
        $leaveProtectedCells = $attendances
            ->filter(fn($r) => $r->entry_type === StaffAttendanceRecord::ENTRY_LEAVE_SYNC && $r->leave_request_id !== null)
            ->map(fn($r) => $r->user_id . '_' . $r->date->format('Y-m-d'))
            ->values()
            ->toArray();

        // Get absent code ID for calculating absent days
        $absentCode = StaffAttendanceCode::where('code', 'A')->first();
        $absentCodeId = $absentCode?->id;

        // Generate weekdays array (Mon-Fri)
        $weekdays = [];
        $current = $weekStart->copy();
        while ($current <= $weekEnd) {
            if ($current->isWeekday()) {
                $weekdays[] = [
                    'date' => $current->format('Y-m-d'),
                    'day' => $current->format('D'),
                    'display' => $current->format('D j'),
                    'isToday' => $current->isToday(),
                ];
            }
            $current->addDay();
        }

        // Calculate total work days for the YEAR (Jan 1 to today)
        // Excludes weekends and public holidays
        $yearStart = Carbon::now()->startOfYear();
        $today = Carbon::today();

        // Get all public holidays for the year
        $publicHolidays = PublicHoliday::active()
            ->forYear($yearStart->year)
            ->get()
            ->map(function ($holiday) use ($yearStart) {
                // For recurring holidays, use current year's date
                if ($holiday->is_recurring) {
                    return Carbon::create($yearStart->year, $holiday->date->month, $holiday->date->day)->format('Y-m-d');
                }
                return $holiday->date->format('Y-m-d');
            })
            ->toArray();

        // Count weekdays from Jan 1 to today, minus public holidays
        $totalWorkDays = 0;
        $currentDay = $yearStart->copy();
        while ($currentDay->lte($today)) {
            if ($currentDay->isWeekday() && !in_array($currentDay->format('Y-m-d'), $publicHolidays)) {
                $totalWorkDays++;
            }
            $currentDay->addDay();
        }

        // Calculate absent days per staff member for the YEAR
        // Excludes days marked as leave (leave_request_id is not null)
        $staffAbsences = [];
        if ($absentCodeId) {
            $staffAbsences = StaffAttendanceRecord::whereBetween('date', [$yearStart, $today])
                ->where('attendance_code_id', $absentCodeId)
                ->whereNull('leave_request_id') // Exclude leave days
                ->selectRaw('user_id, COUNT(*) as absent_count')
                ->groupBy('user_id')
                ->pluck('absent_count', 'user_id')
                ->toArray();
        }

        // Ensure all staff members have an entry (default to 0 if no absences)
        foreach ($staff as $member) {
            if (!isset($staffAbsences[$member->id])) {
                $staffAbsences[$member->id] = 0;
            }
        }

        // Calculate stats for TODAY only (weekly analysis belongs in reports)
        // Only count records for the staff currently being displayed
        $today = Carbon::today();
        $staffIds = $staff->pluck('id')->toArray();

        $todayRecords = StaffAttendanceRecord::forDate($today)
            ->whereIn('user_id', $staffIds)
            ->get();

        // Count on_leave: includes full-day leave AND half-day leave (has leave_request_id)
        $onLeaveCount = $todayRecords->filter(function ($record) {
            return $record->status === StaffAttendanceRecord::STATUS_ON_LEAVE
                || ($record->status === StaffAttendanceRecord::STATUS_HALF_DAY && $record->leave_request_id !== null);
        })->count();

        $stats = [
            'present' => $todayRecords->where('status', StaffAttendanceRecord::STATUS_PRESENT)->count(),
            'absent' => $todayRecords->where('status', StaffAttendanceRecord::STATUS_ABSENT)->count(),
            'late' => $todayRecords->where('status', StaffAttendanceRecord::STATUS_LATE)->count(),
            'on_leave' => $onLeaveCount,
            'total_staff' => $staff->count(),
        ];

        return view('staff-attendance.manual-register.index', compact(
            'departments',
            'codes',
            'weekStart',
            'weekEnd',
            'departmentFilter',
            'staff',
            'attendances',
            'weekdays',
            'stats',
            'staffAbsences',
            'totalWorkDays',
            'leaveProtectedCells'
        ));
    }

    /**
     * Batch update attendance records.
     *
     * Accepts an array of attendance entries and saves them atomically.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        Gate::authorize('manage-staff-attendance-register');

        $validated = $request->validate([
            'attendances' => 'required|array',
            'attendances.*.user_id' => 'required|exists:users,id',
            'attendances.*.date' => 'required|date',
            'attendances.*.attendance_code_id' => 'nullable|exists:staff_attendance_codes,id',
            'attendances.*.notes' => 'nullable|string|max:500',
        ]);

        $count = $this->service->batchSave($validated['attendances'], auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Attendance saved successfully',
            'count' => $count,
        ]);
    }
}
