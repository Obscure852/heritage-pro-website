<?php

namespace Tests\Feature\Invigilation;

use App\Http\Middleware\BlockNonAfricanCountries;
use App\Http\Middleware\EnforceIdleTimeout;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Role;
use App\Models\SchoolSetup;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\Venue;
use App\Models\Invigilation\InvigilationSeries;
use App\Models\Invigilation\InvigilationSessionRoom;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Session\Middleware\AuthenticateSession;
use Tests\Concerns\EnsuresInvigilationSchema;
use Tests\TestCase;

class InvigilationPublishedTeacherRosterTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresInvigilationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureInvigilationSchema();
        $this->ensureRolesTables();

        SchoolSetup::query()->updateOrCreate(
            ['id' => 1],
            [
                'school_name' => 'Merementsi Junior Secondary School',
                'type' => SchoolSetup::TYPE_JUNIOR,
            ]
        );

        $this->withoutMiddleware([EnforceIdleTimeout::class, AuthenticateSession::class, BlockNonAfricanCountries::class]);
    }

    public function test_teacher_can_open_published_teacher_roster_page(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext(1, 2026);
        $teacher = $this->createUserWithRoles('published-roster-teacher@example.com', ['Teacher']);
        $assignedTeacher = $this->createStaffUser('Alpha', 'Teacher');
        $series = $this->createSeries($term, 'Published Series', InvigilationSeries::STATUS_PUBLISHED, $teacher->id);

        $this->createAssignedRoom($series, $gradeSubject, $assignedTeacher, 'Hall A');

        $response = $this->actingAs($teacher)->get(route('invigilation.view.teacher-roster'));

        $response->assertOk()
            ->assertSee('Published Teacher Roster')
            ->assertSee('Teacher Timetable')
            ->assertSee('Published Series')
            ->assertDontSee('CSV')
            ->assertDontSee('Series Manager')
            ->assertDontSee('Daily Roster')
            ->assertDontSee('Room Roster')
            ->assertDontSee('Conflict Report')
            ->assertDontSee(route('invigilation.settings.index'));
    }

    public function test_class_teacher_can_open_published_teacher_roster_page(): void
    {
        $classTeacher = $this->createUserWithRoles('published-roster-class-teacher@example.com', ['Class Teacher']);

        $response = $this->actingAs($classTeacher)->get(route('invigilation.view.teacher-roster'));

        $response->assertOk()
            ->assertSee('No published invigilation roster is available yet.');
    }

    public function test_teacher_cannot_access_admin_invigilation_workspace(): void
    {
        [$term] = $this->createAcademicContext(1, 2026);
        $teacher = $this->createUserWithRoles('published-roster-no-admin@example.com', ['Teacher']);
        $series = $this->createSeries($term, 'Draft Workspace', InvigilationSeries::STATUS_DRAFT);

        $this->actingAs($teacher)
            ->get(route('invigilation.index'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('invigilation.show', $series))
            ->assertForbidden();
    }

    public function test_admin_can_access_both_teacher_roster_view_and_admin_workspace(): void
    {
        [$term] = $this->createAcademicContext(1, 2026);
        $admin = $this->createUserWithRoles('published-roster-admin@example.com', ['Administrator']);
        $series = $this->createSeries($term, 'Admin Workspace', InvigilationSeries::STATUS_DRAFT);

        $this->actingAs($admin)
            ->get(route('invigilation.view.teacher-roster'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('invigilation.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('invigilation.show', $series))
            ->assertOk();
    }

    public function test_staff_page_defaults_to_latest_published_series_and_hides_draft_series_from_selector(): void
    {
        [$term2025] = $this->createAcademicContext(1, 2025);
        [$term2026] = $this->createAcademicContext(2, 2026);
        $teacher = $this->createUserWithRoles('published-roster-default@example.com', ['Teacher']);

        $olderSeries = $this->createSeries($term2025, 'Older Published Series', InvigilationSeries::STATUS_PUBLISHED);
        $latestSeries = $this->createSeries($term2026, 'Latest Published Series', InvigilationSeries::STATUS_PUBLISHED);
        $draftSeries = $this->createSeries($term2026, 'Draft Hidden Series', InvigilationSeries::STATUS_DRAFT);

        $response = $this->actingAs($teacher)->get(route('invigilation.view.teacher-roster'));

        $response->assertOk()
            ->assertSee('Latest Published Series')
            ->assertSee('Older Published Series')
            ->assertDontSee('Draft Hidden Series')
            ->assertSee('value="' . $latestSeries->id . '" selected', false);

        $this->assertNotSame($olderSeries->id, $latestSeries->id);
        $this->assertNotSame($draftSeries->id, $latestSeries->id);
    }

    public function test_staff_page_rejects_unpublished_series_direct_access(): void
    {
        [$term] = $this->createAcademicContext(1, 2026);
        $teacher = $this->createUserWithRoles('published-roster-redirect@example.com', ['Teacher']);
        $series = $this->createSeries($term, 'Draft Redirect Series', InvigilationSeries::STATUS_DRAFT);

        $this->actingAs($teacher)
            ->get(route('invigilation.view.teacher-roster', ['series_id' => $series->id]))
            ->assertRedirect(route('invigilation.view.teacher-roster'));

        $this->followRedirects($this->actingAs($teacher)->get(route('invigilation.view.teacher-roster', ['series_id' => $series->id])))
            ->assertSee('Only published invigilation series are available on this staff page.');
    }

    public function test_staff_page_renders_table_layout_and_print_view(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext(1, 2026);
        $teacher = $this->createUserWithRoles('published-roster-layout@example.com', ['Teacher']);
        $assignedTeacher = $this->createStaffUser('Bravo', 'Teacher');
        $series = $this->createSeries($term, 'Layout Series', InvigilationSeries::STATUS_PUBLISHED, $teacher->id);

        $this->createAssignedRoom($series, $gradeSubject, $assignedTeacher, 'Hall B');

        $this->actingAs($teacher)
            ->get(route('invigilation.view.teacher-roster', ['series_id' => $series->id, 'layout' => 'table']))
            ->assertOk()
            ->assertSee('duty slot(s)')
            ->assertSee('Flags')
            ->assertSee('Venue')
            ->assertDontSee('Teacher Timetable');

        $this->actingAs($teacher)
            ->get(route('invigilation.view.teacher-roster', [
                'series_id' => $series->id,
                'layout' => 'timetable',
                'print' => 1,
            ]))
            ->assertOk()
            ->assertSee('window.print()', false)
            ->assertSee('@page', false)
            ->assertSee('size: landscape', false);
    }

    public function test_unpublishing_series_removes_it_from_staff_page(): void
    {
        [$term] = $this->createAcademicContext(1, 2026);
        $teacher = $this->createUserWithRoles('published-roster-unpublish@example.com', ['Teacher']);
        $series = $this->createSeries($term, 'Published Then Draft', InvigilationSeries::STATUS_PUBLISHED);

        $this->actingAs($teacher)
            ->get(route('invigilation.view.teacher-roster'))
            ->assertOk()
            ->assertSee('Published Then Draft');

        $series->update([
            'status' => InvigilationSeries::STATUS_DRAFT,
            'published_at' => null,
            'published_by' => null,
        ]);

        $this->actingAs($teacher)
            ->get(route('invigilation.view.teacher-roster'))
            ->assertOk()
            ->assertDontSee('Published Then Draft')
            ->assertSee('No published invigilation roster is available yet.');
    }

    public function test_teacher_roster_route_uses_published_roster_gate(): void
    {
        $route = app('router')->getRoutes()->getByName('invigilation.view.teacher-roster');

        $this->assertNotNull($route);
        $this->assertContains('can:access-invigilation-published-roster', $route->gatherMiddleware());
    }

    private function ensureRolesTables(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_users')) {
            Schema::create('role_users', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->timestamps();
            });
        }
    }

    private function createUserWithRoles(string $email, array $roles, array $overrides = []): User
    {
        $user = User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'Published',
            'lastname' => 'Viewer',
            'email' => $email,
            'password' => 'secret',
            'status' => 'Current',
            'position' => 'Teacher',
            'area_of_work' => 'Teaching',
            'year' => 2026,
        ], $overrides)));

        $roleIds = collect($roles)
            ->map(fn (string $name): int => (int) Role::query()->firstOrCreate(
                ['name' => $name],
                ['description' => $name]
            )->id)
            ->all();

        $user->roles()->syncWithoutDetaching($roleIds);

        return $user->fresh();
    }

    private function createAcademicContext(int $termNumber, int $year): array
    {
        $term = Term::query()->updateOrCreate(
            ['term' => $termNumber, 'year' => $year],
            [
                'start_date' => sprintf('%d-01-01', $year),
                'end_date' => sprintf('%d-12-31', $year),
                'closed' => false,
            ]
        );

        $grade = Grade::query()->create([
            'name' => 'Form ' . $termNumber . ' ' . uniqid(),
            'sequence' => $termNumber,
            'promotion' => 'Form ' . ($termNumber + 1),
            'description' => 'Junior grade',
            'level' => SchoolSetup::LEVEL_JUNIOR,
            'active' => true,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Mathematics ' . uniqid(),
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

    private function createSeries(
        Term $term,
        string $name,
        string $status,
        ?int $publishedBy = null
    ): InvigilationSeries {
        return InvigilationSeries::query()->create([
            'name' => $name,
            'type' => InvigilationSeries::TYPE_MOCK,
            'term_id' => $term->id,
            'status' => $status,
            'eligibility_policy' => InvigilationSeries::POLICY_ANY_TEACHER,
            'timetable_conflict_policy' => InvigilationSeries::TIMETABLE_IGNORE,
            'balancing_policy' => 'balanced',
            'default_required_invigilators' => 1,
            'published_at' => $status === InvigilationSeries::STATUS_PUBLISHED ? now() : null,
            'published_by' => $status === InvigilationSeries::STATUS_PUBLISHED ? $publishedBy : null,
        ]);
    }

    private function createStaffUser(string $firstname, string $lastname): User
    {
        return User::withoutEvents(fn () => User::query()->create([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => strtolower($firstname) . '.' . strtolower($lastname) . '.' . uniqid() . '@example.com',
            'password' => 'secret',
            'status' => 'Current',
            'position' => 'Teacher',
            'area_of_work' => 'Teaching',
            'year' => 2026,
        ]));
    }

    private function createAssignedRoom(
        InvigilationSeries $series,
        GradeSubject $gradeSubject,
        User $teacher,
        string $venueName
    ): InvigilationSessionRoom {
        $venue = Venue::query()->create([
            'name' => $venueName,
            'type' => 'Hall',
            'capacity' => 40,
        ]);

        $session = $series->sessions()->create([
            'grade_subject_id' => $gradeSubject->id,
            'paper_label' => 'Paper 1',
            'exam_date' => '2026-05-16',
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $room = $session->rooms()->create([
            'venue_id' => $venue->id,
            'source_type' => InvigilationSessionRoom::SOURCE_MANUAL,
            'group_label' => 'Manual Group',
            'candidate_count' => 20,
            'required_invigilators' => 1,
        ]);

        $room->assignments()->create([
            'user_id' => $teacher->id,
            'assignment_order' => 1,
            'assignment_source' => 'manual',
            'locked' => true,
        ]);

        return $room;
    }
}
