<?php

namespace App\Services\Timetable\Generation;

/**
 * Immutable value object holding all pre-loaded timetable data for the GA.
 *
 * Constructed once by GenerationDataLoader before evolution begins.
 * All arrays are indexed for O(1) lookups. Zero database queries after construction.
 */
class GenerationData {
    public function __construct(
        /** Timetable being generated. */
        public readonly int $timetableId,

        /** Number of teaching periods per day. */
        public readonly int $periodsPerDay,

        /** Number of days in the cycle (always 6). */
        public readonly int $cycleDays,

        /** Period numbers after which a break occurs (blocks cannot span these). */
        public readonly array $breakAfterPeriods,

        /**
         * KlassSubject allocations keyed by klass_subject_id.
         * Each value: [teacher_id, klass_id, grade_id, subject_id, singles, doubles, triples]
         */
        public readonly array $klassSubjects,

        /**
         * Teacher unavailability keyed by teacher_id.
         * Each value: array of [day_of_cycle => int, period_number => int]
         */
        public readonly array $teacherUnavailability,

        /**
         * Teacher preferences keyed by teacher_id.
         * Each value: [preference => string, preferred_periods => int[]]
         */
        public readonly array $teacherPreferences,

        /**
         * Subject spread limits keyed by subject_id.
         * Each value: [max_lessons_per_day => int]
         */
        public readonly array $subjectSpreads,

        /**
         * Consecutive period limits keyed by teacher_id (null key = global).
         * Each value: [max_consecutive_periods => int]
         */
        public readonly array $consecutiveLimits,

        /**
         * Room requirements keyed by subject_id.
         * Each value: venue type string.
         */
        public readonly array $roomRequirements,

        /**
         * Coupling groups from timetable_settings.
         * Each: [grade_id, label, optional_subject_ids[], singles, doubles, triples]
         */
        public readonly array $couplingGroups,

        /**
         * Teacher assignments: teacher_id => [klass_subject_id, ...]
         */
        public readonly array $teacherAssignments,

        /**
         * Class assignments: klass_id => [klass_subject_id, ...]
         */
        public readonly array $klassAssignments,

        /** Teacher names: teacher_id => name string. */
        public readonly array $teacherNames,

        /** Class names: klass_id => name string. */
        public readonly array $klassNames,

        /** Subject names: subject_id => name string. */
        public readonly array $subjectNames,

        /**
         * Locked slots that GA must work around.
         * Each: [
         *   day_of_cycle, period_number, teacher_id, klass_id, duration, venue_id,
         *   assistant_teacher_id, grade_id, is_optional, optional_subject_id, coupling_group_key
         * ]
         */
        public readonly array $lockedSlots,

        /**
         * Optional subject info keyed by optional_subject_id.
         * Each: [teacher_id, subject_id, grade_id]
         */
        public readonly array $optionalSubjectMap,

        /**
         * Valid start periods for 2-period blocks, derived from period segments.
         * Example: [1,3,5,7] when periods are split into 1-4 and 5-8.
         */
        public readonly array $validDoubleStartPeriods = [],

        /** Venue names: venue_id => name string. */
        public readonly array $venueNames = [],

        /**
         * Assistant teacher assignments: assistant_teacher_id => [klass_subject_id, ...]
         */
        public readonly array $assistantTeacherAssignments = [],

        /**
         * Subject pair rules: flat array of [subject_id_a, subject_id_b, klass_id, rule].
         */
        public readonly array $subjectPairs = [],

        /**
         * Period restrictions keyed by subject_id: [restriction, allowed_periods[]].
         */
        public readonly array $periodRestrictions = [],

        /** Venues grouped by normalized type: type => [venue_id, ...] */
        public readonly array $venuesByType = [],

        /** Reverse lookup: venue_id => normalized type string */
        public readonly array $venueTypeMap = [],

        /** Teacher home-room assignments: teacher_id => venue_id */
        public readonly array $teacherRoomAssignments = [],
    ) {}

    /**
     * Get total available teaching slots per cycle.
     */
    public function getAvailableSlots(): int {
        return $this->periodsPerDay * $this->cycleDays;
    }
}
