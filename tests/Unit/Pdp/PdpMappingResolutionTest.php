<?php

namespace Tests\Unit\Pdp;

use App\Models\SchoolSetup;
use App\Models\User;
use App\Models\UserProfileMetadata;
use App\Services\Pdp\PdpPlanService;
use App\Services\Pdp\PdpSettingsService;
use App\Services\Pdp\PdpTemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\EnsuresPdpPhaseTwoSchema;
use Tests\TestCase;

class PdpMappingResolutionTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseTwoSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePdpPhaseTwoSchema();
    }

    public function test_resolve_mapped_values_from_user_settings_profile_plan_and_computed_sources(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $employee = User::withoutEvents(fn () => User::query()->create([
            'firstname' => 'Thato',
            'lastname' => 'Buseng',
            'email' => 'mapping-user@example.com',
            'password' => 'secret',
            'position' => 'Senior Teacher',
            'status' => 'Current',
            'year' => 2026,
        ]));

        UserProfileMetadata::setValue($employee->id, 'payroll_no', 'PAY-7781');
        app(PdpSettingsService::class)->set('labels.ministry_name', 'Ministry of Basic Education');

        $definition = [
            'template' => [
                'template_family_key' => 'staff_pdp_mapping_test',
                'version' => 1,
                'code' => 'staff-pdp-mapping-test-v1',
                'name' => 'Staff PDP Mapping Test',
            ],
            'sections' => [
                [
                    'key' => 'employee_information',
                    'label' => 'Employee Information',
                    'section_type' => 'profile_summary',
                    'sequence' => 1,
                    'fields' => [
                        [
                            'key' => 'employee_name',
                            'label' => 'Employee Name',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_user_field',
                            'mapping_source' => 'user',
                            'mapping_key' => 'full_name',
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
                            'sort_order' => 2,
                        ],
                        [
                            'key' => 'ministry_name',
                            'label' => 'Ministry Name',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_setting',
                            'mapping_source' => 'settings',
                            'mapping_key' => 'pdp.labels.ministry_name',
                            'sort_order' => 3,
                        ],
                        [
                            'key' => 'payroll_no',
                            'label' => 'Payroll Number',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_profile_metadata',
                            'mapping_source' => 'profile_metadata',
                            'mapping_key' => 'payroll_no',
                            'sort_order' => 4,
                        ],
                        [
                            'key' => 'plan_start',
                            'label' => 'Plan Start',
                            'field_type' => 'date',
                            'data_type' => 'date',
                            'input_mode' => 'computed',
                            'mapping_source' => 'plan',
                            'mapping_key' => 'plan_period_start',
                            'sort_order' => 5,
                        ],
                        [
                            'key' => 'final_rating_band',
                            'label' => 'Final Rating Band',
                            'field_type' => 'computed_value',
                            'data_type' => 'string',
                            'input_mode' => 'computed',
                            'mapping_source' => 'computed',
                            'mapping_key' => 'summary.final_rating_band',
                            'sort_order' => 6,
                        ],
                    ],
                ],
            ],
            'periods' => [
                [
                    'key' => 'mid_year',
                    'label' => 'Mid-Year',
                    'sequence' => 1,
                    'window_type' => 'configured_dates',
                ],
            ],
            'rating_schemes' => [
                [
                    'key' => 'performance_percentage',
                    'label' => 'Performance Percentage',
                    'input_type' => 'direct_percentage',
                ],
            ],
            'approval_steps' => [
                [
                    'key' => 'employee_signoff',
                    'label' => 'Employee Signature',
                    'sequence' => 1,
                    'role_type' => 'employee',
                    'required' => true,
                ],
            ],
        ];

        $templateService = app(PdpTemplateService::class);
        $template = $templateService->createDraftFromDefinition($definition);
        $template = $templateService->publish($template);

        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
        ], $template);

        $resolved = app(PdpPlanService::class)->resolveMappedSectionValues(
            $plan,
            'employee_information',
            null,
            ['summary' => ['final_rating_band' => 'Very Good']]
        );

        $this->assertSame('Thato Buseng', $resolved['employee_name']);
        $this->assertSame('Merementsi Junior Secondary School', $resolved['school_name']);
        $this->assertSame('Ministry of Basic Education', $resolved['ministry_name']);
        $this->assertSame('PAY-7781', $resolved['payroll_no']);
        $this->assertSame('2026-01-01', $resolved['plan_start']->toDateString());
        $this->assertSame('Very Good', $resolved['final_rating_band']);
    }

    public function test_seeded_v2_templates_resolve_direct_user_fields_for_payroll_grade_and_appointment_date(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        app(PdpSettingsService::class)->saveGeneralSettings([
            'part_a_ministry_department' => 'Secondary',
        ]);

        $supervisor = User::withoutEvents(fn () => User::query()->create([
            'firstname' => 'Neo',
            'lastname' => 'Matshoga',
            'email' => 'mapping-supervisor@example.com',
            'password' => 'secret',
            'position' => 'HOD',
            'earning_band' => 'D3',
            'status' => 'Current',
            'year' => 2026,
        ]));

        $employee = User::withoutEvents(fn () => User::query()->create([
            'firstname' => 'Kago',
            'lastname' => 'Molefe',
            'email' => 'mapping-user-v2@example.com',
            'password' => 'secret',
            'position' => 'Senior Teacher',
            'reporting_to' => $supervisor->id,
            'personal_payroll_number' => 'PPN-2002',
            'dpsm_personal_file_number' => '81716',
            'date_of_appointment' => '2021-02-20',
            'earning_band' => 'C2',
            'status' => 'Current',
            'year' => 2026,
        ]));

        $template = app(PdpTemplateService::class)->seedDefaults()['school'];
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
        ], $template);

        $resolved = app(PdpPlanService::class)->resolveMappedSectionValues($plan, 'employee_information');

        $this->assertSame('PPN-2002', $resolved['payroll_no']);
        $this->assertSame('81716', $resolved['dpsm_file_no']);
        $this->assertSame('Secondary', $resolved['ministry_department']);
        $this->assertSame('Merementsi Junior Secondary School', $resolved['school_name']);
        $this->assertSame('C2', $resolved['grade']);
        $this->assertSame('2021-02-20', $resolved['date_of_appointment']->toDateString());
        $this->assertSame('Neo Matshoga', $resolved['supervisor_name']);
        $this->assertSame('HOD', $resolved['supervisor_position']);
        $this->assertSame('D3', $resolved['supervisor_grade']);
    }
}
