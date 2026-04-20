<?php

namespace Tests\Unit\Timetable;

use App\Services\Timetable\Generation\Chromosome;
use App\Services\Timetable\Generation\FitnessEvaluator;
use App\Services\Timetable\Generation\Gene;
use App\Services\Timetable\Generation\GenerationData;
use PHPUnit\Framework\TestCase;

class FitnessEvaluatorTest extends TestCase {
    public function test_evaluate_flags_venue_double_booking_as_hard_violation(): void {
        $data = $this->makeGenerationData();

        $chromosome = new Chromosome([
            new Gene(klassSubjectId: 1, teacherId: 1, klassId: 1, subjectId: 11, duration: 1, gradeId: 1, dayOfCycle: 1, startPeriod: 1, venueId: 9),
            new Gene(klassSubjectId: 2, teacherId: 2, klassId: 2, subjectId: 12, duration: 1, gradeId: 2, dayOfCycle: 1, startPeriod: 1, venueId: 9),
        ]);

        $fitness = (new FitnessEvaluator($data))->evaluate($chromosome);

        $this->assertGreaterThan(0, $chromosome->hardViolationCount);
        $this->assertLessThan(1.0, $fitness);
    }

    public function test_evaluate_flags_assistant_teacher_double_booking_as_hard_violation(): void {
        $data = $this->makeGenerationData();

        $chromosome = new Chromosome([
            new Gene(klassSubjectId: 1, teacherId: 1, klassId: 1, subjectId: 11, duration: 1, gradeId: 1, dayOfCycle: 1, startPeriod: 2, assistantTeacherId: 77),
            new Gene(klassSubjectId: 2, teacherId: 2, klassId: 2, subjectId: 12, duration: 1, gradeId: 2, dayOfCycle: 1, startPeriod: 2, assistantTeacherId: 77),
        ]);

        $fitness = (new FitnessEvaluator($data))->evaluate($chromosome);

        $this->assertGreaterThan(0, $chromosome->hardViolationCount);
        $this->assertLessThan(1.0, $fitness);
    }

    public function test_evaluate_applies_period_restriction_penalty(): void {
        $baseData = $this->makeGenerationData();
        $restrictedData = $this->makeGenerationData([
            'periodRestrictions' => [
                11 => [
                    'restriction' => 'fixed_period',
                    'allowed_periods' => [1],
                ],
            ],
        ]);

        $baselineChromosome = new Chromosome([
            new Gene(klassSubjectId: 1, teacherId: 1, klassId: 1, subjectId: 11, duration: 1, gradeId: 1, dayOfCycle: 1, startPeriod: 2),
        ]);
        $restrictedChromosome = clone $baselineChromosome;

        $baselineFitness = (new FitnessEvaluator($baseData))->evaluate($baselineChromosome);
        $restrictedFitness = (new FitnessEvaluator($restrictedData))->evaluate($restrictedChromosome);

        $this->assertSame(0, $restrictedChromosome->hardViolationCount);
        $this->assertLessThan($baselineFitness, $restrictedFitness);
    }

    public function test_evaluate_applies_subject_pair_penalty_for_not_same_day_rule(): void {
        $baseData = $this->makeGenerationData();
        $pairedData = $this->makeGenerationData([
            'subjectPairs' => [[
                'subject_id_a' => 11,
                'subject_id_b' => 12,
                'klass_id' => 1,
                'rule' => 'not_same_day',
            ]],
        ]);

        $chromosome = new Chromosome([
            new Gene(klassSubjectId: 1, teacherId: 1, klassId: 1, subjectId: 11, duration: 1, gradeId: 1, dayOfCycle: 2, startPeriod: 1),
            new Gene(klassSubjectId: 2, teacherId: 2, klassId: 1, subjectId: 12, duration: 1, gradeId: 1, dayOfCycle: 2, startPeriod: 3),
        ]);

        $baselineFitness = (new FitnessEvaluator($baseData))->evaluate(clone $chromosome);
        $pairedFitness = (new FitnessEvaluator($pairedData))->evaluate($chromosome);

        $this->assertLessThan($baselineFitness, $pairedFitness);
    }

    public function test_evaluate_subject_spread_uses_lessons_not_periods_for_double_blocks(): void {
        $baseData = $this->makeGenerationData();
        $spreadData = $this->makeGenerationData([
            'subjectSpreads' => [
                11 => ['max_lessons_per_day' => 1],
            ],
        ]);

        $chromosome = new Chromosome([
            new Gene(
                klassSubjectId: 1,
                teacherId: 1,
                klassId: 1,
                subjectId: 11,
                duration: 2,
                gradeId: 1,
                dayOfCycle: 1,
                startPeriod: 1
            ),
        ]);

        $baselineFitness = (new FitnessEvaluator($baseData))->evaluate(clone $chromosome);
        $spreadFitness = (new FitnessEvaluator($spreadData))->evaluate($chromosome);

        $this->assertSame(0, $chromosome->hardViolationCount);
        $this->assertSame($baselineFitness, $spreadFitness);
    }

    public function test_evaluate_flags_misaligned_double_as_hard_violation(): void {
        $data = $this->makeGenerationData([
            'periodsPerDay' => 6,
            'breakAfterPeriods' => [],
            'validDoubleStartPeriods' => [1, 3, 5],
        ]);

        $chromosome = new Chromosome([
            new Gene(
                klassSubjectId: 1,
                teacherId: 1,
                klassId: 1,
                subjectId: 11,
                duration: 2,
                gradeId: 1,
                dayOfCycle: 1,
                startPeriod: 2
            ),
        ]);

        (new FitnessEvaluator($data))->evaluate($chromosome);

        $this->assertGreaterThan(0, $chromosome->hardViolationCount);
    }

    public function test_evaluate_accepts_aligned_double_without_alignment_hard_violation(): void {
        $data = $this->makeGenerationData([
            'periodsPerDay' => 6,
            'breakAfterPeriods' => [],
            'validDoubleStartPeriods' => [1, 3, 5],
        ]);

        $chromosome = new Chromosome([
            new Gene(
                klassSubjectId: 1,
                teacherId: 1,
                klassId: 1,
                subjectId: 11,
                duration: 2,
                gradeId: 1,
                dayOfCycle: 1,
                startPeriod: 1
            ),
        ]);

        (new FitnessEvaluator($data))->evaluate($chromosome);

        $this->assertSame(0, $chromosome->hardViolationCount);
    }

    public function test_evaluate_exempts_triples_from_double_alignment_rule(): void {
        $data = $this->makeGenerationData([
            'periodsPerDay' => 8,
            'breakAfterPeriods' => [],
            'validDoubleStartPeriods' => [1, 3, 5, 7],
        ]);

        $chromosome = new Chromosome([
            new Gene(
                klassSubjectId: 1,
                teacherId: 1,
                klassId: 1,
                subjectId: 11,
                duration: 3,
                gradeId: 1,
                dayOfCycle: 1,
                startPeriod: 2
            ),
        ]);

        (new FitnessEvaluator($data))->evaluate($chromosome);

        $this->assertSame(0, $chromosome->hardViolationCount);
    }

    public function test_evaluate_flags_optional_gene_conflicting_with_locked_core_grade_slot(): void {
        $data = $this->makeGenerationData([
            'lockedSlots' => [[
                'day_of_cycle' => 1,
                'period_number' => 1,
                'teacher_id' => 0,
                'klass_id' => 1,
                'duration' => 1,
                'venue_id' => 0,
                'assistant_teacher_id' => 0,
                'grade_id' => 1,
                'is_optional' => false,
                'optional_subject_id' => null,
                'coupling_group_key' => null,
            ]],
        ]);

        $chromosome = new Chromosome([
            new Gene(
                klassSubjectId: 0,
                teacherId: 1,
                klassId: 0,
                subjectId: 11,
                duration: 1,
                gradeId: 1,
                dayOfCycle: 1,
                startPeriod: 1,
                optionalSubjectId: 9001
            ),
        ]);

        (new FitnessEvaluator($data))->evaluate($chromosome);

        $this->assertGreaterThan(0, $chromosome->hardViolationCount);
    }

    public function test_evaluate_flags_core_gene_conflicting_with_locked_optional_grade_slot(): void {
        $data = $this->makeGenerationData([
            'lockedSlots' => [[
                'day_of_cycle' => 1,
                'period_number' => 2,
                'teacher_id' => 0,
                'klass_id' => 0,
                'duration' => 1,
                'venue_id' => 0,
                'assistant_teacher_id' => 0,
                'grade_id' => 1,
                'is_optional' => true,
                'optional_subject_id' => 4001,
                'coupling_group_key' => 'cg_x',
            ]],
        ]);

        $chromosome = new Chromosome([
            new Gene(
                klassSubjectId: 1,
                teacherId: 1,
                klassId: 1,
                subjectId: 11,
                duration: 1,
                gradeId: 1,
                dayOfCycle: 1,
                startPeriod: 2
            ),
        ]);

        (new FitnessEvaluator($data))->evaluate($chromosome);

        $this->assertGreaterThan(0, $chromosome->hardViolationCount);
    }

    private function makeGenerationData(array $overrides = []): GenerationData {
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
            'teacherNames' => [1 => 'Teacher One', 2 => 'Teacher Two', 77 => 'Assistant'],
            'klassNames' => [1 => 'Class 1', 2 => 'Class 2'],
            'subjectNames' => [11 => 'Math', 12 => 'Science'],
            'lockedSlots' => [],
            'optionalSubjectMap' => [],
            'venueNames' => [9 => 'Lab'],
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
}
