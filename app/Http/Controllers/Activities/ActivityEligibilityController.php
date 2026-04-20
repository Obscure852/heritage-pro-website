<?php

namespace App\Http\Controllers\Activities;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activities\SyncActivityEligibilityRequest;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEligibilityTarget;
use App\Models\Grade;
use App\Models\House;
use App\Models\Klass;
use App\Models\StudentFilter;
use App\Services\Activities\ActivityOwnershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ActivityEligibilityController extends Controller
{
    public function __construct(private readonly ActivityOwnershipService $activityOwnershipService)
    {
    }

    public function edit(Activity $activity)
    {
        $this->authorize('manageEligibility', $activity);

        $selectedTargets = $activity->eligibilityTargets()
            ->get()
            ->groupBy('target_type')
            ->map(fn ($targets) => $targets->pluck('target_id')->map(fn ($id) => (int) $id)->all());

        [$grades, $selectedGradeIds] = $this->deduplicateOptions(
            Grade::query()
                ->where('term_id', $activity->term_id)
                ->where('year', $activity->year)
                ->where('active', true)
                ->orderBy('sequence')
                ->orderBy('name')
                ->get(),
            $selectedTargets->get(ActivityEligibilityTarget::TARGET_GRADE, []),
            fn (Grade $grade) => Str::lower(trim((string) $grade->name))
        );

        [$klasses, $selectedKlassIds] = $this->deduplicateOptions(
            Klass::query()
                ->with('grade:id,name')
                ->where('term_id', $activity->term_id)
                ->where('year', $activity->year)
                ->orderBy('name')
                ->get(),
            $selectedTargets->get(ActivityEligibilityTarget::TARGET_CLASS, []),
            fn (Klass $klass) => Str::lower(trim($klass->name . '|' . ($klass->grade?->name ?? '')))
        );

        [$houses, $selectedHouseIds] = $this->deduplicateOptions(
            House::query()
                ->where('term_id', $activity->term_id)
                ->where('year', $activity->year)
                ->orderBy('name')
                ->get(),
            $selectedTargets->get(ActivityEligibilityTarget::TARGET_HOUSE, []),
            fn (House $house) => Str::lower(trim((string) $house->name))
        );

        [$studentFilters, $selectedStudentFilterIds] = $this->deduplicateOptions(
            StudentFilter::query()
                ->orderBy('name')
                ->get(),
            $selectedTargets->get(ActivityEligibilityTarget::TARGET_STUDENT_FILTER, []),
            fn (StudentFilter $studentFilter) => Str::lower(trim((string) $studentFilter->name))
        );

        return view('activities.eligibility', [
            'activity' => $activity,
            'grades' => $grades,
            'klasses' => $klasses,
            'houses' => $houses,
            'studentFilters' => $studentFilters,
            'selectedTargets' => [
                ActivityEligibilityTarget::TARGET_GRADE => $selectedGradeIds,
                ActivityEligibilityTarget::TARGET_CLASS => $selectedKlassIds,
                ActivityEligibilityTarget::TARGET_HOUSE => $selectedHouseIds,
                ActivityEligibilityTarget::TARGET_STUDENT_FILTER => $selectedStudentFilterIds,
            ],
        ]);
    }

    public function update(SyncActivityEligibilityRequest $request, Activity $activity): RedirectResponse
    {
        $this->activityOwnershipService->syncEligibilityTargets($activity, $request->validated(), $request->user());

        return redirect()
            ->route('activities.eligibility.edit', $activity)
            ->with('message', 'Activity eligibility targets updated successfully.');
    }

    /**
     * @return array{0: \Illuminate\Support\Collection<int, mixed>, 1: array<int>}
     */
    private function deduplicateOptions(Collection $items, array $selectedIds, callable $displayKeyResolver): array
    {
        $groupedItems = $items->groupBy(function ($item) use ($displayKeyResolver) {
            return (string) $displayKeyResolver($item);
        });

        $canonicalItems = $groupedItems
            ->map(fn (Collection $group) => $group->first())
            ->values();

        $canonicalIdsByKey = $groupedItems
            ->map(fn (Collection $group) => (int) $group->first()->id);

        $resolvedSelectedIds = collect($selectedIds)
            ->map(function (int $selectedId) use ($items, $displayKeyResolver, $canonicalIdsByKey) {
                $item = $items->firstWhere('id', $selectedId);

                if (!$item) {
                    return null;
                }

                $displayKey = (string) $displayKeyResolver($item);

                return $canonicalIdsByKey->get($displayKey);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [$canonicalItems, $resolvedSelectedIds];
    }
}
