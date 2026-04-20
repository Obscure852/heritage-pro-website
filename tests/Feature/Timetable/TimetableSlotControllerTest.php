<?php

namespace Tests\Feature\Timetable;

use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Klass;
use App\Models\OptionalSubject;
use App\Models\Subject;
use App\Models\Term;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableSetting;
use App\Models\Timetable\TimetableSlot;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimetableSlotControllerTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_check_conflicts_reports_invalid_double_alignment_start(): void {
        $teacher = User::factory()->create();
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
            'user_id' => $teacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => (int) $term->year,
            'active' => true,
        ]);

        $timetable = Timetable::create([
            'term_id' => $term->id,
            'name' => 'Draft Timetable',
            'status' => Timetable::STATUS_DRAFT,
            'created_by' => $teacher->id,
        ]);

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

        $response = $this->postJson(route('timetable.slots.check-conflicts'), [
            'timetable_id' => $timetable->id,
            'teacher_id' => $teacher->id,
            'klass_id' => $klass->id,
            'day_of_cycle' => 1,
            'period_number' => 2,
            'block_size' => 2,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'has_conflicts' => true,
            'warnings' => [],
        ]);
        $response->assertJsonPath('conflicts.0', 'Double period must start at one of: 1, 3, 5, 7. Period 2 is not a valid double start.');
    }

    public function test_check_conflicts_reports_coupling_split_for_optional_subject(): void {
        $teacher = User::factory()->create();
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
            'user_id' => $teacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => (int) $term->year,
            'active' => true,
        ]);

        $timetable = Timetable::create([
            'term_id' => $term->id,
            'name' => 'Draft Timetable',
            'status' => Timetable::STATUS_DRAFT,
            'created_by' => $teacher->id,
        ]);

        $departmentId = (int) (Department::query()->value('id') ?? 0);
        if ($departmentId <= 0) {
            $departmentId = (int) Department::create(['name' => 'Default'])->id;
        }

        $subject = Subject::create([
            'abbrev' => 'ART' . random_int(10, 99),
            'name' => 'Art',
            'level' => 'Junior',
            'components' => false,
            'description' => '',
            'department' => 'General',
        ]);
        $gradeSubject = GradeSubject::create([
            'sequence' => 1,
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'department_id' => $departmentId,
            'term_id' => $term->id,
            'year' => (int) $term->year,
            'type' => '0',
            'mandatory' => false,
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
            'name' => 'Art Optional',
            'grade_subject_id' => $gradeSubject->id,
            'user_id' => $optionalTeacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'grouping' => null,
            'venue_id' => $venueId,
            'active' => true,
        ]);

        TimetableSlot::create([
            'timetable_id' => $timetable->id,
            'klass_subject_id' => null,
            'optional_subject_id' => $optionalSubject->id,
            'teacher_id' => $optionalTeacher->id,
            'day_of_cycle' => 1,
            'period_number' => 1,
            'duration' => 1,
            'is_locked' => false,
            'block_id' => null,
            'coupling_group_key' => 'cg_f1_art_s0',
        ]);

        $response = $this->postJson(route('timetable.slots.check-conflicts'), [
            'timetable_id' => $timetable->id,
            'optional_subject_id' => $optionalSubject->id,
            'teacher_id' => $optionalTeacher->id,
            'klass_id' => $klass->id,
            'day_of_cycle' => 2,
            'period_number' => 2,
            'block_size' => 1,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'has_conflicts' => true,
            'warnings' => [],
        ]);
        $this->assertStringContainsString(
            'must remain aligned at Day 1, Period 1',
            (string) $response->json('conflicts.0')
        );
    }
}
