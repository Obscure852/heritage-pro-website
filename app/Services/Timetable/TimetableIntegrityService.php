<?php

namespace App\Services\Timetable;

use App\Models\Timetable\TimetableSlot;
use App\Services\Timetable\Support\BlockPlacementRules;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TimetableIntegrityService {
    public function __construct(
        protected PeriodSettingsService $periodSettingsService
    ) {}

    /**
     * Return cached integrity analysis for read-heavy UI flows.
     *
     * Cached results are invalidated explicitly on slot mutations.
     *
     * @return array{
     *   issues: array<int, array<string, mixed>>,
     *   counts: array<string, int>,
     *   slot_flags: array<int, array<string, bool>>,
     *   locked_blockers: array<int, array<string, mixed>>
     * }
     */
    public function getCachedAnalysis(int $timetableId): array {
        return Cache::rememberForever(
            $this->analysisCacheKey($timetableId),
            fn(): array => $this->analyze($timetableId)
        );
    }

    /**
     * Invalidate cached integrity analysis for a timetable.
     */
    public function forgetCachedAnalysis(int $timetableId): void {
        Cache::forget($this->analysisCacheKey($timetableId));
    }

    /**
     * Analyze timetable slot integrity and return hard-issue metadata.
     *
     * @return array{
     *   issues: array<int, array<string, mixed>>,
     *   counts: array<string, int>,
     *   slot_flags: array<int, array<string, bool>>,
     *   locked_blockers: array<int, array<string, mixed>>
     * }
     */
    public function analyze(int $timetableId): array {
        $periodDefinitions = $this->periodSettingsService->getPeriodDefinitions();
        $totalPeriods = count($periodDefinitions);
        $breakIntervals = $this->periodSettingsService->getBreakIntervals();
        $breakAfterPeriods = array_map('intval', array_column($breakIntervals, 'after_period'));
        $breakSet = array_fill_keys($breakAfterPeriods, true);
        $validDoubleStartSet = array_fill_keys(
            BlockPlacementRules::computeValidDoubleStartPeriods($totalPeriods, $breakAfterPeriods),
            true
        );

        $slots = TimetableSlot::where('timetable_id', $timetableId)
            ->with([
                'klassSubject.klass:id,grade_id,name',
                'optionalSubject:id,grade_id,name',
            ])
            ->get();

        $issues = [];
        $slotFlags = [];

        $this->analyzeBlockIssues($slots, $breakSet, $validDoubleStartSet, $issues, $slotFlags);
        $this->analyzeCoreElectiveOverlap($slots, $issues, $slotFlags);
        $this->analyzeCouplingSplit($slots, $issues, $slotFlags);
        $this->analyzeCouplingDayConflict($slots, $issues, $slotFlags);

        $counts = [
            'double_misalignment' => 0,
            'break_span' => 0,
            'block_shape_invalid' => 0,
            'core_elective_overlap' => 0,
            'coupling_split' => 0,
            'coupling_day_conflict' => 0,
        ];

        foreach ($issues as $issue) {
            $type = (string) ($issue['type'] ?? '');
            if (array_key_exists($type, $counts)) {
                $counts[$type]++;
            }
        }

        $lockedBlockers = array_values(array_filter(
            $issues,
            fn(array $issue) => !empty($issue['locked_slot_ids'])
        ));

        return [
            'issues' => array_values($issues),
            'counts' => $counts,
            'slot_flags' => $slotFlags,
            'locked_blockers' => $lockedBlockers,
        ];
    }

    /**
     * Repair non-locked integrity issues.
     *
     * @return array{
     *   before: array<string, mixed>,
     *   after: array<string, mixed>,
     *   deleted_slot_ids: int[],
     *   deleted_count: int,
     *   unresolved_locked: array<int, array<string, mixed>>
     * }
     */
    public function repairNonLocked(int $timetableId): array {
        $this->forgetCachedAnalysis($timetableId);
        $before = $this->analyze($timetableId);
        $slots = TimetableSlot::where('timetable_id', $timetableId)
            ->select(['id', 'optional_subject_id', 'is_locked'])
            ->get()
            ->keyBy('id');

        $toDelete = [];

        foreach ($before['issues'] as $issue) {
            $type = (string) ($issue['type'] ?? '');

            if (in_array($type, ['double_misalignment', 'break_span', 'block_shape_invalid'], true)) {
                foreach ((array) ($issue['slot_ids'] ?? []) as $slotId) {
                    $slotId = (int) $slotId;
                    $slot = $slots->get($slotId);
                    if ($slot && !$slot->is_locked) {
                        $toDelete[$slotId] = true;
                    }
                }
                continue;
            }

            if ($type === 'core_elective_overlap') {
                foreach ((array) ($issue['optional_slot_ids'] ?? []) as $slotId) {
                    $slotId = (int) $slotId;
                    $slot = $slots->get($slotId);
                    if ($slot && !$slot->is_locked && $slot->optional_subject_id !== null) {
                        $toDelete[$slotId] = true;
                    }
                }
                continue;
            }

            if ($type === 'coupling_split') {
                foreach ((array) ($issue['minority_slot_ids'] ?? []) as $slotId) {
                    $slotId = (int) $slotId;
                    $slot = $slots->get($slotId);
                    if ($slot && !$slot->is_locked) {
                        $toDelete[$slotId] = true;
                    }
                }
            }
        }

        $deleteIds = array_map('intval', array_keys($toDelete));
        if (!empty($deleteIds)) {
            DB::transaction(function () use ($timetableId, $deleteIds): void {
                TimetableSlot::where('timetable_id', $timetableId)
                    ->whereIn('id', $deleteIds)
                    ->where('is_locked', false)
                    ->delete();
            });
        }

        $after = $this->analyze($timetableId);
        $this->forgetCachedAnalysis($timetableId);

        return [
            'before' => $before,
            'after' => $after,
            'deleted_slot_ids' => $deleteIds,
            'deleted_count' => count($deleteIds),
            'unresolved_locked' => $after['locked_blockers'],
        ];
    }

    private function analysisCacheKey(int $timetableId): string {
        return "timetable_integrity_analysis_{$timetableId}";
    }

    private function analyzeBlockIssues(
        Collection $slots,
        array $breakSet,
        array $validDoubleStartSet,
        array &$issues,
        array &$slotFlags
    ): void {
        $blockGroups = $slots
            ->whereNotNull('block_id')
            ->groupBy('block_id');

        foreach ($blockGroups as $blockId => $group) {
            $ordered = $group->sortBy('period_number')->values();
            $first = $ordered->first();
            if (!$first) {
                continue;
            }

            $slotIds = $ordered->pluck('id')->map(fn($id) => (int) $id)->all();
            $lockedSlotIds = $ordered->filter(fn($s) => (bool) $s->is_locked)->pluck('id')->map(fn($id) => (int) $id)->all();
            $duration = max(1, (int) $first->duration);
            $days = $ordered->pluck('day_of_cycle')->unique()->map(fn($d) => (int) $d)->values()->all();
            $periods = $ordered->pluck('period_number')->map(fn($p) => (int) $p)->sort()->values()->all();
            $startPeriod = (int) ($periods[0] ?? 0);
            $startDay = (int) ($days[0] ?? 0);

            $shapeInvalid = false;
            if (count($days) !== 1) {
                $shapeInvalid = true;
            } elseif (count($periods) !== $duration) {
                $shapeInvalid = true;
            } elseif (count($slotIds) !== $duration) {
                $shapeInvalid = true;
            } else {
                $expected = range($startPeriod, $startPeriod + $duration - 1);
                if ($periods !== $expected) {
                    $shapeInvalid = true;
                }
            }

            if ($shapeInvalid) {
                $this->addIssue(
                    $issues,
                    $slotFlags,
                    'block_shape_invalid',
                    $slotIds,
                    $lockedSlotIds,
                    "Block {$blockId} has invalid shape (non-contiguous periods or mismatched duration).",
                    ['block_id' => (string) $blockId]
                );
            }

            if ($startDay > 0 && $duration > 1 && $this->durationSpansBreak($startPeriod, $duration, $breakSet)) {
                $this->addIssue(
                    $issues,
                    $slotFlags,
                    'break_span',
                    $slotIds,
                    $lockedSlotIds,
                    "Block {$blockId} spans a configured break.",
                    ['block_id' => (string) $blockId]
                );
            }

            if ($duration === 2 && BlockPlacementRules::isMisalignedDoubleStart($startPeriod, $duration, $validDoubleStartSet)) {
                $this->addIssue(
                    $issues,
                    $slotFlags,
                    'double_misalignment',
                    $slotIds,
                    $lockedSlotIds,
                    "Block {$blockId} starts at misaligned double period {$startPeriod}.",
                    ['block_id' => (string) $blockId, 'start_period' => $startPeriod]
                );
            }
        }

        $standaloneMulti = $slots
            ->whereNull('block_id')
            ->filter(fn(TimetableSlot $slot) => (int) $slot->duration > 1)
            ->values();

        foreach ($standaloneMulti as $slot) {
            $slotId = (int) $slot->id;
            $lockedSlotIds = $slot->is_locked ? [$slotId] : [];
            $duration = max(1, (int) $slot->duration);
            $startPeriod = (int) $slot->period_number;

            $this->addIssue(
                $issues,
                $slotFlags,
                'block_shape_invalid',
                [$slotId],
                $lockedSlotIds,
                "Slot {$slotId} has duration {$duration} but no block_id grouping."
            );

            if ($this->durationSpansBreak($startPeriod, $duration, $breakSet)) {
                $this->addIssue(
                    $issues,
                    $slotFlags,
                    'break_span',
                    [$slotId],
                    $lockedSlotIds,
                    "Slot {$slotId} spans a configured break."
                );
            }

            if ($duration === 2 && BlockPlacementRules::isMisalignedDoubleStart($startPeriod, $duration, $validDoubleStartSet)) {
                $this->addIssue(
                    $issues,
                    $slotFlags,
                    'double_misalignment',
                    [$slotId],
                    $lockedSlotIds,
                    "Slot {$slotId} is a misaligned double starting at period {$startPeriod}.",
                    ['start_period' => $startPeriod]
                );
            }
        }
    }

    private function analyzeCoreElectiveOverlap(Collection $slots, array &$issues, array &$slotFlags): void {
        $occupancy = [];

        foreach ($slots as $slot) {
            $gradeId = $this->resolveSlotGradeId($slot);
            if ($gradeId <= 0) {
                continue;
            }

            $key = "{$gradeId}:{$slot->day_of_cycle}:{$slot->period_number}";
            if (!isset($occupancy[$key])) {
                $occupancy[$key] = [
                    'core_ids' => [],
                    'optional_ids' => [],
                ];
            }

            if ($slot->optional_subject_id !== null) {
                $occupancy[$key]['optional_ids'][] = (int) $slot->id;
            } else {
                $occupancy[$key]['core_ids'][] = (int) $slot->id;
            }
        }

        foreach ($occupancy as $key => $group) {
            if (empty($group['core_ids']) || empty($group['optional_ids'])) {
                continue;
            }

            $slotIds = array_values(array_unique(array_merge($group['core_ids'], $group['optional_ids'])));
            $lockedSlotIds = $slots
                ->whereIn('id', $slotIds)
                ->filter(fn(TimetableSlot $slot) => (bool) $slot->is_locked)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->all();

            $this->addIssue(
                $issues,
                $slotFlags,
                'core_elective_overlap',
                $slotIds,
                $lockedSlotIds,
                "Core and elective overlap detected at {$key}.",
                [
                    'core_slot_ids' => $group['core_ids'],
                    'optional_slot_ids' => $group['optional_ids'],
                ]
            );
        }
    }

    private function analyzeCouplingSplit(Collection $slots, array &$issues, array &$slotFlags): void {
        $grouped = $slots
            ->filter(fn(TimetableSlot $slot) => !empty($slot->coupling_group_key))
            ->groupBy('coupling_group_key');

        foreach ($grouped as $couplingKey => $groupSlots) {
            $units = $this->buildCouplingUnits($groupSlots);
            if (count($units) <= 1) {
                continue;
            }

            $positionCounts = [];
            foreach ($units as $unit) {
                $posKey = "{$unit['day']}:{$unit['period']}";
                $positionCounts[$posKey] = ($positionCounts[$posKey] ?? 0) + 1;
            }

            if (count($positionCounts) <= 1) {
                continue;
            }

            arsort($positionCounts);
            $majorityPos = (string) array_key_first($positionCounts);
            [$majorityDay, $majorityPeriod] = array_map('intval', explode(':', $majorityPos));

            $minoritySlotIds = [];
            $lockedSlotIds = $groupSlots
                ->filter(fn(TimetableSlot $slot) => (bool) $slot->is_locked)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->values()
                ->all();
            $allGroupSlotIds = $groupSlots
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->values()
                ->all();
            foreach ($units as $unit) {
                $posKey = "{$unit['day']}:{$unit['period']}";
                if ($posKey === $majorityPos) {
                    continue;
                }

                foreach ($unit['slot_ids'] as $slotId) {
                    $minoritySlotIds[] = (int) $slotId;
                }
            }

            if (empty($minoritySlotIds)) {
                continue;
            }

            $this->addIssue(
                $issues,
                $slotFlags,
                'coupling_split',
                $allGroupSlotIds,
                array_values(array_unique($lockedSlotIds)),
                "Coupling group {$couplingKey} is split across multiple periods.",
                [
                    'coupling_group_key' => (string) $couplingKey,
                    'majority_day' => $majorityDay,
                    'majority_period' => $majorityPeriod,
                    'minority_slot_ids' => array_values(array_unique($minoritySlotIds)),
                ]
            );
        }
    }

    /**
     * @return array<int, array{day:int, period:int, slot_ids:int[], locked_slot_ids:int[]}>
     */
    private function buildCouplingUnits(Collection $slots): array {
        $units = [];

        foreach ($slots as $slot) {
            $unitKey = $slot->block_id ? "block:{$slot->block_id}" : "slot:{$slot->id}";
            if (!isset($units[$unitKey])) {
                $units[$unitKey] = [
                    'day' => (int) $slot->day_of_cycle,
                    'period' => (int) $slot->period_number,
                    'slot_ids' => [],
                    'locked_slot_ids' => [],
                ];
            }

            $units[$unitKey]['slot_ids'][] = (int) $slot->id;
            if ($slot->is_locked) {
                $units[$unitKey]['locked_slot_ids'][] = (int) $slot->id;
            }

            if ((int) $slot->period_number < $units[$unitKey]['period']) {
                $units[$unitKey]['period'] = (int) $slot->period_number;
            }
            if ((int) $slot->day_of_cycle < $units[$unitKey]['day']) {
                $units[$unitKey]['day'] = (int) $slot->day_of_cycle;
            }
        }

        return array_values($units);
    }

    private function analyzeCouplingDayConflict(Collection $slots, array &$issues, array &$slotFlags): void {
        $couplingSlots = $slots->filter(fn(TimetableSlot $slot) => !empty($slot->coupling_group_key));
        if ($couplingSlots->isEmpty()) {
            return;
        }

        // Parse each coupling key and group by gradeId => label => day => [slot_ids]
        $gradeLabelDaySlots = [];
        foreach ($couplingSlots as $slot) {
            $key = trim((string) $slot->coupling_group_key);
            if (!preg_match('/^cg_(\d+)_(.+)_[sdt]\d+$/', $key, $m)) {
                continue;
            }
            $gradeId = (int) $m[1];
            $label = $m[2];
            $day = (int) $slot->day_of_cycle;
            $gradeLabelDaySlots[$gradeId][$label][$day][] = (int) $slot->id;
        }

        // For each grade, find label pairs sharing a day
        foreach ($gradeLabelDaySlots as $gradeId => $labels) {
            $labelNames = array_keys($labels);
            $labelCount = count($labelNames);
            for ($i = 0; $i < $labelCount; $i++) {
                for ($j = $i + 1; $j < $labelCount; $j++) {
                    $daysA = array_keys($labels[$labelNames[$i]]);
                    $daysB = array_keys($labels[$labelNames[$j]]);
                    $sharedDays = array_intersect($daysA, $daysB);

                    foreach ($sharedDays as $sharedDay) {
                        $slotIdsA = $labels[$labelNames[$i]][$sharedDay] ?? [];
                        $slotIdsB = $labels[$labelNames[$j]][$sharedDay] ?? [];
                        $allSlotIds = array_values(array_unique(array_merge($slotIdsA, $slotIdsB)));

                        $lockedSlotIds = $slots
                            ->whereIn('id', $allSlotIds)
                            ->filter(fn(TimetableSlot $slot) => (bool) $slot->is_locked)
                            ->pluck('id')
                            ->map(fn($id) => (int) $id)
                            ->all();

                        $this->addIssue(
                            $issues,
                            $slotFlags,
                            'coupling_day_conflict',
                            $allSlotIds,
                            $lockedSlotIds,
                            "Coupling groups '{$labelNames[$i]}' and '{$labelNames[$j]}' (grade {$gradeId}) are both on Day {$sharedDay}. Different coupling groups within a grade must be on different days."
                        );
                    }
                }
            }
        }
    }

    private function durationSpansBreak(int $startPeriod, int $duration, array $breakSet): bool {
        if ($duration <= 1) {
            return false;
        }

        for ($p = $startPeriod; $p < $startPeriod + $duration - 1; $p++) {
            if (isset($breakSet[$p])) {
                return true;
            }
        }

        return false;
    }

    private function resolveSlotGradeId(TimetableSlot $slot): int {
        if ($slot->optional_subject_id !== null) {
            return (int) ($slot->optionalSubject?->grade_id ?? 0);
        }

        return (int) ($slot->klassSubject?->klass?->grade_id ?? 0);
    }

    private function addIssue(
        array &$issues,
        array &$slotFlags,
        string $type,
        array $slotIds,
        array $lockedSlotIds,
        string $message,
        array $details = []
    ): void {
        $slotIds = array_values(array_unique(array_map('intval', $slotIds)));
        $lockedSlotIds = array_values(array_unique(array_map('intval', $lockedSlotIds)));

        if (empty($slotIds)) {
            return;
        }

        $issues[] = array_merge([
            'type' => $type,
            'slot_ids' => $slotIds,
            'locked_slot_ids' => $lockedSlotIds,
            'message' => $message,
        ], $details);

        foreach ($slotIds as $slotId) {
            $slotFlags[$slotId][$type] = true;
        }
    }
}
