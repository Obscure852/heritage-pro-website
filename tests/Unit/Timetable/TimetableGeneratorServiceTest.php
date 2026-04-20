<?php

namespace Tests\Unit\Timetable;

use App\Services\Timetable\Generation\GenerationData;
use App\Services\Timetable\TimetableGeneratorService;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class TimetableGeneratorServiceTest extends TestCase {
    public function test_generate_resolves_shared_teacher_without_hard_conflicts(): void {
        mt_srand(1001);
        $service = $this->makeFastGenerator();

        $data = $this->makeGenerationData([
            'klassSubjects' => [
                101 => ['klass_subject_id' => 101, 'teacher_id' => 1, 'klass_id' => 1, 'subject_id' => 11, 'singles' => 3, 'doubles' => 0, 'triples' => 0],
                102 => ['klass_subject_id' => 102, 'teacher_id' => 1, 'klass_id' => 2, 'subject_id' => 12, 'singles' => 3, 'doubles' => 0, 'triples' => 0],
            ],
            'teacherAssignments' => [1 => [101, 102]],
            'klassAssignments' => [1 => [101], 2 => [102]],
            'teacherNames' => [1 => 'Teacher One'],
            'klassNames' => [1 => 'Class 1', 2 => 'Class 2'],
            'subjectNames' => [11 => 'Math', 12 => 'Science'],
        ]);

        $result = $service->generate($data, function (int $generation, int $maxGeneration, float $fitness): void {});

        $this->assertSame(0, $result->hardViolationCount, 'Expected a feasible schedule for shared-teacher case.');

        $teacherOccupancy = [];
        foreach ($result->chromosome->genes as $gene) {
            for ($p = $gene->startPeriod; $p < $gene->startPeriod + $gene->duration; $p++) {
                $key = "{$gene->teacherId}:{$gene->dayOfCycle}:{$p}";
                $teacherOccupancy[$key] = ($teacherOccupancy[$key] ?? 0) + 1;
            }
        }

        foreach ($teacherOccupancy as $count) {
            $this->assertSame(1, $count, 'Teacher should not be double-booked.');
        }
    }

    public function test_generate_respects_teacher_unavailability_when_feasible(): void {
        mt_srand(2002);
        $service = $this->makeFastGenerator();

        $unavailable = [];
        for ($period = 1; $period <= 6; $period++) {
            $unavailable[] = ['day_of_cycle' => 1, 'period_number' => $period];
        }

        $data = $this->makeGenerationData([
            'klassSubjects' => [
                201 => ['klass_subject_id' => 201, 'teacher_id' => 1, 'klass_id' => 1, 'subject_id' => 21, 'singles' => 5, 'doubles' => 0, 'triples' => 0],
            ],
            'teacherAssignments' => [1 => [201]],
            'klassAssignments' => [1 => [201]],
            'teacherUnavailability' => [1 => $unavailable],
            'teacherNames' => [1 => 'Teacher One'],
            'klassNames' => [1 => 'Class 1'],
            'subjectNames' => [21 => 'English'],
        ]);

        $result = $service->generate($data, function (int $generation, int $maxGeneration, float $fitness): void {});

        $this->assertSame(0, $result->hardViolationCount, 'Expected feasible schedule with availability constraint.');

        foreach ($result->chromosome->genes as $gene) {
            if ($gene->teacherId !== 1) {
                continue;
            }

            $this->assertNotSame(1, $gene->dayOfCycle, 'Teacher should not be scheduled on blocked day.');
        }
    }

    public function test_generate_spread_scoring_uses_lessons_per_day_not_periods(): void {
        mt_srand(2102);
        $service = $this->makeFastGenerator();

        $data = $this->makeGenerationData([
            'klassSubjects' => [
                211 => ['klass_subject_id' => 211, 'teacher_id' => 1, 'klass_id' => 1, 'subject_id' => 11, 'singles' => 0, 'doubles' => 6, 'triples' => 0],
            ],
            'teacherAssignments' => [1 => [211]],
            'klassAssignments' => [1 => [211]],
            'subjectSpreads' => [
                11 => ['max_lessons_per_day' => 1],
            ],
            'teacherNames' => [1 => 'Teacher One'],
            'klassNames' => [1 => 'Class 1'],
            'subjectNames' => [11 => 'Math'],
            'periodsPerDay' => 8,
            'breakAfterPeriods' => [4],
            'validDoubleStartPeriods' => [1, 3, 5, 7],
        ]);

        $result = $service->generate($data, function (int $generation, int $maxGeneration, float $fitness): void {});

        $lessonCountByDay = [];
        foreach ($result->chromosome->genes as $gene) {
            if ($gene->subjectId !== 11 || $gene->klassId !== 1 || $gene->dayOfCycle <= 0) {
                continue;
            }

            $lessonCountByDay[$gene->dayOfCycle] = ($lessonCountByDay[$gene->dayOfCycle] ?? 0) + 1;
        }

        foreach ($lessonCountByDay as $count) {
            $this->assertLessThanOrEqual(1, $count, 'Subject spread should count lessons/day, not period slots/day.');
        }
    }

    public function test_generate_keeps_coupling_groups_synchronized(): void {
        mt_srand(3003);
        $service = $this->makeFastGenerator();

        $data = $this->makeGenerationData([
            'couplingGroups' => [
                [
                    'grade_id' => 1,
                    'label' => 'group-a',
                    'optional_subject_ids' => [501, 502],
                    'singles' => 2,
                    'doubles' => 0,
                    'triples' => 0,
                ],
            ],
            'optionalSubjectMap' => [
                501 => ['teacher_id' => 3, 'subject_id' => 31],
                502 => ['teacher_id' => 4, 'subject_id' => 32],
            ],
            'teacherNames' => [3 => 'Teacher Three', 4 => 'Teacher Four'],
            'subjectNames' => [31 => 'History', 32 => 'Geography'],
        ]);

        $result = $service->generate($data, function (int $generation, int $maxGeneration, float $fitness): void {});

        $this->assertSame(0, $result->hardViolationCount, 'Expected feasible schedule for coupled optional subjects.');

        $positionsByCoupling = [];
        foreach ($result->chromosome->genes as $gene) {
            if ($gene->couplingKey === null) {
                continue;
            }
            $positionsByCoupling[$gene->couplingKey]["{$gene->dayOfCycle}:{$gene->startPeriod}"] = true;
        }

        foreach ($positionsByCoupling as $positions) {
            $this->assertCount(1, $positions, 'All genes in the same coupling group must share one slot.');
        }
    }

    public function test_generate_resolves_shared_venue_without_hard_conflicts(): void {
        mt_srand(4004);
        $service = $this->makeFastGenerator();

        $data = $this->makeGenerationData([
            'klassSubjects' => [
                301 => ['klass_subject_id' => 301, 'teacher_id' => 11, 'klass_id' => 1, 'subject_id' => 41, 'singles' => 4, 'doubles' => 0, 'triples' => 0, 'venue_id' => 50],
                302 => ['klass_subject_id' => 302, 'teacher_id' => 12, 'klass_id' => 2, 'subject_id' => 42, 'singles' => 4, 'doubles' => 0, 'triples' => 0, 'venue_id' => 50],
            ],
            'teacherAssignments' => [11 => [301], 12 => [302]],
            'klassAssignments' => [1 => [301], 2 => [302]],
            'teacherNames' => [11 => 'Teacher 11', 12 => 'Teacher 12'],
            'klassNames' => [1 => 'Class 1', 2 => 'Class 2'],
            'subjectNames' => [41 => 'Biology', 42 => 'Chemistry'],
        ]);

        $result = $service->generate($data, function (int $generation, int $maxGeneration, float $fitness): void {});

        $this->assertSame(0, $result->hardViolationCount, 'Expected a feasible schedule for shared-venue case.');

        $venueOccupancy = [];
        foreach ($result->chromosome->genes as $gene) {
            for ($p = $gene->startPeriod; $p < $gene->startPeriod + $gene->duration; $p++) {
                $key = "{$gene->venueId}:{$gene->dayOfCycle}:{$p}";
                $venueOccupancy[$key] = ($venueOccupancy[$key] ?? 0) + 1;
            }
        }

        foreach ($venueOccupancy as $count) {
            $this->assertSame(1, $count, 'Venue should not be double-booked.');
        }
    }

    public function test_generate_resolves_shared_assistant_teacher_without_hard_conflicts(): void {
        mt_srand(5005);
        $service = $this->makeFastGenerator();

        $data = $this->makeGenerationData([
            'klassSubjects' => [
                401 => ['klass_subject_id' => 401, 'teacher_id' => 21, 'klass_id' => 1, 'subject_id' => 51, 'singles' => 3, 'doubles' => 0, 'triples' => 0, 'assistant_user_id' => 99],
                402 => ['klass_subject_id' => 402, 'teacher_id' => 22, 'klass_id' => 2, 'subject_id' => 52, 'singles' => 3, 'doubles' => 0, 'triples' => 0, 'assistant_user_id' => 99],
            ],
            'teacherAssignments' => [21 => [401], 22 => [402]],
            'klassAssignments' => [1 => [401], 2 => [402]],
            'teacherNames' => [21 => 'Teacher 21', 22 => 'Teacher 22', 99 => 'Assistant 99'],
            'klassNames' => [1 => 'Class 1', 2 => 'Class 2'],
            'subjectNames' => [51 => 'History', 52 => 'Geography'],
        ]);

        $result = $service->generate($data, function (int $generation, int $maxGeneration, float $fitness): void {});

        $this->assertSame(0, $result->hardViolationCount, 'Expected a feasible schedule for shared-assistant case.');

        $assistantOccupancy = [];
        foreach ($result->chromosome->genes as $gene) {
            for ($p = $gene->startPeriod; $p < $gene->startPeriod + $gene->duration; $p++) {
                $key = "{$gene->assistantTeacherId}:{$gene->dayOfCycle}:{$p}";
                $assistantOccupancy[$key] = ($assistantOccupancy[$key] ?? 0) + 1;
            }
        }

        foreach ($assistantOccupancy as $count) {
            $this->assertSame(1, $count, 'Assistant teacher should not be double-booked.');
        }
    }

    public function test_generate_aligns_double_periods_to_valid_start_boundaries(): void {
        mt_srand(6006);
        $service = $this->makeFastGenerator();

        $data = $this->makeGenerationData([
            'periodsPerDay' => 8,
            'breakAfterPeriods' => [4],
            'validDoubleStartPeriods' => [1, 3, 5, 7],
            'klassSubjects' => [
                601 => ['klass_subject_id' => 601, 'teacher_id' => 31, 'klass_id' => 1, 'subject_id' => 61, 'singles' => 0, 'doubles' => 4, 'triples' => 0],
            ],
            'teacherAssignments' => [31 => [601]],
            'klassAssignments' => [1 => [601]],
            'teacherNames' => [31 => 'Teacher 31'],
            'klassNames' => [1 => 'Class 1'],
            'subjectNames' => [61 => 'Chemistry'],
        ]);

        $result = $service->generate($data, function (int $generation, int $maxGeneration, float $fitness): void {});

        $this->assertSame(0, $result->hardViolationCount);

        foreach ($result->chromosome->genes as $gene) {
            if ($gene->duration !== 2) {
                continue;
            }

            $this->assertContains(
                $gene->startPeriod,
                $data->validDoubleStartPeriods,
                'Double period start is misaligned.'
            );
        }
    }

    public function test_generate_avoids_locked_core_grade_period_for_optional_units(): void {
        mt_srand(7007);
        $service = $this->makeFastGenerator();

        $data = $this->makeGenerationData([
            'periodsPerDay' => 6,
            'breakAfterPeriods' => [3],
            'validDoubleStartPeriods' => [1, 4],
            'couplingGroups' => [[
                'grade_id' => 1,
                'label' => 'grp',
                'optional_subject_ids' => [8001],
                'singles' => 1,
                'doubles' => 0,
                'triples' => 0,
            ]],
            'optionalSubjectMap' => [
                8001 => ['teacher_id' => 90, 'subject_id' => 91, 'grade_id' => 1],
            ],
            'lockedSlots' => [[
                'day_of_cycle' => 1,
                'period_number' => 1,
                'teacher_id' => 0,
                'klass_id' => 10,
                'duration' => 1,
                'venue_id' => 0,
                'assistant_teacher_id' => 0,
                'grade_id' => 1,
                'is_optional' => false,
                'optional_subject_id' => null,
                'coupling_group_key' => null,
            ]],
            'teacherNames' => [90 => 'Teacher 90'],
            'subjectNames' => [91 => 'Elective X'],
        ]);

        $result = $service->generate($data, function (int $generation, int $maxGeneration, float $fitness): void {});

        foreach ($result->chromosome->genes as $gene) {
            if ($gene->optionalSubjectId === null) {
                continue;
            }

            $this->assertFalse(
                $gene->dayOfCycle === 1 && $gene->startPeriod === 1,
                'Optional gene was placed on a locked core grade period.'
            );
        }
    }

    public function test_generate_avoids_locked_optional_grade_period_for_core_units(): void {
        mt_srand(8008);
        $service = $this->makeFastGenerator();

        $data = $this->makeGenerationData([
            'periodsPerDay' => 6,
            'breakAfterPeriods' => [3],
            'validDoubleStartPeriods' => [1, 4],
            'klassSubjects' => [
                901 => ['klass_subject_id' => 901, 'teacher_id' => 40, 'klass_id' => 1, 'grade_id' => 1, 'subject_id' => 92, 'singles' => 1, 'doubles' => 0, 'triples' => 0],
            ],
            'teacherAssignments' => [40 => [901]],
            'klassAssignments' => [1 => [901]],
            'lockedSlots' => [[
                'day_of_cycle' => 1,
                'period_number' => 1,
                'teacher_id' => 0,
                'klass_id' => 0,
                'duration' => 1,
                'venue_id' => 0,
                'assistant_teacher_id' => 0,
                'grade_id' => 1,
                'is_optional' => true,
                'optional_subject_id' => 8001,
                'coupling_group_key' => 'cg_any',
            ]],
            'teacherNames' => [40 => 'Teacher 40'],
            'klassNames' => [1 => 'Class 1'],
            'subjectNames' => [92 => 'Core Y'],
        ]);

        $result = $service->generate($data, function (int $generation, int $maxGeneration, float $fitness): void {});

        foreach ($result->chromosome->genes as $gene) {
            if ($gene->klassId <= 0) {
                continue;
            }

            $this->assertFalse(
                $gene->dayOfCycle === 1 && $gene->startPeriod === 1,
                'Core gene was placed on a locked optional grade period.'
            );
        }
    }

    private function makeFastGenerator(): TimetableGeneratorService {
        $service = new TimetableGeneratorService();
        $this->setPrivateProperty($service, 'populationSize', 30);
        $this->setPrivateProperty($service, 'maxGenerations', 120);
        $this->setPrivateProperty($service, 'stagnationLimit', 12);
        $this->setPrivateProperty($service, 'eliteCount', 2);

        return $service;
    }

    private function setPrivateProperty(object $object, string $name, mixed $value): void {
        $property = new ReflectionProperty($object, $name);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    private function makeGenerationData(array $overrides): GenerationData {
        $defaults = [
            'timetableId' => 1,
            'periodsPerDay' => 6,
            'cycleDays' => 6,
            'breakAfterPeriods' => [3],
            'validDoubleStartPeriods' => [1, 4],
            'klassSubjects' => [],
            'teacherUnavailability' => [],
            'teacherPreferences' => [],
            'subjectSpreads' => [],
            'consecutiveLimits' => [],
            'roomRequirements' => [],
            'couplingGroups' => [],
            'teacherAssignments' => [],
            'klassAssignments' => [],
            'teacherNames' => [],
            'klassNames' => [],
            'subjectNames' => [],
            'lockedSlots' => [],
            'optionalSubjectMap' => [],
            'venueNames' => [],
            'assistantTeacherAssignments' => [],
            'subjectPairs' => [],
            'periodRestrictions' => [],
            'venuesByType' => [],
            'venueTypeMap' => [],
            'teacherRoomAssignments' => [],
        ];

        $payload = array_merge($defaults, $overrides);

        return new GenerationData(
            timetableId: $payload['timetableId'],
            periodsPerDay: $payload['periodsPerDay'],
            cycleDays: $payload['cycleDays'],
            breakAfterPeriods: $payload['breakAfterPeriods'],
            validDoubleStartPeriods: $payload['validDoubleStartPeriods'],
            klassSubjects: $payload['klassSubjects'],
            teacherUnavailability: $payload['teacherUnavailability'],
            teacherPreferences: $payload['teacherPreferences'],
            subjectSpreads: $payload['subjectSpreads'],
            consecutiveLimits: $payload['consecutiveLimits'],
            roomRequirements: $payload['roomRequirements'],
            couplingGroups: $payload['couplingGroups'],
            teacherAssignments: $payload['teacherAssignments'],
            klassAssignments: $payload['klassAssignments'],
            teacherNames: $payload['teacherNames'],
            klassNames: $payload['klassNames'],
            subjectNames: $payload['subjectNames'],
            lockedSlots: $payload['lockedSlots'],
            optionalSubjectMap: $payload['optionalSubjectMap'],
            venueNames: $payload['venueNames'],
            assistantTeacherAssignments: $payload['assistantTeacherAssignments'],
            subjectPairs: $payload['subjectPairs'],
            periodRestrictions: $payload['periodRestrictions'],
            venuesByType: $payload['venuesByType'],
            venueTypeMap: $payload['venueTypeMap'],
            teacherRoomAssignments: $payload['teacherRoomAssignments'],
        );
    }

    public function test_generate_resolves_venue_double_booking_via_post_ga(): void {
        mt_srand(9009);
        $service = $this->makeFastGenerator();

        // Two subjects share venue 50 but have different teachers/classes.
        // Provide alternative classrooms (60, 61) so the resolver can reassign.
        $data = $this->makeGenerationData([
            'klassSubjects' => [
                301 => ['klass_subject_id' => 301, 'teacher_id' => 11, 'klass_id' => 1, 'subject_id' => 41, 'singles' => 4, 'doubles' => 0, 'triples' => 0, 'venue_id' => 50],
                302 => ['klass_subject_id' => 302, 'teacher_id' => 12, 'klass_id' => 2, 'subject_id' => 42, 'singles' => 4, 'doubles' => 0, 'triples' => 0, 'venue_id' => 50],
            ],
            'teacherAssignments' => [11 => [301], 12 => [302]],
            'klassAssignments' => [1 => [301], 2 => [302]],
            'teacherNames' => [11 => 'Teacher 11', 12 => 'Teacher 12'],
            'klassNames' => [1 => 'Class 1', 2 => 'Class 2'],
            'subjectNames' => [41 => 'Biology', 42 => 'Chemistry'],
            'venuesByType' => [
                'classroom' => [50, 60, 61],
            ],
            'venueTypeMap' => [50 => 'classroom', 60 => 'classroom', 61 => 'classroom'],
        ]);

        $result = $service->generate($data, function (int $generation, int $maxGeneration, float $fitness): void {});

        // Verify no venue double-bookings remain
        $venueOccupancy = [];
        foreach ($result->chromosome->genes as $gene) {
            if ($gene->venueId <= 0 || $gene->dayOfCycle <= 0) {
                continue;
            }
            for ($p = $gene->startPeriod; $p < $gene->startPeriod + $gene->duration; $p++) {
                $key = "{$gene->venueId}:{$gene->dayOfCycle}:{$p}";
                $venueOccupancy[$key] = ($venueOccupancy[$key] ?? 0) + 1;
            }
        }

        foreach ($venueOccupancy as $key => $count) {
            $this->assertSame(1, $count, "Venue double-booking at {$key} after post-GA resolution.");
        }
    }

    public function test_venue_resolution_respects_room_requirements(): void {
        mt_srand(9010);
        $service = $this->makeFastGenerator();

        // Science requires a lab (venue 70). Accounting can use any classroom.
        // Both initially assigned venue 70 (lab). Resolver should move Accounting
        // to a classroom, NOT move Science out of the lab.
        $data = $this->makeGenerationData([
            'klassSubjects' => [
                401 => ['klass_subject_id' => 401, 'teacher_id' => 21, 'klass_id' => 1, 'subject_id' => 51, 'singles' => 3, 'doubles' => 0, 'triples' => 0, 'venue_id' => 70],
                402 => ['klass_subject_id' => 402, 'teacher_id' => 22, 'klass_id' => 2, 'subject_id' => 52, 'singles' => 3, 'doubles' => 0, 'triples' => 0, 'venue_id' => 70],
            ],
            'teacherAssignments' => [21 => [401], 22 => [402]],
            'klassAssignments' => [1 => [401], 2 => [402]],
            'teacherNames' => [21 => 'Teacher 21', 22 => 'Teacher 22'],
            'klassNames' => [1 => 'Class 1', 2 => 'Class 2'],
            'subjectNames' => [51 => 'Science', 52 => 'Accounting'],
            'roomRequirements' => [
                51 => 'laboratory',  // Science needs a lab
            ],
            'venuesByType' => [
                'laboratory' => [70, 71],
                'classroom' => [80, 81, 82],
            ],
            'venueTypeMap' => [
                70 => 'laboratory', 71 => 'laboratory',
                80 => 'classroom', 81 => 'classroom', 82 => 'classroom',
            ],
        ]);

        $result = $service->generate($data, function (int $generation, int $maxGeneration, float $fitness): void {});

        // Verify Science genes are always in a laboratory
        $labVenues = [70, 71];
        foreach ($result->chromosome->genes as $gene) {
            if ($gene->subjectId === 51 && $gene->dayOfCycle > 0) {
                $this->assertContains(
                    $gene->venueId,
                    $labVenues,
                    'Science should remain in a laboratory venue, not be moved to a classroom.'
                );
            }
        }

        // Verify no venue double-bookings remain
        $venueOccupancy = [];
        foreach ($result->chromosome->genes as $gene) {
            if ($gene->venueId <= 0 || $gene->dayOfCycle <= 0) {
                continue;
            }
            for ($p = $gene->startPeriod; $p < $gene->startPeriod + $gene->duration; $p++) {
                $key = "{$gene->venueId}:{$gene->dayOfCycle}:{$p}";
                $venueOccupancy[$key] = ($venueOccupancy[$key] ?? 0) + 1;
            }
        }

        foreach ($venueOccupancy as $key => $count) {
            $this->assertSame(1, $count, "Venue double-booking at {$key} after resolution.");
        }
    }
}
