<?php

namespace Tests\Feature\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpPlanReview;
use App\Models\Pdp\PdpPlanSignature;
use App\Models\Pdp\PdpTemplate;
use App\Models\SchoolSetup;
use App\Models\User;
use App\Models\UserProfileMetadata;
use App\Services\Pdp\PdpPlanService;
use App\Services\Pdp\PdpTemplateBlueprints;
use App\Services\Pdp\PdpTemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LogicException;
use RuntimeException;
use Tests\Concerns\EnsuresPdpPhaseTwoSchema;
use Tests\TestCase;

class PdpPlanServiceTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseTwoSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePdpPhaseTwoSchema();
    }

    public function test_create_plan_from_published_template_initializes_reviews_and_signatures(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $supervisor = $this->createUser('supervisor@example.com', [
            'firstname' => 'Sarah',
            'lastname' => 'Supervisor',
            'signature_path' => 'signatures/supervisor.png',
        ]);

        $employee = $this->createUser('employee@example.com', [
            'firstname' => 'Thato',
            'lastname' => 'Teacher',
            'position' => 'Teacher',
            'reporting_to' => $supervisor->id,
            'signature_path' => 'signatures/employee.png',
        ]);

        UserProfileMetadata::setValue($employee->id, 'payroll_no', 'PPN-1001');
        UserProfileMetadata::setValue($employee->id, 'grade', 'B4');

        $template = app(PdpTemplateService::class)->seedDefaults()['school'];
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
            'created_by' => $supervisor->id,
        ], $template);

        $this->assertSame($template->id, $plan->pdp_template_id);
        $this->assertSame($employee->id, $plan->user_id);
        $this->assertSame($supervisor->id, $plan->supervisor_id);
        $this->assertSame('mid_year', $plan->current_period_key);
        $this->assertCount(2, $plan->reviews);
        $this->assertSame(['mid_year', 'year_end'], $plan->reviews->pluck('period_key')->all());
        $this->assertSame(PdpPlanReview::STATUS_OPEN, $plan->reviews->firstWhere('period_key', 'mid_year')?->status);
        $this->assertSame(PdpPlanReview::STATUS_PENDING, $plan->reviews->firstWhere('period_key', 'year_end')?->status);
        $this->assertCount(3, $plan->signatures);
        $this->assertEquals(
            ['employee_signoff', 'supervisor_signoff', 'authorized_official_signoff'],
            $plan->signatures->pluck('approval_step_key')->all()
        );
        $this->assertTrue($plan->signatures->every(fn (PdpPlanSignature $signature): bool => $signature->status === PdpPlanSignature::STATUS_PENDING));
        $this->assertTrue($plan->signatures->every(fn (PdpPlanSignature $signature): bool => $signature->pdp_plan_review_id === null));

        $employeeSignature = $plan->signatures->firstWhere('approval_step_key', 'employee_signoff');
        $employeeSignature->update([
            'signer_user_id' => $employee->id,
            'signed_at' => now(),
            'status' => PdpPlanSignature::STATUS_SIGNED,
        ]);

        $this->assertSame(
            'signatures/employee.png',
            $employeeSignature->fresh('signer')->resolved_signature_path
        );
    }

    public function test_create_plan_rejects_unpublished_template_versions(): void
    {
        $employee = $this->createUser('draft-user@example.com');
        $definition = PdpTemplateBlueprints::schoolHalfYearly();
        $definition['template']['code'] = 'staff-pdp-school-draft-test';
        $definition['template']['name'] = 'Draft Test Template';

        $draftTemplate = app(PdpTemplateService::class)->createDraftFromDefinition($definition);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('PDP plans can only be created from a published template version.');

        app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
        ], $draftTemplate);
    }

    public function test_create_plan_blocks_overlapping_plans_for_the_same_template_family(): void
    {
        $employee = $this->createUser('overlap-user@example.com');
        $template = app(PdpTemplateService::class)->seedDefaults()['school'];
        $service = app(PdpPlanService::class);

        $service->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-06-30',
        ], $template);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('An overlapping PDP plan already exists for this employee and template family.');

        $service->createPlan($employee, [
            'plan_period_start' => '2026-06-01',
            'plan_period_end' => '2026-12-31',
        ], $template);
    }

    public function test_cancelled_plans_do_not_block_new_plan_creation(): void
    {
        $employee = $this->createUser('cancelled-user@example.com');
        $template = app(PdpTemplateService::class)->seedDefaults()['school'];
        $service = app(PdpPlanService::class);

        $plan = $service->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-06-30',
        ], $template);

        $plan->update(['status' => PdpPlan::STATUS_CANCELLED]);

        $replacement = $service->createPlan($employee, [
            'plan_period_start' => '2026-06-01',
            'plan_period_end' => '2026-12-31',
        ], $template);

        $this->assertNotSame($plan->id, $replacement->id);
        $this->assertSame('2026-06-01', $replacement->plan_period_start->toDateString());
    }

    public function test_plan_template_binding_is_immutable_after_creation(): void
    {
        $employee = $this->createUser('binding-user@example.com');
        $templates = app(PdpTemplateService::class)->seedDefaults();
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
        ], $templates['school']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('A PDP plan cannot be rebound to a different template version.');

        $plan->pdp_template_id = $templates['official']->id;
        $plan->save();
    }

    public function test_template_row_snapshots_in_existing_plan_do_not_change_when_new_template_version_is_edited(): void
    {
        $employee = $this->createUser('snapshot-user@example.com');
        $service = app(PdpTemplateService::class);

        $draft = $service->createDraftFromBlueprint('school_half_yearly', [
            'template_family_key' => 'staff_pdp_school_snapshot_test',
            'code' => 'staff-pdp-school-snapshot-test-v1',
            'name' => 'Snapshot Test v1',
        ]);

        $section = $draft->sections->firstWhere('key', 'performance_objectives');
        $objectiveRow = $service->createSectionRow($section, [
            'objective_category' => 'Attendance',
            'objective' => 'Original objective',
        ]);
        $service->createSectionRow($section, [
            'output' => 'Original output',
            'measure' => 'Original measure',
            'target' => 'Original target',
        ], null, $objectiveRow);

        $published = $service->publish($draft);
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
        ], $published);

        $clone = $service->cloneAsNextDraft($published, null, [
            'code' => 'staff-pdp-school-snapshot-test-v2',
            'name' => 'Snapshot Test v2',
        ]);

        $cloneSection = $clone->sections->firstWhere('key', 'performance_objectives');
        $cloneRow = $cloneSection->rows->first();
        $cloneChildRow = $cloneRow->childRows->first();
        $service->updateSectionRow($cloneSection, $cloneRow, [
            'objective_category' => 'Attendance',
            'objective' => 'Updated objective',
        ]);
        $service->updateSectionRow($cloneSection, $cloneChildRow, [
            'output' => 'Updated output',
            'measure' => 'Updated measure',
            'target' => 'Updated target',
        ]);

        $entry = $plan->fresh()->sectionEntries()->where('section_key', 'performance_objectives')->firstOrFail();
        $detailEntry = $entry->childEntries()->firstOrFail();
        $this->assertSame('Attendance', $entry->values_json['objective_category']);
        $this->assertSame('Original objective', $entry->values_json['objective']);
        $this->assertSame('Original output', $detailEntry->values_json['output']);
        $this->assertSame('Original target', $detailEntry->values_json['target']);
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
