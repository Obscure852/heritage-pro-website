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

class AssessmentSidebarNavigationTest extends TestCase
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
            'email' => 'support@heritagepro.co',
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

        $this->actingAs(User::findOrFail(1));
    }

    public function test_pref3_sidebar_splits_markbook_and_gradebook_modules(): void
    {
        $html = view('layouts.sidebar')->render();

        $this->assertStringContainsString('>Markbook</span>', $html);
        $this->assertStringContainsString('>Gradebook</span>', $html);
        $this->assertStringContainsString(route('assessment.markbook.primary'), $html);
        $this->assertStringContainsString(route('assessment.markbook.junior'), $html);
        $this->assertStringContainsString(route('assessment.gradebook.primary'), $html);
        $this->assertStringContainsString(route('assessment.gradebook.junior'), $html);
        $this->assertStringContainsString('Elementary', $html);
        $this->assertStringContainsString('Middle School', $html);
        $this->assertStringContainsString(route('assessment.test-list'), $html);
        $this->assertStringNotContainsString('data-key="t-invoices">Assessment</span>', $html);
    }

    public function test_primary_sidebar_keeps_single_assessment_module(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_PRIMARY]);
        Cache::flush();

        $html = view('layouts.sidebar')->render();

        $this->assertStringContainsString('data-key="t-invoices">Assessment</span>', $html);
        $this->assertStringContainsString(route('assessment.markbook.primary'), $html);
        $this->assertStringContainsString(route('assessment.gradebook.primary'), $html);
        $this->assertStringNotContainsString(route('assessment.markbook.junior'), $html);
        $this->assertStringNotContainsString(route('assessment.gradebook.junior'), $html);
    }

    public function test_k12_sidebar_adds_senior_children_to_split_modules(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_K12]);
        Cache::flush();

        $html = view('layouts.sidebar')->render();

        $this->assertStringContainsString(route('assessment.markbook.senior'), $html);
        $this->assertStringContainsString(route('assessment.gradebook.senior'), $html);
        $this->assertStringContainsString('High School', $html);
    }

    public function test_junior_senior_sidebar_shows_only_middle_and_high_school_children(): void
    {
        DB::table('school_setup')->where('id', 1)->update(['type' => SchoolSetup::TYPE_JUNIOR_SENIOR]);
        Cache::flush();

        $html = view('layouts.sidebar')->render();

        $this->assertStringContainsString(route('assessment.markbook.junior'), $html);
        $this->assertStringContainsString(route('assessment.markbook.senior'), $html);
        $this->assertStringContainsString(route('assessment.gradebook.junior'), $html);
        $this->assertStringContainsString(route('assessment.gradebook.senior'), $html);
        $this->assertStringContainsString('Middle School', $html);
        $this->assertStringContainsString('High School', $html);
        $this->assertStringNotContainsString('Elementary', $html);
        $this->assertStringNotContainsString(route('assessment.markbook.primary'), $html);
        $this->assertStringNotContainsString(route('assessment.gradebook.primary'), $html);
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
