<?php

namespace Tests\Feature\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\User;
use App\Services\Pdp\PdpPlanService;
use App\Services\Pdp\PdpTemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\EnsuresPdpPhaseTwoSchema;
use Tests\TestCase;

class PdpReportingTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseTwoSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePdpPhaseTwoSchema();
    }

    public function test_school_head_can_view_pdp_reporting_dashboard(): void
    {
        $admin = $this->createUser('report-admin@example.com', ['position' => 'School Head']);
        $employeeA = $this->createUser('report-a@example.com', ['firstname' => 'Neo', 'lastname' => 'One']);
        $employeeB = $this->createUser('report-b@example.com', ['firstname' => 'Mpho', 'lastname' => 'Two']);
        $template = app(PdpTemplateService::class)->seedDefaults()['school'];

        app(PdpPlanService::class)->createPlan($employeeA, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
        ], $template);

        app(PdpPlanService::class)->createPlan($employeeB, [
            'plan_period_start' => '2025-01-01',
            'plan_period_end' => '2025-12-31',
            'status' => PdpPlan::STATUS_COMPLETED,
        ], $template);

        $this->actingAs($admin)
            ->get(route('staff.pdp.reports.index'))
            ->assertOk()
            ->assertSee('Total Plans')
            ->assertSee('Active Plans')
            ->assertSee('staff-pdp-school-v4')
            ->assertSee('Neo One')
            ->assertSee('Mpho Two');
    }

    public function test_regular_employee_cannot_view_pdp_reporting_dashboard(): void
    {
        $employee = $this->createUser('report-employee@example.com');

        $this->actingAs($employee)
            ->get(route('staff.pdp.reports.index'))
            ->assertForbidden();
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
