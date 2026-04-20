<?php

namespace Tests\Unit\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\SchoolSetup;
use App\Models\User;
use App\Services\Pdp\PdpPlanService;
use App\Services\Pdp\PdpScoringService;
use App\Services\Pdp\PdpTemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\EnsuresPdpPhaseTwoSchema;
use Tests\TestCase;

class PdpScoringServiceTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseTwoSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePdpPhaseTwoSchema();
    }

    public function test_school_template_review_scoring_uses_weighted_objective_and_attribute_scores(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $employee = $this->createUser('scoring-school@example.com');
        $template = app(PdpTemplateService::class)->seedDefaults()['school'];
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
        ], $template);

        $plan->sectionEntries()->create([
            'section_key' => 'performance_objectives',
            'entry_group_key' => 'performance_objectives',
            'sort_order' => 1,
            'values_json' => [
                'objective' => 'Objective 1',
                'output' => 'Output 1',
                'measure' => 'Measure 1',
                'target' => 'Target 1',
                'score_out_of_10' => 7,
            ],
        ]);

        $plan->sectionEntries()->create([
            'section_key' => 'performance_objectives',
            'entry_group_key' => 'performance_objectives',
            'sort_order' => 2,
            'values_json' => [
                'objective' => 'Objective 2',
                'output' => 'Output 2',
                'measure' => 'Measure 2',
                'target' => 'Target 2',
                'score_out_of_10' => 9,
            ],
        ]);

        $attributeEntries = $plan->sectionEntries()->where('section_key', 'behavioural_attributes')->orderBy('id')->get();
        $attributeEntries[0]->update(['values_json' => array_merge($attributeEntries[0]->values_json ?? [], ['mid_year_rating' => 4])]);
        $attributeEntries[1]->update(['values_json' => array_merge($attributeEntries[1]->values_json ?? [], ['mid_year_rating' => 5])]);

        $summary = app(PdpScoringService::class)->calculateReviewSummary($plan, 'mid_year');

        $this->assertSame(64.0, data_get($summary, 'component_scores.performance_percentage.weighted_score'));
        $this->assertSame(18.0, data_get($summary, 'component_scores.behaviour_intensity.weighted_score'));
        $this->assertSame(82.0, data_get($summary, 'total_score'));
        $this->assertSame('Very Good', data_get($summary, 'rating_band'));
        $this->assertSame(64.0, data_get($summary, 'mapped_summary.summary.mid_year_performance'));
        $this->assertSame(18.0, data_get($summary, 'mapped_summary.summary.mid_year_attributes'));
        $this->assertSame(82.0, data_get($summary, 'mapped_summary.summary.mid_year_total'));
    }

    public function test_official_template_review_scoring_supports_band_scale_conversion(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $employee = $this->createUser('scoring-dpsm@example.com');
        $template = app(PdpTemplateService::class)->seedDefaults()['official'];
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
            'current_period_key' => 'quarter_1',
        ], $template);

        $plan->sectionEntries()->create([
            'section_key' => 'performance_objectives',
            'entry_group_key' => 'performance_objectives',
            'sort_order' => 1,
            'values_json' => [
                'objective' => 'Objective 1',
                'output' => 'Output 1',
                'measure' => 'Measure 1',
                'target' => 'Target 1',
                'score_out_of_10' => 7.5,
            ],
        ]);

        $attributeEntry = $plan->sectionEntries()->where('section_key', 'personal_attributes')->orderBy('id')->firstOrFail();
        $attributeEntry->update(['values_json' => array_merge($attributeEntry->values_json ?? [], ['rating' => 4])]);

        $summary = app(PdpScoringService::class)->calculateReviewSummary($plan, 'quarter_1');

        $this->assertSame(60.0, data_get($summary, 'component_scores.performance_percentage.weighted_score'));
        $this->assertSame(16.0, data_get($summary, 'component_scores.personal_attribute_band.weighted_score'));
        $this->assertSame(76.0, data_get($summary, 'total_score'));
        $this->assertSame(76.0, data_get($summary, 'mapped_summary.summary.quarterly_total'));
        $this->assertSame(76.0, data_get($summary, 'mapped_summary.summary.final_rating'));
    }

    private function createUser(string $email, array $overrides = []): User
    {
        return User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => $email,
            'password' => 'secret',
            'status' => 'Current',
            'position' => 'Teacher',
            'year' => 2026,
        ], $overrides)));
    }
}
