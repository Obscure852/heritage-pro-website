<?php

namespace Tests\Feature\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpRollout;
use App\Models\Pdp\PdpTemplate;
use App\Models\User;
use App\Services\Pdp\PdpPlanService;
use App\Services\Pdp\PdpTemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\EnsuresPdpPhaseTwoSchema;
use Tests\TestCase;

class PdpTemplateAdminTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseTwoSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePdpPhaseTwoSchema();
    }

    public function test_school_head_can_create_publish_activate_and_clone_template_versions(): void
    {
        $admin = $this->createUser('template-admin@example.com', ['position' => 'School Head', 'status' => 'Former']);
        app(PdpTemplateService::class)->seedDefaults();

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.store'), [
                'baseline_key' => 'school_half_yearly',
                'template_family_key' => 'staff_pdp_school_custom',
                'code' => 'staff-pdp-school-custom-v1',
                'name' => 'Staff PDP School Custom v1',
                'description' => 'Custom draft for testing.',
                'source_reference' => 'Custom source',
            ])
            ->assertRedirect();

        $draft = PdpTemplate::query()->where('code', 'staff-pdp-school-custom-v1')->firstOrFail();
        $this->assertSame(PdpTemplate::STATUS_DRAFT, $draft->status);

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.publish', $draft))
            ->assertRedirect(route('staff.pdp.templates.show', $draft));

        $this->assertSame(PdpTemplate::STATUS_PUBLISHED, $draft->fresh()->status);

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.activate', $draft->fresh()), [
                'label' => 'Custom 2026 Cycle',
                'cycle_year' => 2026,
                'plan_period_start' => '2026-01-01',
                'plan_period_end' => '2026-12-31',
                'auto_provision_new_staff' => 1,
                'fallback_supervisor_user_id' => $admin->id,
            ])
            ->assertRedirect(route('staff.pdp.templates.show', $draft));

        $this->assertTrue($draft->fresh()->is_default);
        $this->assertSame(1, PdpRollout::query()->where('pdp_template_id', $draft->id)->count());
        $this->assertSame(0, PdpPlan::query()->where('pdp_template_id', $draft->id)->count());

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.clone', $draft->fresh()))
            ->assertRedirect();

        $clone = PdpTemplate::query()
            ->where('template_family_key', 'staff_pdp_school_custom')
            ->where('version', 2)
            ->firstOrFail();

        $this->assertSame(PdpTemplate::STATUS_DRAFT, $clone->status);
        $this->assertSame('staff-pdp-school-custom-v2', $clone->code);
    }

    public function test_school_head_can_archive_a_non_default_published_template(): void
    {
        $admin = $this->createUser('template-archive@example.com', ['position' => 'School Head']);
        $templates = app(PdpTemplateService::class)->seedDefaults();
        $official = $templates['official'];

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.archive', $official))
            ->assertRedirect(route('staff.pdp.templates.show', $official));

        $this->assertSame(PdpTemplate::STATUS_ARCHIVED, $official->fresh()->status);
    }

    public function test_regular_employee_cannot_access_template_admin_routes(): void
    {
        $employee = $this->createUser('template-employee@example.com');

        $this->actingAs($employee)
            ->get(route('staff.pdp.templates.index'))
            ->assertForbidden();
    }

    public function test_draft_template_can_manage_shared_objective_rows_and_clone_them_forward(): void
    {
        $admin = $this->createUser('template-rows-admin@example.com', ['position' => 'School Head']);

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.store'), [
                'baseline_key' => 'school_half_yearly',
                'template_family_key' => 'staff_pdp_school_objectives',
                'code' => 'staff-pdp-school-objectives-v1',
                'name' => 'Staff PDP School Objectives v1',
                'description' => 'Template row test.',
                'source_reference' => 'Custom source',
            ])
            ->assertRedirect();

        $draft = PdpTemplate::query()->where('code', 'staff-pdp-school-objectives-v1')->firstOrFail()->load('sections.rows.childRows');
        $section = $draft->sections->firstWhere('key', 'performance_objectives');

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.sections.rows.store', [$draft, $section]), [
                'values' => [
                    'objective_category' => 'Attendance',
                    'objective' => 'Improve attendance',
                ],
            ])
            ->assertRedirect(route('staff.pdp.templates.show', $draft) . '#section-performance_objectives');

        $objectiveRow = $section->fresh('rows')->rows()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.sections.rows.store', [$draft, $section]), [
                'parent_row_id' => $objectiveRow->id,
                'values' => [
                    'output' => 'Attendance action plan delivered',
                    'measure' => 'Weekly register review',
                    'target' => '95 percent attendance',
                ],
            ])
            ->assertRedirect(route('staff.pdp.templates.show', $draft) . '#section-performance_objectives');

        $this->assertSame(1, $section->fresh()->rows()->count());
        $this->assertSame(1, $objectiveRow->fresh('childRows')->childRows->count());

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.clone', $draft->fresh()))
            ->assertRedirect();

        $clone = PdpTemplate::query()
            ->where('template_family_key', 'staff_pdp_school_objectives')
            ->where('version', 2)
            ->firstOrFail()
            ->load('sections.rows.childRows');

        $cloneSection = $clone->sections->firstWhere('key', 'performance_objectives');
        $this->assertSame(1, $cloneSection->rows->count());
        $this->assertSame('Attendance', $cloneSection->rows->first()->values_json['objective_category']);
        $this->assertSame('Improve attendance', $cloneSection->rows->first()->values_json['objective']);
        $this->assertSame('95 percent attendance', $cloneSection->rows->first()->childRows->first()?->values_json['target']);
    }

    public function test_draft_template_can_update_performance_objective_categories(): void
    {
        $admin = $this->createUser('template-categories-admin@example.com', ['position' => 'School Head']);

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.store'), [
                'baseline_key' => 'blank_bounded',
                'template_family_key' => 'staff_pdp_builder_categories',
                'code' => 'staff-pdp-builder-categories-v1',
                'name' => 'Builder Categories v1',
                'description' => 'Category builder test.',
                'source_reference' => 'Builder source',
            ])
            ->assertRedirect();

        $draft = PdpTemplate::query()->where('code', 'staff-pdp-builder-categories-v1')->firstOrFail()->load('sections.fields');
        $section = $draft->sections->firstWhere('key', 'performance_objectives');

        $this->actingAs($admin)
            ->put(route('staff.pdp.templates.sections.builder.update', [$draft, $section]), [
                'category_options' => "Attendance\nAcademic Performance\nStakeholder Involvement\nSchool Culture",
            ])
            ->assertRedirect(route('staff.pdp.templates.show', $draft) . '#section-performance_objectives');

        $categoryField = $section->fresh('fields')->fields->firstWhere('key', 'objective_category');
        $this->assertSame(
            ['Attendance', 'Academic Performance', 'Stakeholder Involvement', 'School Culture'],
            collect($categoryField?->options_json ?? [])->pluck('value')->all()
        );
    }

    public function test_template_show_groups_performance_objectives_under_their_categories(): void
    {
        $admin = $this->createUser('template-grouping-admin@example.com', ['position' => 'School Head']);

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.store'), [
                'baseline_key' => 'blank_bounded',
                'template_family_key' => 'staff_pdp_category_grouping',
                'code' => 'staff-pdp-category-grouping-v1',
                'name' => 'Category Grouping v1',
                'description' => 'Grouping test.',
                'source_reference' => 'Grouping source',
            ])
            ->assertRedirect();

        $draft = PdpTemplate::query()->where('code', 'staff-pdp-category-grouping-v1')->firstOrFail()->load('sections.rows.childRows');
        $section = $draft->sections->firstWhere('key', 'performance_objectives');

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.sections.rows.store', [$draft, $section]), [
                'values' => [
                    'objective_category' => 'Attendance',
                    'objective' => 'Improve attendance',
                ],
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.sections.rows.store', [$draft, $section]), [
                'values' => [
                    'objective_category' => 'Academic Performance',
                    'objective' => 'Raise pass rate',
                ],
            ])
            ->assertRedirect();

        $response = $this->actingAs($admin)->get(route('staff.pdp.templates.show', $draft->fresh()));
        $response->assertOk();

        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/data-category-key="Attendance".*?Improve attendance/s', $html);
        $this->assertMatchesRegularExpression('/data-category-key="Academic Performance".*?Raise pass rate/s', $html);
    }

    public function test_template_show_scopes_old_input_to_the_matching_objective_form_only(): void
    {
        $admin = $this->createUser('template-old-input-admin@example.com', ['position' => 'School Head']);

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.store'), [
                'baseline_key' => 'blank_bounded',
                'template_family_key' => 'staff_pdp_old_input_scope',
                'code' => 'staff-pdp-old-input-scope-v1',
                'name' => 'Old Input Scope v1',
                'description' => 'Old input scope test.',
                'source_reference' => 'Scope source',
            ])
            ->assertRedirect();

        $draft = PdpTemplate::query()->where('code', 'staff-pdp-old-input-scope-v1')->firstOrFail()->load('sections.rows.childRows');
        $section = $draft->sections->firstWhere('key', 'performance_objectives');

        app(PdpTemplateService::class)->createSectionRow($section, [
            'objective_category' => 'Attendance',
            'objective' => 'Attendance baseline objective',
        ]);

        app(PdpTemplateService::class)->createSectionRow($section, [
            'objective_category' => 'Academic Performance',
            'objective' => 'Academic baseline objective',
        ]);

        $rows = $section->fresh('rows')->rows->values();
        $attendanceRow = $rows->firstWhere('values_json.objective', 'Attendance baseline objective');
        $academicRow = $rows->firstWhere('values_json.objective', 'Academic baseline objective');

        $response = $this->actingAs($admin)
            ->withSession([
                '_old_input' => [
                    '_template_builder_scope' => 'objective-row-' . $attendanceRow->id,
                    'values' => [
                        'objective_category' => 'Attendance',
                        'objective' => 'Attendance retry objective',
                    ],
                    'sort_order' => 7,
                ],
            ])
            ->get(route('staff.pdp.templates.show', $draft->fresh()));

        $response->assertOk();

        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/id="row-' . $attendanceRow->id . '-objective"[^>]*value="Attendance retry objective"/s', $html);
        $this->assertMatchesRegularExpression('/id="row-' . $academicRow->id . '-objective"[^>]*value="Academic baseline objective"/s', $html);
    }

    public function test_draft_template_builder_can_update_metadata_sections_mappings_periods_ratings_and_approvals(): void
    {
        $admin = $this->createUser('template-builder-admin@example.com', ['position' => 'School Head']);

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.store'), [
                'baseline_key' => 'blank_bounded',
                'template_family_key' => 'staff_pdp_blank_builder',
                'code' => 'staff-pdp-blank-builder-v1',
                'name' => 'Blank Builder v1',
                'description' => 'Blank builder test.',
                'source_reference' => 'N/A',
            ])
            ->assertRedirect();

        $draft = PdpTemplate::query()->where('code', 'staff-pdp-blank-builder-v1')->firstOrFail()->load([
            'sections.fields',
            'periods',
            'ratingSchemes',
            'approvalSteps',
        ]);

        $employeeSection = $draft->sections->firstWhere('key', 'employee_information');
        $period = $draft->periods->firstOrFail();
        $scheme = $draft->ratingSchemes->firstWhere('key', 'performance_percentage');
        $step = $draft->approvalSteps->firstWhere('key', 'employee_signoff');

        $this->actingAs($admin)
            ->put(route('staff.pdp.templates.update', $draft), [
                'template_family_key' => 'staff_pdp_blank_builder',
                'code' => 'staff-pdp-blank-builder-v1',
                'name' => 'Blank Builder Updated v1',
                'description' => 'Updated description.',
                'source_reference' => 'Builder source',
            ])
            ->assertRedirect(route('staff.pdp.templates.show', $draft));

        $this->actingAs($admin)
            ->put(route('staff.pdp.templates.sections.update', $draft), [
                'sections' => $draft->sections->mapWithKeys(fn ($section) => [
                    $section->id => [
                        'label' => $section->key === 'coaching' ? 'Part C: Development Coaching' : $section->label,
                        'sequence' => $section->sequence,
                        'min_items' => $section->min_items,
                        'max_items' => $section->max_items,
                    ],
                ])->all(),
            ])
            ->assertRedirect(route('staff.pdp.templates.show', $draft));

        $this->actingAs($admin)
            ->put(route('staff.pdp.templates.employee-information.update', $draft), [
                'fields' => $employeeSection->fields
                    ->whereNull('parent_field_id')
                    ->mapWithKeys(fn ($field) => [
                        $field->id => [
                            'label' => $field->key === 'employee_name' ? 'Staff Member' : $field->label,
                            'mapping_source' => $field->mapping_source,
                            'mapping_key' => $field->mapping_key,
                            'required' => $field->required ? 1 : 0,
                            'sort_order' => $field->sort_order,
                        ],
                    ])->all(),
            ])
            ->assertRedirect(route('staff.pdp.templates.show', $draft));

        $this->actingAs($admin)
            ->put(route('staff.pdp.templates.periods.update', $draft), [
                'periods' => [
                    $period->id => [
                        'key' => 'annual_review',
                        'label' => 'Annual Review',
                        'sequence' => 1,
                        'window_type' => 'configured_dates',
                        'summary_label' => 'Annual Remarks',
                        'include_in_final_score' => 1,
                        'due_rule_json' => '',
                        'open_rule_json' => json_encode(['start_offset_days' => 0]),
                        'close_rule_json' => json_encode(['end_offset_days' => 365]),
                    ],
                ],
            ])
            ->assertRedirect(route('staff.pdp.templates.show', $draft));

        $this->actingAs($admin)
            ->put(route('staff.pdp.templates.ratings.update', $draft), [
                'schemes' => [
                    $scheme->id => [
                        'label' => 'Objective Score Ten Point',
                        'input_type' => 'intensity_scale',
                        'weight' => 0.75,
                        'rounding_rule' => 'round_1',
                        'scale_config_json' => json_encode(['min' => 0, 'max' => 10]),
                        'conversion_config_json' => json_encode(['type' => 'rating_to_percentage']),
                        'formula_config_json' => json_encode(['type' => 'average_then_weight']),
                        'band_config_json' => '',
                    ],
                ] + $draft->ratingSchemes
                    ->reject(fn ($ratingScheme) => $ratingScheme->id === $scheme->id)
                    ->mapWithKeys(fn ($ratingScheme) => [
                        $ratingScheme->id => [
                            'label' => $ratingScheme->label,
                            'input_type' => $ratingScheme->input_type,
                            'weight' => $ratingScheme->weight,
                            'rounding_rule' => $ratingScheme->rounding_rule,
                            'scale_config_json' => $ratingScheme->scale_config_json ? json_encode($ratingScheme->scale_config_json) : '',
                            'conversion_config_json' => $ratingScheme->conversion_config_json ? json_encode($ratingScheme->conversion_config_json) : '',
                            'formula_config_json' => $ratingScheme->formula_config_json ? json_encode($ratingScheme->formula_config_json) : '',
                            'band_config_json' => $ratingScheme->band_config_json ? json_encode($ratingScheme->band_config_json) : '',
                        ],
                    ])->all(),
            ])
            ->assertRedirect(route('staff.pdp.templates.show', $draft));

        $this->actingAs($admin)
            ->put(route('staff.pdp.templates.approvals.update', $draft), [
                'steps' => $draft->approvalSteps->mapWithKeys(fn ($approvalStep) => [
                    $approvalStep->id => [
                        'key' => $approvalStep->key,
                        'label' => $approvalStep->id === $step->id ? 'Employee Acknowledgement' : $approvalStep->label,
                        'sequence' => $approvalStep->sequence,
                        'role_type' => $approvalStep->role_type,
                        'required' => $approvalStep->required ? 1 : 0,
                        'comment_required' => $approvalStep->comment_required ? 1 : 0,
                        'period_scope' => $approvalStep->period_scope,
                    ],
                ])->all(),
            ])
            ->assertRedirect(route('staff.pdp.templates.show', $draft));

        $draft = $draft->fresh(['sections.fields', 'periods', 'ratingSchemes', 'approvalSteps']);
        $this->assertSame('Blank Builder Updated v1', $draft->name);
        $this->assertSame('Part C: Development Coaching', $draft->sections->firstWhere('key', 'coaching')?->label);
        $this->assertSame('Staff Member', $draft->sections->firstWhere('key', 'employee_information')?->fields->whereNull('parent_field_id')->firstWhere('key', 'employee_name')?->label);
        $this->assertSame('annual_review', $draft->periods->first()?->key);
        $this->assertSame('Objective Score Ten Point', $draft->ratingSchemes->firstWhere('key', 'performance_percentage')?->label);
        $this->assertSame('Employee Acknowledgement', $draft->approvalSteps->firstWhere('key', 'employee_signoff')?->label);
    }

    public function test_used_template_delete_requires_confirmation_and_then_hard_deletes_related_records(): void
    {
        $admin = $this->createUser('template-delete-admin@example.com', ['position' => 'School Head', 'status' => 'Former']);
        $employee = $this->createUser('template-delete-employee@example.com');

        $this->actingAs($admin)
            ->post(route('staff.pdp.templates.store'), [
                'baseline_key' => 'school_half_yearly',
                'template_family_key' => 'staff_pdp_delete_family',
                'code' => 'staff-pdp-delete-family-v1',
                'name' => 'Delete Family v1',
                'description' => 'Delete test.',
                'source_reference' => 'Delete source',
            ])
            ->assertRedirect();

        $draft = PdpTemplate::query()->where('code', 'staff-pdp-delete-family-v1')->firstOrFail()->load('sections');
        $section = $draft->sections->firstWhere('key', 'performance_objectives');
        $objectiveRow = app(PdpTemplateService::class)->createSectionRow($section, [
            'objective_category' => 'Attendance',
            'objective' => 'Delete objective',
        ]);
        app(PdpTemplateService::class)->createSectionRow($section, [
            'output' => 'Delete output',
            'measure' => 'Delete measure',
            'target' => 'Delete target',
        ], null, $objectiveRow);

        $published = app(PdpTemplateService::class)->publish($draft);
        app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
        ], $published);

        $this->actingAs($admin)
            ->delete(route('staff.pdp.templates.destroy', $published))
            ->assertRedirect(route('staff.pdp.templates.show', ['template' => $published, 'confirm_delete' => 1]));

        $this->actingAs($admin)
            ->delete(route('staff.pdp.templates.destroy', $published), [
                'confirm_delete' => 1,
            ])
            ->assertRedirect(route('staff.pdp.settings.index', ['tab' => 'templates']));

        $this->assertDatabaseMissing('pdp_templates', ['id' => $published->id]);
        $this->assertDatabaseMissing('pdp_plans', ['pdp_template_id' => $published->id]);
        $this->assertDatabaseMissing('pdp_rollouts', ['pdp_template_id' => $published->id]);
    }

    public function test_show_upgrades_legacy_draft_sections_into_the_full_template_builder(): void
    {
        $admin = $this->createUser('template-upgrade-admin@example.com', ['position' => 'School Head']);

        $draft = app(PdpTemplateService::class)->createDraftFromBlueprint('school_half_yearly', [
            'template_family_key' => 'staff_pdp_legacy_upgrade',
            'code' => 'staff-pdp-legacy-upgrade-v1',
            'name' => 'Legacy Upgrade v1',
        ]);

        $coaching = $draft->sections()->where('key', 'coaching')->firstOrFail();
        $behavioural = $draft->sections()->where('key', 'behavioural_attributes')->firstOrFail();
        $goals = $draft->sections()->where('key', 'personal_development_goals')->firstOrFail();

        $coaching->update(['layout_config_json' => null]);
        $goals->update(['layout_config_json' => null]);
        $behavioural->rows()->delete();
        $behavioural->update([
            'layout_config_json' => [
                'seed_rows' => [
                    [
                        'attribute_name' => 'Legacy Attribute',
                        'description' => 'Legacy behavioural definition',
                        'applicable' => true,
                    ],
                ],
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('staff.pdp.templates.show', $draft))
            ->assertOk()
            ->assertSee('id="section-coaching"', false)
            ->assertSee('id="section-behavioural_attributes"', false)
            ->assertSee('id="section-personal_development_goals"', false);

        $draft = $draft->fresh(['sections.rows']);

        $this->assertTrue($draft->sections->firstWhere('key', 'coaching')->usesTemplateRows());
        $this->assertTrue($draft->sections->firstWhere('key', 'personal_development_goals')->usesTemplateRows());
        $behaviouralSection = $draft->sections->firstWhere('key', 'behavioural_attributes');
        $this->assertTrue($behaviouralSection->usesTemplateRows());
        $this->assertSame('Legacy Attribute', $behaviouralSection->rows->first()?->values_json['attribute_name']);
        $this->assertNotNull($draft->sections->firstWhere('key', 'performance_objectives')?->fields->whereNull('parent_field_id')->firstWhere('key', 'objective_category'));
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
