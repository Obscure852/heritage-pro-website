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

class PdpCrudTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseTwoSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePdpPhaseTwoSchema();
    }

    public function test_plan_create_show_and_edit_flow_works_for_authenticated_user(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $admin = $this->createUser('phase3-admin@example.com', [
            'firstname' => 'School',
            'lastname' => 'Head',
            'position' => 'School Head',
        ]);
        $employee = $this->createUser('phase3-employee@example.com', [
            'firstname' => 'Thato',
            'lastname' => 'Buseng',
            'position' => 'Teacher',
        ]);

        $template = app(PdpTemplateService::class)->seedDefaults()['school'];
        app(PdpSettingsService::class)->saveCommentBank([
            'supervisee_comments' => "I reviewed my progress against the agreed targets.",
            'supervisor_comments' => "The teacher has shown consistent progress in this objective.",
        ]);

        $this->actingAs($admin)
            ->get(route('staff.pdp.plans.create'))
            ->assertOk()
            ->assertSee('Create PDP Plan');

        $storeResponse = $this->actingAs($admin)
            ->post(route('staff.pdp.plans.store'), [
                'user_id' => $employee->id,
                'template_id' => $template->id,
                'plan_period_start' => '2026-01-01',
                'plan_period_end' => '2026-12-31',
                'status' => 'active',
                'current_period_key' => 'mid_year',
            ]);

        $plan = PdpPlan::query()->latest('id')->firstOrFail();

        $storeResponse
            ->assertRedirect(route('staff.pdp.plans.show', $plan));

        $this->actingAs($employee)
            ->get(route('staff.pdp.plans.show', $plan))
            ->assertOk()
            ->assertSee('Plan Review and Export Actions')
            ->assertSee('Part A: Employee Information')
            ->assertSee('Part B: Performance Objectives')
            ->assertSee('Merementsi Junior Secondary School');

        $this->actingAs($admin)
            ->get(route('staff.pdp.plans.edit', $plan))
            ->assertOk()
            ->assertSee('Edit PDP Plan');

        $this->actingAs($admin)
            ->put(route('staff.pdp.plans.update', $plan), [
                'supervisor_id' => '',
                'plan_period_start' => '2026-01-15',
                'plan_period_end' => '2026-12-20',
                'status' => 'draft',
                'current_period_key' => 'year_end',
            ])
            ->assertRedirect(route('staff.pdp.plans.show', $plan));

        $updatedPlan = $plan->fresh();
        $this->assertSame('draft', $updatedPlan->status);
        $this->assertSame('year_end', $updatedPlan->current_period_key);
        $this->assertSame('2026-01-15', $updatedPlan->plan_period_start->format('Y-m-d'));
        $this->assertSame('2026-12-20', $updatedPlan->plan_period_end->format('Y-m-d'));
    }

    public function test_plan_show_displays_canned_comment_pickers_for_supervisee_and_supervisor_fields(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $admin = $this->createUser('phase3-comment-admin@example.com', [
            'firstname' => 'School',
            'lastname' => 'Head',
            'position' => 'School Head',
        ]);
        $employee = $this->createUser('phase3-comment-employee@example.com', [
            'firstname' => 'Neo',
            'lastname' => 'Teacher',
            'position' => 'Teacher',
        ]);

        app(PdpSettingsService::class)->saveCommentBank([
            'supervisee_comments' => "I reviewed my progress against the agreed targets.",
            'supervisor_comments' => "The teacher has shown consistent progress in this objective.",
        ]);

        $templateDefinition = PdpTemplateBlueprints::blankBounded();
        $templateDefinition['template']['template_family_key'] = 'staff_pdp_comment_bank_test';
        $templateDefinition['template']['code'] = 'staff-pdp-comment-bank-v1';
        $templateDefinition['template']['name'] = 'Staff PDP Comment Bank v1';
        $templateDefinition['sections'][] = [
            'key' => 'reflective_comments',
            'label' => 'Reflective Comments',
            'section_type' => 'comments_block',
            'sequence' => 8,
            'is_repeatable' => false,
            'fields' => [
                [
                    'key' => 'supervisee_comment',
                    'label' => 'Supervisee Comment',
                    'field_type' => 'comment',
                    'data_type' => 'string',
                    'input_mode' => 'manual_entry',
                    'sort_order' => 1,
                ],
                [
                    'key' => 'supervisor_comment',
                    'label' => 'Supervisor Comment',
                    'field_type' => 'comment',
                    'data_type' => 'string',
                    'input_mode' => 'manual_entry',
                    'sort_order' => 2,
                ],
            ],
        ];

        $templateService = app(PdpTemplateService::class);
        $template = $templateService->publish($templateService->createDraftFromDefinition($templateDefinition));
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
        ], $template);

        $this->actingAs($employee)
            ->get(route('staff.pdp.plans.show', $plan))
            ->assertOk()
            ->assertSee('Saved supervisee comments')
            ->assertSee('I reviewed my progress against the agreed targets.');

        $this->actingAs($admin)
            ->get(route('staff.pdp.plans.show', $plan))
            ->assertOk()
            ->assertSee('Saved supervisor comments')
            ->assertSee('The teacher has shown consistent progress in this objective.');
    }

    public function test_performance_objective_review_hides_actual_result_and_preserves_saved_supervisor_comment_selection(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $admin = $this->createUser('phase3-performance-admin@example.com', [
            'firstname' => 'School',
            'lastname' => 'Head',
            'position' => 'School Head',
        ]);
        $supervisor = $this->createUser('phase3-performance-supervisor@example.com', [
            'firstname' => 'Sarah',
            'lastname' => 'Supervisor',
        ]);
        $employee = $this->createUser('phase3-performance-employee@example.com', [
            'firstname' => 'Neo',
            'lastname' => 'Teacher',
            'reporting_to' => $supervisor->id,
        ]);
        $savedSupervisorComment = 'The teacher has shown consistent progress in this objective.';

        app(PdpSettingsService::class)->saveCommentBank([
            'supervisee_comments' => "I reviewed my progress against the agreed targets.",
            'supervisor_comments' => $savedSupervisorComment,
        ]);

        $templateService = app(PdpTemplateService::class);
        $draft = $templateService->createDraftFromBlueprint('school_half_yearly', [
            'template_family_key' => 'staff_pdp_performance_objective_test',
            'code' => 'staff-pdp-performance-objective-test-v1',
            'name' => 'Performance Objective Test v1',
        ], $admin->id);

        $section = $draft->sections->firstWhere('key', 'performance_objectives');
        $objectiveRow = $templateService->createSectionRow($section, [
            'objective_category' => 'Attendance',
            'objective' => 'Improve attendance',
        ]);
        $templateService->createSectionRow($section, [
            'output' => 'Attendance plan implemented',
            'measure' => 'Weekly attendance tracking',
            'target' => '95 percent attendance',
        ], null, $objectiveRow);

        $template = $templateService->publish($draft);
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
            'current_period_key' => 'mid_year',
            'supervisor_id' => $supervisor->id,
        ], $template);

        $entry = $plan->sectionEntries()
            ->where('section_key', 'performance_objectives')
            ->whereNull('parent_entry_id')
            ->firstOrFail();

        $this->actingAs($supervisor)
            ->put(route('staff.pdp.plans.sections.entries.update', [$plan, 'performance_objectives', $entry]), [
                'values' => [
                    'score_out_of_10' => 9,
                    'supervisor_comment' => $savedSupervisorComment,
                ],
            ])
            ->assertRedirect(route('staff.pdp.plans.show', $plan) . '#section-performance_objectives');

        $this->assertSame($savedSupervisorComment, $entry->fresh()->values_json['supervisor_comment']);
        $this->assertEquals(9.0, $entry->fresh()->values_json['score_out_of_10']);

        $this->actingAs($supervisor)
            ->get(route('staff.pdp.plans.show', $plan))
            ->assertOk()
            ->assertSee('Score Out of 10')
            ->assertDontSee('Actual Result')
            ->assertSee('Saved supervisor comments')
            ->assertSee('option value="' . $savedSupervisorComment . '" selected', false);
    }

    public function test_employee_supervisee_comment_selection_persists_and_renders_on_performance_objectives(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $admin = $this->createUser('phase3-supervisee-admin@example.com', [
            'firstname' => 'School',
            'lastname' => 'Head',
            'position' => 'School Head',
        ]);
        $supervisor = $this->createUser('phase3-supervisee-supervisor@example.com', [
            'firstname' => 'Sarah',
            'lastname' => 'Supervisor',
        ]);
        $employee = $this->createUser('phase3-supervisee-employee@example.com', [
            'firstname' => 'Neo',
            'lastname' => 'Teacher',
            'reporting_to' => $supervisor->id,
        ]);
        $savedSuperviseeComment = 'I reviewed my progress against the agreed targets.';

        app(PdpSettingsService::class)->saveCommentBank([
            'supervisee_comments' => $savedSuperviseeComment,
            'supervisor_comments' => 'The teacher has shown consistent progress in this objective.',
        ]);

        $templateService = app(PdpTemplateService::class);
        $draft = $templateService->createDraftFromBlueprint('school_half_yearly', [
            'template_family_key' => 'staff_pdp_supervisee_comment_test',
            'code' => 'staff-pdp-supervisee-comment-test-v1',
            'name' => 'Supervisee Comment Test v1',
        ], $admin->id);

        $section = $draft->sections->firstWhere('key', 'performance_objectives');
        $objectiveRow = $templateService->createSectionRow($section, [
            'objective_category' => 'Attendance',
            'objective' => 'Improve attendance',
        ]);
        $templateService->createSectionRow($section, [
            'output' => 'Attendance plan implemented',
            'measure' => 'Weekly attendance tracking',
            'target' => '95 percent attendance',
        ], null, $objectiveRow);

        $template = $templateService->publish($draft);
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
            'current_period_key' => 'mid_year',
            'supervisor_id' => $supervisor->id,
        ], $template);

        $entry = $plan->sectionEntries()
            ->where('section_key', 'performance_objectives')
            ->whereNull('parent_entry_id')
            ->firstOrFail();

        $this->actingAs($employee)
            ->put(route('staff.pdp.plans.sections.entries.update', [$plan, 'performance_objectives', $entry]), [
                'values' => [
                    'supervisee_comment' => $savedSuperviseeComment,
                ],
            ])
            ->assertRedirect(route('staff.pdp.plans.show', $plan) . '#section-performance_objectives');

        $this->assertSame($savedSuperviseeComment, $entry->fresh()->values_json['supervisee_comment']);

        $this->actingAs($employee)
            ->get(route('staff.pdp.plans.show', $plan))
            ->assertOk()
            ->assertSee('Saved supervisee comments')
            ->assertSee('option value="' . $savedSuperviseeComment . '" selected', false)
            ->assertSee('>' . e($savedSuperviseeComment) . '</textarea>', false);
    }

    public function test_employee_supervisee_comment_can_save_from_comment_bank_selection_even_if_textarea_post_is_blank(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $admin = $this->createUser('phase3-supervisee-bank-admin@example.com', [
            'firstname' => 'School',
            'lastname' => 'Head',
            'position' => 'School Head',
        ]);
        $supervisor = $this->createUser('phase3-supervisee-bank-supervisor@example.com', [
            'firstname' => 'Sarah',
            'lastname' => 'Supervisor',
        ]);
        $employee = $this->createUser('phase3-supervisee-bank-employee@example.com', [
            'firstname' => 'Neo',
            'lastname' => 'Teacher',
            'reporting_to' => $supervisor->id,
        ]);
        $savedSuperviseeComment = 'I reviewed my progress against the agreed targets.';

        app(PdpSettingsService::class)->saveCommentBank([
            'supervisee_comments' => $savedSuperviseeComment,
            'supervisor_comments' => 'The teacher has shown consistent progress in this objective.',
        ]);

        $templateService = app(PdpTemplateService::class);
        $draft = $templateService->createDraftFromBlueprint('school_half_yearly', [
            'template_family_key' => 'staff_pdp_supervisee_comment_bank_fallback_test',
            'code' => 'staff-pdp-supervisee-comment-bank-fallback-test-v1',
            'name' => 'Supervisee Comment Bank Fallback Test v1',
        ], $admin->id);

        $section = $draft->sections->firstWhere('key', 'performance_objectives');
        $objectiveRow = $templateService->createSectionRow($section, [
            'objective_category' => 'Attendance',
            'objective' => 'Improve attendance',
        ]);
        $templateService->createSectionRow($section, [
            'output' => 'Attendance plan implemented',
            'measure' => 'Weekly attendance tracking',
            'target' => '95 percent attendance',
        ], null, $objectiveRow);

        $template = $templateService->publish($draft);
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
            'current_period_key' => 'mid_year',
            'supervisor_id' => $supervisor->id,
        ], $template);

        $entry = $plan->sectionEntries()
            ->where('section_key', 'performance_objectives')
            ->whereNull('parent_entry_id')
            ->firstOrFail();

        $this->actingAs($employee)
            ->put(route('staff.pdp.plans.sections.entries.update', [$plan, 'performance_objectives', $entry]), [
                'values' => [
                    'supervisee_comment' => '',
                ],
                'comment_bank' => [
                    'supervisee_comment' => $savedSuperviseeComment,
                ],
            ])
            ->assertRedirect(route('staff.pdp.plans.show', $plan) . '#section-performance_objectives');

        $this->assertSame($savedSuperviseeComment, $entry->fresh()->values_json['supervisee_comment']);
    }

    public function test_plan_show_explains_when_behavioural_ratings_are_locked(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $supervisor = $this->createUser('phase3-locked-ratings-supervisor@example.com', [
            'firstname' => 'Sarah',
            'lastname' => 'Supervisor',
        ]);
        $employee = $this->createUser('phase3-locked-ratings-employee@example.com', [
            'firstname' => 'Neo',
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

        $this->actingAs($employee)
            ->get(route('staff.pdp.plans.show', $plan))
            ->assertOk()
            ->assertSee('How Ratings Unlock')
            ->assertSee('Ratings in this section only become selectable when the relevant review is opened from the Review Timeline.');
    }

    public function test_plan_show_backfills_missing_part_a_fields_for_legacy_school_template(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        app(PdpSettingsService::class)->saveGeneralSettings([
            'part_a_ministry_department' => 'Secondary',
        ]);

        $admin = $this->createUser('phase3-legacy-part-a-admin@example.com', [
            'firstname' => 'School',
            'lastname' => 'Head',
            'position' => 'School Head',
        ]);
        $supervisor = $this->createUser('phase3-legacy-part-a-supervisor@example.com', [
            'firstname' => 'Neo',
            'lastname' => 'Matshoga',
            'position' => 'HOD',
            'earning_band' => 'D3',
        ]);
        $employee = $this->createUser('phase3-legacy-part-a-employee@example.com', [
            'firstname' => 'Sekano',
            'lastname' => 'William',
            'position' => 'Senior Teacher',
            'reporting_to' => $supervisor->id,
            'personal_payroll_number' => '456818005',
            'dpsm_personal_file_number' => '81716',
            'date_of_appointment' => '1999-01-08',
            'earning_band' => 'D4',
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
                'key' => 'position_title',
                'label' => 'Position Title',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'position',
                'required' => true,
                'sort_order' => 2,
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
                'sort_order' => 3,
            ],
            [
                'key' => 'payroll_no',
                'label' => 'Personal Payroll Number',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'personal_payroll_number',
                'required' => false,
                'sort_order' => 4,
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

        $this->actingAs($admin)
            ->get(route('staff.pdp.plans.show', $plan))
            ->assertOk()
            ->assertSee('DPSM Personal File No')
            ->assertSee('81716')
            ->assertSee('Ministry / Department')
            ->assertSee('Secondary')
            ->assertSee('Supervisor Name')
            ->assertSee('Neo Matshoga')
            ->assertSee('Supervisor Grade')
            ->assertSee('D3');
    }

    public function test_regular_employee_cannot_access_manual_plan_create_flow(): void
    {
        $employee = $this->createUser('phase3-manual-block@example.com');

        $this->actingAs($employee)
            ->get(route('staff.pdp.plans.create'))
            ->assertForbidden();
    }

    public function test_generic_section_entry_crud_uses_section_keys_not_hardcoded_routes(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $admin = $this->createUser('phase3-entry-admin@example.com', [
            'firstname' => 'School',
            'lastname' => 'Head',
            'position' => 'School Head',
        ]);
        $employee = $this->createUser('phase3-entry@example.com');
        $templateDefinition = PdpTemplateBlueprints::blankBounded();
        $templateDefinition['template']['template_family_key'] = 'staff_pdp_crud_test';
        $templateDefinition['template']['code'] = 'staff-pdp-crud-test-v1';
        $templateDefinition['template']['name'] = 'Staff PDP CRUD Test v1';
        $templateDefinition['sections'][] = [
            'key' => 'evidence_log',
            'label' => 'Evidence Log',
            'section_type' => 'repeatable_notes',
            'sequence' => 7,
            'is_repeatable' => true,
            'fields' => [
                [
                    'key' => 'log_item',
                    'label' => 'Log Item',
                    'field_type' => 'textarea',
                    'data_type' => 'string',
                    'input_mode' => 'manual_entry',
                    'required' => true,
                    'sort_order' => 1,
                ],
                [
                    'key' => 'follow_up',
                    'label' => 'Follow Up',
                    'field_type' => 'comment',
                    'data_type' => 'string',
                    'input_mode' => 'manual_entry',
                    'sort_order' => 2,
                ],
            ],
        ];

        $templateService = app(PdpTemplateService::class);
        $template = $templateService->publish($templateService->createDraftFromDefinition($templateDefinition));
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
        ], $template);

        $storeResponse = $this->actingAs($admin)
            ->post(route('staff.pdp.plans.sections.entries.store', [$plan, 'evidence_log']), [
                'values' => [
                    'log_item' => 'Weekly planning review completed',
                    'follow_up' => 'Prepare the next classroom observation',
                ],
            ]);

        $entry = $plan->sectionEntries()
            ->where('section_key', 'evidence_log')
            ->latest('id')
            ->firstOrFail();

        $storeResponse
            ->assertRedirect(route('staff.pdp.plans.show', $plan) . '#section-evidence_log');

        $this->assertSame('Weekly planning review completed', $entry->values_json['log_item']);

        $this->actingAs($admin)
            ->put(route('staff.pdp.plans.sections.entries.update', [$plan, 'evidence_log', $entry]), [
                'values' => [
                    'log_item' => 'Weekly planning review completed',
                    'follow_up' => 'Prepare the next coaching checkpoint',
                ],
            ])
            ->assertRedirect(route('staff.pdp.plans.show', $plan) . '#section-evidence_log');

        $this->assertDatabaseHas('pdp_plan_section_entries', [
            'id' => $entry->id,
            'section_key' => 'evidence_log',
        ]);
        $this->assertSame('Prepare the next coaching checkpoint', $entry->fresh()->values_json['follow_up']);

        $this->actingAs($admin)
            ->delete(route('staff.pdp.plans.sections.entries.destroy', [$plan, 'evidence_log', $entry]))
            ->assertRedirect(route('staff.pdp.plans.show', $plan) . '#section-evidence_log');

        $this->assertDatabaseMissing('pdp_plan_section_entries', [
            'id' => $entry->id,
        ]);
    }

    public function test_performance_objectives_do_not_allow_custom_plan_entries(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $admin = $this->createUser('phase3-locked-admin@example.com', [
            'firstname' => 'School',
            'lastname' => 'Head',
            'position' => 'School Head',
        ]);
        $employee = $this->createUser('phase3-locked@example.com');
        $template = app(PdpTemplateService::class)->seedDefaults()['school'];
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
        ], $template);

        $response = $this->actingAs($admin)
            ->post(route('staff.pdp.plans.sections.entries.store', [$plan, 'performance_objectives']), [
                'values' => [
                    'objective_category' => 'Attendance',
                    'objective' => 'Blocked objective',
                    'output' => 'Blocked output',
                    'measure' => 'Blocked measure',
                    'target' => 'Blocked target',
                ],
            ]);

        $response
            ->assertRedirect(route('staff.pdp.plans.show', $plan) . '#section-performance_objectives')
            ->assertSessionHasErrors('pdp');
    }

    public function test_my_pdp_view_lists_only_the_authenticated_users_plans(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $template = app(PdpTemplateService::class)->seedDefaults()['school'];
        $employee = $this->createUser('phase3-my@example.com', [
            'firstname' => 'Neo',
            'lastname' => 'Owner',
        ]);
        $other = $this->createUser('phase3-other@example.com', [
            'firstname' => 'Mpho',
            'lastname' => 'Other',
        ]);

        app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
        ], $template);

        app(PdpPlanService::class)->createPlan($other, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
        ], $template);

        $this->actingAs($employee)
            ->get(route('staff.pdp.my'))
            ->assertOk()
            ->assertSee('Neo Owner')
            ->assertDontSee('Mpho Other');
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
