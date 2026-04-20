<?php

namespace App\Http\Controllers\Schemes;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Schemes\StoreStandardSchemeRequest;
use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\SchoolSetup;
use App\Models\Schemes\StandardScheme;
use App\Models\Schemes\Syllabus;
use App\Models\Term;
use App\Services\Schemes\StandardSchemeService;
use App\Services\Schemes\SyllabusSourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\View\View;

class StandardSchemeController extends Controller {

    public function index(Request $request): View {
        $this->authorize('viewAny', StandardScheme::class);

        $user = auth()->user();

        // Load all active grades
        $grades = \App\Models\Grade::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // Sticky grade filter via session
        $selectedGradeId = $request->input('grade_id');
        if ($selectedGradeId) {
            session(['standard_schemes_grade_id' => $selectedGradeId]);
        } else {
            $selectedGradeId = session('standard_schemes_grade_id');
        }

        // Default to first grade if none selected or session value no longer valid
        if (!$selectedGradeId || !$grades->contains('id', (int) $selectedGradeId)) {
            $selectedGradeId = $grades->first()?->id;
            session(['standard_schemes_grade_id' => $selectedGradeId]);
        }

        $query = StandardScheme::query()
            ->with(['subject', 'grade', 'term', 'department', 'creator', 'panelLead'])
            ->withCount('derivedSchemes')
            ->visibleTo($user)
            ->orderBy('created_at', 'desc');

        if ($selectedGradeId) {
            $query->where('grade_id', $selectedGradeId);
        }

        $schemes = $query->get();

        return view('schemes.standard.index', compact('schemes', 'grades', 'selectedGradeId'));
    }

    public function create(): View {
        $this->authorize('create', StandardScheme::class);

        $user = auth()->user();
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = (int) old('term_id', $currentTerm?->id);
        $terms = Term::orderBy('year', 'desc')->orderBy('term', 'desc')->get();
        $grades = $this->availableGradesForTerm($selectedTermId);
        $selectedGradeId = (int) old('grade_id', $grades->first()?->id);

        if (!$grades->contains('id', $selectedGradeId)) {
            $selectedGradeId = (int) ($grades->first()?->id ?? 0);
        }

        $subjects = $this->availableSubjectsForContext($selectedTermId, $selectedGradeId, $user);

        return view('schemes.standard.create', compact(
            'subjects',
            'grades',
            'terms',
            'currentTerm',
            'selectedTermId',
            'selectedGradeId',
        ));
    }

    public function store(StoreStandardSchemeRequest $request, StandardSchemeService $service): RedirectResponse {
        try {
            $data = $request->validated();
            $scheme = $service->createWithEntries($data, auth()->id());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('standard-schemes.show', $scheme)
            ->with('success', 'Standard scheme created successfully. Fill in the weekly entries below.');
    }

    public function gradesForTerm(Request $request): JsonResponse
    {
        $this->authorize('create', StandardScheme::class);

        $validated = $request->validate([
            'term_id' => ['required', 'integer', 'exists:terms,id'],
        ]);

        $grades = $this->availableGradesForTerm((int) $validated['term_id'])
            ->map(fn (Grade $grade) => [
                'id' => $grade->id,
                'name' => $grade->name,
            ])
            ->values();

        return response()->json($grades);
    }

    public function subjectsForContext(Request $request): JsonResponse
    {
        $this->authorize('create', StandardScheme::class);

        $validated = $request->validate([
            'term_id' => ['required', 'integer', 'exists:terms,id'],
            'grade_id' => ['required', 'integer', 'exists:grades,id'],
        ]);

        $subjects = $this->availableSubjectsForContext(
            (int) $validated['term_id'],
            (int) $validated['grade_id'],
            auth()->user(),
        )->map(fn (object $subject) => [
            'id' => $subject->id,
            'name' => $subject->name,
            'label' => $subject->label,
        ])->values();

        return response()->json($subjects);
    }

    public function show(StandardScheme $standardScheme): View {
        $this->authorize('view', $standardScheme);

        $standardScheme->load([
            'entries.objectives.topic',
            'entries.syllabusTopic',
            'subject',
            'grade',
            'term',
            'department',
            'creator',
            'panelLead',
            'reviewer',
            'publisher',
            'contributors',
            'derivedSchemes.teacher',
            'workflowAudits.actor',
        ]);

        // Load syllabus structure for the planner panel
        $syllabus = Syllabus::query()
            ->where('subject_id', $standardScheme->subject_id)
            ->forGrade($standardScheme->grade->name ?? '')
            ->where('is_active', true)
            ->first();

        $syllabusStructure = null;
        $syllabusUnavailable = false;

        if ($syllabus) {
            $syllabusStructure = app(SyllabusSourceService::class)->getDisplayStructure($syllabus);
        } else {
            $syllabusUnavailable = true;
        }

        $user = auth()->user();
        $canEdit = $user->can('update', $standardScheme);
        $canSubmit = $user->can('submit', $standardScheme);
        $canReview = $user->can('review', $standardScheme);
        $canPublish = $user->can('publish', $standardScheme);
        $canUnpublish = $user->can('unpublish', $standardScheme);
        $canClone = $user->can('clone', $standardScheme);
        $copyTerms = Term::query()
            ->where('id', '!=', $standardScheme->term_id)
            ->orderBy('year', 'desc')
            ->orderBy('term', 'desc')
            ->get();

        return view('schemes.standard.show', compact(
            'standardScheme',
            'syllabus',
            'syllabusStructure',
            'syllabusUnavailable',
            'canEdit',
            'canSubmit',
            'canReview',
            'canPublish',
            'canUnpublish',
            'canClone',
            'copyTerms'
        ));
    }

    public function document(StandardScheme $standardScheme): View
    {
        $this->authorize('view', $standardScheme);

        $standardScheme->loadMissing([
            'entries.objectives.topic',
            'subject',
            'grade',
            'term',
            'department',
            'creator',
            'panelLead',
            'reviewer',
            'publisher',
            'contributors',
            'derivedSchemes.teacher',
        ]);

        return view('schemes.standard.document', [
            'standardScheme' => $standardScheme,
            'school' => SchoolSetup::current(),
        ]);
    }

    public function clone(Request $request, StandardScheme $standardScheme, StandardSchemeService $service): RedirectResponse {
        $this->authorize('clone', $standardScheme);

        $validated = $request->validate([
            'term_id' => ['required', 'integer', 'exists:terms,id'],
        ]);

        try {
            $clone = $service->cloneScheme($standardScheme, (int) auth()->id(), (int) $validated['term_id']);
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('standard-schemes.show', $clone)
            ->with('success', 'Standard scheme copied successfully.');
    }

    public function destroy(StandardScheme $standardScheme): RedirectResponse {
        $this->authorize('delete', $standardScheme);

        $standardScheme->delete();

        return redirect()
            ->route('standard-schemes.index')
            ->with('success', 'Standard scheme deleted.');
    }

    private function availableGradesForTerm(?int $termId)
    {
        if (!$termId) {
            return collect();
        }

        return Grade::query()
            ->where('term_id', $termId)
            ->orderBy('sequence')
            ->orderBy('name')
            ->get(['id', 'name', 'term_id', 'year', 'sequence'])
            ->unique(fn (Grade $grade) => strtolower(trim((string) $grade->name)))
            ->values();
    }

    private function availableSubjectsForContext(?int $termId, ?int $gradeId, $user)
    {
        if (!$termId || !$gradeId) {
            return collect();
        }

        $query = GradeSubject::query()
            ->with([
                'subject:id,name',
                'grade:id,name',
            ])
            ->where('term_id', $termId)
            ->where('grade_id', $gradeId)
            ->orderBy('sequence')
            ->orderBy('id');

        if (!$user->hasAnyRoles(['Administrator', 'Academic Admin', 'Scheme Admin'])) {
            $departmentIds = Department::query()
                ->where('department_head', $user->id)
                ->orWhere('assistant', $user->id)
                ->pluck('id');

            $query->whereIn('department_id', $departmentIds);
        }

        return $query->get()
            ->filter(fn (GradeSubject $gradeSubject) => filled($gradeSubject->subject?->name))
            ->map(fn (GradeSubject $gradeSubject) => (object) [
                'id' => $gradeSubject->subject_id,
                'name' => $gradeSubject->subject?->name,
                'label' => sprintf(
                    '%s (%s)',
                    $gradeSubject->subject?->name,
                    $gradeSubject->grade?->name
                ),
            ])
            ->unique('id')
            ->values();
    }

}
