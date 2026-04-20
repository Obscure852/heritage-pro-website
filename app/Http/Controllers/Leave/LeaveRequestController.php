<?php

namespace App\Http\Controllers\Leave;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\StoreLeaveRequestRequest;
use App\Models\Leave\LeaveAttachment;
use App\Models\Leave\LeaveRequest;
use App\Models\Leave\LeaveType;
use App\Models\Term;
use App\Services\Leave\LeaveApprovalService;
use App\Services\Leave\LeaveAuditService;
use App\Services\Leave\LeaveBalanceService;
use App\Services\Leave\LeaveCalculationService;
use App\Services\Leave\LeaveRequestService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Controller for leave request submission, viewing, and approval workflow.
 *
 * Handles the complete leave request lifecycle from submission to approval/rejection.
 * Integrates with LeaveRequestService for business logic and LeaveApprovalService for approvals.
 * Also provides team history view for managers to see their direct reports' requests.
 */
class LeaveRequestController extends Controller {
    /**
     * @var LeaveRequestService
     */
    protected LeaveRequestService $leaveRequestService;

    /**
     * @var LeaveApprovalService
     */
    protected LeaveApprovalService $leaveApprovalService;

    /**
     * @var LeaveCalculationService
     */
    protected LeaveCalculationService $leaveCalculationService;

    /**
     * @var LeaveBalanceService
     */
    protected LeaveBalanceService $leaveBalanceService;

    /**
     * @var LeaveAuditService
     */
    protected LeaveAuditService $leaveAuditService;

    /**
     * Create a new controller instance.
     *
     * @param LeaveRequestService $leaveRequestService
     * @param LeaveApprovalService $leaveApprovalService
     * @param LeaveCalculationService $leaveCalculationService
     * @param LeaveBalanceService $leaveBalanceService
     * @param LeaveAuditService $leaveAuditService
     */
    public function __construct(
        LeaveRequestService $leaveRequestService,
        LeaveApprovalService $leaveApprovalService,
        LeaveCalculationService $leaveCalculationService,
        LeaveBalanceService $leaveBalanceService,
        LeaveAuditService $leaveAuditService
    ) {
        $this->leaveRequestService = $leaveRequestService;
        $this->leaveApprovalService = $leaveApprovalService;
        $this->leaveCalculationService = $leaveCalculationService;
        $this->leaveBalanceService = $leaveBalanceService;
        $this->leaveAuditService = $leaveAuditService;
    }

    /**
     * Display a list of the user's leave requests.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View {
        $user = Auth::user();

        // Get current year from TermHelper
        $currentTerm = TermHelper::getCurrentTerm();
        $currentYear = $currentTerm ? $currentTerm->year : date('Y');
        $year = $request->input('year', $currentYear);

        // Get requests for user with optional year filter
        $requests = LeaveRequest::forUser($user->id)
            ->with(['leaveType', 'approver', 'attachments'])
            ->when($year, function ($query) use ($year) {
                return $query->whereYear('start_date', $year);
            })
            ->orderByDesc('submitted_at')
            ->paginate(15);

        // Calculate stats
        $allRequests = LeaveRequest::forUser($user->id)
            ->when($year, function ($query) use ($year) {
                return $query->whereYear('start_date', $year);
            })
            ->get();

        $stats = [
            'total' => $allRequests->count(),
            'pending' => $allRequests->where('status', LeaveRequest::STATUS_PENDING)->count(),
            'approved' => $allRequests->where('status', LeaveRequest::STATUS_APPROVED)->count(),
            'rejected' => $allRequests->where('status', LeaveRequest::STATUS_REJECTED)->count(),
        ];

        // Get years from Term table
        $years = Term::distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        return view('leave.requests.index', compact(
            'requests',
            'stats',
            'year',
            'years'
        ));
    }

    /**
     * Display the manager's pending approval queue.
     *
     * @param Request $request
     * @return View
     */
    public function pendingApprovals(Request $request): View {
        $user = Auth::user();

        // Get pending requests for this approver
        $pendingRequests = $this->leaveApprovalService->getPendingForApprover($user);

        return view('leave.requests.pending', compact('pendingRequests'));
    }

    /**
     * Show the form for creating a new leave request.
     *
     * @return View
     */
    public function create(): View {
        $user = Auth::user();

        // Get active leave types available for user's gender
        $leaveTypes = LeaveType::active()
            ->forGender($user->gender)
            ->ordered()
            ->get();

        // Get current leave year
        $leaveYear = $this->leaveBalanceService->getCurrentLeaveYear();

        // Get user's balances for each leave type, initializing if necessary
        $balances = [];
        foreach ($leaveTypes as $leaveType) {
            $balance = $this->leaveBalanceService->getBalanceForUser(
                $user->id,
                $leaveType->id,
                $leaveYear
            );

            // If balance doesn't exist, create and allocate it
            if (!$balance) {
                $balance = $this->leaveBalanceService->getOrCreateBalance($user, $leaveType, $leaveYear);
                $this->leaveBalanceService->allocateBalance($balance);
                $balance->refresh();
            }

            $balances[$leaveType->id] = $balance->available;
        }

        // Get current term year and actual calendar year for warning
        $currentTerm = TermHelper::getCurrentTerm();
        $termYear = $currentTerm ? (int) $currentTerm->year : (int) date('Y');
        $calendarYear = (int) date('Y');

        return view('leave.requests.create', compact(
            'leaveTypes',
            'balances',
            'leaveYear',
            'termYear',
            'calendarYear'
        ));
    }

    /**
     * Store a newly created leave request.
     *
     * @param StoreLeaveRequestRequest $request
     * @return RedirectResponse
     */
    public function store(StoreLeaveRequestRequest $request): RedirectResponse {
        $user = Auth::user();
        $validated = $request->validated();

        try {
            // Create leave request via service
            $leaveRequest = $this->leaveRequestService->submit($user, $validated);

            // Handle file uploads if present
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('leave-attachments/' . $leaveRequest->id, 'public');

                    LeaveAttachment::create([
                        'leave_request_id' => $leaveRequest->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            return redirect()
                ->route('leave.requests.index')
                ->with('message', 'Leave request submitted successfully. Request ID: ' . substr($leaveRequest->ulid, 0, 8));

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to submit leave request: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified leave request.
     *
     * Includes audit trail for the request.
     *
     * @param LeaveRequest $leaveRequest
     * @return View
     */
    public function show(LeaveRequest $leaveRequest): View {
        // Authorize view access
        $this->authorize('view', $leaveRequest);

        // Load relationships
        $leaveRequest->load(['leaveType', 'user', 'approver', 'attachments', 'cancelledBy']);

        $user = Auth::user();

        // Calculate authorization flags
        $canApprove = $this->leaveApprovalService->canApprove($leaveRequest, $user);
        $canCancel = $this->leaveApprovalService->canCancel($leaveRequest, $user);

        // Get audit history for this request (AUDT-04)
        $auditLogs = $this->leaveAuditService->getAuditHistoryForRequest($leaveRequest);
        $auditService = $this->leaveAuditService;

        return view('leave.requests.show', compact(
            'leaveRequest',
            'canApprove',
            'canCancel',
            'auditLogs',
            'auditService'
        ));
    }

    /**
     * Approve a leave request.
     *
     * @param Request $request
     * @param LeaveRequest $leaveRequest
     * @return JsonResponse|RedirectResponse
     */
    public function approve(Request $request, LeaveRequest $leaveRequest) {
        // Authorize approval
        $this->authorize('approve', $leaveRequest);

        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        try {
            $this->leaveApprovalService->approve(
                $leaveRequest,
                Auth::user(),
                $request->input('comments')
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Leave request approved successfully.',
                ]);
            }

            return redirect()
                ->route('leave.requests.show', $leaveRequest)
                ->with('message', 'Leave request approved successfully.');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    /**
     * Reject a leave request.
     *
     * @param Request $request
     * @param LeaveRequest $leaveRequest
     * @return JsonResponse|RedirectResponse
     */
    public function reject(Request $request, LeaveRequest $leaveRequest) {
        // Authorize rejection (same as approval)
        $this->authorize('approve', $leaveRequest);

        $request->validate([
            'reason' => 'required|string|max:1000',
        ], [
            'reason.required' => 'A reason is required when rejecting a leave request.',
        ]);

        try {
            $this->leaveApprovalService->reject(
                $leaveRequest,
                Auth::user(),
                $request->input('reason')
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Leave request rejected.',
                ]);
            }

            return redirect()
                ->route('leave.requests.show', $leaveRequest)
                ->with('message', 'Leave request rejected.');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Failed to reject request: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a leave request.
     *
     * @param Request $request
     * @param LeaveRequest $leaveRequest
     * @return RedirectResponse
     */
    public function cancel(Request $request, LeaveRequest $leaveRequest): RedirectResponse {
        // Check if user can cancel
        $user = Auth::user();
        if (!$this->leaveApprovalService->canCancel($leaveRequest, $user)) {
            return back()->with('error', 'You are not authorized to cancel this request.');
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ], [
            'reason.required' => 'A reason is required when cancelling a leave request.',
        ]);

        try {
            $this->leaveApprovalService->cancel(
                $leaveRequest,
                $user,
                $request->input('reason')
            );

            return redirect()
                ->route('leave.requests.index')
                ->with('message', 'Leave request cancelled successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel request: ' . $e->getMessage());
        }
    }

    /**
     * Calculate leave days for the given date range (AJAX endpoint).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function calculateDays(Request $request): JsonResponse {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_half_day' => 'nullable|in:am,pm',
            'end_half_day' => 'nullable|in:am,pm',
        ]);

        try {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
            $startHalfDay = $request->input('start_half_day');
            $endHalfDay = $request->input('end_half_day');

            $days = $this->leaveCalculationService->calculateLeaveDays(
                $startDate,
                $endDate,
                $startHalfDay,
                $endHalfDay
            );

            // Format for display
            $formatted = number_format($days, 1);
            $unit = $days == 1 ? 'day' : 'days';

            return response()->json([
                'success' => true,
                'days' => $days,
                'formatted' => "{$formatted} {$unit}",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to calculate leave days: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display team leave history for managers.
     *
     * Shows all leave requests from direct reports with filtering options.
     * Protected by approve-leave-requests gate.
     *
     * @param Request $request
     * @return View
     */
    public function teamHistory(Request $request): View {
        $manager = Auth::user();
        $currentTerm = TermHelper::getCurrentTerm();
        $currentYear = $currentTerm ? (int) $currentTerm->year : (int) date('Y');
        $year = $request->input('year', $currentYear);
        $status = $request->input('status');
        $staffId = $request->input('staff_id');
        $leaveTypeId = $request->input('leave_type_id');

        // Get direct report IDs
        $directReportIds = $manager->subordinates()
            ->where('status', 'Current')
            ->pluck('id')
            ->toArray();

        // Get direct reports for filter dropdown
        $directReports = $manager->subordinates()
            ->where('status', 'Current')
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->select('id', 'firstname', 'lastname')
            ->get();

        // Build query - only for direct reports
        $query = LeaveRequest::whereIn('user_id', $directReportIds)
            ->with(['leaveType', 'user', 'approver', 'attachments']);

        // Apply year filter
        if ($year) {
            $query->whereYear('start_date', $year);
        }

        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Apply staff filter
        if ($staffId) {
            $query->where('user_id', $staffId);
        }

        // Apply leave type filter
        if ($leaveTypeId) {
            $query->where('leave_type_id', $leaveTypeId);
        }

        $requests = $query->orderByDesc('submitted_at')
            ->paginate(20)
            ->withQueryString();

        // Get leave types for filter
        $leaveTypes = LeaveType::active()
            ->orderBy('name')
            ->select('id', 'name', 'code')
            ->get();

        // Calculate stats for the filtered view
        $statsQuery = LeaveRequest::whereIn('user_id', $directReportIds);
        if ($year) {
            $statsQuery->whereYear('start_date', $year);
        }
        $allRequests = $statsQuery->get();

        $stats = [
            'total' => $allRequests->count(),
            'pending' => $allRequests->where('status', LeaveRequest::STATUS_PENDING)->count(),
            'approved' => $allRequests->where('status', LeaveRequest::STATUS_APPROVED)->count(),
            'rejected' => $allRequests->where('status', LeaveRequest::STATUS_REJECTED)->count(),
            'cancelled' => $allRequests->where('status', LeaveRequest::STATUS_CANCELLED)->count(),
        ];

        // Get years from Terms table
        $years = Term::distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        return view('leave.requests.team-history', compact(
            'requests',
            'directReports',
            'leaveTypes',
            'stats',
            'year',
            'years',
            'status',
            'staffId',
            'leaveTypeId'
        ));
    }
}
