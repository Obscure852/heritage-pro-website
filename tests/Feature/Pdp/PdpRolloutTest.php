<?php

namespace Tests\Feature\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpRollout;
use App\Models\SchoolSetup;
use App\Models\User;
use App\Services\Pdp\PdpPlanService;
use App\Services\Pdp\PdpTemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\EnsuresPdpPhaseTwoSchema;
use Tests\TestCase;

class PdpRolloutTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseTwoSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePdpPhaseTwoSchema();
    }

    public function test_activating_a_published_template_launches_a_rollout_and_provisions_staff(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $templates = app(PdpTemplateService::class)->seedDefaults();
        $official = $templates['official'];
        $admin = $this->createUser('activation-admin@example.com', [
            'firstname' => 'School',
            'lastname' => 'Head',
            'position' => 'School Head',
            'status' => 'Former',
        ]);
        $supervisor = $this->createUser('activation-supervisor@example.com', [
            'firstname' => 'Sarah',
            'lastname' => 'Supervisor',
            'position' => 'HOD',
        ]);
        $employee = $this->createUser('activation-employee@example.com', [
            'firstname' => 'Thato',
            'lastname' => 'Teacher',
            'reporting_to' => $supervisor->id,
        ]);
        $unassigned = $this->createUser('activation-unassigned@example.com', [
            'firstname' => 'Mpho',
            'lastname' => 'Unassigned',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('staff.pdp.templates.activate', $official), [
                'label' => '2026 Activated Cycle',
                'cycle_year' => 2026,
                'plan_period_start' => '2026-01-01',
                'plan_period_end' => '2026-12-31',
                'auto_provision_new_staff' => 1,
                'fallback_supervisor_user_id' => $admin->id,
            ]);

        $rollout = PdpRollout::query()->latest('id')->firstOrFail();

        $response->assertRedirect(route('staff.pdp.templates.show', $official));
        $this->assertTrue($official->fresh()->is_default);
        $this->assertSame(3, PdpPlan::query()->where('pdp_rollout_id', $rollout->id)->count());
        $this->assertSame($admin->id, PdpPlan::query()->where('user_id', $unassigned->id)->value('supervisor_id'));
    }

    public function test_launching_rollout_provisions_current_staff_and_future_staff(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        app(PdpTemplateService::class)->seedDefaults();

        $admin = $this->createUser('rollout-admin@example.com', [
            'firstname' => 'School',
            'lastname' => 'Head',
            'position' => 'School Head',
            'status' => 'Former',
        ]);
        $supervisor = $this->createUser('rollout-supervisor@example.com', [
            'firstname' => 'Sarah',
            'lastname' => 'Supervisor',
            'position' => 'HOD',
        ]);
        $employee = $this->createUser('rollout-employee@example.com', [
            'firstname' => 'Thato',
            'lastname' => 'Teacher',
            'reporting_to' => $supervisor->id,
        ]);
        $unassigned = $this->createUser('rollout-unassigned@example.com', [
            'firstname' => 'Mpho',
            'lastname' => 'Unassigned',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('staff.pdp.rollouts.store'), [
                'label' => '2026 Staff PDP Rollout',
                'cycle_year' => 2026,
                'plan_period_start' => '2026-01-01',
                'plan_period_end' => '2026-12-31',
                'auto_provision_new_staff' => 1,
                'fallback_supervisor_user_id' => $admin->id,
            ]);

        $rollout = PdpRollout::query()->latest('id')->firstOrFail();

        $response->assertRedirect(route('staff.pdp.rollouts.show', $rollout));
        $this->assertSame(3, PdpPlan::query()->where('pdp_rollout_id', $rollout->id)->count());
        $this->assertSame($admin->id, PdpPlan::query()->where('user_id', $unassigned->id)->value('supervisor_id'));

        $futureStaff = User::query()->create([
            'firstname' => 'Future',
            'lastname' => 'Teacher',
            'email' => 'future-rollout@example.com',
            'password' => 'secret',
            'status' => 'Current',
            'active' => true,
            'position' => 'Teacher',
            'reporting_to' => $supervisor->id,
            'year' => 2026,
        ]);

        $this->assertDatabaseHas('pdp_plans', [
            'user_id' => $futureStaff->id,
            'pdp_rollout_id' => $rollout->id,
        ]);
    }

    public function test_rollout_records_overlapping_existing_plans_as_skipped_exceptions(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $template = app(PdpTemplateService::class)->seedDefaults()['school'];
        $admin = $this->createUser('rollout-skip-admin@example.com', [
            'position' => 'School Head',
            'status' => 'Former',
        ]);
        $employee = $this->createUser('rollout-skip-employee@example.com');

        app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
        ], $template);

        $this->actingAs($admin)
            ->post(route('staff.pdp.rollouts.store'), [
                'label' => '2026 Overlap Rollout',
                'cycle_year' => 2026,
                'plan_period_start' => '2026-01-01',
                'plan_period_end' => '2026-12-31',
                'auto_provision_new_staff' => 1,
                'fallback_supervisor_user_id' => $admin->id,
            ])
            ->assertRedirect();

        $rollout = PdpRollout::query()->latest('id')->firstOrFail();

        $this->assertSame(1, $rollout->skipped_count);
        $this->assertStringContainsString(
            'overlapping pdp plan already exists',
            strtolower((string) ($rollout->exceptions_json[0]['reason'] ?? ''))
        );
    }

    private function createUser(string $email, array $overrides = []): User
    {
        return User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => $email,
            'password' => 'secret',
            'status' => 'Current',
            'active' => true,
            'position' => 'Teacher',
            'year' => 2026,
        ], $overrides)));
    }
}
