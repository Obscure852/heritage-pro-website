<?php

namespace Tests\Feature\Finals;

use App\Models\FinalKlass;
use App\Models\Grade;
use App\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalSubjectsReportClassesEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_classes_endpoint_returns_classes_for_requested_year(): void
    {
        $this->withoutMiddleware();

        $grade = $this->createGrade('F3', 2026);

        FinalKlass::create([
            'original_klass_id' => 1001,
            'name' => '3B',
            'user_id' => null,
            'graduation_term_id' => $grade->term_id,
            'grade_id' => $grade->id,
            'type' => 'core',
            'graduation_year' => 2026,
        ]);

        FinalKlass::create([
            'original_klass_id' => 1002,
            'name' => '3A',
            'user_id' => null,
            'graduation_term_id' => $grade->term_id,
            'grade_id' => $grade->id,
            'type' => 'core',
            'graduation_year' => 2026,
        ]);

        FinalKlass::create([
            'original_klass_id' => 1003,
            'name' => '2A',
            'user_id' => null,
            'graduation_term_id' => $grade->term_id,
            'grade_id' => $grade->id,
            'type' => 'core',
            'graduation_year' => 2025,
        ]);

        $response = $this->getJson(route('finals.subjects.report-classes', ['year' => 2026]));

        $response->assertOk()
            ->assertJsonCount(2, 'classes')
            ->assertJsonPath('classes.0.name', '3A')
            ->assertJsonPath('classes.1.name', '3B')
            ->assertJsonPath('classes.0.grade_name', 'F3')
            ->assertJsonPath('classes.0.graduation_year', 2026);
    }

    public function test_report_classes_endpoint_returns_empty_array_for_year_with_no_classes(): void
    {
        $this->withoutMiddleware();

        $response = $this->getJson(route('finals.subjects.report-classes', ['year' => 2099]));

        $response->assertOk()
            ->assertExactJson([
                'classes' => [],
            ]);
    }

    public function test_report_classes_endpoint_validates_required_and_invalid_year(): void
    {
        $this->withoutMiddleware();

        $this->getJson(route('finals.subjects.report-classes'))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['year']);

        $this->getJson(route('finals.subjects.report-classes', ['year' => 1999]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['year']);
    }

    public function test_report_classes_route_has_expected_middleware(): void
    {
        $route = app('router')->getRoutes()->getByName('finals.subjects.report-classes');

        $this->assertNotNull($route);
        $middleware = $route->gatherMiddleware();

        $this->assertContains('auth', $middleware);
        $this->assertContains('throttle:auth', $middleware);
        $this->assertContains('block.non.african', $middleware);
    }

    private function createGrade(string $name, int $year): Grade
    {
        $term = Term::query()
            ->where('year', $year)
            ->where('term', 3)
            ->first();

        if (!$term) {
            $term = Term::create([
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->addDays(10)->toDateString(),
                'term' => 3,
                'year' => $year,
                'closed' => false,
            ]);
        }

        return Grade::create([
            'sequence' => 3,
            'name' => $name,
            'promotion' => 'Alumni',
            'description' => $name . ' Grade',
            'level' => 'Junior',
            'active' => true,
            'term_id' => $term->id,
            'year' => $year,
        ]);
    }
}

