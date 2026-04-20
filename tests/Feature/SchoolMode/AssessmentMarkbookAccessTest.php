<?php

namespace Tests\Feature\SchoolMode;

use App\Http\Middleware\BlockNonAfricanCountries;
use App\Http\Middleware\EnsureProfileComplete;
use App\Models\SchoolSetup;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\EnsuresPreF3SchoolModeSchema;
use Tests\TestCase;

class AssessmentMarkbookAccessTest extends TestCase
{
    use EnsuresPreF3SchoolModeSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            BlockNonAfricanCountries::class,
            EnsureProfileComplete::class,
            ThrottleRequests::class,
        ]);

        $this->ensurePreF3SchoolModeSchema();
        $this->ensureRoleTables();
        $this->resetPreF3SchoolModeTables();
        $this->resetRoleTables();
        Cache::flush();

        DB::table('terms')->insert([
            'id' => 1,
            'term' => 1,
            'year' => 2026,
            'start_date' => '2026-01-10',
            'end_date' => '2026-04-10',
            'closed' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('school_setup')->insert([
            'id' => 1,
            'school_name' => 'Combined School',
            'type' => SchoolSetup::TYPE_K12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'firstname' => 'System',
            'lastname' => 'Owner',
            'email' => 'system.owner@example.com',
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'year' => 2026,
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session(['selected_term_id' => 1]);

        $this->seedAssessmentData();
    }

    public function test_k12_primary_teacher_only_sees_elementary_markbook_and_is_blocked_from_other_contexts(): void
    {
        $teacher = $this->createUser(2, 'Primary', 'Teacher');
        $this->assignRole($teacher->id, 'Teacher');
        $this->seedKlassSubject(1, 2, 1, $teacher->id);

        $this->actingAs($teacher);

        $html = view('layouts.sidebar')->render();

        $this->assertStringContainsString(route('assessment.markbook.primary'), $html);
        $this->assertStringNotContainsString(route('assessment.markbook.junior'), $html);
        $this->assertStringNotContainsString(route('assessment.markbook.senior'), $html);

        $this->get(route('assessment.markbook'))
            ->assertRedirect(route('assessment.markbook.primary'));

        $this->get(route('assessment.markbook.junior'))
            ->assertForbidden();
    }

    public function test_teacher_without_markbook_assignments_cannot_see_or_open_markbook(): void
    {
        $teacher = $this->createUser(3, 'Idle', 'Teacher');
        $this->assignRole($teacher->id, 'Teacher');

        $this->actingAs($teacher);

        $html = view('layouts.sidebar')->render();

        $this->assertStringNotContainsString(route('assessment.markbook.primary'), $html);
        $this->assertStringNotContainsString(route('assessment.markbook.junior'), $html);
        $this->assertStringNotContainsString(route('assessment.markbook.senior'), $html);

        $this->get(route('assessment.markbook'))
            ->assertForbidden();
    }

    public function test_supervisor_sees_union_of_supervisee_markbook_contexts_and_can_open_selector(): void
    {
        $supervisor = $this->createUser(10, 'Supervisor', 'User');
        $juniorTeacher = $this->createUser(11, 'Junior', 'Teacher', 10);
        $seniorTeacher = $this->createUser(12, 'Senior', 'Teacher', 10);

        $this->assignRole($juniorTeacher->id, 'Teacher');
        $this->assignRole($seniorTeacher->id, 'Teacher');

        $this->seedKlassSubject(2, 3, 2, $juniorTeacher->id);
        $this->seedOptionalSubject(1, 3, 4, 'Commerce A', $seniorTeacher->id);

        $this->actingAs($supervisor);

        $html = view('layouts.sidebar')->render();

        $this->assertStringNotContainsString(route('assessment.markbook.primary'), $html);
        $this->assertStringContainsString(route('assessment.markbook.junior'), $html);
        $this->assertStringContainsString(route('assessment.markbook.senior'), $html);

        $this->get(route('assessment.markbook'))
            ->assertOk()
            ->assertViewIs('assessment.shared.markbook-selector')
            ->assertSee('Middle School Markbook')
            ->assertSee('High School Markbook')
            ->assertDontSee('Elementary Markbook');
    }

    public function test_supervisor_markbook_loader_only_returns_supervisee_classes_for_requested_context(): void
    {
        $supervisor = $this->createUser(20, 'Supervisor', 'User');
        $juniorTeacher = $this->createUser(21, 'Junior', 'Teacher', 20);
        $seniorTeacher = $this->createUser(22, 'Senior', 'Teacher', 20);

        $this->assignRole($juniorTeacher->id, 'Teacher');
        $this->assignRole($seniorTeacher->id, 'Teacher');

        $this->seedKlassSubject(3, 3, 2, $juniorTeacher->id);
        $this->seedKlassSubject(4, 4, 3, $seniorTeacher->id);

        $this->actingAs($supervisor);

        $response = $this->getJson(route('assessment.fetch-classes', [
            'context' => 'junior',
        ]));

        $response->assertOk();

        $classNames = collect($response->json())->pluck('klass_name')->all();

        $this->assertSame(['1C'], $classNames);
        $this->assertNotContains('4A', $classNames);
    }

    public function test_single_mode_teacher_keeps_markbook_access_for_assigned_context(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_JUNIOR]);
        Cache::flush();

        $teacher = $this->createUser(30, 'Junior', 'Teacher');
        $this->assignRole($teacher->id, 'Teacher');
        $this->seedKlassSubject(5, 3, 2, $teacher->id);

        $this->actingAs($teacher);

        $this->get(route('assessment.markbook'))
            ->assertRedirect(route('assessment.markbook.junior'));
    }

    private function seedAssessmentData(): void
    {
        DB::table('departments')->insert([
            'id' => 1,
            'name' => 'Academics',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('grades')->insert([
            [
                'id' => 1,
                'sequence' => 1,
                'name' => 'REC',
                'promotion' => 'STD 1',
                'description' => 'Reception',
                'level' => SchoolSetup::LEVEL_PRE_PRIMARY,
                'active' => true,
                'term_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'sequence' => 2,
                'name' => 'STD 1',
                'promotion' => 'STD 2',
                'description' => 'Standard 1',
                'level' => SchoolSetup::LEVEL_PRIMARY,
                'active' => true,
                'term_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'sequence' => 8,
                'name' => 'F1',
                'promotion' => 'F2',
                'description' => 'Form 1',
                'level' => SchoolSetup::LEVEL_JUNIOR,
                'active' => true,
                'term_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'sequence' => 12,
                'name' => 'F4',
                'promotion' => 'F5',
                'description' => 'Form 4',
                'level' => SchoolSetup::LEVEL_SENIOR,
                'active' => true,
                'term_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('subjects')->insert([
            [
                'id' => 1,
                'name' => 'Mathematics',
                'abbrev' => 'MATH',
                'level' => SchoolSetup::LEVEL_PRIMARY,
                'components' => false,
                'canonical_key' => 'mathematics',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Mathematics',
                'abbrev' => 'MATH',
                'level' => SchoolSetup::LEVEL_JUNIOR,
                'components' => false,
                'canonical_key' => 'mathematics',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Commerce',
                'abbrev' => 'COMM',
                'level' => SchoolSetup::LEVEL_SENIOR,
                'components' => false,
                'canonical_key' => 'commerce',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('grade_subject')->insert([
            [
                'id' => 1,
                'grade_id' => 2,
                'subject_id' => 1,
                'term_id' => 1,
                'department_id' => 1,
                'type' => 1,
                'mandatory' => true,
                'sequence' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'grade_id' => 3,
                'subject_id' => 2,
                'term_id' => 1,
                'department_id' => 1,
                'type' => 1,
                'mandatory' => true,
                'sequence' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'grade_id' => 4,
                'subject_id' => 3,
                'term_id' => 1,
                'department_id' => 1,
                'type' => 1,
                'mandatory' => false,
                'sequence' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('klasses')->insert([
            [
                'id' => 1,
                'name' => 'REC A',
                'user_id' => 1,
                'term_id' => 1,
                'grade_id' => 1,
                'active' => true,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => '1A',
                'user_id' => 1,
                'term_id' => 1,
                'grade_id' => 2,
                'active' => true,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => '1C',
                'user_id' => 1,
                'term_id' => 1,
                'grade_id' => 3,
                'active' => true,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => '4A',
                'user_id' => 1,
                'term_id' => 1,
                'grade_id' => 4,
                'active' => true,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function seedKlassSubject(int $id, int $klassId, int $gradeSubjectId, int $userId, ?int $assistantUserId = null): void
    {
        DB::table('klass_subject')->insert([
            'id' => $id,
            'klass_id' => $klassId,
            'grade_subject_id' => $gradeSubjectId,
            'user_id' => $userId,
            'assistant_user_id' => $assistantUserId,
            'term_id' => 1,
            'grade_id' => DB::table('grade_subject')->where('id', $gradeSubjectId)->value('grade_id'),
            'year' => 2026,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedOptionalSubject(int $id, int $gradeSubjectId, int $gradeId, string $name, int $userId, ?int $assistantUserId = null): void
    {
        DB::table('optional_subjects')->insert([
            'id' => $id,
            'name' => $name,
            'grade_subject_id' => $gradeSubjectId,
            'grade_id' => $gradeId,
            'user_id' => $userId,
            'assistant_user_id' => $assistantUserId,
            'term_id' => 1,
            'grouping' => 'A',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUser(int $id, string $firstname, string $lastname, ?int $reportingTo = null): User
    {
        DB::table('users')->insert([
            'id' => $id,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => strtolower($firstname) . $id . '@example.com',
            'area_of_work' => 'Teaching',
            'reporting_to' => $reportingTo,
            'status' => 'Current',
            'year' => 2026,
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::findOrFail($id);
    }

    private function assignRole(int $userId, string $roleName): void
    {
        $roleId = DB::table('roles')->where('name', $roleName)->value('id');

        if (!$roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => $roleName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('role_users')->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureRoleTables(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_users')) {
            Schema::create('role_users', function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->timestamps();
            });
        }
    }

    private function resetRoleTables(): void
    {
        DB::table('role_users')->delete();
        DB::table('roles')->delete();
    }
}
