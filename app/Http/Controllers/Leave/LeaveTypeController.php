<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\StoreLeaveTypeRequest;
use App\Http\Requests\Leave\UpdateLeaveTypeRequest;
use App\Models\Leave\LeaveType;
use App\Services\Leave\LeaveTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller for managing leave types.
 */
class LeaveTypeController extends Controller {
    /**
     * The leave type service instance.
     *
     * @var LeaveTypeService
     */
    protected LeaveTypeService $leaveTypeService;

    /**
     * Create a new controller instance.
     *
     * @param LeaveTypeService $leaveTypeService
     */
    public function __construct(LeaveTypeService $leaveTypeService) {
        $this->middleware('auth');
        $this->leaveTypeService = $leaveTypeService;
    }

    /**
     * Display a listing of leave types.
     *
     * @return View
     */
    public function index(): View {
        $leaveTypes = $this->leaveTypeService->getAll();
        $counts = $this->leaveTypeService->getCounts();

        return view('leave.types.index', [
            'leaveTypes' => $leaveTypes,
            'totalCount' => $counts['total'],
            'activeCount' => $counts['active'],
            'inactiveCount' => $counts['inactive'],
        ]);
    }

    /**
     * Show the form for creating a new leave type.
     *
     * @return View
     */
    public function create(): View {
        return view('leave.types.create');
    }

    /**
     * Store a newly created leave type.
     *
     * @param StoreLeaveTypeRequest $request
     * @return RedirectResponse
     */
    public function store(StoreLeaveTypeRequest $request): RedirectResponse {
        $this->leaveTypeService->create($request->validated());

        return redirect()
            ->to(route('leave.settings.index') . '#leaveTypes')
            ->with('message', 'Leave type created successfully.');
    }

    /**
     * Show the form for editing a leave type.
     *
     * @param LeaveType $leaveType
     * @return View
     */
    public function edit(LeaveType $leaveType): View {
        return view('leave.types.edit', [
            'leaveType' => $leaveType,
        ]);
    }

    /**
     * Update the specified leave type.
     *
     * @param UpdateLeaveTypeRequest $request
     * @param LeaveType $leaveType
     * @return RedirectResponse
     */
    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse {
        $this->leaveTypeService->update($leaveType, $request->validated());

        return redirect()
            ->to(route('leave.settings.index') . '#leaveTypes')
            ->with('message', 'Leave type updated successfully.');
    }

    /**
     * Toggle the active status of a leave type.
     *
     * @param LeaveType $leaveType
     * @return JsonResponse
     */
    public function toggleStatus(LeaveType $leaveType): JsonResponse {
        $updatedLeaveType = $this->leaveTypeService->toggleStatus($leaveType);

        return response()->json([
            'success' => true,
            'message' => $updatedLeaveType->is_active
                ? 'Leave type activated successfully.'
                : 'Leave type deactivated successfully.',
            'is_active' => $updatedLeaveType->is_active,
        ]);
    }
}
