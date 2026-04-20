<?php

namespace Tests\Feature\Invigilation;

use App\Http\Middleware\EnforceIdleTimeout;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\SchoolSetup;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\Venue;
use App\Models\Invigilation\InvigilationSeries;
use App\Models\Invigilation\InvigilationSessionRoom;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Session\Middleware\AuthenticateSession;
use Tests\Concerns\EnsuresInvigilationSchema;
use Tests\TestCase;

class InvigilationTeacherReportViewTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresInvigilationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureInvigilationSchema();

        SchoolSetup::query()->updateOrCreate(
            ['id' => 1],
            [
                'school_name' => 'Merementsi Junior Secondary School',
                'type' => SchoolSetup::TYPE_JUNIOR,
            ]
        );

        $this->withoutMiddleware([EnforceIdleTimeout::class, AuthenticateSession::class, Authorize::class]);
    }

    public function test_teacher_duties_defaults_to_timetable_layout(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext();
        $series = $this->createSeries($term);
        $user = $this->createUser('viewer');
        $teacherAlpha = $this->createUser('teacher-alpha');
        $teacherBravo = $this->createUser('teacher-bravo');

        $this->createAssignedRoom($series, $gradeSubject, $this->createVenue('Hall A'), '2026-05-10', '08:00', '09:00', 'Group A', $teacherAlpha, true, 'manual');
        $this->createAssignedRoom($series, $gradeSubject, $this->createVenue('Hall B'), '2026-05-10', '08:00', '09:00', 'Group B', $teacherBravo, false, 'auto');
        $this->createAssignedRoom($series, $gradeSubject, $this->createVenue('Hall C'), '2026-05-11', '10:00', '11:00', 'Group C', $teacherAlpha, true, 'manual');

        $response = $this->actingAs($user)->get(route('invigilation.reports.teacher.index', ['series_id' => $series->id]));

        $response->assertOk()
            ->assertSee('Teacher Timetable')
            ->assertSee('Teacher')
            ->assertSee('No session')
            ->assertSee('teacher-alpha')
            ->assertSee('teacher-bravo')
            ->assertDontSee('Time Slot')
            ->assertSee('name="layout"', false)
            ->assertSee('value="timetable"', false)
            ->assertSee(route('invigilation.reports.teacher.index', [
                'series_id' => $series->id,
                'layout' => 'timetable',
                'print' => 1,
            ]));
    }

    public function test_teacher_duties_table_layout_renders_grouped_table_and_preserves_layout_selector(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext();
        $series = $this->createSeries($term);
        $user = $this->createUser('viewer');
        $teacher = $this->createUser('teacher-alpha');

        $this->createAssignedRoom($series, $gradeSubject, $this->createVenue('Hall A'), '2026-05-10', '08:00', '09:00', 'Group A', $teacher, true, 'manual');

        $response = $this->actingAs($user)->get(route('invigilation.reports.teacher.index', ['series_id' => $series->id, 'layout' => 'table']));

        $response->assertOk()
            ->assertSee('duty slot(s)')
            ->assertSee('Flags')
            ->assertSee('Venue')
            ->assertSee('name="layout"', false)
            ->assertSee('value="table"', false)
            ->assertDontSee('Teacher Timetable');
    }

    public function test_teacher_duties_table_print_view_includes_auto_print_script(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext();
        $series = $this->createSeries($term);
        $user = $this->createUser('viewer');
        $teacher = $this->createUser('teacher-alpha');

        $this->createAssignedRoom($series, $gradeSubject, $this->createVenue('Hall A'), '2026-05-10', '08:00', '09:00', 'Group A', $teacher, true, 'manual');

        $response = $this->actingAs($user)->get(route('invigilation.reports.teacher.index', [
            'series_id' => $series->id,
            'layout' => 'table',
            'print' => 1,
        ]));

        $response->assertOk()
            ->assertSee('window.print()', false);
    }

    public function test_teacher_duties_timetable_print_view_includes_landscape_print_css_and_auto_print_script(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext();
        $series = $this->createSeries($term);
        $user = $this->createUser('viewer');
        $teacher = $this->createUser('teacher-alpha');

        $this->createAssignedRoom($series, $gradeSubject, $this->createVenue('Hall A'), '2026-05-10', '08:00', '09:00', 'Group A', $teacher, true, 'manual');

        $response = $this->actingAs($user)->get(route('invigilation.reports.teacher.index', [
            'series_id' => $series->id,
            'layout' => 'timetable',
            'print' => 1,
        ]));

        $response->assertOk()
            ->assertSee('window.print()', false)
            ->assertSee('@page', false)
            ->assertSee('size: landscape', false);
    }

    private function createAcademicContext(): array
    {
        $term = Term::query()->updateOrCreate(
            ['term' => 1, 'year' => 2026],
            [
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'closed' => false,
            ]
        );

        $grade = Grade::query()->create([
            'name' => 'Form 1',
            'sequence' => 1,
            'promotion' => 'Form 2',
            'description' => 'Junior grade',
            'level' => SchoolSetup::LEVEL_JUNIOR,
            'active' => true,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Mathematics',
            'abbrev' => 'MATH',
            'canonical_key' => 'math_' . uniqid(),
            'level' => SchoolSetup::LEVEL_JUNIOR,
            'components' => false,
        ]);

        $departmentId = DB::table('departments')->where('name', 'Academic')->value('id');
        if (!$departmentId) {
            $departmentId = DB::table('departments')->insertGetId([
                'name' => 'Academic',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('grade_subject')->updateOrInsert(
            [
                'term_id' => $term->id,
                'grade_id' => $grade->id,
                'subject_id' => $subject->id,
            ],
            [
                'department_id' => $departmentId,
                'year' => $term->year,
                'sequence' => 1,
                'type' => 'core',
                'mandatory' => true,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]
        );

        $gradeSubject = GradeSubject::query()
            ->where('term_id', $term->id)
            ->where('grade_id', $grade->id)
            ->where('subject_id', $subject->id)
            ->firstOrFail();

        return [$term, $gradeSubject];
    }

    private function createSeries(Term $term): InvigilationSeries
    {
        return InvigilationSeries::query()->create([
            'name' => 'Teacher Series ' . uniqid(),
            'type' => InvigilationSeries::TYPE_MOCK,
            'term_id' => $term->id,
            'status' => InvigilationSeries::STATUS_DRAFT,
            'eligibility_policy' => InvigilationSeries::POLICY_ANY_TEACHER,
            'timetable_conflict_policy' => InvigilationSeries::TIMETABLE_IGNORE,
            'balancing_policy' => 'balanced',
            'default_required_invigilators' => 1,
        ]);
    }

    private function createUser(string $token): User
    {
        return User::withoutEvents(fn () => User::query()->create([
            'firstname' => 'Invigilation',
            'lastname' => $token,
            'email' => 'teacher.report.' . $token . '.' . uniqid() . '@example.com',
            'password' => 'secret',
            'status' => 'Current',
            'position' => 'Teacher',
            'area_of_work' => 'Teaching',
            'year' => 2026,
        ]));
    }

    private function createVenue(string $name): Venue
    {
        return Venue::query()->create([
            'name' => $name,
            'type' => 'Hall',
            'capacity' => 40,
        ]);
    }

    private function createAssignedRoom(
        InvigilationSeries $series,
        GradeSubject $gradeSubject,
        Venue $venue,
        string $examDate,
        string $startTime,
        string $endTime,
        string $groupLabel,
        User $teacher,
        bool $locked,
        string $source
    ): InvigilationSessionRoom {
        $session = $series->sessions()->create([
            'grade_subject_id' => $gradeSubject->id,
            'paper_label' => 'Paper 1',
            'exam_date' => $examDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $room = $session->rooms()->create([
            'venue_id' => $venue->id,
            'source_type' => InvigilationSessionRoom::SOURCE_MANUAL,
            'group_label' => $groupLabel,
            'candidate_count' => 20,
            'required_invigilators' => 1,
        ]);

        $room->assignments()->create([
            'user_id' => $teacher->id,
            'assignment_order' => 1,
            'assignment_source' => $source,
            'locked' => $locked,
        ]);

        return $room;
    }
}
