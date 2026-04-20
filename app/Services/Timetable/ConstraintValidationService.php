<?php

namespace App\Services\Timetable;

use App\Models\Klass;
use App\Models\Timetable\TimetableConstraint;
use App\Models\Timetable\TimetableSlot;
use App\Models\Venue;

class ConstraintValidationService {
    /**
     * Cached constraints keyed by timetable_id.
     *
     * @var array<int, \Illuminate\Support\Collection>
     */
    private array $constraintCache = [];

    /**
     * Cached runtime aggregates keyed by timetable_id.
     *
     * @var array<int, array<string, array>>
     */
    private array $runtimeCache = [];

    /**
     * Cached klass student counts for room-capacity checks.
     *
     * @var array<int, int>
     */
    private array $klassStudentCountCache = [];

    /**
     * Cached venue type existence flags.
     *
     * @var array<string, bool>
     */
    private array $venueTypeExistsCache = [];

    private ?int $maxVenueCapacity = null;

    /**
     * Validate a potential slot placement against all active constraints.
     *
     * Returns array of violations: [['type' => 'hard'|'soft', 'constraint_type' => string, 'message' => string]]
     *
     * @param int $timetableId
     * @param int|null $teacherId
     * @param int $klassId
     * @param int $dayOfCycle
     * @param int $periodNumber
     * @param int|null $subjectId
     * @return array
     */
    public function validateSlotPlacement(
        int $timetableId,
        ?int $teacherId,
        int $klassId,
        int $dayOfCycle,
        int $periodNumber,
        ?int $subjectId = null,
        array $excludeSlotIds = []
    ): array {
        $constraints = $this->loadConstraints($timetableId);
        $runtime = $this->loadRuntimeContext($timetableId);
        $excludedLookup = array_fill_keys(array_map('intval', $excludeSlotIds), true);
        $violations = [];

        foreach ($constraints as $constraint) {
            $violation = match ($constraint->constraint_type) {
                TimetableConstraint::TYPE_TEACHER_AVAILABILITY => $this->checkTeacherAvailability($constraint, $teacherId, $dayOfCycle, $periodNumber),
                TimetableConstraint::TYPE_TEACHER_PREFERENCE => $this->checkTeacherPreference($constraint, $teacherId, $periodNumber),
                TimetableConstraint::TYPE_ROOM_REQUIREMENT => $this->checkRoomRequirement($constraint, $subjectId),
                TimetableConstraint::TYPE_ROOM_CAPACITY => $this->checkRoomCapacity($constraint, $klassId),
                TimetableConstraint::TYPE_SUBJECT_SPREAD => $this->checkSubjectSpread($constraint, $subjectId, $klassId, $dayOfCycle, $runtime, $excludedLookup),
                TimetableConstraint::TYPE_CONSECUTIVE_LIMIT => $this->checkConsecutiveLimit($constraint, $teacherId, $dayOfCycle, $periodNumber, $runtime, $excludedLookup),
                TimetableConstraint::TYPE_SUBJECT_PAIR => $this->checkSubjectPair($constraint, $subjectId, $klassId, $dayOfCycle, $runtime, $excludedLookup),
                TimetableConstraint::TYPE_PERIOD_RESTRICTION => $this->checkPeriodRestriction($constraint, $subjectId, $periodNumber),
                TimetableConstraint::TYPE_TEACHER_ROOM_ASSIGNMENT => null,
                default => null,
            };

            if ($violation !== null) {
                $violations[] = $violation;
            }
        }

        return $violations;
    }

    /**
     * Clear the constraint cache.
     *
     * @param int|null $timetableId Null clears all
     * @return void
     */
    public function clearCache(?int $timetableId = null): void {
        if ($timetableId === null) {
            $this->constraintCache = [];
            $this->runtimeCache = [];
            $this->klassStudentCountCache = [];
            $this->venueTypeExistsCache = [];
            $this->maxVenueCapacity = null;
        } else {
            unset($this->constraintCache[$timetableId]);
            unset($this->runtimeCache[$timetableId]);
        }
    }

    /**
     * Load all active constraints for a timetable, caching per-request.
     *
     * @param int $timetableId
     * @return \Illuminate\Support\Collection
     */
    private function loadConstraints(int $timetableId) {
        if (!isset($this->constraintCache[$timetableId])) {
            $this->constraintCache[$timetableId] = TimetableConstraint::where('timetable_id', $timetableId)
                ->active()
                ->get();
        }

        return $this->constraintCache[$timetableId];
    }

    /**
     * Load timetable slot aggregates used by soft-constraint checks.
     *
     * @return array<string, array>
     */
    private function loadRuntimeContext(int $timetableId): array {
        if (isset($this->runtimeCache[$timetableId])) {
            return $this->runtimeCache[$timetableId];
        }

        $subjectDayLessons = [];
        $teacherDayPeriods = [];
        $subjectDayPresence = [];

        $slots = TimetableSlot::where('timetable_id', $timetableId)
            ->with([
                'klassSubject:id,klass_id,grade_subject_id',
                'klassSubject.gradeSubject:id,subject_id',
            ])
            ->get([
                'id',
                'klass_subject_id',
                'teacher_id',
                'day_of_cycle',
                'period_number',
                'block_id',
            ]);

        foreach ($slots as $slot) {
            $klassId = (int) ($slot->klassSubject?->klass_id ?? 0);
            $subjectId = (int) ($slot->klassSubject?->gradeSubject?->subject_id ?? 0);
            $slotId = (int) $slot->id;
            $day = (int) $slot->day_of_cycle;
            $period = (int) $slot->period_number;

            if ((int) $slot->teacher_id > 0) {
                $teacherDayPeriods["{$slot->teacher_id}:{$day}"][$slotId] = $period;
            }

            if ($klassId <= 0 || $subjectId <= 0) {
                continue;
            }

            $subjectDayKey = "{$subjectId}:{$klassId}:{$day}";
            $lessonKey = !empty($slot->block_id)
                ? 'block:' . $slot->block_id
                : 'slot:' . $slotId;

            $subjectDayLessons[$subjectDayKey][$lessonKey][] = $slotId;
            $subjectDayPresence[$subjectDayKey][$slotId] = true;
        }

        return $this->runtimeCache[$timetableId] = [
            'subject_day_lessons' => $subjectDayLessons,
            'teacher_day_periods' => $teacherDayPeriods,
            'subject_day_presence' => $subjectDayPresence,
        ];
    }

    /**
     * Check teacher availability constraint.
     * Hard violation if teacher is marked unavailable at this day/period.
     */
    private function checkTeacherAvailability(TimetableConstraint $constraint, ?int $teacherId, int $dayOfCycle, int $periodNumber): ?array {
        if ($teacherId === null) {
            return null;
        }

        $config = $constraint->constraint_config;
        if (($config['teacher_id'] ?? null) !== $teacherId) {
            return null;
        }

        $unavailableSlots = $config['unavailable_slots'] ?? [];
        foreach ($unavailableSlots as $slot) {
            if ((int) ($slot['day_of_cycle'] ?? 0) === $dayOfCycle && (int) ($slot['period_number'] ?? 0) === $periodNumber) {
                return [
                    'type' => 'hard',
                    'constraint_type' => TimetableConstraint::TYPE_TEACHER_AVAILABILITY,
                    'message' => "Teacher is marked unavailable on day {$dayOfCycle}, period {$periodNumber}",
                ];
            }
        }

        return null;
    }

    /**
     * Check teacher preference constraint.
     * Soft violation if period is outside teacher's preferred slots.
     */
    private function checkTeacherPreference(TimetableConstraint $constraint, ?int $teacherId, int $periodNumber): ?array {
        if ($teacherId === null) {
            return null;
        }

        $config = $constraint->constraint_config;
        if (($config['teacher_id'] ?? null) !== $teacherId) {
            return null;
        }

        $preference = $config['preference'] ?? 'none';
        if ($preference === 'none') {
            return null;
        }

        $preferredPeriods = $config['preferred_periods'] ?? [];
        if (!empty($preferredPeriods) && !in_array($periodNumber, $preferredPeriods, false)) {
            return [
                'type' => 'soft',
                'constraint_type' => TimetableConstraint::TYPE_TEACHER_PREFERENCE,
                'message' => "Period {$periodNumber} is outside teacher's preferred {$preference} slots",
            ];
        }

        return null;
    }

    /**
     * Check room requirement constraint.
     * Hard violation if subject requires a venue type and no venues of that type exist.
     */
    private function checkRoomRequirement(TimetableConstraint $constraint, ?int $subjectId): ?array {
        if ($subjectId === null) {
            return null;
        }

        $config = $constraint->constraint_config;
        if (($config['subject_id'] ?? null) !== $subjectId) {
            return null;
        }

        $requiredType = $config['required_venue_type'] ?? null;
        if ($requiredType === null) {
            return null;
        }

        if (!array_key_exists($requiredType, $this->venueTypeExistsCache)) {
            $this->venueTypeExistsCache[$requiredType] = Venue::where('type', $requiredType)->exists();
        }

        if (!$this->venueTypeExistsCache[$requiredType]) {
            return [
                'type' => 'hard',
                'constraint_type' => TimetableConstraint::TYPE_ROOM_REQUIREMENT,
                'message' => "Subject requires a '{$requiredType}' venue but none exist",
            ];
        }

        return null;
    }

    /**
     * Check room capacity constraint.
     * Violation severity depends on enforcement setting ('strict' = hard, 'warn_only' = soft).
     */
    private function checkRoomCapacity(TimetableConstraint $constraint, int $klassId): ?array {
        $config = $constraint->constraint_config;
        if (!($config['enabled'] ?? false)) {
            return null;
        }

        if ($klassId <= 0) {
            return null;
        }

        if (!array_key_exists($klassId, $this->klassStudentCountCache)) {
            $klass = Klass::withCount('students')->find($klassId);
            $this->klassStudentCountCache[$klassId] = (int) ($klass?->students_count ?? 0);
        }

        $studentCount = $this->klassStudentCountCache[$klassId];
        if ($studentCount === 0) {
            return null;
        }

        if ($this->maxVenueCapacity === null) {
            $this->maxVenueCapacity = (int) (Venue::max('capacity') ?? 0);
        }

        $maxCapacity = $this->maxVenueCapacity > 0 ? $this->maxVenueCapacity : null;
        if ($maxCapacity === null || $studentCount <= $maxCapacity) {
            return null;
        }

        $enforcement = $config['enforcement'] ?? 'strict';
        return [
            'type' => $enforcement === 'strict' ? 'hard' : 'soft',
            'constraint_type' => TimetableConstraint::TYPE_ROOM_CAPACITY,
            'message' => "Class has {$studentCount} students but largest venue capacity is {$maxCapacity}",
        ];
    }

    /**
     * Check subject spread constraint.
     * Soft violation if subject already has max lessons on this day for this class.
     */
    private function checkSubjectSpread(
        TimetableConstraint $constraint,
        ?int $subjectId,
        int $klassId,
        int $dayOfCycle,
        array $runtime,
        array $excludedLookup
    ): ?array {
        if ($subjectId === null) {
            return null;
        }

        $config = $constraint->constraint_config;
        if (($config['subject_id'] ?? null) !== $subjectId) {
            return null;
        }

        $max = (int) ($config['max_lessons_per_day'] ?? 0);
        if ($max <= 0) {
            return null;
        }

        $subjectDayKey = "{$subjectId}:{$klassId}:{$dayOfCycle}";
        $lessonGroups = $runtime['subject_day_lessons'][$subjectDayKey] ?? [];
        $existingLessonCount = 0;

        foreach ($lessonGroups as $slotIds) {
            if ($this->hasIncludedSlots($slotIds, $excludedLookup)) {
                $existingLessonCount++;
            }
        }

        if ($existingLessonCount >= $max) {
            return [
                'type' => 'soft',
                'constraint_type' => TimetableConstraint::TYPE_SUBJECT_SPREAD,
                'message' => "Subject already has {$existingLessonCount} lesson(s) on day {$dayOfCycle} (max: {$max})",
            ];
        }

        return null;
    }

    /**
     * Check consecutive limit constraint.
     * Soft violation if placing this period would exceed max consecutive teaching periods.
     */
    private function checkConsecutiveLimit(
        TimetableConstraint $constraint,
        ?int $teacherId,
        int $dayOfCycle,
        int $periodNumber,
        array $runtime,
        array $excludedLookup
    ): ?array {
        if ($teacherId === null) {
            return null;
        }

        $config = $constraint->constraint_config;
        $configTeacherId = $config['teacher_id'] ?? null;

        // If constraint is teacher-specific and not for this teacher, skip
        if ($configTeacherId !== null && $configTeacherId !== $teacherId) {
            return null;
        }

        $max = $config['max_consecutive_periods'] ?? 0;
        if ($max <= 0) {
            return null;
        }

        $existingPeriods = [];
        foreach (($runtime['teacher_day_periods']["{$teacherId}:{$dayOfCycle}"] ?? []) as $slotId => $existingPeriod) {
            if (!isset($excludedLookup[(int) $slotId])) {
                $existingPeriods[] = (int) $existingPeriod;
            }
        }

        // Add proposed period
        $allPeriods = array_unique(array_merge($existingPeriods, [$periodNumber]));
        sort($allPeriods);

        // Find longest consecutive run that includes the proposed period
        $runLength = $this->findConsecutiveRunLength($allPeriods, $periodNumber);

        if ($runLength > $max) {
            return [
                'type' => 'soft',
                'constraint_type' => TimetableConstraint::TYPE_CONSECUTIVE_LIMIT,
                'message' => "Teacher would have {$runLength} consecutive periods (max: {$max})",
            ];
        }

        return null;
    }

    /**
     * Check subject pair constraint.
     * Soft violation if the other subject in the pair already exists on the same day (for not_same_day rule).
     */
    private function checkSubjectPair(
        TimetableConstraint $constraint,
        ?int $subjectId,
        int $klassId,
        int $dayOfCycle,
        array $runtime,
        array $excludedLookup
    ): ?array {
        if ($subjectId === null) {
            return null;
        }

        $config = $constraint->constraint_config;
        $subjectIdA = (int) ($config['subject_id_a'] ?? 0);
        $subjectIdB = (int) ($config['subject_id_b'] ?? 0);
        $pairKlassId = $config['klass_id'] ?? null;
        $rule = $config['rule'] ?? '';

        // Check if this subject is part of this pair
        if ($subjectId !== $subjectIdA && $subjectId !== $subjectIdB) {
            return null;
        }

        // Check class filter
        if ($pairKlassId !== null && (int) $pairKlassId !== $klassId) {
            return null;
        }

        $otherSubjectId = $subjectId === $subjectIdA ? $subjectIdB : $subjectIdA;

        // Check if other subject is on the same day
        $otherOnSameDay = $this->hasIncludedSlots(
            array_keys($runtime['subject_day_presence']["{$otherSubjectId}:{$klassId}:{$dayOfCycle}"] ?? []),
            $excludedLookup
        );

        if ($rule === 'not_same_day' && $otherOnSameDay) {
            return [
                'type' => 'soft',
                'constraint_type' => TimetableConstraint::TYPE_SUBJECT_PAIR,
                'message' => "Subject pair rule: these two subjects should not be on the same day",
            ];
        }

        return null;
    }

    /**
     * Check period restriction constraint.
     * Soft violation if subject is placed outside its allowed periods.
     */
    private function checkPeriodRestriction(TimetableConstraint $constraint, ?int $subjectId, int $periodNumber): ?array {
        if ($subjectId === null) {
            return null;
        }

        $config = $constraint->constraint_config;
        if (($config['subject_id'] ?? null) !== $subjectId) {
            return null;
        }

        $allowedPeriods = $config['allowed_periods'] ?? [];
        if (empty($allowedPeriods)) {
            return null;
        }

        if (!in_array($periodNumber, $allowedPeriods, false)) {
            $restriction = $config['restriction'] ?? 'unknown';
            return [
                'type' => 'soft',
                'constraint_type' => TimetableConstraint::TYPE_PERIOD_RESTRICTION,
                'message' => "Period {$periodNumber} violates '{$restriction}' restriction for this subject",
            ];
        }

        return null;
    }

    /**
     * Find the length of the consecutive run that includes the target period.
     *
     * @param array $sortedPeriods Sorted array of period numbers
     * @param int $target The period to find the run for
     * @return int Length of the consecutive run
     */
    private function findConsecutiveRunLength(array $sortedPeriods, int $target): int {
        if (empty($sortedPeriods)) {
            return 0;
        }

        // Build consecutive runs
        $runs = [];
        $currentRun = [$sortedPeriods[0]];

        for ($i = 1, $count = count($sortedPeriods); $i < $count; $i++) {
            if ($sortedPeriods[$i] === $sortedPeriods[$i - 1] + 1) {
                $currentRun[] = $sortedPeriods[$i];
            } else {
                $runs[] = $currentRun;
                $currentRun = [$sortedPeriods[$i]];
            }
        }
        $runs[] = $currentRun;

        // Find the run that contains the target
        foreach ($runs as $run) {
            if (in_array($target, $run, true)) {
                return count($run);
            }
        }

        return 1;
    }

    /**
     * Determine whether at least one slot in a group remains after exclusions.
     *
     * @param array<int, int|string> $slotIds
     * @param array<int, bool> $excludedLookup
     */
    private function hasIncludedSlots(array $slotIds, array $excludedLookup): bool {
        foreach ($slotIds as $slotId) {
            if (!isset($excludedLookup[(int) $slotId])) {
                return true;
            }
        }

        return false;
    }
}
