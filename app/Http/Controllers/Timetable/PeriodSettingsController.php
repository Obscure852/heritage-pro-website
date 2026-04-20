<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\Timetable\UpdateBlockAllocationsRequest;
use App\Http\Requests\Timetable\UpdateCouplingGroupsRequest;
use App\Http\Requests\Timetable\UpdatePeriodSettingsRequest;
use App\Models\Klass;
use App\Models\OptionalSubject;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableSetting;
use App\Services\Timetable\PeriodSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeriodSettingsController extends Controller {
    private PeriodSettingsService $periodSettingsService;

    public function __construct(PeriodSettingsService $periodSettingsService) {
        $this->periodSettingsService = $periodSettingsService;
    }

    /**
     * Display the period settings page.
     */
    public function index(): View {
        $settings = TimetableSetting::all()->pluck('value', 'key')->toArray();
        $daySchedule = $this->periodSettingsService->getDaySchedule();
        $timetables = Timetable::draft()->get();
        $klasses = Klass::with(['grade', 'subjects.gradeSubject.subject', 'subjects.teacher'])
            ->where('term_id', session('selected_term_id'))
            ->get();

        $optionalSubjectsByGrade = OptionalSubject::with('gradeSubject.subject')
            ->where('term_id', session('selected_term_id'))
            ->where('active', true)
            ->get()
            ->groupBy('grade_id')
            ->map(fn($group) => $group->map(fn($os) => [
                'id' => $os->id,
                'name' => $os->name,
                'subject' => $os->gradeSubject?->subject?->name,
            ]));

        return view('timetable.period-settings.index', compact(
            'settings', 'daySchedule', 'timetables', 'klasses', 'optionalSubjectsByGrade'
        ));
    }

    /**
     * Update bell schedule (period definitions).
     */
    public function updatePeriods(UpdatePeriodSettingsRequest $request): JsonResponse {
        $validated = $request->validated();

        try {
            $breaks = $validated['break_intervals'] ?? $this->periodSettingsService->getBreakIntervals();
            $breakConflicts = $this->periodSettingsService->findBlocksSpanningBreaks($breaks);
            if (!empty($breakConflicts)) {
                return $this->breakConflictResponse($breakConflicts);
            }

            $this->periodSettingsService->savePeriodDefinitions(
                $validated['period_definitions'],
                $breaks,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Bell schedule saved successfully.',
                'daySchedule' => $this->periodSettingsService->getDaySchedule(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving bell schedule: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update break intervals.
     */
    public function updateBreaks(UpdatePeriodSettingsRequest $request): JsonResponse {
        $validated = $request->validated();

        try {
            $newBreaks = $validated['break_intervals'] ?? [];
            $breakConflicts = $this->periodSettingsService->findBlocksSpanningBreaks($newBreaks);
            if (!empty($breakConflicts)) {
                return $this->breakConflictResponse($breakConflicts);
            }

            $this->periodSettingsService->saveBreakIntervals(
                $newBreaks,
                auth()->id()
            );

            // Recalculate period times with new breaks
            $periods = $this->periodSettingsService->getPeriodDefinitions();
            $breaks = $this->periodSettingsService->getBreakIntervals();

            $this->periodSettingsService->savePeriodDefinitions(
                $periods,
                $breaks,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Break intervals saved successfully.',
                'daySchedule' => $this->periodSettingsService->getDaySchedule(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving break intervals: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build a consistent response when existing blocks would cross breaks.
     */
    private function breakConflictResponse(array $conflicts): JsonResponse {
        $examples = collect($conflicts)
            ->take(5)
            ->map(function (array $c) {
                return "{$c['klass_name']} - {$c['subject_name']} (Day {$c['day_of_cycle']}, Period {$c['start_period']}-{$c['end_period']}) crosses break after period {$c['break_after_period']}.";
            })
            ->values()
            ->all();

        return response()->json([
            'success' => false,
            'message' => 'Cannot save these break settings because some existing double/triple blocks would cross a break. Move or delete those blocks first.',
            'errors' => $examples,
            'conflict_count' => count($conflicts),
        ], 422);
    }

    /**
     * Update block allocations for a timetable.
     */
    public function updateBlockAllocations(UpdateBlockAllocationsRequest $request): JsonResponse {
        $validated = $request->validated();

        try {
            $this->periodSettingsService->saveBlockAllocations(
                $validated['timetable_id'],
                $validated['allocations'],
                auth()->id()
            );

            // Validate allocation for each affected class
            $warnings = [];
            $klassIds = collect($validated['allocations'])->pluck('klass_subject_id')->unique();
            foreach ($klassIds as $klassSubjectId) {
                $klassSubject = \App\Models\KlassSubject::find($klassSubjectId);
                if ($klassSubject) {
                    $validation = $this->periodSettingsService->validateClassAllocation(
                        $validated['timetable_id'],
                        $klassSubject->klass_id
                    );
                    if ($validation['exceeded']) {
                        $warnings[] = "Class allocation exceeded: {$validation['allocated']}/{$validation['available']} periods used.";
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Block allocations saved successfully.',
                'warnings' => $warnings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving block allocations: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update coupling groups.
     */
    public function updateCouplingGroups(UpdateCouplingGroupsRequest $request): JsonResponse {
        $validated = $request->validated();

        try {
            $this->periodSettingsService->saveCouplingGroups(
                $validated['coupling_groups'],
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Coupling groups saved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving coupling groups: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get block allocations for a timetable (AJAX endpoint).
     */
    public function getBlockAllocations(Request $request): JsonResponse {
        try {
            $timetableId = $request->query('timetable_id');
            $klassId = $request->query('klass_id');

            if (!$timetableId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timetable ID is required.',
                ], 422);
            }

            $allocations = $this->periodSettingsService->getBlockAllocations(
                (int) $timetableId,
                $klassId ? (int) $klassId : null
            );

            $validation = null;
            if ($klassId) {
                $validation = $this->periodSettingsService->validateClassAllocation(
                    (int) $timetableId,
                    (int) $klassId
                );
            }

            return response()->json([
                'success' => true,
                'allocations' => $allocations,
                'validation' => $validation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading block allocations: ' . $e->getMessage(),
            ], 500);
        }
    }
}
