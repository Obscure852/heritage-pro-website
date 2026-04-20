<?php

namespace Tests\Feature\Dashboard;

use App\Http\Middleware\EnsureProfileComplete;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\BuildsActivitiesRosterFixtures;
use Tests\TestCase;

class HomeDashboardTest extends TestCase
{
    use DatabaseTransactions;
    use BuildsActivitiesRosterFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->withoutMiddleware(EnsureProfileComplete::class);
        $this->withoutMiddleware(AuthenticateSession::class);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->seedActivitiesSchoolSetup();
    }

    public function test_dashboard_term_data_includes_grades_present_in_term_even_if_not_in_preset_grade_list(): void
    {
        $admin = $this->createActivityUser('dashboard-admin@example.com', ['Administrator']);
        $term = $this->createActivityTerm(2026, 1);
        $grade = $this->createGradeForTerm($term, 'F4', 4);

        $this->createKlassForTerm($term, $grade, $admin, 'F4 Blue');
        $this->createStudentForActivity($term, $grade, null, null, null, [
            'first_name' => 'Neo',
            'last_name' => 'Dashboard',
            'gender' => 'M',
        ]);

        $this->actingAs($admin)
            ->withSession(['selected_term_id' => $term->id])
            ->get(route('dashboard.dashboard-get-data'))
            ->assertOk()
            ->assertSee('F4')
            ->assertSee('Total Students');
    }

    public function test_dashboard_term_data_does_not_repeat_duplicate_grade_names_for_same_term(): void
    {
        $admin = $this->createActivityUser('dashboard-admin-dup@example.com', ['Administrator']);
        $term = $this->createActivityTerm(2026, 1);
        $grade = $this->createGradeForTerm($term, 'F1', 1);
        $klass = $this->createKlassForTerm($term, $grade, $admin, 'F1 Blue');

        $this->createGradeForTerm($term, 'F1', 1);
        $this->createGradeForTerm($term, 'F2', 2);
        $this->createStudentForActivity($term, $grade, $klass, null, null, [
            'first_name' => 'Kabelo',
            'last_name' => 'Header',
            'gender' => 'M',
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['selected_term_id' => $term->id])
            ->get(route('dashboard.dashboard-get-data'));

        $response->assertOk()
            ->assertViewHas('grades', function ($grades) {
                return $grades->pluck('name')
                    ->filter(fn ($name) => $name === 'F1')
                    ->count() === 1;
            });
    }
}
