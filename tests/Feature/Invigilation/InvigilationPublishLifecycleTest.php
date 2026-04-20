<?php

namespace Tests\Feature\Invigilation;

use App\Http\Controllers\Invigilation\InvigilationController;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Role;
use App\Models\SchoolSetup;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\Venue;
use App\Models\Invigilation\InvigilationSeries;
use App\Models\Invigilation\InvigilationSession;
use App\Models\Invigilation\InvigilationSessionRoom;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\EnforceIdleTimeout;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\EnsuresInvigilationSchema;
use Tests\TestCase;

class InvigilationPublishLifecycleTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresInvigilationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureInvigilationSchema();
        $this->ensureRolesTables();
        $this->withoutMiddleware([Authenticate::class, EnforceIdleTimeout::class, AuthenticateSession::class, Authorize::class]);

        SchoolSetup::query()->updateOrCreate(
            ['id' => 1],
            [
                'school_name' => 'Merementsi Junior Secondary School',
                'type' => SchoolSetup::TYPE_JUNIOR,
            ]
        );
    }

    public function test_draft_series_page_shows_publish_action(): void
    {
        $admin = $this->createUserWithRoles('invigilation-draft-admin@example.com', ['Administrator']);
        [$term, $gradeSubject] = $this->createAcademicContext();
        $teacher = $this->createTeacher('Draft', 'Teacher');
        $series = $this->createSeries($term, InvigilationSeries::STATUS_DRAFT);

        $this->createAssignedRoom($series, $gradeSubject, $teacher, 'Draft Hall');

        $response = $this->actingAs($admin)->get(route('invigilation.show', $series));

        $response->assertOk()
            ->assertSee('Publish Series')
            ->assertDontSee('Unpublish Series');
    }

    public function test_published_series_page_shows_unpublish_action(): void
    {
        $admin = $this->createUserWithRoles('invigilation-published-admin@example.com', ['Administrator']);
        [$term, $gradeSubject] = $this->createAcademicContext();
        $teacher = $this->createTeacher('Published', 'Teacher');
        $series = $this->createSeries($term, InvigilationSeries::STATUS_PUBLISHED, $admin->id);

        $this->createAssignedRoom($series, $gradeSubject, $teacher, 'Published Hall');

        $response = $this->actingAs($admin)->get(route('invigilation.show', $series));

        $response->assertOk()
            ->assertSee('Unpublish Series')
            ->assertDontSee('Publish Series')
            ->assertDontSee('Add Session');
    }

    public function test_controller_guard_rejects_published_series_edits(): void
    {
        [$term] = $this->createAcademicContext();
        $series = $this->createSeries($term, InvigilationSeries::STATUS_PUBLISHED, 1);
        $controller = app(InvigilationController::class);

        try {
            (fn (InvigilationSeries $invigilationSeries) => $this->ensureSeriesEditable($invigilationSeries))
                ->call($controller, $series);

            $this->fail('Expected published-series edit guard to reject the mutation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('series', $exception->errors());
        }
    }

    public function test_controller_unpublish_returns_series_to_draft(): void
    {
        $admin = $this->createUserWithRoles('invigilation-unpublish-admin@example.com', ['Administrator']);
        [$term, $gradeSubject] = $this->createAcademicContext();
        $teacher = $this->createTeacher('Return', 'Teacher');
        $series = $this->createSeries($term, InvigilationSeries::STATUS_PUBLISHED, $admin->id);

        $this->createAssignedRoom($series, $gradeSubject, $teacher, 'Return Hall');

        $response = app(InvigilationController::class)->unpublish($series);

        $this->assertSame(route('invigilation.show', $series), $response->getTargetUrl());
        $this->assertDatabaseHas('invigilation_series', [
            'id' => $series->id,
            'status' => InvigilationSeries::STATUS_DRAFT,
            'published_by' => null,
        ]);
        $this->assertNull($series->fresh()->published_at);
    }

    public function test_unpublish_route_is_protected_by_manage_invigilation(): void
    {
        $route = app('router')->getRoutes()->getByName('invigilation.unpublish');

        $this->assertNotNull($route);
        $this->assertContains('can:manage-invigilation', $route->gatherMiddleware());
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

    private function createUserWithRoles(string $email, array $roles): User
    {
        $user = User::withoutEvents(fn () => User::query()->create([
            'firstname' => 'Invigilation',
            'lastname' => 'Admin',
            'email' => $email,
            'password' => 'secret',
            'status' => 'Current',
            'position' => 'Teacher',
            'area_of_work' => 'Teaching',
            'year' => 2026,
        ]));

        $roleIds = collect($roles)
            ->map(fn (string $name): int => (int) Role::query()->firstOrCreate(
                ['name' => $name],
                ['description' => $name]
            )->id)
            ->all();

        $user->roles()->syncWithoutDetaching($roleIds);

        return $user->fresh();
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

    private function createTeacher(string $firstname, string $lastname): User
    {
        return User::withoutEvents(fn () => User::query()->create([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => strtolower($firstname) . '.' . strtolower($lastname) . '.' . uniqid() . '@example.com',
            'password' => 'secret',
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'position' => 'Teacher',
            'year' => 2026,
        ]));
    }

    private function createSeries(Term $term, string $status, ?int $publishedBy = null): InvigilationSeries
    {
        return InvigilationSeries::query()->create([
            'name' => 'Lifecycle Series ' . uniqid(),
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

    private function createAssignedRoom(
        InvigilationSeries $series,
        GradeSubject $gradeSubject,
        User $teacher,
        string $venueName
    ): array {
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

        $assignment = $room->assignments()->create([
            'user_id' => $teacher->id,
            'assignment_order' => 1,
            'assignment_source' => 'manual',
            'locked' => true,
        ]);

        return [$session, $room, $assignment];
    }
}
