<?php

namespace Tests\Feature\Pdp;

use App\Models\SchoolSetup;
use App\Models\User;
use App\Services\Pdp\PdpSettingsService;
use App\Services\Pdp\PdpTemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\EnsuresStaffProfileSchema;
use Tests\TestCase;

class PdpSettingsPageTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresStaffProfileSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureStaffProfileSchema();
    }

    public function test_settings_page_renders_all_tabs_and_active_template_summary(): void
    {
        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        app(PdpTemplateService::class)->seedDefaults();
        $admin = $this->createUser('settings-admin@example.com', ['position' => 'School Head']);

        $this->actingAs($admin)
            ->get(route('staff.pdp.settings.index', ['tab' => 'workflow']))
            ->assertOk()
            ->assertSee('PDP Settings')
            ->assertSee('Templates')
            ->assertSee('Rollouts')
            ->assertSee('General')
            ->assertSee('Canned Comments')
            ->assertSee('Review Periods')
            ->assertSee('Scoring &amp; Ratings', false)
            ->assertSee('Approvals &amp; Signatures', false)
            ->assertSee('PDP Workflow')
            ->assertSee('Template Activation and Rollout Binding');

        $this->actingAs($admin)
            ->get(route('staff.pdp.settings.index', ['tab' => 'general']))
            ->assertOk()
            ->assertSee('General Guidance')
            ->assertSee('Part A Ministry / Department');
    }

    public function test_templates_entrypoint_redirects_to_settings_templates_tab(): void
    {
        $admin = $this->createUser('template-entry@example.com', ['position' => 'School Head']);

        $this->actingAs($admin)
            ->get(route('staff.pdp.templates.index'))
            ->assertRedirect(route('staff.pdp.settings.index', ['tab' => 'templates']));
    }

    public function test_general_access_and_comment_settings_can_be_updated_from_settings_page(): void
    {
        $admin = $this->createUser('settings-save@example.com', ['position' => 'School Head']);
        $service = app(PdpSettingsService::class);

        $this->actingAs($admin)
            ->post(route('staff.pdp.settings.update', 'general'), [
                'active_template_support_label' => 'PDP Support',
                'active_template_support_contact' => 'hr@example.com',
                'active_template_support_note' => 'Use the active template for changes.',
                'part_a_ministry_department' => 'Secondary',
                'general_guidance' => "Use the PDP for the agreed objectives.\nDiscuss the review with the appraisee.",
                'default_plan_start_month' => 2,
                'default_plan_start_day' => 10,
                'default_plan_end_month' => 11,
                'default_plan_end_day' => 25,
            ])
            ->assertRedirect(route('staff.pdp.settings.index', ['tab' => 'general']));

        $this->actingAs($admin)
            ->post(route('staff.pdp.settings.update', 'approvals-signatures'), [
                'elevated_positions' => "School Head\nDeputy School Head",
                'elevated_roles' => "Administrator\nHR Admin",
            ])
            ->assertRedirect(route('staff.pdp.settings.index', ['tab' => 'approvals-signatures']));

        $this->actingAs($admin)
            ->post(route('staff.pdp.settings.update', 'comments-bank'), [
                'supervisee_comments' => "I reviewed my own progress.\nI need more time to embed this practice.",
                'supervisor_comments' => "The teacher has demonstrated good progress.\nFurther evidence is still required.",
            ])
            ->assertRedirect(route('staff.pdp.settings.index', ['tab' => 'comments-bank']));

        $this->assertSame('PDP Support', $service->generalSettings()['active_template_support_label']);
        $this->assertSame('Secondary', $service->generalSettings()['part_a_ministry_department']);
        $this->assertSame(
            "Use the PDP for the agreed objectives.\nDiscuss the review with the appraisee.",
            $service->generalSettings()['general_guidance']
        );
        $this->assertSame(['School Head', 'Deputy School Head'], $service->accessSettings()['elevated_positions']);
        $this->assertSame([
            'I reviewed my own progress.',
            'I need more time to embed this practice.',
        ], $service->commentBank()['supervisee_comments']);
        $this->assertSame([
            'The teacher has demonstrated good progress.',
            'Further evidence is still required.',
        ], $service->commentBank()['supervisor_comments']);
    }

    private function createUser(string $email, array $overrides = []): User
    {
        return User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'Settings',
            'lastname' => 'Admin',
            'email' => $email,
            'password' => 'secret',
            'status' => 'Current',
            'position' => 'Teacher',
            'year' => 2026,
        ], $overrides)));
    }
}
