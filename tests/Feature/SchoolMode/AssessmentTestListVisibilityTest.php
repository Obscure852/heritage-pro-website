<?php

namespace Tests\Feature\SchoolMode;

use App\Models\SchoolSetup;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\Concerns\EnsuresPreF3SchoolModeSchema;
use Tests\TestCase;

class AssessmentTestListVisibilityTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPreF3SchoolModeSchema;

    protected function setUp(): void
    {
        parent::setUp();

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
            'type' => SchoolSetup::TYPE_PRE_F3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'firstname' => 'Academic',
            'lastname' => 'Admin',
            'email' => 'academic@example.com',
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'year' => 2026,
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('roles')->insert([
            'id' => 1,
            'name' => 'Academic Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('role_users')->insert([
            'user_id' => 1,
            'role_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('subjects')->insert([
            'id' => 1,
            'name' => 'Mathematics',
            'abbrev' => 'MATH',
            'level' => SchoolSetup::LEVEL_JUNIOR,
            'canonical_key' => 'mathematics',
            'components' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('subjects')->insert([
            'id' => 2,
            'name' => 'English',
            'abbrev' => 'ENG',
            'level' => SchoolSetup::LEVEL_SENIOR,
            'canonical_key' => 'english',
            'components' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session(['selected_term_id' => 1]);
        $this->actingAs(User::findOrFail(1));
        $this->withoutMiddleware();
    }

    public function test_pref3_assessments_page_shows_value_addition_mapping_tab(): void
    {
        $response = $this->get(route('assessment.test-list'));

        $response->assertOk()
            ->assertSee('href="#value-addition-mapping-junior"', false)
            ->assertSee('name="exam_type"', false)
            ->assertSee('value="PSLE"', false)
            ->assertSee('valueAdditionMappingForm-junior', false);
    }

    public function test_primary_assessments_page_hides_value_addition_mapping_tab(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_PRIMARY]);
        Cache::flush();

        $response = $this->get(route('assessment.test-list'));

        $response->assertOk()
            ->assertDontSee('href="#value-addition-mapping"', false)
            ->assertDontSee('valueAdditionMappingForm', false);
    }

    public function test_k12_assessments_page_shows_both_psle_and_jce_mapping_tabs(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_K12]);
        Cache::flush();

        $response = $this->get(route('assessment.test-list'));

        $response->assertOk()
            ->assertSee('href="#value-addition-mapping-junior"', false)
            ->assertSee('href="#value-addition-mapping-senior"', false)
            ->assertSee('value="PSLE"', false)
            ->assertSee('value="JCE"', false)
            ->assertSee('valueAdditionMappingForm-junior', false)
            ->assertSee('valueAdditionMappingForm-senior', false);
    }

    public function test_junior_senior_assessments_page_shows_both_mapping_tabs_without_criteria_tests(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_JUNIOR_SENIOR]);
        Cache::flush();

        $response = $this->get(route('assessment.test-list'));

        $response->assertOk()
            ->assertSee('href="#value-addition-mapping-junior"', false)
            ->assertSee('href="#value-addition-mapping-senior"', false)
            ->assertSee('value="PSLE"', false)
            ->assertSee('value="JCE"', false)
            ->assertSee('valueAdditionMappingForm-junior', false)
            ->assertSee('valueAdditionMappingForm-senior', false)
            ->assertDontSee('Criteria Based Tests');
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
