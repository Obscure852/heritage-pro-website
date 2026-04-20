<?php

namespace Tests\Unit\Timetable;

use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\Subject;
use App\Models\Term;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableSlot;
use App\Models\User;
use App\Models\Venue;
use App\Services\Timetable\PeriodSettingsService;
use App\Services\Timetable\TimetableIntegrityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TimetableIntegrityServiceTest extends TestCase {
    use RefreshDatabase;

    public function test_analyze_detects_core_elective_overlap_and_coupling_split(): void {
        $service = $this->makeIntegrityService();
        $ctx = $this->createAcademicContext();

        $coreSlot = TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => $ctx['klassSubject']->id,
            'optional_subject_id' => null,
            'teacher_id' => $ctx['coreTeacher']->id,
            'day_of_cycle' => 1,
            'period_number' => 1,
            'duration' => 1,
            'is_locked' => false,
            'block_id' => null,
        ]);

        $optionalA = TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => null,
            'optional_subject_id' => $ctx['optionalSubject']->id,
            'teacher_id' => $ctx['optionalTeacher']->id,
            'day_of_cycle' => 1,
            'period_number' => 1,
            'duration' => 1,
            'is_locked' => false,
            'block_id' => null,
            'coupling_group_key' => 'cg_test',
        ]);

        $optionalB = TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => null,
            'optional_subject_id' => $ctx['optionalSubject']->id,
            'teacher_id' => $ctx['optionalTeacher']->id,
            'day_of_cycle' => 2,
            'period_number' => 2,
            'duration' => 1,
            'is_locked' => false,
            'block_id' => null,
            'coupling_group_key' => 'cg_test',
        ]);

        $analysis = $service->analyze($ctx['timetable']->id);

        $this->assertGreaterThanOrEqual(1, $analysis['counts']['core_elective_overlap']);
        $this->assertGreaterThanOrEqual(1, $analysis['counts']['coupling_split']);
        $this->assertTrue((bool) ($analysis['slot_flags'][$coreSlot->id]['core_elective_overlap'] ?? false));
        $this->assertTrue((bool) ($analysis['slot_flags'][$optionalA->id]['coupling_split'] ?? false));
        $this->assertTrue((bool) ($analysis['slot_flags'][$optionalB->id]['coupling_split'] ?? false));
    }

    public function test_repair_non_locked_removes_optional_overlap_and_keeps_core(): void {
        $service = $this->makeIntegrityService();
        $ctx = $this->createAcademicContext();

        $coreSlot = TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => $ctx['klassSubject']->id,
            'optional_subject_id' => null,
            'teacher_id' => $ctx['coreTeacher']->id,
            'day_of_cycle' => 1,
            'period_number' => 1,
            'duration' => 1,
            'is_locked' => false,
            'block_id' => null,
        ]);

        $optionalSlot = TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => null,
            'optional_subject_id' => $ctx['optionalSubject']->id,
            'teacher_id' => $ctx['optionalTeacher']->id,
            'day_of_cycle' => 1,
            'period_number' => 1,
            'duration' => 1,
            'is_locked' => false,
            'block_id' => null,
            'coupling_group_key' => 'cg_test',
        ]);

        $repair = $service->repairNonLocked($ctx['timetable']->id);

        $this->assertSame(1, $repair['deleted_count']);
        $this->assertDatabaseHas('timetable_slots', ['id' => $coreSlot->id]);
        $this->assertDatabaseMissing('timetable_slots', ['id' => $optionalSlot->id]);
        $this->assertSame(0, $repair['after']['counts']['core_elective_overlap']);
    }

    public function test_repair_reports_locked_misaligned_double_as_unresolved(): void {
        $service = $this->makeIntegrityService();
        $ctx = $this->createAcademicContext();
        $blockId = Str::uuid()->toString();

        TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => $ctx['klassSubject']->id,
            'optional_subject_id' => null,
            'teacher_id' => $ctx['coreTeacher']->id,
            'day_of_cycle' => 1,
            'period_number' => 2,
            'duration' => 2,
            'is_locked' => true,
            'block_id' => $blockId,
        ]);
        TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => $ctx['klassSubject']->id,
            'optional_subject_id' => null,
            'teacher_id' => $ctx['coreTeacher']->id,
            'day_of_cycle' => 1,
            'period_number' => 3,
            'duration' => 2,
            'is_locked' => true,
            'block_id' => $blockId,
        ]);

        $repair = $service->repairNonLocked($ctx['timetable']->id);
        $types = collect($repair['unresolved_locked'])->pluck('type')->values()->all();

        $this->assertSame(0, $repair['deleted_count']);
        $this->assertContains('double_misalignment', $types);
    }

    private function makeIntegrityService(): TimetableIntegrityService {
        $periodSettings = $this->createMock(PeriodSettingsService::class);

        $periodDefinitions = [];
        for ($i = 1; $i <= 8; $i++) {
            $periodDefinitions[] = ['period' => $i];
        }

        $periodSettings->method('getPeriodDefinitions')->willReturn($periodDefinitions);
        $periodSettings->method('getBreakIntervals')->willReturn([
            ['after_period' => 4, 'label' => 'Tea Break'],
        ]);

        return new TimetableIntegrityService($periodSettings);
    }

    /**
     * @return array{
     *   timetable: Timetable,
     *   klassSubject: KlassSubject,
     *   optionalSubject: OptionalSubject,
     *   coreTeacher: User,
     *   optionalTeacher: User
     * }
     */
    private function createAcademicContext(): array {
        $coreTeacher = User::factory()->create();
        $optionalTeacher = User::factory()->create();

        $term = Term::query()->first();
        if (!$term) {
            $term = Term::create([
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->addDays(60)->toDateString(),
                'term' => 1,
                'year' => (int) now()->format('Y'),
                'closed' => false,
                'extension_days' => 0,
            ]);
        }

        $grade = Grade::create([
            'sequence' => 1,
            'name' => 'F1',
            'promotion' => 'F2',
            'description' => 'Form 1',
            'level' => 'Junior',
            'active' => true,
            'term_id' => $term->id,
            'year' => (int) $term->year,
        ]);

        $klass = Klass::create([
            'name' => '1A',
            'user_id' => $coreTeacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => (int) $term->year,
            'active' => true,
        ]);

        $departmentId = (int) (Department::query()->value('id') ?? 0);
        if ($departmentId <= 0) {
            $departmentId = (int) Department::create(['name' => 'Default'])->id;
        }

        $coreSubject = Subject::create([
            'abbrev' => 'CORE' . random_int(100, 999),
            'name' => 'Core Subject',
            'level' => 'Junior',
            'components' => false,
            'description' => '',
            'department' => 'General',
        ]);
        $optionalBaseSubject = Subject::create([
            'abbrev' => 'OPT' . random_int(100, 999),
            'name' => 'Optional Subject',
            'level' => 'Junior',
            'components' => false,
            'description' => '',
            'department' => 'General',
        ]);

        $coreGradeSubject = GradeSubject::create([
            'sequence' => 1,
            'grade_id' => $grade->id,
            'subject_id' => $coreSubject->id,
            'department_id' => $departmentId,
            'term_id' => $term->id,
            'year' => (int) $term->year,
            'type' => '1',
            'mandatory' => true,
            'active' => true,
        ]);
        $optionalGradeSubject = GradeSubject::create([
            'sequence' => 2,
            'grade_id' => $grade->id,
            'subject_id' => $optionalBaseSubject->id,
            'department_id' => $departmentId,
            'term_id' => $term->id,
            'year' => (int) $term->year,
            'type' => '0',
            'mandatory' => false,
            'active' => true,
        ]);

        $klassSubject = KlassSubject::create([
            'klass_id' => $klass->id,
            'grade_subject_id' => $coreGradeSubject->id,
            'user_id' => $coreTeacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => (int) $term->year,
            'active' => true,
        ]);

        $venueId = (int) (Venue::query()->value('id') ?? 0);
        if ($venueId <= 0) {
            $venueId = (int) Venue::create([
                'name' => 'V-' . random_int(10, 999),
                'type' => 'Classroom',
                'capacity' => 40,
            ])->id;
        }

        $optionalSubject = OptionalSubject::create([
            'name' => 'Optional Subject',
            'grade_subject_id' => $optionalGradeSubject->id,
            'user_id' => $optionalTeacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'grouping' => null,
            'venue_id' => $venueId,
            'active' => true,
        ]);

        $timetable = Timetable::create([
            'term_id' => $term->id,
            'name' => 'Integrity Test Timetable',
            'status' => Timetable::STATUS_DRAFT,
            'created_by' => $coreTeacher->id,
        ]);

        return [
            'timetable' => $timetable,
            'klassSubject' => $klassSubject,
            'optionalSubject' => $optionalSubject,
            'coreTeacher' => $coreTeacher,
            'optionalTeacher' => $optionalTeacher,
        ];
    }
}
