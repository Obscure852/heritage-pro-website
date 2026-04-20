<?php

namespace App\Http\Controllers\Schemes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schemes\StoreLessonPlanRequest;
use App\Http\Requests\Schemes\UpdateLessonPlanRequest;
use App\Models\Department;
use App\Models\Schemes\LessonPlan;
use App\Models\Schemes\SchemeOfWork;
use App\Models\Schemes\SchemeOfWorkEntry;
use App\Policies\SchemeOfWorkPolicy;
use App\Services\Schemes\LessonPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class LessonPlanController extends Controller {
    /**
     * View access stays aligned with scheme view access.
     */
    private function authorizeViewPlan(LessonPlan $plan): void {
        $user = auth()->user();

        if (SchemeOfWorkPolicy::isAdmin($user)) {
            return;
        }

        if ($plan->teacher_id === $user->id) {
            return;
        }

        // Supervisor can view subordinate lesson plans
        if ($plan->teacher && (int) $plan->teacher->reporting_to === $user->id) {
            return;
        }

        // HOD can view department lesson plans
        if ($plan->scheme && $this->isHodForPlan($user, $plan)) {
            return;
        }

        if ($plan->scheme) {
            $this->authorize('view', $plan->scheme);
            return;
        }

        abort(403);
    }

    /**
     * Only the owning teacher or an admin may mutate lesson plans.
     */
    private function authorizeMutatePlan(LessonPlan $plan): void {
        $user = auth()->user();

        if (SchemeOfWorkPolicy::isAdmin($user)) {
            return;
        }

        if ($plan->teacher_id === $user->id) {
            return;
        }

        if ($plan->scheme && $plan->scheme->teacher_id === $user->id) {
            return;
        }

        abort(403);
    }

    private function authorizeMutateScheme(SchemeOfWork $scheme): void {
        $user = auth()->user();

        if (SchemeOfWorkPolicy::isAdmin($user)) {
            return;
        }

        abort_unless($scheme->teacher_id === $user->id, 403);
    }

    /**
     * Check if user is the supervisor (reporting_to) of the lesson plan's teacher.
     */
    private function authorizeSupervisorReview(LessonPlan $plan): void {
        $user = auth()->user();

        if (SchemeOfWorkPolicy::isAdmin($user)) {
            return;
        }

        $teacher = $plan->teacher;
        abort_unless($teacher && (int) $teacher->reporting_to === $user->id, 403);
    }

    /**
     * Check if user is HOD for the lesson plan's scheme department.
     */
    private function authorizeHodReview(LessonPlan $plan): void {
        $user = auth()->user();

        if (SchemeOfWorkPolicy::isAdmin($user)) {
            return;
        }

        abort_unless($this->isHodForPlan($user, $plan), 403);
    }

    /**
     * Resolve whether a user is HOD/assistant for the plan's scheme department.
     */
    private function isHodForPlan($user, LessonPlan $plan): bool {
        $scheme = $plan->scheme;
        if (!$scheme) {
            return false;
        }

        $gradeSubject = $scheme->gradeSubject;
        if (!$gradeSubject || is_null($gradeSubject->department_id)) {
            return false;
        }

        $department = Department::find($gradeSubject->department_id);
        if (!$department) {
            return false;
        }

        return (!is_null($department->department_head) && $department->department_head === $user->id)
            || (!is_null($department->assistant) && $department->assistant === $user->id);
    }

    /**
     * Show the form to create a new lesson plan.
     */
    public function create(Request $request): View {
        $entry = null;
        $scheme = null;

        if ($request->filled('scheme_entry_id')) {
            $entry = SchemeOfWorkEntry::with('scheme')->findOrFail($request->scheme_entry_id);
            $scheme = $entry->scheme;
            $this->authorizeMutateScheme($scheme);
        }

        return view('schemes.lesson-plans.create', compact('entry', 'scheme'));
    }

    /**
     * Store a newly created lesson plan.
     */
    public function store(StoreLessonPlanRequest $request): RedirectResponse {
        $data = $request->validated();
        $scheme = null;

        if (!empty($data['scheme_of_work_entry_id']) && empty($data['scheme_of_work_id'])) {
            $entry = SchemeOfWorkEntry::findOrFail($data['scheme_of_work_entry_id']);
            $data['scheme_of_work_id'] = $entry->scheme_of_work_id;
        }

        if (!empty($data['scheme_of_work_id'])) {
            $scheme = SchemeOfWork::findOrFail($data['scheme_of_work_id']);
        }

        if (!$scheme) {
            throw ValidationException::withMessages([
                'scheme_of_work_id' => 'Select a linked scheme or weekly entry before creating a lesson plan.',
            ]);
        }

        $this->authorizeMutateScheme($scheme);

        $data['teacher_id'] = auth()->id();
        $data['status'] = 'draft';

        $plan = LessonPlan::create($data);

        return redirect()->route('lesson-plans.show', $plan)
            ->with('success', 'Lesson plan created successfully.');
    }

    /**
     * Display the specified lesson plan.
     */
    public function show(LessonPlan $lessonPlan): View {
        $this->authorizeViewPlan($lessonPlan);
        $lessonPlan->load([
            'entry',
            'teacher.reportsTo',
            'scheme.klassSubject.gradeSubject.subject',
            'scheme.klassSubject.gradeSubject.grade',
            'scheme.klassSubject.klass',
            'scheme.optionalSubject.gradeSubject.subject',
            'supervisorReviewer',
            'reviewer',
        ]);

        $user = auth()->user();
        $isTeacherOwner = $lessonPlan->teacher_id === $user->id;
        $isAdmin = SchemeOfWorkPolicy::isAdmin($user);
        $isSupervisor = $lessonPlan->teacher && (int) $lessonPlan->teacher->reporting_to === $user->id;
        $isHod = $lessonPlan->scheme ? $this->isHodForPlan($user, $lessonPlan) : false;

        $canSubmit = ($isTeacherOwner || $isAdmin) && in_array($lessonPlan->status, ['draft', 'revision_required'], true);
        $canSupervisorReview = ($isSupervisor || $isAdmin) && $lessonPlan->status === 'submitted';
        $canHodReview = ($isHod || $isAdmin) && in_array($lessonPlan->status, ['submitted', 'supervisor_reviewed'], true);
        $canMarkTaught = ($isTeacherOwner || $isAdmin) && $lessonPlan->status === 'approved';
        $canEdit = ($isTeacherOwner || $isAdmin) && $lessonPlan->isEditable();

        return view('schemes.lesson-plans.show', compact(
            'lessonPlan',
            'isTeacherOwner',
            'canSubmit',
            'canSupervisorReview',
            'canHodReview',
            'canMarkTaught',
            'canEdit'
        ));
    }

    /**
     * Show the form for editing the specified lesson plan.
     */
    public function edit(LessonPlan $lessonPlan): View {
        $this->authorizeMutatePlan($lessonPlan);

        abort_unless($lessonPlan->isEditable() || $lessonPlan->status === 'taught', 403, 'This lesson plan cannot be edited in its current status.');

        $lessonPlan->load([
            'entry',
            'scheme.klassSubject.gradeSubject.subject',
            'scheme.klassSubject.klass',
            'scheme.optionalSubject.gradeSubject.subject',
        ]);

        return view('schemes.lesson-plans.edit', compact('lessonPlan'));
    }

    /**
     * Update the specified lesson plan.
     */
    public function update(UpdateLessonPlanRequest $request, LessonPlan $lessonPlan): RedirectResponse {
        $this->authorizeMutatePlan($lessonPlan);

        DB::transaction(function () use ($lessonPlan, $request) {
            $fresh = LessonPlan::query()->lockForUpdate()->findOrFail($lessonPlan->id);
            abort_unless(
                $fresh->isEditable() || $fresh->status === 'taught',
                403,
                'This lesson plan cannot be edited in its current status.'
            );

            $fresh->update($request->validated());
        });

        return redirect()->route('lesson-plans.show', $lessonPlan)
            ->with('success', 'Lesson plan updated successfully.');
    }

    /**
     * Remove the specified lesson plan (soft delete).
     */
    public function destroy(LessonPlan $lessonPlan): RedirectResponse {
        $this->authorizeMutatePlan($lessonPlan);
        $schemeId = $lessonPlan->scheme_of_work_id;
        $lessonPlan->delete();

        if ($schemeId) {
            return redirect()->route('schemes.show', $schemeId)
                ->with('success', 'Lesson plan deleted.');
        }

        return redirect()->route('schemes.teacher.dashboard')
            ->with('success', 'Lesson plan deleted.');
    }

    /**
     * Teacher submits lesson plan for review.
     */
    public function submit(LessonPlan $lessonPlan, LessonPlanService $service): RedirectResponse {
        $this->authorizeMutatePlan($lessonPlan);

        try {
            $service->submitPlan($lessonPlan, auth()->user());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->route('lesson-plans.show', $lessonPlan)
            ->with('success', 'Lesson plan submitted for review.');
    }

    /**
     * Supervisor approves a submitted lesson plan.
     */
    public function supervisorApprove(Request $request, LessonPlan $lessonPlan, LessonPlanService $service): RedirectResponse {
        $this->authorizeSupervisorReview($lessonPlan);

        try {
            $service->supervisorApprove($lessonPlan, auth()->user(), $request->input('comments'));
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->route('lesson-plans.show', $lessonPlan)
            ->with('success', 'Lesson plan approved and forwarded to HOD.');
    }

    /**
     * Supervisor returns a submitted lesson plan for revision.
     */
    public function supervisorReturn(Request $request, LessonPlan $lessonPlan, LessonPlanService $service): RedirectResponse {
        $this->authorizeSupervisorReview($lessonPlan);

        $request->validate(['comments' => ['required', 'string', 'min:5']]);

        try {
            $service->supervisorReturn($lessonPlan, auth()->user(), $request->input('comments'));
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->route('lesson-plans.show', $lessonPlan)
            ->with('success', 'Lesson plan returned for revision.');
    }

    /**
     * HOD approves a lesson plan.
     */
    public function approve(Request $request, LessonPlan $lessonPlan, LessonPlanService $service): RedirectResponse {
        $this->authorizeHodReview($lessonPlan);

        try {
            $service->approvePlan($lessonPlan, auth()->user(), $request->input('comments'));
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->route('lesson-plans.show', $lessonPlan)
            ->with('success', 'Lesson plan approved.');
    }

    /**
     * HOD returns a lesson plan for revision.
     */
    public function returnForRevision(Request $request, LessonPlan $lessonPlan, LessonPlanService $service): RedirectResponse {
        $this->authorizeHodReview($lessonPlan);

        $request->validate(['comments' => ['required', 'string', 'min:5']]);

        try {
            $service->returnForRevision($lessonPlan, auth()->user(), $request->input('comments'));
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->route('lesson-plans.show', $lessonPlan)
            ->with('success', 'Lesson plan returned for revision.');
    }

    /**
     * Transition the lesson plan status to 'taught'.
     * Only allowed when status is 'approved'.
     */
    public function markTaught(LessonPlan $lessonPlan): RedirectResponse {
        $this->authorizeMutatePlan($lessonPlan);

        DB::transaction(function () use ($lessonPlan) {
            $fresh = LessonPlan::query()->lockForUpdate()->findOrFail($lessonPlan->id);
            abort_unless($fresh->status === 'approved', 422, 'Lesson plan must be approved before marking as taught.');

            $fresh->update(['status' => 'taught', 'taught_at' => now()]);
        });

        return redirect()->route('lesson-plans.edit', $lessonPlan)
            ->with('success', 'Lesson marked as taught. Add your reflection notes below.');
    }
}
