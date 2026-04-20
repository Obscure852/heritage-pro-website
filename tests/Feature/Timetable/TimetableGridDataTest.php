<?php

namespace Tests\Feature\Timetable;

use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\Subject;
use App\Models\Term;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableSetting;
use App\Models\Timetable\TimetableSlot;
use App\Models\User;
use App\Models\Venue;
use App\Services\Timetable\SlotManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TimetableGridDataTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_grid_data_includes_integrity_flags_for_cells(): void {
        $ctx = $this->createAcademicContext();

        TimetableSetting::set('period_definitions', [
            ['period' => 1, 'start_time' => '07:30', 'end_time' => '08:10', 'duration' => 40],
            ['period' => 2, 'start_time' => '08:10', 'end_time' => '08:50', 'duration' => 40],
            ['period' => 3, 'start_time' => '08:50', 'end_time' => '09:30', 'duration' => 40],
            ['period' => 4, 'start_time' => '09:30', 'end_time' => '10:10', 'duration' => 40],
            ['period' => 5, 'start_time' => '10:30', 'end_time' => '11:10', 'duration' => 40],
            ['period' => 6, 'start_time' => '11:10', 'end_time' => '11:50', 'duration' => 40],
            ['period' => 7, 'start_time' => '11:50', 'end_time' => '12:30', 'duration' => 40],
            ['period' => 8, 'start_time' => '12:30', 'end_time' => '13:10', 'duration' => 40],
        ]);
        TimetableSetting::set('break_intervals', [
            ['after_period' => 4, 'duration' => 20, 'label' => 'Tea Break'],
        ]);

        // Core/elective overlap + coupling split setup
        TimetableSlot::create([
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

        TimetableSlot::create([
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

        TimetableSlot::create([
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

        // Misaligned double at period 2 (valid starts with break-after-4 are 1,3,5,7)
        $blockId = Str::uuid()->toString();
        TimetableSlot::create([
            'timetable_id' => $ctx['timetable']->id,
            'klass_subject_id' => $ctx['klassSubject']->id,
            'optional_subject_id' => null,
            'teacher_id' => $ctx['coreTeacher']->id,
            'day_of_cycle' => 1,
            'period_number' => 2,
            'duration' => 2,
            'is_locked' => false,
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
            'is_locked' => false,
            'block_id' => $blockId,
        ]);

        /** @var SlotManagementService $slotService */
        $slotService = app(SlotManagementService::class);
        $grid = $slotService->getGridData($ctx['timetable']->id, $ctx['klass']->id);

        $this->assertTrue((bool) ($grid['1']['1']['has_core_elective_overlap'] ?? false));
        $this->assertTrue((bool) ($grid['1']['2']['has_double_alignment_issue'] ?? false));
        $this->assertTrue((bool) ($grid['1']['1']['has_coupling_split_issue'] ?? false));
        $this->assertIsArray($grid['1']['1']['issue_reasons'] ?? null);
        $this->assertNotEmpty($grid['1']['1']['issue_reasons'] ?? []);
    }

    /**
     * @return array{
     *   timetable: Timetable,
     *   klass: Klass,
     *   klassSubject: KlassSubject,
     *   optionalSubject: OptionalSubject,
     *   coreTeacher: User,
     *   optionalTeacher: User
     * }
     */
    private function createAcademicContext(): array {
        $coreTeacher = User::factory()->create();
        $optionalTeacher = User::factory()->create();
        $term = Term::query()->first() ?? Term::create([
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'term' => 1,
            'year' => (int) now()->format('Y'),
            'closed' => false,
            'extension_days' => 0,
        ]);

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
            'abbrev' => 'C' . random_int(100, 999),
            'name' => 'Core',
            'level' => 'Junior',
            'components' => false,
            'description' => '',
            'department' => 'General',
        ]);
        $optionalBaseSubject = Subject::create([
            'abbrev' => 'O' . random_int(100, 999),
            'name' => 'Optional',
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
            'name' => 'Grid Data Test Timetable',
            'status' => Timetable::STATUS_DRAFT,
            'created_by' => $coreTeacher->id,
        ]);

        return [
            'timetable' => $timetable,
            'klass' => $klass,
            'klassSubject' => $klassSubject,
            'optionalSubject' => $optionalSubject,
            'coreTeacher' => $coreTeacher,
            'optionalTeacher' => $optionalTeacher,
        ];
    }
}
