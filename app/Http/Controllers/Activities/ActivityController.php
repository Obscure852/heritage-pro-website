<?php

namespace App\Http\Controllers\Activities;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Activities\StoreActivityRequest;
use App\Http\Requests\Activities\UpdateActivityRequest;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEligibilityTarget;
use App\Models\Activities\ActivityStaffAssignment;
use App\Models\Fee\FeeType;
use App\Models\Grade;
use App\Models\House;
use App\Models\Klass;
use App\Models\StudentFilter;
use App\Models\Term;
use App\Services\Activities\ActivityService;
use App\Services\Activities\ActivitySettingsService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ActivityController extends Controller
{
    public function __construct(
        private readonly ActivityService $activityService,
        private readonly ActivitySettingsService $activitySettingsService
    )
    {
    }

    public function index(Request $request)
    {
        $selectedTerm = $this->resolveSelectedTerm();
        $user = $request->user();
        $hasGlobalViewAccess = $user?->hasAnyRoles([
            'Administrator',
            'Activities Admin',
            'Activities Edit',
            'Activities View',
        ]) ?? false;

        $activities = Activity::query()
            ->with(['creator', 'term', 'feeType'])
            ->withCount([
                'staffAssignments as active_staff_assignments_count' => fn ($query) => $query->where('active', true),
                'enrollments as enrollments_count' => fn ($query) => $query->where('status', \App\Models\Activities\ActivityEnrollment::STATUS_ACTIVE),
                'sessions',
                'events',
            ])
            ->when($selectedTerm, fn ($query) => $query->where('term_id', $selectedTerm->id))
            ->when(
                !$hasGlobalViewAccess,
                fn ($query) => $query->whereHas('staffAssignments', function ($assignmentQuery) use ($user) {
                    $assignmentQuery
                        ->where('user_id', $user?->id)
                        ->where('active', true);
                })
            )
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . trim((string) $request->input('search')) . '%';

                $query->where(function ($activityQuery) use ($search) {
                    $activityQuery->where('name', 'like', $search)
                        ->orWhere('code', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhere('default_location', 'like', $search);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->value()))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->value()))
            ->when($request->filled('delivery_mode'), fn ($query) => $query->where('delivery_mode', $request->string('delivery_mode')->value()))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('activities.index', [
            'activities' => $activities,
            'selectedTerm' => $selectedTerm,
            'statuses' => Activity::statuses(),
            'categories' => $this->activitySettingsService->activeCategoryOptions(),
            'deliveryModes' => $this->activitySettingsService->activeDeliveryModeOptions(),
        ]);
    }

    public function create()
    {
        $selectedTerm = $this->resolveSelectedTerm();
        $activity = new Activity($this->activitySettingsService->activityDefaults());

        return view('activities.create', [
            'activity' => $activity,
            'selectedTerm' => $selectedTerm,
            'categories' => $this->activitySettingsService->activeCategoryOptions(),
            'deliveryModes' => $this->activitySettingsService->activeDeliveryModeOptions(),
            'participationModes' => $this->activitySettingsService->activeParticipationModeOptions(),
            'resultModes' => $this->activitySettingsService->activeResultModeOptions(),
            'genderPolicies' => $this->activitySettingsService->activeGenderPolicyOptions(),
            'feeTypes' => $this->optionalFeeTypes(),
        ]);
    }

    public function store(StoreActivityRequest $request): RedirectResponse
    {
        $activity = $this->activityService->create(
            $request->validated(),
            $request->user(),
            $this->resolveSelectedTerm()
        );

        return redirect()
            ->route('activities.show', $activity)
            ->with('message', 'Activity created successfully.');
    }

    public function show(Activity $activity)
    {
        $this->authorize('view', $activity);

        $activity->load([
            'creator',
            'updater',
            'term',
            'feeType',
            'staffAssignments.user',
            'eligibilityTargets',
        ])->loadCount([
            'staffAssignments as active_staff_assignments_count' => fn ($query) => $query->where('active', true),
            'enrollments as enrollments_count' => fn ($query) => $query->where('status', \App\Models\Activities\ActivityEnrollment::STATUS_ACTIVE),
            'sessions',
            'events',
            'feeCharges',
        ]);

        return view('activities.show', [
            'activity' => $activity,
            'staffSummary' => $this->buildStaffSummary($activity),
            'eligibilitySummary' => $this->buildEligibilitySummary($activity->eligibilityTargets, $activity->term_id, $activity->year),
        ]);
    }

    public function edit(Activity $activity)
    {
        $this->authorize('update', $activity);

        return view('activities.edit', [
            'activity' => $activity,
            'selectedTerm' => $activity->term,
            'categories' => $this->activitySettingsService->categoryOptionsForValue($activity->category),
            'deliveryModes' => $this->activitySettingsService->deliveryModeOptionsForValue($activity->delivery_mode),
            'participationModes' => $this->activitySettingsService->participationModeOptionsForValue($activity->participation_mode),
            'resultModes' => $this->activitySettingsService->resultModeOptionsForValue($activity->result_mode),
            'genderPolicies' => $this->activitySettingsService->genderPolicyOptionsForValue($activity->gender_policy),
            'feeTypes' => $this->optionalFeeTypes(),
        ]);
    }

    public function update(UpdateActivityRequest $request, Activity $activity): RedirectResponse
    {
        $this->authorize('update', $activity);

        $this->activityService->update($activity, $request->validated(), $request->user());

        return redirect()
            ->route('activities.show', $activity)
            ->with('message', 'Activity updated successfully.');
    }

    public function activate(Request $request, Activity $activity): RedirectResponse
    {
        return $this->transition($request, $activity, Activity::STATUS_ACTIVE, 'Activity activated successfully.');
    }

    public function pause(Request $request, Activity $activity): RedirectResponse
    {
        return $this->transition($request, $activity, Activity::STATUS_PAUSED, 'Activity paused successfully.');
    }

    public function close(Request $request, Activity $activity): RedirectResponse
    {
        return $this->transition($request, $activity, Activity::STATUS_CLOSED, 'Activity closed successfully.');
    }

    public function archive(Request $request, Activity $activity): RedirectResponse
    {
        return $this->transition($request, $activity, Activity::STATUS_ARCHIVED, 'Activity archived successfully.');
    }

    private function transition(Request $request, Activity $activity, string $targetStatus, string $successMessage): RedirectResponse
    {
        $this->authorize('update', $activity);

        try {
            $this->activityService->transition($activity, $targetStatus, $request->user());
        } catch (ValidationException $exception) {
            return redirect()
                ->back()
                ->withErrors($exception->errors())
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()
            ->route('activities.show', $activity)
            ->with('message', $successMessage);
    }

    private function resolveSelectedTerm(): ?Term
    {
        $selectedTermId = session('selected_term_id');

        if ($selectedTermId) {
            return Term::query()->find($selectedTermId);
        }

        return TermHelper::getCurrentTerm();
    }

    private function optionalFeeTypes()
    {
        return FeeType::query()
            ->active()
            ->optional()
            ->orderBy('name')
            ->get();
    }

    private function buildStaffSummary(Activity $activity): array
    {
        $activeAssignments = $activity->staffAssignments
            ->where('active', true)
            ->sortByDesc('is_primary')
            ->values();

        return [
            'primaryCoordinator' => $activeAssignments->firstWhere('is_primary', true),
            'activeAssignments' => $activeAssignments,
            'historicalCount' => $activity->staffAssignments->where('active', false)->count(),
        ];
    }

    private function buildEligibilitySummary(Collection $targets, int $termId, int $year): array
    {
        $groupedIds = $targets
            ->groupBy('target_type')
            ->map(fn (Collection $group) => $group->pluck('target_id')->map(fn ($id) => (int) $id)->all());

        $grades = Grade::query()
            ->whereIn('id', $groupedIds->get(ActivityEligibilityTarget::TARGET_GRADE, []))
            ->orderBy('sequence')
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        $klasses = Klass::query()
            ->with('grade:id,name')
            ->whereIn('id', $groupedIds->get(ActivityEligibilityTarget::TARGET_CLASS, []))
            ->orderBy('name')
            ->get()
            ->map(fn (Klass $klass) => trim($klass->name . ($klass->grade ? ' (' . $klass->grade->name . ')' : '')))
            ->unique()
            ->values()
            ->all();

        $houses = House::query()
            ->where('term_id', $termId)
            ->where('year', $year)
            ->whereIn('id', $groupedIds->get(ActivityEligibilityTarget::TARGET_HOUSE, []))
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        $studentFilters = StudentFilter::query()
            ->whereIn('id', $groupedIds->get(ActivityEligibilityTarget::TARGET_STUDENT_FILTER, []))
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        return [
            [
                'label' => 'Grades',
                'items' => $grades,
            ],
            [
                'label' => 'Classes',
                'items' => $klasses,
            ],
            [
                'label' => 'Houses',
                'items' => $houses,
            ],
            [
                'label' => 'Student Filters',
                'items' => $studentFilters,
            ],
        ];
    }
}
