<?php

namespace Tests\Feature\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\SchoolSetup;
use App\Models\User;
use App\Services\Pdp\PdpPlanService;
use App\Services\Pdp\PdpTemplateBlueprints;
use App\Services\Pdp\PdpTemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\EnsuresPdpPhaseTwoSchema;
use Tests\TestCase;

class PdpAuthorizationTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseTwoSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePdpPhaseTwoSchema();
    }

    public function test_employee_cannot_open_reviews_but_supervisor_can(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $supervisor = $this->createUser('auth-supervisor@example.com');
        $employee = $this->createUser('auth-employee@example.com', ['reporting_to' => $supervisor->id]);

        $plan = $this->createPlanFor($employee, $supervisor);
        $plan->reviews()->where('period_key', 'mid_year')->update(['status' => 'pending', 'opened_at' => null]);
        $plan->update(['current_period_key' => 'mid_year', 'status' => PdpPlan::STATUS_DRAFT]);

        $this->actingAs($employee)
            ->post(route('staff.pdp.plans.reviews.open', [$plan, 'mid_year']))
            ->assertForbidden();

        $this->actingAs($supervisor)
            ->post(route('staff.pdp.plans.reviews.open', [$plan, 'mid_year']))
            ->assertRedirect(route('staff.pdp.plans.show', $plan));
    }

    public function test_unrelated_user_cannot_manage_plan_entries(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $supervisor = $this->createUser('auth2-supervisor@example.com');
        $employee = $this->createUser('auth2-employee@example.com', ['reporting_to' => $supervisor->id]);
        $other = $this->createUser('auth2-other@example.com');

        $plan = $this->createPlanFor($employee, $supervisor);

        $this->actingAs($other)
            ->post(route('staff.pdp.plans.sections.entries.store', [$plan, 'performance_objectives']), [
                'values' => [
                    'objective_category' => 'Attendance',
                    'objective' => 'Blocked objective',
                    'output' => 'Blocked output',
                    'measure' => 'Blocked measure',
                    'target' => 'Blocked target',
                ],
            ])
            ->assertForbidden();
    }

    public function test_closed_review_locks_period_scoped_supervisor_edits(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $supervisor = $this->createUser('auth3-supervisor@example.com');
        $employee = $this->createUser('auth3-employee@example.com', ['reporting_to' => $supervisor->id]);

        $plan = $this->createPlanFor($employee, $supervisor);

        $entry = $plan->sectionEntries()
            ->where('section_key', 'behavioural_attributes')
            ->orderBy('id')
            ->firstOrFail();

        $plan->reviews()->where('period_key', 'mid_year')->update([
            'status' => 'closed',
            'opened_at' => now(),
            'closed_at' => now(),
            'score_summary_json' => ['total_score' => 80],
        ]);

        $this->actingAs($supervisor)
            ->put(route('staff.pdp.plans.sections.entries.update', [$plan, 'behavioural_attributes', $entry]), [
                'values' => [
                    'mid_year_rating' => 5,
                ],
            ])
            ->assertRedirect(route('staff.pdp.plans.show', $plan) . '#section-behavioural_attributes');

        $this->assertNull($entry->fresh()->values_json['mid_year_rating'] ?? null);
    }

    public function test_template_snapshot_objective_fields_are_locked_but_supervisee_comment_remains_editable(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $supervisor = $this->createUser('auth4-supervisor@example.com');
        $employee = $this->createUser('auth4-employee@example.com', ['reporting_to' => $supervisor->id]);
        $admin = $this->createUser('auth4-admin@example.com', ['position' => 'School Head']);

        $draft = app(PdpTemplateService::class)->createDraftFromBlueprint('school_half_yearly', [
            'template_family_key' => 'staff_pdp_school_locked_objectives',
            'code' => 'staff-pdp-school-locked-objectives-v1',
            'name' => 'Locked Objectives v1',
        ], $admin->id);

        $section = $draft->sections->firstWhere('key', 'performance_objectives');
        $objectiveRow = app(PdpTemplateService::class)->createSectionRow($section, [
            'objective_category' => 'Attendance',
            'objective' => 'Improve attendance',
        ]);
        app(PdpTemplateService::class)->createSectionRow($section, [
            'output' => 'Attendance plan implemented',
            'measure' => 'Weekly attendance tracking',
            'target' => '95 percent attendance',
        ], null, $objectiveRow);

        $template = app(PdpTemplateService::class)->publish($draft);
        $plan = app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
        ], $template);

        $entry = $plan->sectionEntries()->where('section_key', 'performance_objectives')->firstOrFail();

        $this->actingAs($employee)
            ->put(route('staff.pdp.plans.sections.entries.update', [$plan, 'performance_objectives', $entry]), [
                'values' => [
                    'objective_category' => 'Academic Performance',
                    'objective' => 'Changed objective',
                    'score_out_of_10' => 10,
                    'supervisee_comment' => 'Employee acknowledgement recorded.',
                ],
            ])
            ->assertRedirect(route('staff.pdp.plans.show', $plan) . '#section-performance_objectives');

        $entry = $entry->fresh();
        $this->assertSame('Attendance', $entry->values_json['objective_category']);
        $this->assertSame('Improve attendance', $entry->values_json['objective']);
        $this->assertSame('Employee acknowledgement recorded.', $entry->values_json['supervisee_comment']);
        $this->assertNull($entry->values_json['score_out_of_10'] ?? null);
    }

    public function test_create_entry_ignores_read_only_supervisor_comment_values_from_employee_requests(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        $supervisor = $this->createUser('auth5-supervisor@example.com');
        $employee = $this->createUser('auth5-employee@example.com', ['reporting_to' => $supervisor->id]);

        $templateDefinition = PdpTemplateBlueprints::blankBounded();
        $templateDefinition['template']['template_family_key'] = 'staff_pdp_read_only_comment_guard';
        $templateDefinition['template']['code'] = 'staff-pdp-read-only-comment-guard-v1';
        $templateDefinition['template']['name'] = 'Read Only Comment Guard v1';
        $templateDefinition['sections'][] = [
            'key' => 'progress_notes',
            'label' => 'Progress Notes',
            'section_type' => 'repeatable_notes',
            'sequence' => 8,
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
            'supervisor_id' => $supervisor->id,
        ], $template);

        $this->actingAs($employee)
            ->post(route('staff.pdp.plans.sections.entries.store', [$plan, 'progress_notes']), [
                'values' => [
                    'log_item' => 'Weekly reflection logged.',
                    'supervisor_comment' => 'This should not be accepted from the employee form.',
                ],
            ])
            ->assertRedirect(route('staff.pdp.plans.show', $plan) . '#section-progress_notes');

        $entry = $plan->sectionEntries()
            ->where('section_key', 'progress_notes')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('Weekly reflection logged.', $entry->values_json['log_item']);
        $this->assertNull($entry->values_json['supervisor_comment'] ?? null);
    }

    private function createPlanFor(User $employee, User $supervisor): PdpPlan
    {
        $template = app(PdpTemplateService::class)->seedDefaults()['school'];

        return app(PdpPlanService::class)->createPlan($employee, [
            'plan_period_start' => '2026-01-01',
            'plan_period_end' => '2026-12-31',
            'status' => PdpPlan::STATUS_ACTIVE,
            'current_period_key' => 'mid_year',
            'supervisor_id' => $supervisor->id,
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
