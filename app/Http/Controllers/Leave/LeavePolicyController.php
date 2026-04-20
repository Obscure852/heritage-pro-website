<?php

namespace App\Http\Controllers\Leave;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\StoreLeavePolicyRequest;
use App\Http\Requests\Leave\UpdateLeavePolicyRequest;
use App\Models\Leave\LeavePolicy;
use App\Models\Leave\LeaveType;
use App\Models\Term;
use App\Services\Leave\LeavePolicyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for managing leave policies.
 *
 * Handles CRUD operations for leave policies via AJAX from the leave type edit page.
 */
class LeavePolicyController extends Controller {
    /**
     * The leave policy service instance.
     *
     * @var LeavePolicyService
     */
    protected LeavePolicyService $leavePolicyService;

    /**
     * Create a new controller instance.
     *
     * @param LeavePolicyService $leavePolicyService
     */
    public function __construct(LeavePolicyService $leavePolicyService) {
        $this->middleware('auth');
        $this->leavePolicyService = $leavePolicyService;
    }

    /**
     * Display the policies management page for a leave type.
     *
     * @param LeaveType $leaveType
     * @return View
     */
    public function index(LeaveType $leaveType): View {
        $policies = $this->leavePolicyService->getPoliciesForType($leaveType->id);
        $leaveYearStartMonth = $this->leavePolicyService->getLeaveYearStartMonth();

        return view('leave.policies.manage', compact('leaveType', 'policies', 'leaveYearStartMonth'));
    }

    /**
     * Show the form for creating a new policy.
     *
     * @param LeaveType $leaveType
     * @return View
     */
    public function create(LeaveType $leaveType): View {
        $leaveYearStartMonth = $this->leavePolicyService->getLeaveYearStartMonth();
        $existingYears = $this->leavePolicyService->getPoliciesForType($leaveType->id)->pluck('leave_year')->toArray();

        // Get years from Term table
        $years = Term::distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        // Get current year from TermHelper
        $currentTerm = TermHelper::getCurrentTerm();
        $currentYear = $currentTerm ? $currentTerm->year : (int) date('Y');

        return view('leave.policies.create', compact('leaveType', 'leaveYearStartMonth', 'existingYears', 'years', 'currentYear'));
    }

    /**
     * Store a newly created leave policy.
     *
     * @param StoreLeavePolicyRequest $request
     * @param LeaveType $leaveType
     * @return RedirectResponse
     */
    public function store(StoreLeavePolicyRequest $request, LeaveType $leaveType): RedirectResponse {
        try {
            $data = $request->validated();
            $data['leave_type_id'] = $leaveType->id;

            $policy = $this->leavePolicyService->create($data);

            return redirect()
                ->route('leave.policies.index', $leaveType)
                ->with('message', 'Policy for ' . $policy->leave_year . ' created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create policy: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing a policy.
     *
     * @param LeaveType $leaveType
     * @param LeavePolicy $policy
     * @return View
     */
    public function edit(LeaveType $leaveType, LeavePolicy $policy): View {
        // Ensure policy belongs to this leave type
        if ($policy->leave_type_id !== $leaveType->id) {
            abort(403, 'Policy does not belong to this leave type.');
        }

        $leaveYearStartMonth = $this->leavePolicyService->getLeaveYearStartMonth();

        return view('leave.policies.edit', compact('leaveType', 'policy', 'leaveYearStartMonth'));
    }

    /**
     * Update the specified leave policy.
     *
     * @param UpdateLeavePolicyRequest $request
     * @param LeaveType $leaveType
     * @param LeavePolicy $policy
     * @return RedirectResponse
     */
    public function update(UpdateLeavePolicyRequest $request, LeaveType $leaveType, LeavePolicy $policy): RedirectResponse {
        try {
            // Ensure policy belongs to this leave type
            if ($policy->leave_type_id !== $leaveType->id) {
                return redirect()
                    ->route('leave.policies.index', $leaveType)
                    ->with('error', 'Policy does not belong to this leave type.');
            }

            $updatedPolicy = $this->leavePolicyService->update($policy, $request->validated());

            return redirect()
                ->route('leave.policies.index', $leaveType)
                ->with('message', 'Policy for ' . $updatedPolicy->leave_year . ' updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update policy: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified leave policy.
     *
     * @param LeaveType $leaveType
     * @param LeavePolicy $policy
     * @return RedirectResponse
     */
    public function destroy(LeaveType $leaveType, LeavePolicy $policy): RedirectResponse {
        try {
            // Ensure policy belongs to this leave type
            if ($policy->leave_type_id !== $leaveType->id) {
                return redirect()
                    ->route('leave.policies.index', $leaveType)
                    ->with('error', 'Policy does not belong to this leave type.');
            }

            $year = $policy->leave_year;
            $this->leavePolicyService->delete($policy);

            return redirect()
                ->route('leave.policies.index', $leaveType)
                ->with('message', 'Policy for ' . $year . ' deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('leave.policies.index', $leaveType)
                ->with('error', 'Failed to delete policy: ' . $e->getMessage());
        }
    }
}
