<?php

namespace Tests\Unit\Pdp;

use App\Services\Pdp\PdpSettingsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\EnsuresPdpPhaseOneSchema;
use Tests\TestCase;

class PdpSettingsServiceTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePdpPhaseOneSchema();
    }

    public function test_settings_service_can_store_and_retrieve_json_values(): void
    {
        $service = app(PdpSettingsService::class);

        $service->set('templates.active_template_id', 123);
        $service->set('branding.defaults', [
            'show_logo' => true,
            'title' => 'PDP',
        ]);

        $this->assertSame(123, $service->get('templates.active_template_id'));
        $this->assertSame(
            ['show_logo' => true, 'title' => 'PDP'],
            $service->get('branding.defaults')
        );
    }

    public function test_settings_service_can_forget_values(): void
    {
        $service = app(PdpSettingsService::class);

        $service->set('templates.active_template_code', 'staff-pdp-school-v1');
        $this->assertSame('staff-pdp-school-v1', $service->get('templates.active_template_code'));

        $service->forget('templates.active_template_code');
        $this->assertNull($service->get('templates.active_template_code'));
    }

    public function test_general_and_access_settings_return_normalized_defaults_and_saved_values(): void
    {
        $service = app(PdpSettingsService::class);

        $service->saveGeneralSettings([
            'active_template_support_label' => 'PDP Admin',
            'active_template_support_contact' => 'hr@example.com',
            'active_template_support_note' => 'Configured centrally.',
            'part_a_ministry_department' => 'Secondary',
            'general_guidance' => "One guidance line.\nTwo guidance line.",
            'default_plan_start_month' => 2,
            'default_plan_start_day' => 5,
            'default_plan_end_month' => 11,
            'default_plan_end_day' => 29,
        ]);

        $service->saveAccessSettings([
            'elevated_positions' => "School Head\nDeputy School Head",
            'elevated_roles' => "Administrator\nHR Admin",
        ]);

        $this->assertSame('PDP Admin', $service->generalSettings()['active_template_support_label']);
        $this->assertSame('Secondary', $service->generalSettings()['part_a_ministry_department']);
        $this->assertSame("One guidance line.\nTwo guidance line.", $service->generalSettings()['general_guidance']);
        $this->assertSame(2, $service->generalSettings()['default_plan_start_month']);
        $this->assertSame(['School Head', 'Deputy School Head'], $service->accessSettings()['elevated_positions']);
        $this->assertSame(['Administrator', 'HR Admin'], $service->accessSettings()['elevated_roles']);
    }

    public function test_comment_bank_returns_defaults_and_can_be_saved(): void
    {
        $service = app(PdpSettingsService::class);

        $defaults = $service->commentBank();
        $this->assertCount(20, $defaults['supervisee_comments']);
        $this->assertCount(20, $defaults['supervisor_comments']);

        $service->saveCommentBank([
            'supervisee_comments' => "I reflected on my progress.\nI need more support with learner interventions.",
            'supervisor_comments' => "The teacher has made measurable progress.\nMore consistency is still required.",
        ]);

        $saved = $service->commentBank();

        $this->assertSame([
            'I reflected on my progress.',
            'I need more support with learner interventions.',
        ], $saved['supervisee_comments']);
        $this->assertSame([
            'The teacher has made measurable progress.',
            'More consistency is still required.',
        ], $saved['supervisor_comments']);
    }
}
