<?php

namespace Tests\Feature\SchoolMode;

use App\Models\SchoolSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\Concerns\EnsuresPreF3SchoolModeSchema;
use Tests\TestCase;

class AssessmentGradebookNavigationTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPreF3SchoolModeSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
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

        session(['selected_term_id' => 1]);
        $this->actingAs(\App\Models\User::findOrFail(1));
    }

    public function test_pref3_generic_gradebook_route_renders_selector_page(): void
    {
        $response = $this->get(route('assessment.index'));

        $response->assertOk()
            ->assertViewIs('assessment.shared.gradebook-selector')
            ->assertSee('Elementary Gradebook')
            ->assertSee('Middle School Gradebook');
    }

    public function test_pref3_generic_markbook_route_renders_selector_page(): void
    {
        $response = $this->get(route('assessment.markbook'));

        $response->assertOk()
            ->assertViewIs('assessment.shared.markbook-selector')
            ->assertSee('Elementary Markbook')
            ->assertSee('Middle School Markbook');
    }

    public function test_k12_generic_gradebook_route_renders_selector_page_with_senior(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_K12]);
        Cache::flush();

        $response = $this->get(route('assessment.index'));

        $response->assertOk()
            ->assertViewIs('assessment.shared.gradebook-selector')
            ->assertSee('High School Gradebook');
    }

    public function test_k12_generic_markbook_route_renders_selector_page_with_senior(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_K12]);
        Cache::flush();

        $response = $this->get(route('assessment.markbook'));

        $response->assertOk()
            ->assertViewIs('assessment.shared.markbook-selector')
            ->assertSee('High School Markbook');
    }

    public function test_junior_senior_generic_gradebook_route_renders_middle_and_high_selector_only(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_JUNIOR_SENIOR]);
        Cache::flush();

        $response = $this->get(route('assessment.index'));

        $response->assertOk()
            ->assertViewIs('assessment.shared.gradebook-selector')
            ->assertSee('Middle School Gradebook')
            ->assertSee('High School Gradebook')
            ->assertDontSee('Elementary Gradebook');
    }

    public function test_pref3_primary_gradebook_route_uses_primary_index_view(): void
    {
        $this->seedAssessmentGradesAndClasses();

        $response = $this->withViewErrors([])->get(route('assessment.gradebook.primary'));

        $response->assertOk()
            ->assertViewIs('assessment.primary.primary-index')
            ->assertViewHas('assessmentContext', 'primary');
    }

    public function test_pref3_primary_markbook_route_uses_primary_markbook_view(): void
    {
        $this->seedAssessmentGradesAndClasses();
        $this->seedMarkbookSubjects();

        $response = $this->withViewErrors([])->get(route('assessment.markbook.primary'));

        $response->assertOk()
            ->assertViewIs('assessment.primary.markbook-primary')
            ->assertViewHas('assessmentContext', 'primary');
    }

    public function test_pref3_primary_class_loader_returns_only_primary_levels(): void
    {
        $this->seedAssessmentGradesAndClasses();

        $response = $this->getJson(route('assessment.klasses-for-term', [
            'term_id' => 1,
            'context' => 'primary',
        ]));

        $response->assertOk();

        $classNames = collect($response->json())->pluck('name')->all();

        $this->assertSame(['1A', 'REC A'], $classNames);
        $this->assertNotContains('1C', $classNames);
    }

    public function test_pref3_junior_class_loader_returns_only_junior_levels(): void
    {
        $this->seedAssessmentGradesAndClasses();

        $response = $this->getJson(route('assessment.klasses-for-term', [
            'term_id' => 1,
            'context' => 'junior',
        ]));

        $response->assertOk();

        $classNames = collect($response->json())->pluck('name')->all();

        $this->assertSame(['1C'], $classNames);
        $this->assertNotContains('REC A', $classNames);
        $this->assertNotContains('1A', $classNames);
    }

    public function test_pref3_primary_markbook_loader_returns_only_primary_levels(): void
    {
        $this->seedAssessmentGradesAndClasses();
        $this->seedMarkbookSubjects();

        $response = $this->getJson(route('assessment.fetch-classes', [
            'context' => 'primary',
        ]));

        $response->assertOk();

        $classNames = collect($response->json())->pluck('klass_name')->all();

        $this->assertSame(['1A'], $classNames);
        $this->assertNotContains('1C', $classNames);
    }

    public function test_pref3_junior_markbook_loader_returns_only_junior_levels(): void
    {
        $this->seedAssessmentGradesAndClasses();
        $this->seedMarkbookSubjects();

        $response = $this->getJson(route('assessment.fetch-classes', [
            'context' => 'junior',
        ]));

        $response->assertOk();

        $classNames = collect($response->json())->pluck('klass_name')->all();

        $this->assertSame(['1C'], $classNames);
        $this->assertNotContains('1A', $classNames);
    }

    public function test_k12_senior_class_loader_returns_only_senior_levels(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_K12]);
        Cache::flush();
        $this->seedAssessmentGradesAndClasses();

        $response = $this->getJson(route('assessment.klasses-for-term', [
            'term_id' => 1,
            'context' => 'senior',
        ]));

        $response->assertOk();

        $classNames = collect($response->json())->pluck('name')->all();

        $this->assertSame(['4A'], $classNames);
        $this->assertNotContains('REC A', $classNames);
        $this->assertNotContains('1A', $classNames);
        $this->assertNotContains('1C', $classNames);
    }

    public function test_k12_senior_gradebook_route_uses_senior_index_view(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_K12]);
        Cache::flush();
        $this->seedAssessmentGradesAndClasses();

        $response = $this->withViewErrors([])->get(route('assessment.gradebook.senior'));

        $response->assertOk()
            ->assertViewIs('assessment.senior.senior-index')
            ->assertViewHas('assessmentContext', 'senior');
    }

    public function test_k12_senior_class_loader_uses_gradebook_session_context_when_query_context_is_missing(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_K12]);
        Cache::flush();
        $this->seedAssessmentGradesAndClasses();
        session(['assessment_gradebook_context' => 'senior']);

        $response = $this->getJson(route('assessment.klasses-for-term', [
            'term_id' => 1,
        ]));

        $response->assertOk();

        $classNames = collect($response->json())->pluck('name')->all();

        $this->assertSame(['4A'], $classNames);
        $this->assertNotContains('REC A', $classNames);
        $this->assertNotContains('1A', $classNames);
        $this->assertNotContains('1C', $classNames);
    }

    public function test_k12_senior_markbook_loader_returns_only_senior_levels(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_K12]);
        Cache::flush();
        $this->seedAssessmentGradesAndClasses();
        $this->seedMarkbookSubjects();

        $response = $this->getJson(route('assessment.fetch-classes', [
            'context' => 'senior',
        ]));

        $response->assertOk();

        $classNames = collect($response->json())->pluck('klass_name')->all();

        $this->assertSame(['4A'], $classNames);
        $this->assertNotContains('1A', $classNames);
        $this->assertNotContains('1C', $classNames);
    }

    public function test_junior_senior_junior_class_loader_returns_only_f1_to_f3_levels(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_JUNIOR_SENIOR]);
        Cache::flush();
        $this->seedAssessmentGradesAndClasses();

        $response = $this->getJson(route('assessment.klasses-for-term', [
            'term_id' => 1,
            'context' => 'junior',
        ]));

        $response->assertOk();

        $classNames = collect($response->json())->pluck('name')->all();

        $this->assertSame(['1C'], $classNames);
        $this->assertNotContains('REC A', $classNames);
        $this->assertNotContains('1A', $classNames);
        $this->assertNotContains('4A', $classNames);
    }

    public function test_junior_senior_senior_class_loader_returns_only_f4_to_f5_levels(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_JUNIOR_SENIOR]);
        Cache::flush();
        $this->seedAssessmentGradesAndClasses();

        $response = $this->getJson(route('assessment.klasses-for-term', [
            'term_id' => 1,
            'context' => 'senior',
        ]));

        $response->assertOk();

        $classNames = collect($response->json())->pluck('name')->all();

        $this->assertSame(['4A'], $classNames);
        $this->assertNotContains('REC A', $classNames);
        $this->assertNotContains('1A', $classNames);
        $this->assertNotContains('1C', $classNames);
    }

    public function test_primary_school_generic_gradebook_route_redirects_to_primary_gradebook(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_PRIMARY]);
        Cache::flush();

        $response = $this->get(route('assessment.index'));

        $response->assertRedirect(route('assessment.gradebook.primary'));
    }

    public function test_primary_school_generic_markbook_route_redirects_to_primary_markbook(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_PRIMARY]);
        Cache::flush();

        $response = $this->get(route('assessment.markbook'));

        $response->assertRedirect(route('assessment.markbook.primary'));
    }

    private function seedAssessmentGradesAndClasses(): void
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
                'name' => 'English',
                'abbrev' => 'ENG',
                'level' => SchoolSetup::LEVEL_SENIOR,
                'components' => false,
                'canonical_key' => 'english',
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
                'mandatory' => true,
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

    private function seedMarkbookSubjects(): void
    {
        DB::table('klass_subject')->insert([
            [
                'id' => 1,
                'klass_id' => 2,
                'grade_subject_id' => 1,
                'user_id' => 1,
                'term_id' => 1,
                'grade_id' => 2,
                'year' => 2026,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'klass_id' => 3,
                'grade_subject_id' => 2,
                'user_id' => 1,
                'term_id' => 1,
                'grade_id' => 3,
                'year' => 2026,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'klass_id' => 4,
                'grade_subject_id' => 3,
                'user_id' => 1,
                'term_id' => 1,
                'grade_id' => 4,
                'year' => 2026,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
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
