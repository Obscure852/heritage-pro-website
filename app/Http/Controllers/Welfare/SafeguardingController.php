<?php

namespace App\Http\Controllers\Welfare;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Welfare\SafeguardingCategory;
use App\Models\Welfare\SafeguardingConcern;
use App\Services\Welfare\SafeguardingService;
use Illuminate\Http\Request;

class SafeguardingController extends Controller{
    protected SafeguardingService $safeguardingService;

    public function __construct(SafeguardingService $safeguardingService){
        $this->safeguardingService = $safeguardingService;
    }


    public function index(Request $request){
        $this->authorize('viewAny', SafeguardingConcern::class);

        $filters = $request->only(['status', 'risk_level', 'category_id', 'student_id', 'reported_by', 'date_from', 'date_to']);

        $concerns = $this->safeguardingService->getConcerns($filters, 20);
        $categories = $this->safeguardingService->getCategories();

        // Get urgent alerts
        $criticalConcerns = $this->safeguardingService->getCriticalConcerns();
        $awaitingNotification = $this->safeguardingService->getConcernsAwaitingAuthorityNotification();
        $requiresAction = $this->safeguardingService->getConcernsRequiringImmediateAction();

        return view('welfare.safeguarding.index', compact(
            'concerns', 'categories', 'filters',
            'criticalConcerns', 'awaitingNotification', 'requiresAction'
        ));
    }

    /**
     * Show the form for creating a new concern.
     */
    public function create(Request $request)
    {
        $this->authorize('create', SafeguardingConcern::class);

        $students = Student::orderBy('first_name')->get();
        $categories = SafeguardingCategory::active()->orderBy('name')->get();

        // Get users who can be lead officers (staff with safeguarding/welfare permissions)
        $officers = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Administrator', 'Safeguarding Lead', 'Welfare Officer', 'Deputy Principal', 'Principal']);
        })->orderBy('firstname')->get();

        $selectedStudent = $request->has('student_id')
            ? Student::find($request->student_id)
            : null;

        return view('welfare.safeguarding.create', compact('students', 'categories', 'officers', 'selectedStudent'));
    }

    /**
     * Store a newly created concern.
     */
    public function store(Request $request)
    {
        $this->authorize('create', SafeguardingConcern::class);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'category_id' => 'required|exists:safeguarding_categories,id',
            'risk_level' => 'required|in:low,medium,high,critical',
            'date_identified' => 'required|date|before_or_equal:today',
            'source_of_concern' => 'required|in:student_disclosure,staff_observation,parent_report,peer_report,external_referral,anonymous',
            'concern_details' => 'required|string',
            'indicators_observed' => 'nullable|string',
            'disclosure_details' => 'nullable|string',
        ]);

        $result = $this->safeguardingService->reportConcern($validated, auth()->user());

        // Handle duplicate detection
        if (is_array($result) && ($result['duplicate'] ?? false)) {
            return redirect()
                ->route('welfare.safeguarding.edit', $result['existing_concern'])
                ->with('warning', $result['message']);
        }

        // Alert for critical concerns
        $message = 'Safeguarding concern reported successfully.';
        if ($result->isCritical()) {
            $message .= ' CRITICAL: This concern requires immediate attention.';
        }

        return redirect()
            ->route('welfare.safeguarding.edit', $result)
            ->with('success', $message);
    }


    /**
     * Show the form for editing the concern.
     */
    public function edit(SafeguardingConcern $concern)
    {
        $this->authorize('update', $concern);

        $concern->load(['student', 'category', 'reportedBy', 'closedBy', 'welfareCase']);

        $categories = SafeguardingCategory::active()->orderBy('name')->get();
        $canViewSensitive = auth()->user()->can('viewSensitiveDetails', $concern);

        return view('welfare.safeguarding.edit', compact('concern', 'categories', 'canViewSensitive'));
    }

    /**
     * Update the specified concern.
     */
    public function update(Request $request, SafeguardingConcern $concern)
    {
        $this->authorize('update', $concern);

        $validated = $request->validate([
            'category_id' => 'required|exists:safeguarding_categories,id',
            'risk_level' => 'required|in:low,medium,high,critical',
            'source_of_concern' => 'required|in:student_disclosure,staff_observation,parent_report,peer_report,external_referral,anonymous',
            'concern_details' => 'required|string',
            'indicators_observed' => 'nullable|string',
            'status' => 'required|in:identified,investigating,referred,monitoring,closed',
        ]);

        $this->safeguardingService->updateConcern($concern, $validated);

        return redirect()
            ->route('welfare.safeguarding.edit', $concern)
            ->with('success', 'Concern updated successfully.');
    }

    /**
     * Remove the specified concern.
     */
    public function destroy(SafeguardingConcern $concern)
    {
        $this->authorize('delete', $concern);

        try {
            $this->safeguardingService->deleteConcern($concern);

            return redirect()
                ->route('welfare.safeguarding.index')
                ->with('success', 'Concern deleted successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('welfare.safeguarding.edit', $concern)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Record immediate action taken.
     */
    public function recordImmediateAction(Request $request, SafeguardingConcern $concern)
    {
        $this->authorize('recordImmediateAction', $concern);

        $validated = $request->validate([
            'immediate_action_details' => 'required|string',
        ]);

        $this->safeguardingService->recordImmediateAction($concern, $validated['immediate_action_details']);

        return redirect()
            ->route('welfare.safeguarding.edit', $concern)
            ->with('success', 'Immediate action recorded.');
    }

    /**
     * Notify authorities.
     */
    public function notifyAuthorities(Request $request, SafeguardingConcern $concern)
    {
        $this->authorize('notifyAuthorities', $concern);

        $validated = $request->validate([
            'authority_reference' => 'required|string|max:255',
        ]);

        $this->safeguardingService->notifyAuthorities($concern, $validated['authority_reference']);

        return redirect()
            ->route('welfare.safeguarding.edit', $concern)
            ->with('success', 'Authority notification recorded with reference: ' . $validated['authority_reference']);
    }

    /**
     * Notify parents.
     */
    public function notifyParents(Request $request, SafeguardingConcern $concern)
    {
        $this->authorize('notifyParents', $concern);

        $validated = $request->validate([
            'parent_response' => 'nullable|string|max:1000',
        ]);

        $this->safeguardingService->notifyParents($concern, $validated['parent_response'] ?? null);

        return redirect()
            ->route('welfare.safeguarding.edit', $concern)
            ->with('success', 'Parent notification recorded.');
    }

    /**
     * Close the concern.
     */
    public function close(Request $request, SafeguardingConcern $concern)
    {
        $this->authorize('close', $concern);

        $validated = $request->validate([
            'outcome' => 'required|string|max:1000',
        ]);

        try {
            $this->safeguardingService->closeConcern($concern, auth()->user(), $validated['outcome']);

            return redirect()
                ->route('welfare.safeguarding.edit', $concern)
                ->with('success', 'Concern closed successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('welfare.safeguarding.edit', $concern)
                ->with('error', $e->getMessage());
        }
    }
}
