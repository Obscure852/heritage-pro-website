<?php

namespace Tests\Feature\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\SchoolSetup;
use App\Models\User;
use App\Services\Pdp\PdpPlanService;
use App\Services\Pdp\PdpSettingsService;
use App\Services\Pdp\PdpTemplateBlueprints;
use App\Services\Pdp\PdpTemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\EnsuresPdpPhaseTwoSchema;
use Tests\TestCase;

class PdpPdfTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseTwoSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePdpPhaseTwoSchema();
    }

    public function test_employee_can_preview_and_download_their_pdp_pdf(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $employee = $this->createUser('pdf-employee@example.com', [
            'firstname' => 'Thato',
            'lastname' => 'Buseng',
        ]);

        app(PdpSettingsService::class)->saveGeneralSettings([
            'general_guidance' => "Use the PDP for agreed objectives.\nDiscuss the review with the appraisee.",
        ]);

        $plan = $this->createPlanFor($employee);

        $this->actingAs($employee)
            ->get(route('staff.pdp.plans.print', $plan))
            ->assertOk()
            ->assertSee('Staff Performance Development Plan')
            ->assertSee('staff-pdp-school-v4')
            ->assertSee('Thato Buseng')
            ->assertSee('General Guidance')
            ->assertSee('Use the PDP for agreed objectives.');

        $response = $this->actingAs($employee)
            ->get(route('staff.pdp.plans.pdf', $plan));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_unrelated_user_cannot_preview_someone_elses_pdp_pdf(): void
    {
        $employee = $this->createUser('pdf-owner@example.com');
        $other = $this->createUser('pdf-other@example.com');

        $plan = $this->createPlanFor($employee);

        $this->actingAs($other)
            ->get(route('staff.pdp.plans.print', $plan))
            ->assertForbidden();
    }

    public function test_preview_print_shows_part_a_compatibility_fields_for_legacy_school_template(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        app(PdpSettingsService::class)->saveGeneralSettings([
            'part_a_ministry_department' => 'Secondary',
        ]);

        $supervisor = $this->createUser('pdf-legacy-supervisor@example.com', [
            'firstname' => 'Neo',
            'lastname' => 'Matshoga',
            'position' => 'HOD',
            'earning_band' => 'D3',
        ]);
        $employee = $this->createUser('pdf-legacy-employee@example.com', [
            'firstname' => 'Sekano',
            'lastname' => 'William',
            'position' => 'Senior Teacher',
            'reporting_to' => $supervisor->id,
            'personal_payroll_number' => '456818005',
            'dpsm_personal_file_number' => '81716',
        ]);

        $templateDefinition = PdpTemplateBlueprints::schoolHalfYearly();
        $templateDefinition['template']['template_family_key'] = 'default';
        $templateDefinition['template']['version'] = 1;
        $templateDefinition['template']['code'] = '001';
        $templateDefinition['template']['name'] = 'Default PDP Template';
        $employeeSectionIndex = collect($templateDefinition['sections'])->search(fn (array $section): bool => $section['key'] === 'employee_information');
        $templateDefinition['sections'][$employeeSectionIndex]['fields'] = [
            [
                'key' => 'employee_name',
                'label' => 'Employee Name',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'full_name',
                'required' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'school_name',
                'label' => 'School Name',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_setting',
                'mapping_source' => 'settings',
                'mapping_key' => 'school_setup.school_name',
                'required' => false,
                'sort_order' => 2,
            ],
        ];

        $templateService = app(PdpTemplateService::class);
        $template = $templateService->publish($templateService->createDraftFromDefinition($templateDefinition));
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
            'supervisor_id' => $supervisor->id,
        ], $template);

        $this->actingAs($employee)
            ->get(route('staff.pdp.plans.print', $plan))
            ->assertOk()
            ->assertSee('DPSM Personal File No')
            ->assertSee('81716')
            ->assertSee('Supervisor Name')
            ->assertSee('Neo Matshoga')
            ->assertSee('Ministry / Department')
            ->assertSee('Secondary');
    }

    private function createPlanFor(User $employee): PdpPlan
    {
        $template = app(PdpTemplateService::class)->seedDefaults()['school'];

        return app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
        ], $template);
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
