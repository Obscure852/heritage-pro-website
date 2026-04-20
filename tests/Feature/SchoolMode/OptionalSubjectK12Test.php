<?php

namespace Tests\Feature\SchoolMode;

use App\Http\Controllers\OptionalSubjectController;
use App\Models\SchoolSetup;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\EnsuresPreF3SchoolModeSchema;
use Tests\TestCase;

class OptionalSubjectK12Test extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPreF3SchoolModeSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        $this->ensurePreF3SchoolModeSchema();
        $this->ensureRoleTables();
        $this->ensureVenuesTable();
        $this->resetPreF3SchoolModeTables();
        $this->resetRoleTables();
        $this->resetVenueTables();
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
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => 'admin@example.com',
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'year' => 2026,
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('roles')->insert([
            'id' => 1,
            'name' => 'Administrator',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('role_users')->insert([
            'user_id' => 1,
            'role_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('grades')->insert([
            [
                'id' => 1,
                'sequence' => 7,
                'name' => 'STD 6',
                'promotion' => 'STD 7',
                'description' => 'Standard 6',
                'level' => SchoolSetup::LEVEL_PRIMARY,
                'active' => true,
                'term_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'sequence' => 10,
                'name' => 'F2',
                'promotion' => 'F3',
                'description' => 'Form 2',
                'level' => SchoolSetup::LEVEL_JUNIOR,
                'active' => true,
                'term_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'sequence' => 13,
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
            [
                'id' => 4,
                'sequence' => 14,
                'name' => 'F5',
                'promotion' => 'Alumni',
                'description' => 'Form 5',
                'level' => SchoolSetup::LEVEL_SENIOR,
                'active' => true,
                'term_id' => 1,
                'year' => 2026,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('departments')->insert([
            ['id' => 1, 'name' => 'Accounting', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Biology', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('subjects')->insert([
            [
                'id' => 1,
                'abbrev' => 'AGRI',
                'name' => 'Agriculture',
                'canonical_key' => 'agriculture',
                'level' => SchoolSetup::LEVEL_JUNIOR,
                'components' => false,
                'department' => 'Academics',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'abbrev' => 'ACC',
                'name' => 'Accounting',
                'canonical_key' => 'accounting',
                'level' => SchoolSetup::LEVEL_SENIOR,
                'components' => false,
                'department' => 'Accounting',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'abbrev' => 'BIO',
                'name' => 'Biology',
                'canonical_key' => 'biology',
                'level' => SchoolSetup::LEVEL_SENIOR,
                'components' => false,
                'department' => 'Biology',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('grade_subject')->insert([
            [
                'id' => 1,
                'sequence' => 1,
                'grade_id' => 2,
                'subject_id' => 1,
                'department_id' => 1,
                'term_id' => 1,
                'year' => 2026,
                'type' => 0,
                'mandatory' => false,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'sequence' => 1,
                'grade_id' => 3,
                'subject_id' => 2,
                'department_id' => 1,
                'term_id' => 1,
                'year' => 2026,
                'type' => 0,
                'mandatory' => false,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'sequence' => 2,
                'grade_id' => 4,
                'subject_id' => 3,
                'department_id' => 2,
                'term_id' => 1,
                'year' => 2026,
                'type' => 0,
                'mandatory' => false,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('venues')->insert([
            'id' => 1,
            'name' => 'Science Lab',
            'type' => 'Classroom',
            'capacity' => 40,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session(['selected_term_id' => 1]);
        $this->actingAs(User::findOrFail(1));
    }

    public function test_k12_optional_index_includes_junior_and_senior_grades_only(): void
    {
        $response = $this->get(route('optional.index'));

        $response->assertOk()->assertViewHas('grades', function ($grades) {
            $gradeNames = collect($grades)->pluck('name')->values()->all();

            return $gradeNames === ['F2', 'F4', 'F5'];
        });
    }

    public function test_k12_optional_grade_endpoint_returns_senior_grades_for_selected_term(): void
    {
        $response = $this->getJson(route('optional.grades-for-term', ['term_id' => 1]));

        $response->assertOk();

        $gradeNames = collect($response->json())->pluck('name')->values()->all();

        $this->assertSame(['F2', 'F4', 'F5'], $gradeNames);
    }

    public function test_k12_optional_create_view_exposes_junior_and_senior_groupings(): void
    {
        $view = app(OptionalSubjectController::class)->create();

        $this->assertSame('optional.add-new-option', $view->name());
        $this->assertArrayHasKey('groupingOptionsByLevel', $view->getData());
        $groupingOptionsByLevel = $view->getData()['groupingOptionsByLevel'];

        $this->assertTrue((function (array $groupingOptionsByLevel): bool {
            return ($groupingOptionsByLevel[SchoolSetup::LEVEL_JUNIOR] ?? null) === ['Core', 'Practicals', 'Generals', 'Other']
                && in_array('Accounting', $groupingOptionsByLevel[SchoolSetup::LEVEL_SENIOR] ?? [], true)
                && in_array('Biology', $groupingOptionsByLevel[SchoolSetup::LEVEL_SENIOR] ?? [], true)
                && in_array('Other', $groupingOptionsByLevel[SchoolSetup::LEVEL_SENIOR] ?? [], true);
        })($groupingOptionsByLevel));
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

    private function ensureVenuesTable(): void
    {
        if (!Schema::hasTable('venues')) {
            Schema::create('venues', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('type')->nullable();
                $table->integer('capacity')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    private function resetRoleTables(): void
    {
        DB::table('role_users')->delete();
        DB::table('roles')->delete();
    }

    private function resetVenueTables(): void
    {
        DB::table('venues')->delete();
    }
}
