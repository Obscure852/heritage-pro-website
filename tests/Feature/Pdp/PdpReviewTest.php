<?php

namespace Tests\Feature\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpPlanReview;
use App\Models\Pdp\PdpPlanSignature;
use App\Models\SchoolSetup;
use App\Models\User;
use App\Services\Pdp\PdpPlanService;
use App\Services\Pdp\PdpReviewService;
use App\Services\Pdp\PdpTemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use RuntimeException;
use Tests\Concerns\EnsuresPdpPhaseTwoSchema;
use Tests\TestCase;

class PdpReviewTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseTwoSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePdpPhaseTwoSchema();
    }

    public function test_closing_a_review_calculates_scores_and_moves_plan_forward(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $supervisor = $this->createUser('review-supervisor@example.com', [
            'firstname' => 'Sarah',
            'lastname' => 'Supervisor',
        ]);

        $employee = $this->createUser('review-employee@example.com', [
            'firstname' => 'Thato',
            'lastname' => 'Teacher',
            'reporting_to' => $supervisor->id,
        ]);

        $template = app(PdpTemplateService::class)->seedDefaults()['school'];
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
            'current_period_key' => 'mid_year',
            'supervisor_id' => $supervisor->id,
        ], $template);

        $plan->sectionEntries()->create([
            'section_key' => 'performance_objectives',
            'entry_group_key' => 'performance_objectives',
            'sort_order' => 1,
            'values_json' => [
                'objective' => 'Objective',
                'output' => 'Output',
                'measure' => 'Measure',
                'target' => 'Target',
                'score_out_of_10' => 8,
            ],
        ]);

        $attributeEntry = $plan->sectionEntries()->where('section_key', 'behavioural_attributes')->orderBy('id')->firstOrFail();
        $attributeEntry->update(['values_json' => array_merge($attributeEntry->values_json ?? [], [
            'mid_year_rating' => 4,
            'year_end_rating' => 5,
        ])]);

        $closedReview = app(PdpReviewService::class)->closeReview($plan, 'mid_year', $supervisor, 'Mid-year complete');

        $this->assertSame(PdpPlanReview::STATUS_CLOSED, $closedReview->status);
        $this->assertEquals(64.0, data_get($closedReview->score_summary_json, 'component_scores.performance_percentage.weighted_score'));
        $this->assertSame('year_end', $plan->fresh()->current_period_key);
        $this->assertSame(PdpPlan::STATUS_ACTIVE, $plan->fresh()->status);
        $this->assertEquals(80.0, $plan->fresh()->calculated_summary_json['summary.mid_year_total']);

        $openedYearEnd = app(PdpReviewService::class)->openReview($plan->fresh(), 'year_end', $supervisor);
        $this->assertSame(PdpPlanReview::STATUS_OPEN, $openedYearEnd->status);
    }

    public function test_plan_level_signatures_require_closed_reviews_and_enforce_order(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $supervisor = $this->createUser('sign-supervisor@example.com');
        $employee = $this->createUser('sign-employee@example.com', ['reporting_to' => $supervisor->id]);
        $official = $this->createUser('sign-official@example.com', ['position' => 'School Head']);

        $template = app(PdpTemplateService::class)->seedDefaults()['school'];
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
            'supervisor_id' => $supervisor->id,
        ], $template);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('All PDP reviews must be closed before plan-level sign-off can begin.');

        $employeeSignature = $plan->signatures()->where('approval_step_key', 'employee_signoff')->firstOrFail();
        app(PdpReviewService::class)->signSignature($plan, $employeeSignature, $employee);
    }

    public function test_signature_order_blocks_later_steps_until_prior_steps_are_signed(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $supervisor = $this->createUser('order-supervisor@example.com');
        $employee = $this->createUser('order-employee@example.com', ['reporting_to' => $supervisor->id]);
        $official = $this->createUser('order-official@example.com', ['position' => 'School Head']);

        $template = app(PdpTemplateService::class)->seedDefaults()['school'];
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_COMPLETED,
            'supervisor_id' => $supervisor->id,
        ], $template);

        foreach ($plan->reviews as $review) {
            $review->update([
                'status' => PdpPlanReview::STATUS_CLOSED,
                'opened_at' => now(),
                'closed_at' => now(),
                'score_summary_json' => ['total_score' => 80],
            ]);
        }

        $supervisorSignature = $plan->signatures()->where('approval_step_key', 'supervisor_signoff')->firstOrFail();

        try {
            app(PdpReviewService::class)->signSignature($plan->fresh(['template.approvalSteps', 'reviews', 'signatures.review']), $supervisorSignature, $supervisor);
            $this->fail('Expected signature order enforcement.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Complete the prior required PDP sign-off steps first.', $exception->getMessage());
        }

        $employeeSignature = $plan->signatures()->where('approval_step_key', 'employee_signoff')->firstOrFail();
        $signedEmployee = app(PdpReviewService::class)->signSignature($plan->fresh(['template.approvalSteps', 'reviews', 'signatures.review']), $employeeSignature, $employee);
        $this->assertSame(PdpPlanSignature::STATUS_SIGNED, $signedEmployee->status);

        $signedSupervisor = app(PdpReviewService::class)->signSignature($plan->fresh(['template.approvalSteps', 'reviews', 'signatures.review']), $supervisorSignature->fresh(), $supervisor);
        $this->assertSame(PdpPlanSignature::STATUS_SIGNED, $signedSupervisor->status);

        $officialSignature = $plan->signatures()->where('approval_step_key', 'authorized_official_signoff')->firstOrFail();
        $signedOfficial = app(PdpReviewService::class)->signSignature($plan->fresh(['template.approvalSteps', 'reviews', 'signatures.review']), $officialSignature->fresh(), $official);
        $this->assertSame(PdpPlanSignature::STATUS_SIGNED, $signedOfficial->status);
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
