<?php

namespace App\Services\Timetable;

use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableAuditLog;
use App\Models\Timetable\TimetableBlockAllocation;
use App\Models\Timetable\TimetableSetting;
use App\Models\Timetable\TimetableSlot;
use App\Services\Timetable\Support\BlockPlacementRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SlotManagementService {
    /**
     * Request-local conflict snapshots keyed by timetable_id.
     *
     * @var array<int, array<string, mixed>>
     */
    private array $conflictSnapshotCache = [];

    /**
     * Request-local klass-subject metadata cache.
     *
     * @var array<int, array<string, int|null>>
     */
    private array $klassSubjectContextCache = [];

    /**
     * Request-local optional-subject metadata cache.
     *
     * @var array<int, array<string, int|null>>
     */
    private array $optionalSubjectContextCache = [];

    /**
     * Request-local fallback lookup cache keyed by "klass_id:teacher_id".
     *
     * @var array<string, array<string, int|null>>
     */
    private array $teacherKlassContextCache = [];

    public function __construct(
        protected PeriodSettingsService $periodSettingsService,
        protected ConstraintValidationService $constraintValidationService,
        protected ?TimetableIntegrityService $timetableIntegrityService = null
    ) {}

    /**
     * Clear request-local timetable caches after slot mutations.
     */
    public function invalidateTimetableCaches(int $timetableId): void {
        unset($this->conflictSnapshotCache[$timetableId]);
        $this->constraintValidationService->clearCache($timetableId);
        ($this->timetableIntegrityService ?? app(TimetableIntegrityService::class))
            ->forgetCachedAnalysis($timetableId);
    }

    /**
     * Assign a slot (single or multi-period block) to a timetable.
     *
     * Validates conflicts, break-spanning for blocks, and creates slot(s) atomically.
     *
     * @param int $timetableId
     * @param int $klassSubjectId
     * @param int $dayOfCycle
     * @param int $startPeriod
     * @param int $duration 1 = single, 2 = double, 3 = triple
     * @return array ['success' => bool, 'slots' => array|null, 'errors' => array|null]
     */
    public function assignSlot(
        int $timetableId,
        int $klassSubjectId,
        int $dayOfCycle,
        int $startPeriod,
        int $duration = 1
    ): array {
        return DB::transaction(function () use ($timetableId, $klassSubjectId, $dayOfCycle, $startPeriod, $duration) {
            $klassSubject = KlassSubject::lockForUpdate()->findOrFail($klassSubjectId);
            $teacherId = $klassSubject->user_id;
            $gradeId = (int) ($klassSubject->grade_id ?? 0);
            $venueId = $klassSubject->venue_id ?: null;
            $assistantTeacherId = $klassSubject->assistant_user_id ?: null;

            // Validate block periods don't span across breaks
            if ($duration > 1) {
                $breakError = $this->validateBlockPeriods($startPeriod, $duration);
                if ($breakError) {
                    return ['success' => false, 'slots' => null, 'errors' => [$breakError]];
                }
            }

            // Detect conflicts for all periods in the block
            $periods = range($startPeriod, $startPeriod + $duration - 1);
            $errors = [];

            foreach ($periods as $periodNumber) {
                $conflicts = $this->detectConflicts(
                    $timetableId,
                    $teacherId,
                    $klassSubject->klass_id,
                    $dayOfCycle,
                    $periodNumber,
                    null,
                    $klassSubjectId,
                    [],
                    $gradeId,
                    false,
                    null
                );

                if (!empty($conflicts)) {
                    $errors = array_merge($errors, $conflicts);
                }
            }

            if (!empty($errors)) {
                return ['success' => false, 'slots' => null, 'errors' => $errors];
            }

            // Create slot(s)
            $blockId = $duration > 1 ? Str::uuid()->toString() : null;
            $createdSlots = [];

            foreach ($periods as $periodNumber) {
                $createdSlots[] = TimetableSlot::create([
                    'timetable_id' => $timetableId,
                    'klass_subject_id' => $klassSubjectId,
                    'teacher_id' => $teacherId,
                    'venue_id' => $venueId,
                    'assistant_teacher_id' => $assistantTeacherId,
                    'day_of_cycle' => $dayOfCycle,
                    'period_number' => $periodNumber,
                    'duration' => $duration,
                    'is_locked' => false,
                    'block_id' => $blockId,
                ]);
            }

            // Audit log
            $timetable = Timetable::findOrFail($timetableId);
            $blockLabel = $duration === 1 ? 'single' : ($duration === 2 ? 'double' : 'triple');
            TimetableAuditLog::log(
                $timetable,
                'slot_assigned',
                "Assigned {$blockLabel} slot for KlassSubject #{$klassSubjectId} on day {$dayOfCycle}, period {$startPeriod}" .
                    ($duration > 1 ? "-{$periods[count($periods) - 1]}" : ''),
                null,
                [
                    'klass_subject_id' => $klassSubjectId,
                    'teacher_id' => $teacherId,
                    'day_of_cycle' => $dayOfCycle,
                    'start_period' => $startPeriod,
                    'duration' => $duration,
                    'block_id' => $blockId,
                ]
            );

            $this->invalidateTimetableCaches($timetableId);

            return ['success' => true, 'slots' => $createdSlots, 'errors' => null];
        });
    }

    /**
     * Delete a slot or entire multi-period block atomically.
     *
     * If the slot has a block_id, all sibling slots in the block are deleted.
     *
     * @param int $slotId
     * @return array ['success' => bool, 'deleted_count' => int]
     */
    public function deleteSlot(int $slotId): array {
        return DB::transaction(function () use ($slotId) {
            $slot = TimetableSlot::lockForUpdate()->findOrFail($slotId);
            $timetable = Timetable::findOrFail($slot->timetable_id);

            $deletedCount = 0;

            if ($slot->block_id) {
                // Delete all slots in the block
                $blockSlots = TimetableSlot::lockForUpdate()
                    ->forBlock($slot->block_id)
                    ->get();

                $deletedCount = $blockSlots->count();

                TimetableAuditLog::log(
                    $timetable,
                    'block_deleted',
                    "Deleted block ({$deletedCount} slots) for KlassSubject #{$slot->klass_subject_id} on day {$slot->day_of_cycle}",
                    [
                        'block_id' => $slot->block_id,
                        'klass_subject_id' => $slot->klass_subject_id,
                        'day_of_cycle' => $slot->day_of_cycle,
                        'periods' => $blockSlots->pluck('period_number')->toArray(),
                    ]
                );

                TimetableSlot::forBlock($slot->block_id)->delete();
            } else {
                // Delete single slot
                $deletedCount = 1;

                TimetableAuditLog::log(
                    $timetable,
                    'slot_deleted',
                    "Deleted single slot for KlassSubject #{$slot->klass_subject_id} on day {$slot->day_of_cycle}, period {$slot->period_number}",
                    [
                        'klass_subject_id' => $slot->klass_subject_id,
                        'day_of_cycle' => $slot->day_of_cycle,
                        'period_number' => $slot->period_number,
                    ]
                );

                $slot->delete();
            }

            $this->invalidateTimetableCaches((int) $slot->timetable_id);

            return ['success' => true, 'deleted_count' => $deletedCount];
        });
    }

    /**
     * Move a slot or block to a new day/period position within the same class.
     *
     * Validates lock state, break boundaries for multi-period blocks, and conflicts at target.
     *
     * @param int $slotId
     * @param int $targetDayOfCycle
     * @param int $targetPeriodNumber
     * @return array ['success' => bool, 'slot' => TimetableSlot|null, 'errors' => array|null]
     */
    public function moveSlot(int $slotId, int $targetDayOfCycle, int $targetPeriodNumber): array {
        return DB::transaction(function () use ($slotId, $targetDayOfCycle, $targetPeriodNumber) {
            $slot = TimetableSlot::lockForUpdate()->findOrFail($slotId);

            if ($slot->is_locked) {
                return ['success' => false, 'errors' => ['This slot is locked and cannot be moved.']];
            }

            if ($slot->duration > 1 && $slot->block_id) {
                return $this->moveBlockSlot($slot, $targetDayOfCycle, $targetPeriodNumber);
            }

            $couplingError = $this->detectCouplingSplitConflict(
                (int) $slot->timetable_id,
                $slot->coupling_group_key,
                $targetDayOfCycle,
                $targetPeriodNumber,
                [$slot->id]
            );
            if ($couplingError !== null) {
                return ['success' => false, 'errors' => [$couplingError]];
            }

            $dayConflict = $this->detectCouplingDayConflict(
                (int) $slot->timetable_id,
                $slot->coupling_group_key,
                $targetDayOfCycle,
                [$slot->id]
            );
            if ($dayConflict !== null) {
                return ['success' => false, 'errors' => [$dayConflict]];
            }

            $klassSubject = KlassSubject::findOrFail($slot->klass_subject_id);
            $gradeId = (int) ($klassSubject->grade_id ?? 0);

            $conflicts = $this->detectConflicts(
                $slot->timetable_id,
                $slot->teacher_id,
                $klassSubject->klass_id,
                $targetDayOfCycle,
                $targetPeriodNumber,
                $slot->id,
                $slot->klass_subject_id,
                [],
                $gradeId,
                false,
                null
            );

            if (!empty($conflicts)) {
                return ['success' => false, 'errors' => $conflicts];
            }

            $oldDay = $slot->day_of_cycle;
            $oldPeriod = $slot->period_number;

            $slot->update([
                'day_of_cycle' => $targetDayOfCycle,
                'period_number' => $targetPeriodNumber,
            ]);

            $timetable = Timetable::findOrFail($slot->timetable_id);
            TimetableAuditLog::log(
                $timetable,
                'slot_moved',
                "Moved slot from day {$oldDay} period {$oldPeriod} to day {$targetDayOfCycle} period {$targetPeriodNumber}",
                ['day_of_cycle' => $oldDay, 'period_number' => $oldPeriod],
                ['day_of_cycle' => $targetDayOfCycle, 'period_number' => $targetPeriodNumber]
            );

            $this->invalidateTimetableCaches((int) $slot->timetable_id);

            return ['success' => true, 'slot' => $slot->fresh()];
        });
    }

    /**
     * Move a multi-period block atomically.
     */
    private function moveBlockSlot(TimetableSlot $slot, int $targetDayOfCycle, int $targetPeriodNumber): array {
        $blockSlots = TimetableSlot::lockForUpdate()
            ->forBlock($slot->block_id)
            ->orderBy('period_number')
            ->get();

        if ($blockSlots->isEmpty()) {
            return ['success' => false, 'errors' => ['Could not load slots for this block.']];
        }

        if ($blockSlots->contains(fn(TimetableSlot $s) => (bool) $s->is_locked)) {
            return ['success' => false, 'errors' => ['This block is locked and cannot be moved.']];
        }

        $duration = max((int) $slot->duration, $blockSlots->count());
        $breakError = $this->validateBlockPeriods($targetPeriodNumber, $duration);
        if ($breakError) {
            return ['success' => false, 'errors' => [$breakError]];
        }

            $klassId = 0;
            $gradeId = 0;
            $klassSubjectId = $slot->klass_subject_id ? (int) $slot->klass_subject_id : null;
            if ($klassSubjectId) {
                $klassSubject = KlassSubject::findOrFail($klassSubjectId);
                $klassId = (int) $klassSubject->klass_id;
                $gradeId = (int) ($klassSubject->grade_id ?? 0);
            } elseif ($slot->optional_subject_id) {
                $optional = \App\Models\OptionalSubject::find($slot->optional_subject_id);
                $gradeId = (int) ($optional?->grade_id ?? 0);
            }

        $excludeIds = $blockSlots
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        $couplingError = $this->detectCouplingSplitConflict(
            (int) $slot->timetable_id,
            $slot->coupling_group_key,
            $targetDayOfCycle,
            $targetPeriodNumber,
            $excludeIds
        );
        if ($couplingError !== null) {
            return ['success' => false, 'errors' => [$couplingError]];
        }

        $dayConflict = $this->detectCouplingDayConflict(
            (int) $slot->timetable_id,
            $slot->coupling_group_key,
            $targetDayOfCycle,
            $excludeIds
        );
        if ($dayConflict !== null) {
            return ['success' => false, 'errors' => [$dayConflict]];
        }

        $allConflicts = [];
        for ($offset = 0; $offset < $duration; $offset++) {
            $targetPeriod = $targetPeriodNumber + $offset;
                $conflicts = $this->detectConflicts(
                    $slot->timetable_id,
                    $slot->teacher_id,
                    $klassId,
                    $targetDayOfCycle,
                    $targetPeriod,
                    null,
                    $klassSubjectId,
                    $excludeIds,
                    $gradeId,
                    $slot->optional_subject_id !== null,
                    $slot->optional_subject_id ? (int) $slot->optional_subject_id : null
                );

            if (!empty($conflicts)) {
                $allConflicts = array_merge($allConflicts, $conflicts);
            }
        }

        if (!empty($allConflicts)) {
            return ['success' => false, 'errors' => array_values(array_unique($allConflicts))];
        }

        $oldDay = (int) $blockSlots->first()->day_of_cycle;
        $oldStartPeriod = (int) $blockSlots->first()->period_number;

        foreach ($blockSlots->values() as $index => $blockSlot) {
            $blockSlot->update([
                'day_of_cycle' => $targetDayOfCycle,
                'period_number' => $targetPeriodNumber + $index,
                'duration' => $duration,
            ]);
        }

        $timetable = Timetable::findOrFail($slot->timetable_id);
        TimetableAuditLog::log(
            $timetable,
            'block_moved',
            "Moved {$duration}-period block from day {$oldDay} period {$oldStartPeriod} to day {$targetDayOfCycle} period {$targetPeriodNumber}",
            ['day_of_cycle' => $oldDay, 'period_number' => $oldStartPeriod],
            ['day_of_cycle' => $targetDayOfCycle, 'period_number' => $targetPeriodNumber]
        );

        $this->invalidateTimetableCaches((int) $slot->timetable_id);

        return ['success' => true, 'slot' => TimetableSlot::find($slot->id)?->fresh()];
    }

    /**
     * Swap positions of two single slots.
     *
     * Validates: neither locked, neither multi-period, no conflicts at swapped positions.
     *
     * @param int $slotIdA
     * @param int $slotIdB
     * @return array ['success' => bool, 'slots' => array|null, 'errors' => array|null]
     */
    public function swapSlots(int $slotIdA, int $slotIdB): array {
        return DB::transaction(function () use ($slotIdA, $slotIdB) {
            $slotA = TimetableSlot::lockForUpdate()->findOrFail($slotIdA);
            $slotB = TimetableSlot::lockForUpdate()->findOrFail($slotIdB);

            if ($slotA->timetable_id !== $slotB->timetable_id) {
                return ['success' => false, 'errors' => ['Cannot swap slots from different timetables.']];
            }

            if ($slotA->is_locked || $slotB->is_locked) {
                return ['success' => false, 'errors' => ['Cannot swap: one or both slots are locked.']];
            }

            if ($slotA->duration > 1 || $slotB->duration > 1) {
                return ['success' => false, 'errors' => ['Cannot swap multi-period blocks.']];
            }

            if (!$slotA->klass_subject_id || !$slotB->klass_subject_id) {
                return ['success' => false, 'errors' => ['Cannot swap optional subject slots without a class-subject assignment.']];
            }

            $klassSubjectA = KlassSubject::findOrFail($slotA->klass_subject_id);
            $klassSubjectB = KlassSubject::findOrFail($slotB->klass_subject_id);
            $gradeIdA = (int) ($klassSubjectA->grade_id ?? 0);
            $gradeIdB = (int) ($klassSubjectB->grade_id ?? 0);

            // Validate A at B's position (exclude B so it doesn't conflict with itself)
            $conflictsA = $this->detectConflicts(
                $slotA->timetable_id,
                $slotA->teacher_id,
                $klassSubjectA->klass_id,
                $slotB->day_of_cycle,
                $slotB->period_number,
                $slotB->id,
                $slotA->klass_subject_id,
                [],
                $gradeIdA,
                false,
                null
            );

            // Validate B at A's position (exclude A so it doesn't conflict with itself)
            $conflictsB = $this->detectConflicts(
                $slotB->timetable_id,
                $slotB->teacher_id,
                $klassSubjectB->klass_id,
                $slotA->day_of_cycle,
                $slotA->period_number,
                $slotA->id,
                $slotB->klass_subject_id,
                [],
                $gradeIdB,
                false,
                null
            );

            $allConflicts = array_merge($conflictsA, $conflictsB);
            if (!empty($allConflicts)) {
                return ['success' => false, 'errors' => $allConflicts];
            }

            $oldDayA = $slotA->day_of_cycle;
            $oldPeriodA = $slotA->period_number;
            $oldDayB = $slotB->day_of_cycle;
            $oldPeriodB = $slotB->period_number;

            $slotA->update([
                'day_of_cycle' => $oldDayB,
                'period_number' => $oldPeriodB,
            ]);
            $slotB->update([
                'day_of_cycle' => $oldDayA,
                'period_number' => $oldPeriodA,
            ]);

            $timetable = Timetable::findOrFail($slotA->timetable_id);
            TimetableAuditLog::log(
                $timetable,
                'slots_swapped',
                "Swapped slot #{$slotIdA} (day {$oldDayA} period {$oldPeriodA}) with slot #{$slotIdB} (day {$oldDayB} period {$oldPeriodB})",
                ['slot_a' => ['day_of_cycle' => $oldDayA, 'period_number' => $oldPeriodA], 'slot_b' => ['day_of_cycle' => $oldDayB, 'period_number' => $oldPeriodB]],
                ['slot_a' => ['day_of_cycle' => $oldDayB, 'period_number' => $oldPeriodB], 'slot_b' => ['day_of_cycle' => $oldDayA, 'period_number' => $oldPeriodA]]
            );

            $this->invalidateTimetableCaches((int) $slotA->timetable_id);

            return ['success' => true, 'slots' => [$slotA->fresh(), $slotB->fresh()]];
        });
    }

    /**
     * Toggle the is_locked state of a slot (or entire block if block_id exists).
     *
     * @param int $slotId
     * @return array ['success' => bool, 'is_locked' => bool, 'count' => int]
     */
    public function toggleLock(int $slotId): array {
        return DB::transaction(function () use ($slotId) {
            $slot = TimetableSlot::lockForUpdate()->findOrFail($slotId);
            $newState = !$slot->is_locked;

            if ($slot->block_id) {
                // Toggle entire block atomically (same pattern as deleteSlot)
                TimetableSlot::where('block_id', $slot->block_id)
                    ->update(['is_locked' => $newState]);
                $count = TimetableSlot::where('block_id', $slot->block_id)->count();
            } else {
                $slot->update(['is_locked' => $newState]);
                $count = 1;
            }

            $timetable = Timetable::findOrFail($slot->timetable_id);
            TimetableAuditLog::log(
                $timetable,
                $newState ? 'slot_locked' : 'slot_unlocked',
                ($newState ? 'Locked' : 'Unlocked') . " {$count} slot(s) for KlassSubject #{$slot->klass_subject_id}"
            );

            $this->invalidateTimetableCaches((int) $slot->timetable_id);

            return ['success' => true, 'is_locked' => $newState, 'count' => $count];
        });
    }

    /**
     * Detect conflicts for a given teacher + class at a specific day/period.
     *
     * Returns an array of conflict descriptions. Empty array = no conflicts.
     *
     * HARD conflict: teacher double-booking (same teacher, same day+period, ANY class in timetable).
     * HARD conflict: class double-booking (same class, same day+period).
     *
     * @param int $timetableId
     * @param int|null $teacherId
     * @param int $klassId
     * @param int $dayOfCycle
     * @param int $periodNumber
     * @param int|null $excludeSlotId Slot to exclude (for editing)
     * @return array Array of conflict description strings
     */
    public function detectConflicts(
        int $timetableId,
        ?int $teacherId,
        int $klassId,
        int $dayOfCycle,
        int $periodNumber,
        ?int $excludeSlotId = null,
        ?int $klassSubjectId = null,
        array $excludeSlotIds = [],
        ?int $gradeId = null,
        bool $isOptional = false,
        ?int $optionalSubjectId = null
    ): array {
        $conflicts = [];
        $excludeIds = array_values(array_unique(array_filter(array_merge(
            $excludeSlotId !== null ? [(int) $excludeSlotId] : [],
            array_map('intval', $excludeSlotIds)
        ))));
        $excludedLookup = array_fill_keys($excludeIds, true);
        $snapshot = $this->loadConflictSnapshot($timetableId);
        $resourceContext = $this->resolvePlacementContext(
            $klassSubjectId,
            $optionalSubjectId,
            $teacherId,
            $klassId
        );

        // Teacher double-booking: same teacher, same day+period across ALL classes in this timetable
        if ($teacherId) {
            $teacherConflictId = $this->findFirstConflictingSlotId(
                $snapshot['teacher'],
                "{$teacherId}:{$dayOfCycle}:{$periodNumber}",
                $excludedLookup
            );

            if ($teacherConflictId !== null) {
                $className = $snapshot['slot_meta'][$teacherConflictId]['class_name'] ?? 'unknown class';
                $className = $className !== '' ? $className : 'unknown class';
                $conflicts[] = "Teacher is already assigned to {$className} on day {$dayOfCycle}, period {$periodNumber}";
            }
        }

        // Class double-booking: same class, same day+period
        if ($klassId > 0) {
            $classConflictId = $this->findFirstConflictingSlotId(
                $snapshot['klass'],
                "{$klassId}:{$dayOfCycle}:{$periodNumber}",
                $excludedLookup
            );

            if ($classConflictId !== null) {
                $subjectName = $snapshot['slot_meta'][$classConflictId]['subject_name'] ?? 'unknown subject';
                $conflicts[] = "Class already has {$subjectName} scheduled on day {$dayOfCycle}, period {$periodNumber}";
            }
        }

        // Grade-level rule: core and electives cannot run at the same time.
        if ($gradeId !== null && $gradeId > 0) {
            $gradeConflict = $this->detectCoreElectiveGradeConflict(
                $dayOfCycle,
                $periodNumber,
                $gradeId,
                $isOptional,
                $snapshot,
                $excludedLookup
            );
            if ($gradeConflict !== null) {
                $conflicts[] = $gradeConflict;
            }
        }

        // Venue and assistant teacher conflicts.
        $venueId = (int) ($resourceContext['venue_id'] ?? 0);
        $assistantTeacherId = (int) ($resourceContext['assistant_teacher_id'] ?? 0);

        if ($venueId > 0) {
            $venueConflictId = $this->findFirstConflictingSlotId(
                $snapshot['venue'],
                "{$venueId}:{$dayOfCycle}:{$periodNumber}",
                $excludedLookup
            );

            if ($venueConflictId !== null) {
                $venueName = $snapshot['slot_meta'][$venueConflictId]['venue_name'] ?? 'the assigned venue';
                $conflicts[] = "{$venueName} is already in use on day {$dayOfCycle}, period {$periodNumber}";
            }
        }

        if ($assistantTeacherId > 0) {
            $assistantConflictId = $this->findFirstConflictingSlotId(
                $snapshot['assistant'],
                "{$assistantTeacherId}:{$dayOfCycle}:{$periodNumber}",
                $excludedLookup
            );

            if ($assistantConflictId !== null) {
                $conflicts[] = "Assistant teacher is already assigned elsewhere on day {$dayOfCycle}, period {$periodNumber}";
            }
        }

        // Constraint-based validation (hard constraints only -- soft returned separately)
        $subjectId = $resourceContext['subject_id'] ?? null;

        if ($klassId > 0) {
            $violations = $this->constraintValidationService->validateSlotPlacement(
                $timetableId,
                $teacherId,
                $klassId,
                $dayOfCycle,
                $periodNumber,
                $subjectId,
                $excludeIds
            );

            foreach ($violations as $violation) {
                if ($violation['type'] === 'hard') {
                    $conflicts[] = $violation['message'];
                }
            }
        }

        return $conflicts;
    }

    /**
     * Detect grade-level overlap between core and elective slots.
     */
    private function detectCoreElectiveGradeConflict(
        int $dayOfCycle,
        int $periodNumber,
        int $gradeId,
        bool $isOptional,
        array $snapshot,
        array $excludedLookup
    ): ?string {
        if ($isOptional) {
            $coreConflictId = $this->findFirstConflictingSlotId(
                $snapshot['grade_core'],
                "{$gradeId}:{$dayOfCycle}:{$periodNumber}",
                $excludedLookup
            );

            if ($coreConflictId !== null) {
                $klassName = $snapshot['slot_meta'][$coreConflictId]['class_name'] ?? "grade {$gradeId}";
                $klassName = $klassName !== '' ? $klassName : "grade {$gradeId}";
                return "Cannot place elective here: {$klassName} has a core subject on day {$dayOfCycle}, period {$periodNumber}.";
            }

            return null;
        }

        $electiveConflictId = $this->findFirstConflictingSlotId(
            $snapshot['grade_optional'],
            "{$gradeId}:{$dayOfCycle}:{$periodNumber}",
            $excludedLookup
        );

        if ($electiveConflictId !== null) {
            $subjectName = $snapshot['slot_meta'][$electiveConflictId]['subject_name']
                ?? 'an elective';
            return "Cannot place core subject here: coupled elective {$subjectName} is scheduled for grade {$gradeId} on day {$dayOfCycle}, period {$periodNumber}.";
        }

        return null;
    }

    /**
     * Public break-span validation used by controller flows before creating blocks.
     */
    public function validateBlockPlacement(int $startPeriod, int $duration): ?string {
        return $this->validateBlockPeriods($startPeriod, $duration);
    }

    /**
     * Validate that a coupled elective member remains aligned with its group.
     */
    public function validateCouplingPlacement(
        int $timetableId,
        ?string $couplingGroupKey,
        int $targetDayOfCycle,
        int $targetStartPeriod,
        array $excludeSlotIds = []
    ): ?string {
        return $this->detectCouplingSplitConflict(
            $timetableId,
            $couplingGroupKey,
            $targetDayOfCycle,
            $targetStartPeriod,
            $excludeSlotIds
        );
    }

    /**
     * Get soft constraint warnings for a potential slot placement.
     * Used by UI to display non-blocking warnings.
     *
     * @param int $timetableId
     * @param int|null $teacherId
     * @param int $klassId
     * @param int $dayOfCycle
     * @param int $periodNumber
     * @param int|null $subjectId
     * @return array
     */
    public function getConstraintWarnings(
        int $timetableId,
        ?int $teacherId,
        int $klassId,
        int $dayOfCycle,
        int $periodNumber,
        ?int $subjectId = null
    ): array {
        $violations = $this->constraintValidationService->validateSlotPlacement(
            $timetableId, $teacherId, $klassId, $dayOfCycle, $periodNumber, $subjectId
        );

        return array_values(array_filter($violations, fn($v) => $v['type'] === 'soft'));
    }

    /**
     * Validate that a multi-period block does not span across a break.
     *
     * @param int $startPeriod
     * @param int $duration
     * @return string|null Error message if invalid, null if valid
     */
    private function validateBlockPeriods(int $startPeriod, int $duration): ?string {
        // Ensure block doesn't exceed total configured periods
        $periodDefinitions = $this->periodSettingsService->getPeriodDefinitions();
        $totalPeriods = count($periodDefinitions);
        $endPeriod = $startPeriod + $duration - 1;

        if ($endPeriod > $totalPeriods) {
            return "Block of {$duration} starting at period {$startPeriod} exceeds available periods (max {$totalPeriods})";
        }

        // Check if any period in the block (except the last) has a break after it
        $breakIntervals = $this->periodSettingsService->getBreakIntervals();
        $breakAfterPeriods = array_map('intval', array_column($breakIntervals, 'after_period'));

        for ($p = $startPeriod; $p < $endPeriod; $p++) {
            if (in_array($p, $breakAfterPeriods, true)) {
                $breakLabel = '';
                foreach ($breakIntervals as $break) {
                    if ((int) $break['after_period'] === $p) {
                        $breakLabel = $break['label'] ?? 'break';
                        break;
                    }
                }
                return "Block cannot span across {$breakLabel} (after period {$p})";
            }
        }

        if ($duration === 2) {
            $validDoubleStarts = BlockPlacementRules::computeValidDoubleStartPeriods($totalPeriods, $breakAfterPeriods);
            $validDoubleStartSet = array_fill_keys($validDoubleStarts, true);
            if (BlockPlacementRules::isMisalignedDoubleStart($startPeriod, $duration, $validDoubleStartSet)) {
                $validStartsStr = implode(', ', $validDoubleStarts);
                return "Double period must start at one of: {$validStartsStr}. Period {$startPeriod} is not a valid double start.";
            }
        }

        return null;
    }

    /**
     * Detect whether moving a coupling group to a target day would conflict
     * with a different coupling label from the same grade already on that day.
     */
    private function detectCouplingDayConflict(
        int $timetableId,
        ?string $couplingGroupKey,
        int $targetDayOfCycle,
        array $excludeSlotIds = []
    ): ?string {
        $couplingGroupKey = trim((string) $couplingGroupKey);
        if ($couplingGroupKey === '') {
            return null;
        }

        if (!preg_match('/^cg_(\d+)_(.+)_[sdt]\d+$/', $couplingGroupKey, $m)) {
            return null;
        }

        $gradeId = (int) $m[1];
        $label = $m[2];

        $query = TimetableSlot::where('timetable_id', $timetableId)
            ->where('day_of_cycle', $targetDayOfCycle)
            ->whereNotNull('coupling_group_key')
            ->where('coupling_group_key', '!=', '');

        if (!empty($excludeSlotIds)) {
            $query->whereNotIn('id', array_map('intval', $excludeSlotIds));
        }

        $slotsOnDay = $query->get();

        foreach ($slotsOnDay as $slot) {
            $otherKey = trim((string) $slot->coupling_group_key);
            if ($otherKey === '' || !preg_match('/^cg_(\d+)_(.+)_[sdt]\d+$/', $otherKey, $om)) {
                continue;
            }

            if ((int) $om[1] === $gradeId && $om[2] !== $label) {
                return "Cannot place coupling group '{$label}' on Day {$targetDayOfCycle}: coupling group '{$om[2]}' from the same grade is already on this day. Different coupling groups within a grade must be on different days.";
            }
        }

        return null;
    }

    /**
     * Detect whether a coupling-group move/placement would split the group.
     */
    private function detectCouplingSplitConflict(
        int $timetableId,
        ?string $couplingGroupKey,
        int $targetDayOfCycle,
        int $targetStartPeriod,
        array $excludeSlotIds = []
    ): ?string {
        $couplingGroupKey = trim((string) $couplingGroupKey);
        if ($couplingGroupKey === '') {
            return null;
        }

        $peerSlots = TimetableSlot::where('timetable_id', $timetableId)
            ->where('coupling_group_key', $couplingGroupKey)
            ->when(!empty($excludeSlotIds), fn($q) => $q->whereNotIn('id', array_map('intval', $excludeSlotIds)))
            ->orderBy('day_of_cycle')
            ->orderBy('period_number')
            ->get();

        if ($peerSlots->isEmpty()) {
            return null;
        }

        $units = [];
        foreach ($peerSlots as $slot) {
            $unitKey = $slot->block_id ? "b:{$slot->block_id}" : "s:{$slot->id}";
            if (!isset($units[$unitKey])) {
                $units[$unitKey] = [
                    'day' => (int) $slot->day_of_cycle,
                    'period' => (int) $slot->period_number,
                ];
                continue;
            }

            if ((int) $slot->period_number < $units[$unitKey]['period']) {
                $units[$unitKey]['period'] = (int) $slot->period_number;
            }
            if ((int) $slot->day_of_cycle < $units[$unitKey]['day']) {
                $units[$unitKey]['day'] = (int) $slot->day_of_cycle;
            }
        }

        $positions = [];
        foreach ($units as $unit) {
            $positions["{$unit['day']}:{$unit['period']}"] = true;
        }
        $positionKeys = array_keys($positions);

        if (count($positionKeys) > 1) {
            return "Coupling group {$couplingGroupKey} is already split across multiple times. Run timetable repair before moving this slot.";
        }

        [$expectedDay, $expectedPeriod] = array_map('intval', explode(':', $positionKeys[0]));
        if ($targetDayOfCycle !== $expectedDay || $targetStartPeriod !== $expectedPeriod) {
            return "Coupling group {$couplingGroupKey} must remain aligned at Day {$expectedDay}, Period {$expectedPeriod}.";
        }

        return null;
    }

    /**
     * Get used allocation counts for a timetable, grouped by klass_subject_id.
     *
     * Returns an array keyed by klass_subject_id with counts of singles, doubles, triples.
     * A "double" is identified by a shared block_id with 2 slots, "triple" with 3 slots.
     * Singles have null block_id.
     *
     * @param int $timetableId
     * @param int|null $klassId Optional filter by class
     * @return array [klass_subject_id => ['singles' => int, 'doubles' => int, 'triples' => int]]
     */
    public function getUsedAllocations(int $timetableId, ?int $klassId = null): array {
        $query = TimetableSlot::where('timetable_id', $timetableId);

        if ($klassId !== null) {
            $query->whereHas('klassSubject', function ($q) use ($klassId) {
                $q->where('klass_id', $klassId);
            });
        }

        $slots = $query->get();
        $allocations = [];

        // Group by klass_subject_id
        $grouped = $slots->groupBy('klass_subject_id');

        foreach ($grouped as $ksId => $ksSlots) {
            $singles = 0;
            $doubles = 0;
            $triples = 0;

            // Separate into single slots and block groups
            $singleSlots = $ksSlots->whereNull('block_id');
            $blockSlots = $ksSlots->whereNotNull('block_id')->groupBy('block_id');

            $singles = $singleSlots->count();

            foreach ($blockSlots as $blockId => $blockGroup) {
                $blockSize = $blockGroup->count();
                if ($blockSize === 2) {
                    $doubles++;
                } elseif ($blockSize === 3) {
                    $triples++;
                }
            }

            $allocations[$ksId] = [
                'singles' => $singles,
                'doubles' => $doubles,
                'triples' => $triples,
            ];
        }

        return $allocations;
    }

    /**
     * Get allocation status: planned vs used vs remaining.
     *
     * @param int $timetableId
     * @param int|null $klassId Optional filter by class
     * @return array [klass_subject_id => ['planned' => [...], 'used' => [...], 'remaining' => [...]]]
     */
    public function getAllocationStatus(int $timetableId, ?int $klassId = null): array {
        // Get planned allocations from block_allocations table
        $plannedQuery = TimetableBlockAllocation::where('timetable_id', $timetableId);

        if ($klassId !== null) {
            $plannedQuery->whereHas('klassSubject', function ($q) use ($klassId) {
                $q->where('klass_id', $klassId);
            });
        }

        $planned = $plannedQuery->get()->keyBy('klass_subject_id');
        $used = $this->getUsedAllocations($timetableId, $klassId);

        $status = [];

        foreach ($planned as $ksId => $allocation) {
            $usedData = $used[$ksId] ?? ['singles' => 0, 'doubles' => 0, 'triples' => 0];
            $status[$ksId] = [
                'planned' => [
                    'singles' => $allocation->singles,
                    'doubles' => $allocation->doubles,
                    'triples' => $allocation->triples,
                ],
                'used' => $usedData,
                'remaining' => [
                    'singles' => $allocation->singles - $usedData['singles'],
                    'doubles' => $allocation->doubles - $usedData['doubles'],
                    'triples' => $allocation->triples - $usedData['triples'],
                ],
            ];
        }

        // Include any used allocations without planned entries (edge case)
        foreach ($used as $ksId => $usedData) {
            if (!isset($status[$ksId])) {
                $status[$ksId] = [
                    'planned' => ['singles' => 0, 'doubles' => 0, 'triples' => 0],
                    'used' => $usedData,
                    'remaining' => [
                        'singles' => 0 - $usedData['singles'],
                        'doubles' => 0 - $usedData['doubles'],
                        'triples' => 0 - $usedData['triples'],
                    ],
                ];
            }
        }

        return $status;
    }

    /**
     * Get grid data for a timetable: structured nested array for rendering.
     *
     * Returns [day_of_cycle => [period_number => [slot data with eager-loaded relationships]]].
     *
     * @param int $timetableId
     * @param int|null $klassId Optional filter by class
     * @return array
     */
    public function getGridData(int $timetableId, ?int $klassId = null): array {
        $couplingLabelMap = $this->buildCouplingKeyLabelMap();
        $periodDefinitions = $this->periodSettingsService->getPeriodDefinitions();
        $totalPeriods = count($periodDefinitions);
        $breakIntervals = $this->periodSettingsService->getBreakIntervals();
        $breakAfterPeriods = array_map('intval', array_column($breakIntervals, 'after_period'));
        $validDoubleStartSet = array_fill_keys(
            BlockPlacementRules::computeValidDoubleStartPeriods($totalPeriods, $breakAfterPeriods),
            true
        );
        $integrityService = $this->timetableIntegrityService ?? app(TimetableIntegrityService::class);
        $integrity = $integrityService->getCachedAnalysis($timetableId);
        $slotFlags = $integrity['slot_flags'] ?? [];
        $slotIssueMessages = [];
        foreach (($integrity['issues'] ?? []) as $issue) {
            $message = trim((string) ($issue['message'] ?? ''));
            if ($message === '') {
                continue;
            }
            foreach ((array) ($issue['slot_ids'] ?? []) as $issueSlotId) {
                $issueSlotId = (int) $issueSlotId;
                if ($issueSlotId <= 0) {
                    continue;
                }
                $slotIssueMessages[$issueSlotId][] = $message;
            }
        }

        $query = TimetableSlot::where('timetable_id', $timetableId)
            ->with([
                'klassSubject.teacher',
                'klassSubject.klass',
                'klassSubject.gradeSubject.subject',
                'optionalSubject.gradeSubject.subject',
                'optionalSubject.teacher',
                'teacher',
                'venue',
            ]);

        if ($klassId !== null) {
            // Include slots for this class AND optional subject slots for the same grade
            $klass = \App\Models\Klass::find($klassId);
            $gradeId = $klass?->grade_id;

            $query->where(function ($q) use ($klassId, $gradeId) {
                $q->whereHas('klassSubject', function ($sub) use ($klassId) {
                    $sub->where('klass_id', $klassId);
                })->orWhere(function ($sub) use ($gradeId) {
                    $sub->whereNotNull('optional_subject_id')
                        ->whereHas('optionalSubject', function ($os) use ($gradeId) {
                            $os->where('grade_id', $gradeId);
                        });
                });
            });
        }

        $slots = $query->orderBy('day_of_cycle')
            ->orderBy('period_number')
            ->get();
        $blockSlotIds = $slots
            ->filter(fn($slot) => !empty($slot->block_id))
            ->groupBy('block_id')
            ->map(
                fn($group) => $group
                    ->pluck('id')
                    ->map(fn($id) => (int) $id)
                    ->values()
                    ->all()
            )
            ->toArray();

        $grid = [];

        // Group by day+period so coupled optional subjects are visible in a single cell.
        $cellGroups = $slots->groupBy(fn($slot) => $slot->day_of_cycle . ':' . $slot->period_number);
        foreach ($cellGroups as $group) {
            /** @var \App\Models\Timetable\TimetableSlot|null $primary */
            $primary = $group->first(fn($slot) => $slot->klass_subject_id !== null) ?? $group->first();
            if (!$primary) {
                continue;
            }

            $day = (int) $primary->day_of_cycle;
            $period = (int) $primary->period_number;

            $subjectName = $this->resolveSlotSubjectName($primary);
            $teacherName = $this->resolveSlotTeacherName($primary);

            $coupledSlots = $group
                ->filter(fn($slot) => $slot->optional_subject_id !== null && !empty($slot->coupling_group_key))
                ->values();

            $coupledSubjects = $coupledSlots
                ->map(fn($slot) => $this->resolveSlotSubjectName($slot))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $coupledTeachers = $coupledSlots
                ->map(fn($slot) => $this->resolveSlotTeacherName($slot))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $couplingKey = (string) ($primary->coupling_group_key ?? '');
            if ($couplingKey === '' && $coupledSlots->isNotEmpty()) {
                $couplingKey = (string) ($coupledSlots->first()->coupling_group_key ?? '');
            }

            $couplingLabel = $this->resolveCouplingLabel($couplingKey, $couplingLabelMap);
            $hasCoreSlot = $group->contains(fn($slot) => $slot->klass_subject_id !== null);
            $hasOptionalOverlay = $coupledSlots->isNotEmpty();
            $hasCoreOverlap = $hasCoreSlot && $hasOptionalOverlay;
            $groupSlotIds = $group->pluck('id')->map(fn($id) => (int) $id)->all();
            if (!empty($primary->block_id) && isset($blockSlotIds[$primary->block_id])) {
                $groupSlotIds = array_values(array_unique(array_merge($groupSlotIds, $blockSlotIds[$primary->block_id])));
            }
            $groupHasDoubleAlignmentIssue = false;
            $groupHasCoreElectiveOverlap = false;
            $groupHasCouplingSplitIssue = false;
            $groupHasCouplingDayConflict = false;
            $groupIssueReasons = [];
            foreach ($groupSlotIds as $slotId) {
                if (!empty($slotFlags[$slotId]['double_misalignment'])) {
                    $groupHasDoubleAlignmentIssue = true;
                }
                if (!empty($slotFlags[$slotId]['core_elective_overlap'])) {
                    $groupHasCoreElectiveOverlap = true;
                }
                if (!empty($slotFlags[$slotId]['coupling_split'])) {
                    $groupHasCouplingSplitIssue = true;
                }
                if (!empty($slotFlags[$slotId]['coupling_day_conflict'])) {
                    $groupHasCouplingDayConflict = true;
                }
                foreach (($slotIssueMessages[$slotId] ?? []) as $msg) {
                    $groupIssueReasons[] = (string) $msg;
                }
            }
            $groupIssueReasons = array_values(array_unique($groupIssueReasons));
            $computedDoubleMisalignment = BlockPlacementRules::isMisalignedDoubleStart(
                (int) $primary->period_number,
                (int) $primary->duration,
                $validDoubleStartSet
            );
            if ($computedDoubleMisalignment && !$groupHasDoubleAlignmentIssue) {
                $groupHasDoubleAlignmentIssue = true;
                $groupIssueReasons[] = "Double period starts at misaligned period {$period}.";
            }

            if (!isset($grid[$day])) {
                $grid[$day] = [];
            }

            $grid[$day][$period] = [
                'id' => $primary->id,
                'klass_subject_id' => $primary->klass_subject_id,
                'optional_subject_id' => $primary->optional_subject_id,
                'teacher_id' => $primary->teacher_id,
                'day_of_cycle' => $day,
                'period_number' => $period,
                'duration' => $primary->duration,
                'is_locked' => $primary->is_locked,
                'block_id' => $primary->block_id,
                'subject_name' => $subjectName,
                'teacher_name' => $teacherName,
                'class_name' => $primary->klassSubject?->klass?->name,
                'venue_name' => $primary->venue?->name,
                'is_optional' => $primary->optional_subject_id !== null,
                'coupling_group_key' => $couplingKey !== '' ? $couplingKey : null,
                'coupling_group_label' => $couplingLabel,
                'coupled_optional_count' => count($coupledSubjects),
                'coupled_optional_subjects' => $coupledSubjects,
                'coupled_optional_teachers' => $coupledTeachers,
                'has_optional_overlay' => $hasOptionalOverlay,
                'has_core_overlap' => $hasCoreOverlap,
                'has_double_alignment_issue' => $groupHasDoubleAlignmentIssue,
                'has_core_elective_overlap' => $groupHasCoreElectiveOverlap,
                'has_coupling_split_issue' => $groupHasCouplingSplitIssue,
                'has_coupling_day_conflict' => $groupHasCouplingDayConflict,
                'issue_reasons' => $groupIssueReasons,
            ];
        }

        return $grid;
    }

    /**
     * Load a request-local occupancy snapshot for direct conflict checks.
     *
     * @return array<string, mixed>
     */
    private function loadConflictSnapshot(int $timetableId): array {
        if (isset($this->conflictSnapshotCache[$timetableId])) {
            return $this->conflictSnapshotCache[$timetableId];
        }

        $snapshot = [
            'slot_meta' => [],
            'teacher' => [],
            'klass' => [],
            'grade_core' => [],
            'grade_optional' => [],
            'venue' => [],
            'assistant' => [],
        ];

        $slots = TimetableSlot::where('timetable_id', $timetableId)
            ->with([
                'klassSubject.klass:id,name,grade_id',
                'klassSubject.gradeSubject.subject:id,name',
                'optionalSubject:id,grade_id,name,grade_subject_id',
                'optionalSubject.gradeSubject.subject:id,name',
                'venue:id,name',
            ])
            ->get([
                'id',
                'klass_subject_id',
                'optional_subject_id',
                'teacher_id',
                'venue_id',
                'assistant_teacher_id',
                'day_of_cycle',
                'period_number',
            ]);

        foreach ($slots as $slot) {
            $slotId = (int) $slot->id;
            $day = (int) $slot->day_of_cycle;
            $period = (int) $slot->period_number;
            $klassId = (int) ($slot->klassSubject?->klass_id ?? 0);
            $gradeId = (int) ($slot->klassSubject?->klass?->grade_id ?? $slot->optionalSubject?->grade_id ?? 0);

            $snapshot['slot_meta'][$slotId] = [
                'class_name' => (string) ($slot->klassSubject?->klass?->name ?? ''),
                'subject_name' => (string) (
                    $slot->optionalSubject?->gradeSubject?->subject?->name
                    ?? $slot->optionalSubject?->name
                    ?? $slot->klassSubject?->gradeSubject?->subject?->name
                    ?? 'unknown subject'
                ),
                'venue_name' => (string) ($slot->venue?->name ?? 'the assigned venue'),
            ];

            $this->addConflictOccupancy($snapshot['teacher'], (int) ($slot->teacher_id ?? 0), $day, $period, $slotId);
            $this->addConflictOccupancy($snapshot['klass'], $klassId, $day, $period, $slotId);
            $this->addConflictOccupancy($snapshot['venue'], (int) ($slot->venue_id ?? 0), $day, $period, $slotId);
            $this->addConflictOccupancy($snapshot['assistant'], (int) ($slot->assistant_teacher_id ?? 0), $day, $period, $slotId);

            if ($gradeId > 0) {
                if ($slot->optional_subject_id !== null) {
                    $this->addConflictOccupancy($snapshot['grade_optional'], $gradeId, $day, $period, $slotId);
                } elseif ($klassId > 0) {
                    $this->addConflictOccupancy($snapshot['grade_core'], $gradeId, $day, $period, $slotId);
                }
            }
        }

        return $this->conflictSnapshotCache[$timetableId] = $snapshot;
    }

    /**
     * Add one slot to an occupancy map.
     *
     * @param array<string, array<int>> $occupancy
     */
    private function addConflictOccupancy(array &$occupancy, int $resourceId, int $dayOfCycle, int $periodNumber, int $slotId): void {
        if ($resourceId <= 0) {
            return;
        }

        $occupancy["{$resourceId}:{$dayOfCycle}:{$periodNumber}"][] = $slotId;
    }

    /**
     * Find the first conflicting slot ID for an occupancy key after exclusions.
     *
     * @param array<string, array<int>> $occupancy
     * @param array<int, bool> $excludedLookup
     */
    private function findFirstConflictingSlotId(array $occupancy, string $key, array $excludedLookup): ?int {
        foreach ($occupancy[$key] ?? [] as $slotId) {
            if (!isset($excludedLookup[$slotId])) {
                return $slotId;
            }
        }

        return null;
    }

    /**
     * Resolve subject and resource metadata for a candidate placement.
     *
     * @return array<string, int|null>
     */
    private function resolvePlacementContext(
        ?int $klassSubjectId,
        ?int $optionalSubjectId,
        ?int $teacherId,
        int $klassId
    ): array {
        if ($klassSubjectId !== null) {
            return $this->resolveKlassSubjectContext($klassSubjectId);
        }

        if ($optionalSubjectId !== null) {
            return $this->resolveOptionalSubjectContext($optionalSubjectId);
        }

        if ($teacherId !== null && $klassId > 0) {
            $cacheKey = "{$klassId}:{$teacherId}";
            if (!isset($this->teacherKlassContextCache[$cacheKey])) {
                $klassSubject = KlassSubject::with('gradeSubject:id,subject_id')
                    ->select(['id', 'grade_subject_id', 'venue_id', 'assistant_user_id'])
                    ->where('klass_id', $klassId)
                    ->where('user_id', $teacherId)
                    ->first();

                $this->teacherKlassContextCache[$cacheKey] = [
                    'subject_id' => $klassSubject?->gradeSubject?->subject_id ? (int) $klassSubject->gradeSubject->subject_id : null,
                    'venue_id' => $klassSubject?->venue_id ? (int) $klassSubject->venue_id : 0,
                    'assistant_teacher_id' => $klassSubject?->assistant_user_id ? (int) $klassSubject->assistant_user_id : 0,
                ];
            }

            return $this->teacherKlassContextCache[$cacheKey];
        }

        return [
            'subject_id' => null,
            'venue_id' => 0,
            'assistant_teacher_id' => 0,
        ];
    }

    /**
     * Resolve klass-subject metadata once per request.
     *
     * @return array<string, int|null>
     */
    private function resolveKlassSubjectContext(int $klassSubjectId): array {
        if (!isset($this->klassSubjectContextCache[$klassSubjectId])) {
            $klassSubject = KlassSubject::with('gradeSubject:id,subject_id')
                ->select(['id', 'grade_subject_id', 'venue_id', 'assistant_user_id'])
                ->find($klassSubjectId);

            $this->klassSubjectContextCache[$klassSubjectId] = [
                'subject_id' => $klassSubject?->gradeSubject?->subject_id ? (int) $klassSubject->gradeSubject->subject_id : null,
                'venue_id' => $klassSubject?->venue_id ? (int) $klassSubject->venue_id : 0,
                'assistant_teacher_id' => $klassSubject?->assistant_user_id ? (int) $klassSubject->assistant_user_id : 0,
            ];
        }

        return $this->klassSubjectContextCache[$klassSubjectId];
    }

    /**
     * Resolve optional-subject metadata once per request.
     *
     * @return array<string, int|null>
     */
    private function resolveOptionalSubjectContext(int $optionalSubjectId): array {
        if (!isset($this->optionalSubjectContextCache[$optionalSubjectId])) {
            $optionalSubject = OptionalSubject::with('gradeSubject:id,subject_id')
                ->select(['id', 'grade_subject_id', 'venue_id'])
                ->find($optionalSubjectId);

            $this->optionalSubjectContextCache[$optionalSubjectId] = [
                'subject_id' => $optionalSubject?->gradeSubject?->subject_id ? (int) $optionalSubject->gradeSubject->subject_id : null,
                'venue_id' => $optionalSubject?->venue_id ? (int) $optionalSubject->venue_id : 0,
                'assistant_teacher_id' => 0,
            ];
        }

        return $this->optionalSubjectContextCache[$optionalSubjectId];
    }

    /**
     * Resolve display subject name from either klassSubject or optionalSubject.
     */
    private function resolveSlotSubjectName(TimetableSlot $slot): string {
        if ($slot->optional_subject_id) {
            return $slot->optionalSubject?->gradeSubject?->subject?->name
                ?? $slot->optionalSubject?->name
                ?? 'Optional';
        }

        return $slot->klassSubject?->gradeSubject?->subject?->name ?? 'Unknown';
    }

    /**
     * Resolve display teacher name from slot fallback relations.
     */
    private function resolveSlotTeacherName(TimetableSlot $slot): ?string {
        if ($slot->optional_subject_id) {
            return $slot->teacher?->full_name ?? $slot->optionalSubject?->teacher?->full_name;
        }

        return $slot->teacher?->full_name ?? $slot->klassSubject?->teacher?->full_name;
    }

    /**
     * Build mapping of generated coupling keys to human labels.
     *
     * Keys are created by Chromosome::fromAllocations using:
     * cg_{grade}_{label}_{s|d|t}{index}
     */
    private function buildCouplingKeyLabelMap(): array {
        $groups = TimetableSetting::get('optional_coupling_groups', []);
        $map = [];

        foreach ($groups as $group) {
            $gradeId = (int) ($group['grade_id'] ?? 0);
            $label = (string) ($group['label'] ?? '');
            if ($gradeId <= 0 || $label === '') {
                continue;
            }

            $singles = max(0, (int) ($group['singles'] ?? 0));
            $doubles = max(0, (int) ($group['doubles'] ?? 0));
            $triples = max(0, (int) ($group['triples'] ?? 0));

            for ($i = 0; $i < $singles; $i++) {
                $map["cg_{$gradeId}_{$label}_s{$i}"] = $label;
            }
            for ($i = 0; $i < $doubles; $i++) {
                $map["cg_{$gradeId}_{$label}_d{$i}"] = $label;
            }
            for ($i = 0; $i < $triples; $i++) {
                $map["cg_{$gradeId}_{$label}_t{$i}"] = $label;
            }
        }

        return $map;
    }

    /**
     * Resolve a readable coupling label from key map or key pattern fallback.
     */
    private function resolveCouplingLabel(string $couplingKey, array $labelMap): ?string {
        if ($couplingKey === '') {
            return null;
        }

        if (isset($labelMap[$couplingKey])) {
            return (string) $labelMap[$couplingKey];
        }

        if (preg_match('/^cg_\d+_(.+)_[sdt]\d+$/', $couplingKey, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
