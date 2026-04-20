<?php

namespace Tests\Feature\Timetable;

use App\Jobs\Timetable\GenerateTimetableJob;
use App\Models\Term;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableSetting;
use App\Models\User;
use App\Services\Timetable\Generation\Chromosome;
use App\Services\Timetable\Generation\GenerationData;
use App\Services\Timetable\Generation\GenerationResult;
use App\Services\Timetable\GenerationDataLoader;
use App\Services\Timetable\TimetableGeneratorService;
use App\Services\Timetable\TimetableIntegrityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GenerateTimetableJobTest extends TestCase {
    use RefreshDatabase;

    protected function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    public function test_generation_continues_when_locked_integrity_blockers_exist(): void {
        $ctx = $this->createTimetableContext();
        $job = new GenerateTimetableJob($ctx['timetable'], $ctx['user']->id);
        $data = $this->makeGenerationData($ctx['timetable']->id);
        $result = $this->makeGenerationResult(hardViolationCount: 0, violations: []);

        $generator = Mockery::mock(TimetableGeneratorService::class);
        $loader = Mockery::mock(GenerationDataLoader::class);
        $integrity = Mockery::mock(TimetableIntegrityService::class);

        $integrity->shouldReceive('repairNonLocked')
            ->once()
            ->with($ctx['timetable']->id)
            ->andReturn([
                'unresolved_locked' => [[
                    'type' => 'double_misalignment',
                    'message' => 'Locked misaligned double exists.',
                    'locked_slot_ids' => [10, 11],
                ]],
            ]);

        $loader->shouldReceive('load')
            ->once()
            ->with($ctx['timetable'])
            ->andReturn($data);

        $generator->shouldReceive('validatePreConditions')
            ->once()
            ->with($data)
            ->andReturn([]);

        $generator->shouldReceive('generate')
            ->once()
            ->with($data, Mockery::type('callable'))
            ->andReturn($result);

        $generator->shouldReceive('persistSolution')
            ->once()
            ->with($ctx['timetable'], $result, $ctx['user']->id);

        $job->handle($generator, $loader, $integrity);

        $status = TimetableSetting::get("generation_status_{$ctx['timetable']->id}", []);
        $this->assertSame('completed', $status['status'] ?? null);
    }

    public function test_generation_with_hard_violations_persists_partial_and_marks_completed_with_conflicts(): void {
        $ctx = $this->createTimetableContext();
        $job = new GenerateTimetableJob($ctx['timetable'], $ctx['user']->id);
        $data = $this->makeGenerationData($ctx['timetable']->id);
        $result = new GenerationResult(
            chromosome: new Chromosome([]),
            generations: 10,
            fitness: 0.70,
            totalSlots: 20,
            hardViolationCount: 2,
            violationReport: ['Hard conflict remains.'],
            geneViolationMap: [1 => ['x'], 2 => ['y']],
            placedCount: 18,
            skippedCount: 2,
        );

        $generator = Mockery::mock(TimetableGeneratorService::class);
        $loader = Mockery::mock(GenerationDataLoader::class);
        $integrity = Mockery::mock(TimetableIntegrityService::class);

        $integrity->shouldReceive('repairNonLocked')
            ->once()
            ->with($ctx['timetable']->id)
            ->andReturn(['unresolved_locked' => []]);

        $loader->shouldReceive('load')
            ->once()
            ->with($ctx['timetable'])
            ->andReturn($data);

        $generator->shouldReceive('validatePreConditions')
            ->once()
            ->with($data)
            ->andReturn([]);

        $generator->shouldReceive('generate')
            ->once()
            ->with($data, Mockery::type('callable'))
            ->andReturn($result);

        $generator->shouldReceive('persistPartialSolution')
            ->once()
            ->with($ctx['timetable'], $result, $ctx['user']->id);
        $generator->shouldNotReceive('persistSolution');

        $job->handle($generator, $loader, $integrity);

        $status = TimetableSetting::get("generation_status_{$ctx['timetable']->id}", []);
        $this->assertSame('completed_with_conflicts', $status['status'] ?? null);
        $this->assertStringContainsString('slots placed successfully', (string) ($status['message'] ?? ''));
        $this->assertSame(['Hard conflict remains.'], $status['errors'] ?? []);
    }

    public function test_generation_with_feasible_solution_persists_and_completes(): void {
        $ctx = $this->createTimetableContext();
        $job = new GenerateTimetableJob($ctx['timetable'], $ctx['user']->id);
        $data = $this->makeGenerationData($ctx['timetable']->id);
        $result = $this->makeGenerationResult(hardViolationCount: 0, violations: []);

        $generator = Mockery::mock(TimetableGeneratorService::class);
        $loader = Mockery::mock(GenerationDataLoader::class);
        $integrity = Mockery::mock(TimetableIntegrityService::class);

        $integrity->shouldReceive('repairNonLocked')
            ->once()
            ->with($ctx['timetable']->id)
            ->andReturn(['unresolved_locked' => []]);

        $loader->shouldReceive('load')
            ->once()
            ->with($ctx['timetable'])
            ->andReturn($data);

        $generator->shouldReceive('validatePreConditions')
            ->once()
            ->with($data)
            ->andReturn([]);

        $generator->shouldReceive('generate')
            ->once()
            ->with($data, Mockery::type('callable'))
            ->andReturn($result);

        $generator->shouldReceive('persistSolution')
            ->once()
            ->with($ctx['timetable'], $result, $ctx['user']->id);

        $job->handle($generator, $loader, $integrity);

        $status = TimetableSetting::get("generation_status_{$ctx['timetable']->id}", []);
        $this->assertSame('completed', $status['status'] ?? null);
    }

    /**
     * @return array{user: User, timetable: Timetable}
     */
    private function createTimetableContext(): array {
        $user = User::factory()->create();
        $term = Term::query()->first();
        if (!$term) {
            $term = Term::create([
                'start_date' => now()->subDays(5)->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
                'term' => 1,
                'year' => (int) now()->format('Y'),
                'closed' => false,
                'extension_days' => 0,
            ]);
        }

        $timetable = Timetable::create([
            'term_id' => $term->id,
            'name' => 'GA Job Test Timetable',
            'status' => Timetable::STATUS_DRAFT,
            'created_by' => $user->id,
        ]);

        return ['user' => $user, 'timetable' => $timetable];
    }

    private function makeGenerationData(int $timetableId): GenerationData {
        return new GenerationData(
            timetableId: $timetableId,
            periodsPerDay: 8,
            cycleDays: 6,
            breakAfterPeriods: [4],
            klassSubjects: [],
            teacherUnavailability: [],
            teacherPreferences: [],
            subjectSpreads: [],
            consecutiveLimits: [],
            roomRequirements: [],
            couplingGroups: [],
            teacherAssignments: [],
            klassAssignments: [],
            teacherNames: [],
            klassNames: [],
            subjectNames: [],
            lockedSlots: [],
            optionalSubjectMap: [],
            validDoubleStartPeriods: [1, 3, 5, 7],
            venueNames: [],
            assistantTeacherAssignments: [],
            subjectPairs: [],
            periodRestrictions: [],
        );
    }

    private function makeGenerationResult(int $hardViolationCount, array $violations): GenerationResult {
        return new GenerationResult(
            chromosome: new Chromosome([]),
            generations: 10,
            fitness: 0.85,
            totalSlots: 0,
            hardViolationCount: $hardViolationCount,
            violationReport: $violations,
            geneViolationMap: [],
            placedCount: 0,
            skippedCount: 0,
        );
    }
}
