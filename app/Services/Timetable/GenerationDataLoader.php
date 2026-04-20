<?php

namespace App\Services\Timetable;

use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\Subject;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableBlockAllocation;
use App\Models\Timetable\TimetableConstraint;
use App\Models\Timetable\TimetableSetting;
use App\Models\Timetable\TimetableSlot;
use App\Models\User;
use App\Models\Venue;
use App\Services\Timetable\Generation\GenerationData;
use App\Services\Timetable\Support\BlockPlacementRules;

/**
 * Pre-loads all timetable data into in-memory arrays for the genetic algorithm.
 *
 * Called once before GA starts. After load(), the returned GenerationData object
 * contains everything the GA needs with zero additional database queries.
 */
class GenerationDataLoader {
    /**
     * Load all data needed for GA into a GenerationData value object.
     */
    public function load(Timetable $timetable): GenerationData {
        $periodsPerDay = (int) TimetableSetting::get('periods_per_day', 7);
        $cycleDays = 6;

        $breakAfterPeriods = $this->loadBreakPeriods();
        $validDoubleStartPeriods = BlockPlacementRules::computeValidDoubleStartPeriods(
            $periodsPerDay,
            $breakAfterPeriods
        );
        $klassSubjects = $this->loadKlassSubjects($timetable);

        [
            $teacherUnavailability,
            $teacherPreferences,
            $subjectSpreads,
            $consecutiveLimits,
            $roomRequirements,
            $subjectPairs,
            $periodRestrictions,
            $teacherRoomAssignments,
        ] = $this->loadConstraintsByType($timetable);

        $couplingGroups = $this->loadCouplingGroups();
        $optionalSubjectMap = $this->loadOptionalSubjectMap($couplingGroups);

        $teacherAssignments = $this->buildTeacherIndex($klassSubjects);
        $klassAssignments = $this->buildKlassIndex($klassSubjects);

        $teacherIds = array_unique(array_column($klassSubjects, 'teacher_id'));
        $klassIds = array_unique(array_column($klassSubjects, 'klass_id'));

        // Also include teacher IDs from optional subjects
        foreach ($optionalSubjectMap as $info) {
            $teacherIds[] = (int) $info['teacher_id'];
        }

        // Also include assistant teacher IDs
        foreach ($klassSubjects as $ks) {
            if (($ks['assistant_user_id'] ?? 0) > 0) {
                $teacherIds[] = (int) $ks['assistant_user_id'];
            }
        }
        $teacherIds = array_unique(array_filter($teacherIds));

        $subjectIds = array_unique(array_filter(array_column($klassSubjects, 'subject_id')));
        // Also include subject IDs from optional subjects
        foreach ($optionalSubjectMap as $info) {
            $subjectIds[] = (int) $info['subject_id'];
        }
        $subjectIds = array_unique(array_filter($subjectIds));

        $teacherNames = $this->loadTeacherNames($teacherIds);
        $klassNames = $this->loadKlassNames($klassIds);
        $subjectNames = $this->loadSubjectNames($subjectIds);
        $lockedSlots = $this->loadLockedSlots($timetable);
        $venueNames = $this->loadVenueNames($klassSubjects, $optionalSubjectMap);
        $assistantTeacherAssignments = $this->buildAssistantTeacherIndex($klassSubjects);
        [$venuesByType, $venueTypeMap] = $this->loadVenuesByType();

        // Stamp home-room venues onto klassSubjects where no higher-priority venue is set
        foreach ($klassSubjects as $ksId => &$ks) {
            if ($ks['venue_id'] !== 0) continue;                                  // explicit venue wins
            if (!isset($teacherRoomAssignments[$ks['teacher_id']])) continue;     // no home room
            if (isset($roomRequirements[$ks['subject_id']])) continue;            // room requirement wins
            $ks['venue_id'] = $teacherRoomAssignments[$ks['teacher_id']];
        }
        unset($ks);

        return new GenerationData(
            timetableId: $timetable->id,
            periodsPerDay: $periodsPerDay,
            cycleDays: $cycleDays,
            breakAfterPeriods: $breakAfterPeriods,
            validDoubleStartPeriods: $validDoubleStartPeriods,
            klassSubjects: $klassSubjects,
            teacherUnavailability: $teacherUnavailability,
            teacherPreferences: $teacherPreferences,
            subjectSpreads: $subjectSpreads,
            consecutiveLimits: $consecutiveLimits,
            roomRequirements: $roomRequirements,
            couplingGroups: $couplingGroups,
            teacherAssignments: $teacherAssignments,
            klassAssignments: $klassAssignments,
            teacherNames: $teacherNames,
            klassNames: $klassNames,
            subjectNames: $subjectNames,
            lockedSlots: $lockedSlots,
            optionalSubjectMap: $optionalSubjectMap,
            venueNames: $venueNames,
            assistantTeacherAssignments: $assistantTeacherAssignments,
            subjectPairs: $subjectPairs,
            periodRestrictions: $periodRestrictions,
            venuesByType: $venuesByType,
            venueTypeMap: $venueTypeMap,
            teacherRoomAssignments: $teacherRoomAssignments,
        );
    }

    /**
     * Load break period numbers from break_intervals setting.
     *
     * @return int[] Period numbers after which a break occurs
     */
    private function loadBreakPeriods(): array {
        $breakIntervals = TimetableSetting::get('break_intervals', []);
        $breakAfterPeriods = [];

        foreach ($breakIntervals as $break) {
            $breakAfterPeriods[] = (int) $break['after_period'];
        }

        return $breakAfterPeriods;
    }

    /**
     * Load all block allocations for the timetable.
     *
     * @return array Keyed by klass_subject_id => assoc array
     */
    private function loadKlassSubjects(Timetable $timetable): array {
        $allocations = TimetableBlockAllocation::where('timetable_id', $timetable->id)
            ->with(['klassSubject.gradeSubject.subject', 'klassSubject.klass', 'klassSubject.teacher'])
            ->get();

        $result = [];
        foreach ($allocations as $alloc) {
            $ks = $alloc->klassSubject;
            if (!$ks) {
                continue;
            }

            $result[$ks->id] = [
                'klass_subject_id' => $ks->id,
                'teacher_id' => (int) $ks->user_id,
                'klass_id' => (int) $ks->klass_id,
                'grade_id' => (int) ($ks->klass?->grade_id ?? 0),
                'subject_id' => (int) ($ks->gradeSubject?->subject_id ?? 0),
                'singles' => (int) $alloc->singles,
                'doubles' => (int) $alloc->doubles,
                'triples' => (int) $alloc->triples,
                'venue_id' => (int) ($ks->venue_id ?? 0),
                'assistant_user_id' => (int) ($ks->assistant_user_id ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Load all active constraints grouped by type.
     *
     * @return array [teacherUnavailability, teacherPreferences, subjectSpreads, consecutiveLimits, roomRequirements, subjectPairs, periodRestrictions]
     */
    private function loadConstraintsByType(Timetable $timetable): array {
        $constraints = TimetableConstraint::where('timetable_id', $timetable->id)
            ->active()
            ->get();

        $teacherUnavailability = [];
        $teacherPreferences = [];
        $subjectSpreads = [];
        $consecutiveLimits = [];
        $roomRequirements = [];
        $subjectPairs = [];
        $periodRestrictions = [];
        $teacherRoomAssignments = [];

        foreach ($constraints as $constraint) {
            $config = $constraint->constraint_config;

            switch ($constraint->constraint_type) {
                case TimetableConstraint::TYPE_TEACHER_AVAILABILITY:
                    $teacherId = (int) ($config['teacher_id'] ?? 0);
                    $unavailableSlots = $config['unavailable_slots'] ?? [];
                    if ($teacherId && !empty($unavailableSlots)) {
                        $teacherUnavailability[$teacherId] = array_map(fn($slot) => [
                            'day_of_cycle' => (int) ($slot['day_of_cycle'] ?? 0),
                            'period_number' => (int) ($slot['period_number'] ?? 0),
                        ], $unavailableSlots);
                    }
                    break;

                case TimetableConstraint::TYPE_TEACHER_PREFERENCE:
                    $teacherId = (int) ($config['teacher_id'] ?? 0);
                    $preference = $config['preference'] ?? 'none';
                    if ($teacherId && $preference !== 'none') {
                        $teacherPreferences[$teacherId] = [
                            'preference' => $preference,
                            'preferred_periods' => array_map('intval', $config['preferred_periods'] ?? []),
                        ];
                    }
                    break;

                case TimetableConstraint::TYPE_SUBJECT_SPREAD:
                    $subjectId = (int) ($config['subject_id'] ?? 0);
                    $maxLessons = (int) ($config['max_lessons_per_day'] ?? 0);
                    if ($subjectId && $maxLessons > 0) {
                        $subjectSpreads[$subjectId] = [
                            'max_lessons_per_day' => $maxLessons,
                        ];
                    }
                    break;

                case TimetableConstraint::TYPE_CONSECUTIVE_LIMIT:
                    $teacherId = $config['teacher_id'] ?? null;
                    $max = (int) ($config['max_consecutive_periods'] ?? 0);
                    if ($max > 0) {
                        $key = $teacherId !== null ? (int) $teacherId : 'global';
                        $consecutiveLimits[$key] = [
                            'max_consecutive_periods' => $max,
                        ];
                    }
                    break;

                case TimetableConstraint::TYPE_ROOM_REQUIREMENT:
                    $subjectId = (int) ($config['subject_id'] ?? 0);
                    $venueType = $config['required_venue_type'] ?? null;
                    if ($subjectId && $venueType) {
                        $roomRequirements[$subjectId] = $venueType;
                    }
                    break;

                case TimetableConstraint::TYPE_SUBJECT_PAIR:
                    $subjectIdA = (int) ($config['subject_id_a'] ?? 0);
                    $subjectIdB = (int) ($config['subject_id_b'] ?? 0);
                    $klassId = ($config['klass_id'] ?? null) !== null ? (int) $config['klass_id'] : null;
                    $rule = $config['rule'] ?? '';
                    if ($subjectIdA && $subjectIdB && $rule) {
                        $subjectPairs[] = [
                            'subject_id_a' => $subjectIdA,
                            'subject_id_b' => $subjectIdB,
                            'klass_id' => $klassId,
                            'rule' => $rule,
                        ];
                    }
                    break;

                case TimetableConstraint::TYPE_PERIOD_RESTRICTION:
                    $subjectId = (int) ($config['subject_id'] ?? 0);
                    $restriction = $config['restriction'] ?? '';
                    $allowedPeriods = array_map('intval', $config['allowed_periods'] ?? []);
                    if ($subjectId && $restriction && !empty($allowedPeriods)) {
                        $periodRestrictions[$subjectId] = [
                            'restriction' => $restriction,
                            'allowed_periods' => $allowedPeriods,
                        ];
                    }
                    break;

                case TimetableConstraint::TYPE_TEACHER_ROOM_ASSIGNMENT:
                    $teacherId = (int) ($config['teacher_id'] ?? 0);
                    $venueId = (int) ($config['venue_id'] ?? 0);
                    if ($teacherId && $venueId) {
                        $teacherRoomAssignments[$teacherId] = $venueId;
                    }
                    break;
            }
        }

        return [$teacherUnavailability, $teacherPreferences, $subjectSpreads, $consecutiveLimits, $roomRequirements, $subjectPairs, $periodRestrictions, $teacherRoomAssignments];
    }

    /**
     * Load coupling groups from timetable_settings.
     */
    private function loadCouplingGroups(): array {
        return TimetableSetting::get('optional_coupling_groups', []);
    }

    /**
     * Load optional subject info for coupling group scheduling.
     *
     * @return array Keyed by optional_subject_id => [teacher_id, subject_id, grade_id]
     */
    private function loadOptionalSubjectMap(array $couplingGroups): array {
        $allOptSubjectIds = [];
        foreach ($couplingGroups as $group) {
            foreach ($group['optional_subject_ids'] ?? [] as $osId) {
                $allOptSubjectIds[] = (int) $osId;
            }
        }

        if (empty($allOptSubjectIds)) {
            return [];
        }

        $allOptSubjectIds = array_unique($allOptSubjectIds);
        $optSubjects = OptionalSubject::whereIn('id', $allOptSubjectIds)
            ->with('gradeSubject')
            ->get();

        $map = [];
        foreach ($optSubjects as $os) {
            $map[$os->id] = [
                'teacher_id' => (int) $os->user_id,
                'subject_id' => (int) ($os->gradeSubject?->subject_id ?? 0),
                'grade_id' => (int) $os->grade_id,
                'venue_id' => (int) ($os->venue_id ?? 0),
            ];
        }

        return $map;
    }

    /**
     * Build teacher_id => [klass_subject_id, ...] index.
     */
    private function buildTeacherIndex(array $klassSubjects): array {
        $index = [];
        foreach ($klassSubjects as $ksId => $data) {
            $teacherId = $data['teacher_id'];
            $index[$teacherId][] = $ksId;
        }
        return $index;
    }

    /**
     * Build klass_id => [klass_subject_id, ...] index.
     */
    private function buildKlassIndex(array $klassSubjects): array {
        $index = [];
        foreach ($klassSubjects as $ksId => $data) {
            $klassId = $data['klass_id'];
            $index[$klassId][] = $ksId;
        }
        return $index;
    }

    /**
     * Load teacher names by IDs.
     *
     * @return array teacher_id => "Firstname Lastname"
     */
    private function loadTeacherNames(array $teacherIds): array {
        if (empty($teacherIds)) {
            return [];
        }

        return User::whereIn('id', $teacherIds)
            ->select('id', 'firstname', 'lastname')
            ->get()
            ->mapWithKeys(fn($t) => [$t->id => $t->firstname . ' ' . $t->lastname])
            ->toArray();
    }

    /**
     * Load class names by IDs.
     *
     * @return array klass_id => name string
     */
    private function loadKlassNames(array $klassIds): array {
        if (empty($klassIds)) {
            return [];
        }

        return Klass::whereIn('id', $klassIds)
            ->select('id', 'name')
            ->get()
            ->mapWithKeys(fn($k) => [$k->id => $k->name])
            ->toArray();
    }

    /**
     * Load subject names by IDs.
     *
     * @return array subject_id => name string
     */
    private function loadSubjectNames(array $subjectIds): array {
        if (empty($subjectIds)) {
            return [];
        }

        return Subject::whereIn('id', $subjectIds)
            ->select('id', 'name')
            ->get()
            ->mapWithKeys(fn($s) => [$s->id => $s->name])
            ->toArray();
    }

    /**
     * Load locked slots for the timetable.
     *
     * @return array Each:
     *   [day_of_cycle, period_number, teacher_id, klass_id, duration, venue_id,
     *    assistant_teacher_id, grade_id, is_optional, optional_subject_id, coupling_group_key]
     */
    private function loadLockedSlots(Timetable $timetable): array {
        return TimetableSlot::where('timetable_id', $timetable->id)
            ->where('is_locked', true)
            ->with(['klassSubject.klass:id,grade_id', 'optionalSubject:id,grade_id'])
            ->get()
            ->map(fn($slot) => [
                'day_of_cycle' => (int) $slot->day_of_cycle,
                'period_number' => (int) $slot->period_number,
                'teacher_id' => (int) $slot->teacher_id,
                'klass_id' => (int) ($slot->klassSubject?->klass_id ?? 0),
                'duration' => (int) $slot->duration,
                'venue_id' => (int) ($slot->venue_id ?? 0),
                'assistant_teacher_id' => (int) ($slot->assistant_teacher_id ?? 0),
                'grade_id' => (int) ($slot->klassSubject?->klass?->grade_id ?? $slot->optionalSubject?->grade_id ?? 0),
                'is_optional' => (bool) ($slot->optional_subject_id !== null),
                'optional_subject_id' => $slot->optional_subject_id !== null ? (int) $slot->optional_subject_id : null,
                'coupling_group_key' => $slot->coupling_group_key,
            ])
            ->toArray();
    }

    /**
     * Load venue names for all venue IDs referenced by klassSubjects and optional subjects.
     *
     * @return array venue_id => name string
     */
    private function loadVenueNames(array $klassSubjects, array $optionalSubjectMap): array {
        $venueIds = [];
        foreach ($klassSubjects as $ks) {
            if (($ks['venue_id'] ?? 0) > 0) {
                $venueIds[] = (int) $ks['venue_id'];
            }
        }
        foreach ($optionalSubjectMap as $info) {
            if (($info['venue_id'] ?? 0) > 0) {
                $venueIds[] = (int) $info['venue_id'];
            }
        }

        $venueIds = array_unique($venueIds);
        if (empty($venueIds)) {
            return [];
        }

        return Venue::whereIn('id', $venueIds)
            ->select('id', 'name')
            ->get()
            ->mapWithKeys(fn($v) => [$v->id => $v->name])
            ->toArray();
    }

    /**
     * Load all venues grouped by their normalized type.
     *
     * @return array{0: array<string, int[]>, 1: array<int, string>}
     */
    private function loadVenuesByType(): array {
        $venues = Venue::whereNull('deleted_at')
            ->select('id', 'type')
            ->get();

        $venuesByType = [];
        $venueTypeMap = [];

        foreach ($venues as $venue) {
            $type = strtolower(trim($venue->type ?? 'classroom'));
            $venuesByType[$type][] = (int) $venue->id;
            $venueTypeMap[(int) $venue->id] = $type;
        }

        return [$venuesByType, $venueTypeMap];
    }

    /**
     * Build assistant_teacher_id => [klass_subject_id, ...] index.
     */
    private function buildAssistantTeacherIndex(array $klassSubjects): array {
        $index = [];
        foreach ($klassSubjects as $ksId => $data) {
            $assistantId = (int) ($data['assistant_user_id'] ?? 0);
            if ($assistantId > 0) {
                $index[$assistantId][] = $ksId;
            }
        }
        return $index;
    }
}
