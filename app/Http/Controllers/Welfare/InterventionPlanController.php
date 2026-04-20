<?php

namespace App\Http\Controllers\Welfare;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Welfare\InterventionPlan;
use App\Models\Welfare\InterventionPlanReview;
use App\Models\Welfare\WelfareCase;
use App\Services\Welfare\WelfareAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterventionPlanController extends Controller
{
    protected WelfareAuditService $auditService;

    public function __construct(WelfareAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = InterventionPlan::with(['student', 'coordinator', 'welfareCase'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('intervention_type')) {
            $query->where('intervention_type', $request->intervention_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $plans = $query->paginate(20);

        $stats = [
            'active' => InterventionPlan::where('status', 'active')->count(),
            'due_review' => InterventionPlan::where('status', 'active')
                ->where('next_review_date', '<=', now()->addDays(7))->count(),
            'completed_this_term' => InterventionPlan::where('status', 'completed')
                ->where('term_id', session('selected_term_id'))->count(),
        ];

        return view('welfare.interventions.index', compact('plans', 'stats'));
    }

    public function create()
    {
        $students = Student::where('status', 'Current')->orderBy('first_name')->get();
        $coordinators = User::where('status', 'Current')->orderBy('firstname')->get();
        $cases = WelfareCase::whereNotIn('status', ['closed'])->orderBy('case_number', 'desc')->get();

        return view('welfare.interventions.create', compact('students', 'coordinators', 'cases'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'welfare_case_id' => 'nullable|exists:welfare_cases,id',
            'intervention_type' => 'required|in:academic,behavioral,social,emotional,attendance,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'goals' => 'required|string',
            'strategies' => 'required|string',
            'coordinator_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'target_end_date' => 'nullable|date|after:start_date',
            'review_frequency' => 'required|in:weekly,fortnightly,monthly,termly',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'draft';
        $validated['term_id'] = session('selected_term_id');

        $plan = InterventionPlan::create($validated);

        $this->auditService->log('create', 'intervention_plan', $plan->id, [
            'student_id' => $validated['student_id'],
            'type' => $validated['intervention_type'],
        ]);

        return redirect()->route('welfare.intervention-plans.edit', $plan)
            ->with('success', 'Intervention plan created.');
    }


    public function edit(InterventionPlan $interventionPlan)
    {
        $students = Student::where('status', 'Current')->orderBy('first_name')->get();
        $coordinators = User::where('status', 'Current')->orderBy('firstname')->get();
        $cases = WelfareCase::whereNotIn('status', ['closed'])->orderBy('case_number', 'desc')->get();

        return view('welfare.interventions.edit', [
            'plan' => $interventionPlan,
            'students' => $students,
            'coordinators' => $coordinators,
            'cases' => $cases,
        ]);
    }

    public function update(Request $request, InterventionPlan $interventionPlan)
    {
        $validated = $request->validate([
            'intervention_type' => 'required|in:academic,behavioral,social,emotional,attendance,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'goals' => 'required|string',
            'strategies' => 'required|string',
            'coordinator_id' => 'required|exists:users,id',
            'target_end_date' => 'nullable|date',
            'review_frequency' => 'required|in:weekly,fortnightly,monthly,termly',
        ]);

        $interventionPlan->update($validated);

        $this->auditService->log('update', 'intervention_plan', $interventionPlan->id, $validated);

        return redirect()->route('welfare.intervention-plans.edit', $interventionPlan)
            ->with('success', 'Intervention plan updated.');
    }

    public function destroy(InterventionPlan $interventionPlan)
    {
        if ($interventionPlan->status !== 'draft') {
            return back()->with('error', 'Cannot delete active plans.');
        }

        $this->auditService->log('delete', 'intervention_plan', $interventionPlan->id);

        $interventionPlan->delete();

        return redirect()->route('welfare.intervention-plans.index')
            ->with('success', 'Intervention plan deleted.');
    }

    public function activate(Request $request, InterventionPlan $plan)
    {
        if ($plan->status !== 'draft') {
            return back()->with('error', 'Plan is already active.');
        }

        $plan->update([
            'status' => 'active',
            'activated_at' => now(),
        ]);

        $this->auditService->log('activate', 'intervention_plan', $plan->id);

        return back()->with('success', 'Intervention plan activated.');
    }

    public function hold(Request $request, InterventionPlan $plan)
    {
        $validated = $request->validate([
            'hold_reason' => 'required|string',
        ]);

        $plan->update([
            'status' => 'on_hold',
            'hold_reason' => $validated['hold_reason'],
        ]);

        $this->auditService->log('hold', 'intervention_plan', $plan->id, $validated);

        return back()->with('success', 'Plan placed on hold.');
    }

    public function resume(Request $request, InterventionPlan $plan)
    {
        if ($plan->status !== 'on_hold') {
            return back()->with('error', 'Plan is not on hold.');
        }

        $plan->update([
            'status' => 'active',
            'hold_reason' => null,
        ]);

        $this->auditService->log('resume', 'intervention_plan', $plan->id);

        return back()->with('success', 'Plan resumed.');
    }

    public function complete(Request $request, InterventionPlan $plan)
    {
        $validated = $request->validate([
            'outcome_summary' => 'required|string',
            'goals_achieved' => 'boolean',
        ]);

        $plan->update([
            'status' => 'completed',
            'outcome_summary' => $validated['outcome_summary'],
            'goals_achieved' => $validated['goals_achieved'] ?? false,
            'completed_at' => now(),
        ]);

        $this->auditService->log('complete', 'intervention_plan', $plan->id, $validated);

        return back()->with('success', 'Intervention plan completed.');
    }

    public function recordConsent(Request $request, InterventionPlan $plan)
    {
        $validated = $request->validate([
            'consent_obtained' => 'required|boolean',
            'consent_date' => 'required_if:consent_obtained,true|date',
            'consent_notes' => 'nullable|string',
        ]);

        $plan->update([
            'parent_consent' => $validated['consent_obtained'],
            'consent_date' => $validated['consent_date'] ?? null,
            'consent_notes' => $validated['consent_notes'],
        ]);

        $this->auditService->log('consent', 'intervention_plan', $plan->id, $validated);

        return back()->with('success', 'Consent recorded.');
    }

    public function addReview(Request $request, InterventionPlan $plan)
    {
        $validated = $request->validate([
            'review_date' => 'required|date',
            'progress_rating' => 'required|in:1,2,3,4,5',
            'summary' => 'required|string',
            'recommendations' => 'nullable|string',
            'next_review_date' => 'nullable|date|after:review_date',
        ]);

        $validated['intervention_plan_id'] = $plan->id;
        $validated['reviewer_id'] = Auth::id();

        $review = InterventionPlanReview::create($validated);

        if ($validated['next_review_date']) {
            $plan->update(['next_review_date' => $validated['next_review_date']]);
        }

        $this->auditService->log('add_review', 'intervention_plan', $plan->id, [
            'review_id' => $review->id,
            'progress_rating' => $validated['progress_rating'],
        ]);

        return back()->with('success', 'Review added.');
    }
}
