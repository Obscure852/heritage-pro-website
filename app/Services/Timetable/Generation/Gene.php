<?php

namespace App\Services\Timetable\Generation;

/**
 * A single scheduling unit in the genetic algorithm.
 *
 * Represents one block of teaching that needs to be placed on the timetable grid.
 * For a single period: duration=1. For a double: duration=2. For a triple: duration=3.
 * Unassigned genes have dayOfCycle=0 and startPeriod=0.
 *
 * @property int $klassSubjectId  KlassSubject pivot ID (0 for optional subjects)
 * @property int $teacherId       Teacher (User) ID
 * @property int $klassId         Class ID (0 for grade-wide optional subjects)
 * @property int $subjectId       Subject ID for constraint lookups
 * @property int $duration        Block size: 1=single, 2=double, 3=triple
 * @property int $gradeId         Grade ID (used to separate core vs elective windows)
 * @property int $dayOfCycle      Day position (1-6), 0=unassigned
 * @property int $startPeriod     Period position (1-N), 0=unassigned
 * @property string|null $couplingKey  Shared key for coupled optional subjects (null for regular)
 * @property int|null $optionalSubjectId  OptionalSubject ID (null for regular KlassSubjects)
 */
class Gene {
    public function __construct(
        public int $klassSubjectId,
        public int $teacherId,
        public int $klassId,
        public int $subjectId,
        public int $duration,
        public int $gradeId = 0,
        public int $dayOfCycle = 0,
        public int $startPeriod = 0,
        public ?string $couplingKey = null,
        public ?int $optionalSubjectId = null,
        public int $venueId = 0,
        public int $assistantTeacherId = 0,
    ) {}

    /**
     * Deep clone support (trivial since all properties are scalars).
     */
    public function __clone(): void {
        // All properties are scalars/nullables -- no nested objects to clone.
    }
}
