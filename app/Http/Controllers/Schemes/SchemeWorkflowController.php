<?php

namespace App\Http\Controllers\Schemes;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Schemes\ReviewSchemeRequest;
use App\Models\Department;
use App\Policies\SchemeOfWorkPolicy;
use App\Models\Schemes\SchemeOfWork;
use App\Models\Term;
use App\Services\Schemes\CoverageService;
use App\Services\Schemes\SchemeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use InvalidArgumentException;

class SchemeWorkflowController extends Controller {
    /**
     * Teacher submits a scheme for HOD review.
     * Allowed from: draft, revision_required.
     */
    public function submit(Request $request, SchemeOfWork $scheme, SchemeService $schemeService): RedirectResponse {
        $this->authorize('submit', $scheme);

        $request->validate(['comments' => 'nullable|string|max:2000']);

        try {
            $schemeService->submitScheme($scheme, auth()->user(), $request->input('comments'));
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $scheme->load('teacher');
        $message = $scheme->requiresSupervisorReview() && !$scheme->hasPassedSupervisorReview()
            ? 'Scheme submitted for supervisor review.'
            : 'Scheme submitted for HOD review.';

        return redirect()->route('schemes.show', $scheme)
            ->with('success', $message);
    }

    /**
     * HOD places a submitted scheme under active review.
     * Allowed from: submitted.
     */
    public function placeUnderReview(Request $request, SchemeOfWork $scheme, SchemeService $schemeService): RedirectResponse {
        $this->authorize('review', $scheme);

        $request->validate(['comments' => 'nullable|string|max:2000']);

        try {
            $schemeService->placeUnderReview($scheme, auth()->user(), $request->input('comments'));
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('schemes.show', $scheme)
            ->with('success', 'Scheme placed under review.');
    }

    /**
     * HOD approves a scheme that is currently under review.
     * Allowed from: under_review.
     */
    public function approve(Request $request, SchemeOfWork $scheme, SchemeService $schemeService): RedirectResponse {
        $this->authorize('review', $scheme);

        $request->validate(['comments' => 'nullable|string|max:2000']);

        try {
            $schemeService->approveScheme($scheme, auth()->user(), $request->input('comments'));
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('schemes.show', $scheme)
            ->with('success', 'Scheme approved successfully.');
    }

    /**
     * HOD returns a scheme for revision with mandatory comments.
     * Allowed from: under_review.
     */
    public function returnForRevision(ReviewSchemeRequest $request, SchemeOfWork $scheme, SchemeService $schemeService): RedirectResponse {
        $this->authorize('review', $scheme);

        try {
            $schemeService->returnForRevision($scheme, auth()->user(), $request->validated('comments'));
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('schemes.show', $scheme)
            ->with('success', 'Scheme returned for revision.');
    }

    /**
     * Publish an approved scheme as the reference planner for its subject/grade/term.
     */
    public function publishReference(SchemeOfWork $scheme, SchemeService $schemeService): RedirectResponse {
        $this->authorize('publishReference', $scheme);

        try {
            $schemeService->publishReference($scheme, auth()->user());
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('schemes.show', $scheme)
            ->with('success', 'Scheme published as the reference scheme for this subject.');
    }

    /**
     * Remove an approved scheme from the reference planner slot.
     */
    public function unpublishReference(SchemeOfWork $scheme, SchemeService $schemeService): RedirectResponse {
        $this->authorize('publishReference', $scheme);

        try {
            $schemeService->unpublishReference($scheme, auth()->user());
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('schemes.show', $scheme)
            ->with('success', 'Scheme removed from the reference slot.');
    }

    /**
     * Supervisor approves a submitted scheme, forwarding it to the HOD queue.
     */
    public function supervisorApprove(Request $request, SchemeOfWork $scheme, SchemeService $schemeService): RedirectResponse {
        $this->authorize('supervisorReview', $scheme);

        $request->validate(['comments' => 'nullable|string|max:2000']);

        try {
            $schemeService->supervisorApprove($scheme, auth()->user(), $request->input('comments'));
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('schemes.show', $scheme)
            ->with('success', 'Scheme forwarded to HOD for review.');
    }

    /**
     * Supervisor returns a submitted scheme for revision.
     */
    public function supervisorReturnForRevision(Request $request, SchemeOfWork $scheme, SchemeService $schemeService): RedirectResponse {
        $this->authorize('supervisorReview', $scheme);

        $request->validate(['comments' => 'nullable|string|max:2000']);

        try {
            $schemeService->supervisorReturnForRevision($scheme, auth()->user(), $request->input('comments'));
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('schemes.show', $scheme)
            ->with('success', 'Scheme returned for revision.');
    }

    /**
     * Supervisor dashboard — shows submitted schemes from direct reports.
     */
    public function supervisorDashboard(Request $request): View {
        $user = auth()->user();

        abort_unless(
            $user->hasAnyRoles(SchemeOfWorkPolicy::ADMIN_ROLES) || $user->subordinates()->exists(),
            403
        );

        $defaultTerm = Term::currentOrLastActiveTerm();
        $currentTerm = $request->filled('term_id')
            ? (Term::find($request->query('term_id')) ?? $defaultTerm)
            : $defaultTerm;

        $terms = TermHelper::getSelectableTerms($defaultTerm);

        $subordinateIds = $user->subordinates()->pluck('id');

        $schemes = SchemeOfWork::with([
                'teacher:id,name',
                'term:id,term,year',
                'klassSubject.gradeSubject.subject',
                'klassSubject.klass:id,name',
                'optionalSubject.gradeSubject.subject',
            ])
            ->where('term_id', $currentTerm->id)
            ->whereIn('teacher_id', $subordinateIds)
            ->orderBy('teacher_id')
            ->get();

        $pending = $schemes->where('status', 'submitted');

        return view('schemes.supervisor.dashboard', compact('schemes', 'pending', 'currentTerm', 'terms'));
    }

    /**
     * HOD dashboard — shows all department schemes grouped by teacher
     * with a pending reviews queue (submitted + under_review).
     */
    public function hodDashboard(Request $request): View {
        $user = auth()->user();

        abort_unless(
            $user->hasAnyRoles(SchemeOfWorkPolicy::ADMIN_ROLES)
            || Department::where('department_head', $user->id)->orWhere('assistant', $user->id)->exists(),
            403
        );

        $defaultTerm = Term::currentOrLastActiveTerm();
        $currentTerm = $request->filled('term_id')
            ? (Term::find($request->query('term_id')) ?? $defaultTerm)
            : $defaultTerm;

        $terms = TermHelper::getSelectableTerms($defaultTerm);

        // Resolve departments this user heads (department_head OR assistant)
        $departments = Department::where('department_head', $user->id)
            ->orWhere('assistant', $user->id)
            ->with('allGradeSubjects')
            ->get();

        // If user heads no departments, return empty dashboard
        if ($departments->isEmpty()) {
            return view('schemes.hod.dashboard', [
                'schemes'     => collect(),
                'pending'     => collect(),
                'coverage'    => collect(),
                'departments' => $departments,
                'currentTerm' => $currentTerm,
                'terms'       => $terms,
            ]);
        }

        $currentTermId = $currentTerm->id;

        // Collect all grade_subject IDs for the HOD's departments
        $gradeSubjectIds = $departments->flatMap(fn ($d) => $d->allGradeSubjects->pluck('id'))->unique()->values();

        // Use Cache::remember with user+term scoped key (5 min TTL)
        $cacheKey = "hod_schemes_{$user->id}_{$currentTermId}";

        $schemes = Cache::remember($cacheKey, 300, function () use ($gradeSubjectIds, $currentTermId) {
            return SchemeOfWork::with([
                    'teacher:id,name',
                    'term:id,term,year',
                    'klassSubject.gradeSubject.subject',
                    'klassSubject.klass:id,name',
                    'optionalSubject.gradeSubject.subject',
                ])
                ->where('term_id', $currentTermId)
                ->where(function ($q) use ($gradeSubjectIds) {
                    $q->whereHas('klassSubject', fn ($q2) => $q2->whereIn('grade_subject_id', $gradeSubjectIds))
                      ->orWhereHas('optionalSubject', fn ($q2) => $q2->whereIn('grade_subject_id', $gradeSubjectIds));
                })
                ->orderBy('teacher_id')
                ->get();
        });

        // Filter pending reviews from the cached collection
        // Include supervisor_reviewed, under_review, and submitted (only if no supervisor step)
        $pending = $schemes->filter(function ($s) {
            if (in_array($s->status, ['supervisor_reviewed', 'under_review'])) {
                return true;
            }
            if ($s->status === 'submitted') {
                return !$s->requiresSupervisorReview();
            }
            return false;
        });

        // Coverage metrics via CoverageService (cached 5 min, keyed by hod+term)
        $coverageService = new CoverageService();
        $coverage = $coverageService->hodCoverage($user, $currentTerm);

        return view('schemes.hod.dashboard', compact('schemes', 'pending', 'departments', 'currentTerm', 'terms', 'coverage'));
    }
}
