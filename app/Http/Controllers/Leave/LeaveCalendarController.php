<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Leave\LeaveRequest;
use App\Services\Leave\PublicHolidayService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for leave calendar views.
 *
 * Provides personal calendar view for staff to visualize their leave
 * and public holidays using FullCalendar integration.
 * Also provides team calendar view for managers to see their direct reports' leave.
 */
class LeaveCalendarController extends Controller {
    /**
     * @var PublicHolidayService
     */
    protected PublicHolidayService $publicHolidayService;

    /**
     * Create a new controller instance.
     *
     * @param PublicHolidayService $publicHolidayService
     */
    public function __construct(PublicHolidayService $publicHolidayService) {
        $this->middleware('auth');
        $this->publicHolidayService = $publicHolidayService;
    }

    /**
     * Display personal leave calendar.
     *
     * @return View
     */
    public function personal(): View {
        return view('leave.calendar.personal');
    }

    /**
     * Get calendar events for personal calendar (AJAX).
     *
     * Returns user's approved/pending leave requests and public holidays
     * in FullCalendar-compatible JSON format.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function personalEvents(Request $request): JsonResponse {
        $start = $request->input('start');
        $end = $request->input('end');

        $events = [];

        // Get user's approved/pending leave requests in date range
        $leaveRequests = LeaveRequest::where('user_id', auth()->id())
            ->whereIn('status', [LeaveRequest::STATUS_APPROVED, LeaveRequest::STATUS_PENDING])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($subQuery) use ($start, $end) {
                        $subQuery->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->with('leaveType')
            ->get();

        foreach ($leaveRequests as $leaveRequest) {
            $events[] = [
                'id' => 'leave-' . $leaveRequest->id,
                'title' => $leaveRequest->leaveType->name ?? 'Leave',
                'start' => $leaveRequest->start_date->format('Y-m-d'),
                // FullCalendar end date is exclusive, so add 1 day
                'end' => $leaveRequest->end_date->addDay()->format('Y-m-d'),
                'color' => $leaveRequest->leaveType->color ?? '#3b82f6',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'leave',
                    'status' => $leaveRequest->status,
                    'days' => $leaveRequest->total_days,
                    'leaveType' => $leaveRequest->leaveType->name ?? 'Leave',
                    'requestId' => $leaveRequest->id,
                ],
                'classNames' => $leaveRequest->status === LeaveRequest::STATUS_PENDING ? ['pending-leave'] : [],
            ];
        }

        // Get public holidays in date range
        $startYear = (int) date('Y', strtotime($start));
        $endYear = (int) date('Y', strtotime($end));

        for ($year = $startYear; $year <= $endYear; $year++) {
            $holidays = $this->publicHolidayService->getActiveForYear($year);

            foreach ($holidays as $holiday) {
                $holidayDate = $holiday->is_recurring
                    ? $year . '-' . $holiday->date->format('m-d')
                    : $holiday->date->format('Y-m-d');

                // Only include holidays within the requested range
                if ($holidayDate >= $start && $holidayDate <= $end) {
                    $events[] = [
                        'id' => 'holiday-' . $holiday->id . '-' . $year,
                        'title' => $holiday->name,
                        'start' => $holidayDate,
                        'allDay' => true,
                        'color' => '#dc2626', // Red for holidays
                        'textColor' => '#ffffff',
                        'extendedProps' => [
                            'type' => 'holiday',
                            'description' => $holiday->description,
                        ],
                    ];
                }
            }
        }

        return response()->json($events);
    }

    /**
     * Display team leave calendar for managers.
     *
     * Shows calendar with all direct reports' approved/pending leave
     * and public holidays. Only accessible to users with approve-leave-requests gate.
     *
     * @return View
     */
    public function team(): View {
        $user = auth()->user();

        // Get direct reports for legend display
        $directReports = $user->subordinates()
            ->where('status', 'Current')
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->select('id', 'firstname', 'lastname')
            ->get();

        return view('leave.calendar.team', [
            'directReports' => $directReports,
        ]);
    }

    /**
     * Get calendar events for team calendar (AJAX).
     *
     * Returns all direct reports' approved/pending leave requests and public holidays
     * in FullCalendar-compatible JSON format.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function teamEvents(Request $request): JsonResponse {
        $start = $request->input('start');
        $end = $request->input('end');
        $user = auth()->user();

        $events = [];

        // Get IDs of direct reports
        $directReportIds = $user->subordinates()
            ->where('status', 'Current')
            ->pluck('id')
            ->toArray();

        // Get all direct reports' approved/pending leave requests in date range
        $leaveRequests = LeaveRequest::whereIn('user_id', $directReportIds)
            ->whereIn('status', [LeaveRequest::STATUS_APPROVED, LeaveRequest::STATUS_PENDING])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($subQuery) use ($start, $end) {
                        $subQuery->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->with(['leaveType', 'user'])
            ->get();

        foreach ($leaveRequests as $leaveRequest) {
            $events[] = [
                'id' => 'leave-' . $leaveRequest->id,
                'title' => ($leaveRequest->user->name ?? 'Unknown') . ' - ' . ($leaveRequest->leaveType->name ?? 'Leave'),
                'start' => $leaveRequest->start_date->format('Y-m-d'),
                // FullCalendar end date is exclusive, so add 1 day
                'end' => $leaveRequest->end_date->addDay()->format('Y-m-d'),
                'color' => $leaveRequest->leaveType->color ?? '#3b82f6',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'leave',
                    'status' => $leaveRequest->status,
                    'days' => $leaveRequest->total_days,
                    'leaveType' => $leaveRequest->leaveType->name ?? 'Leave',
                    'staffName' => $leaveRequest->user->name ?? 'Unknown',
                    'staffId' => $leaveRequest->user_id,
                    'requestId' => $leaveRequest->id,
                ],
                'classNames' => $leaveRequest->status === LeaveRequest::STATUS_PENDING ? ['pending-leave'] : [],
            ];
        }

        // Get public holidays in date range
        $startYear = (int) date('Y', strtotime($start));
        $endYear = (int) date('Y', strtotime($end));

        for ($year = $startYear; $year <= $endYear; $year++) {
            $holidays = $this->publicHolidayService->getActiveForYear($year);

            foreach ($holidays as $holiday) {
                $holidayDate = $holiday->is_recurring
                    ? $year . '-' . $holiday->date->format('m-d')
                    : $holiday->date->format('Y-m-d');

                // Only include holidays within the requested range
                if ($holidayDate >= $start && $holidayDate <= $end) {
                    $events[] = [
                        'id' => 'holiday-' . $holiday->id . '-' . $year,
                        'title' => $holiday->name,
                        'start' => $holidayDate,
                        'allDay' => true,
                        'color' => '#dc2626', // Red for holidays
                        'textColor' => '#ffffff',
                        'extendedProps' => [
                            'type' => 'holiday',
                            'description' => $holiday->description,
                        ],
                    ];
                }
            }
        }

        return response()->json($events);
    }
}
