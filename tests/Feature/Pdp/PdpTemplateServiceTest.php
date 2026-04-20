<?php

namespace Tests\Feature\Pdp;

use App\Models\Pdp\PdpTemplate;
use App\Services\Pdp\PdpSettingsService;
use App\Services\Pdp\PdpTemplateBlueprints;
use App\Services\Pdp\PdpTemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LogicException;
use RuntimeException;
use Tests\Concerns\EnsuresPdpPhaseOneSchema;
use Tests\TestCase;

class PdpTemplateServiceTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePdpPhaseOneSchema();
    }

    public function test_seed_defaults_creates_published_templates_and_activates_school_template(): void
    {
        $service = app(PdpTemplateService::class);
        $settings = app(PdpSettingsService::class);

        $result = $service->seedDefaults();

        $this->assertSame('staff-pdp-school-v4', $result['school']->code);
        $this->assertSame('staff-pdp-dpsm-v4', $result['official']->code);
        $this->assertSame(PdpTemplate::STATUS_PUBLISHED, $result['school']->status);
        $this->assertSame(PdpTemplate::STATUS_PUBLISHED, $result['official']->status);
        $this->assertTrue($result['school']->is_default);
        $this->assertFalse($result['official']->is_default);
        $this->assertCount(6, $result['school']->sections);
        $this->assertCount(2, $result['school']->periods);
        $this->assertCount(3, $result['school']->ratingSchemes);
        $this->assertCount(3, $result['school']->approvalSteps);
        $this->assertSame($result['school']->id, $settings->get('templates.active_template_id'));
        $this->assertSame($result['school']->code, $settings->get('templates.active_template_code'));
    }

    public function test_seed_defaults_is_idempotent(): void
    {
        $service = app(PdpTemplateService::class);

        $service->seedDefaults();
        $service->seedDefaults();

        $this->assertSame(2, PdpTemplate::query()->count());
        $this->assertSame(1, PdpTemplate::query()->where('is_default', true)->count());
        $this->assertSame(1, PdpTemplate::query()->where('code', 'staff-pdp-school-v4')->count());
        $this->assertSame(1, PdpTemplate::query()->where('code', 'staff-pdp-dpsm-v4')->count());
    }

    public function test_activate_switches_default_template_and_updates_settings(): void
    {
        $service = app(PdpTemplateService::class);
        $settings = app(PdpSettingsService::class);
        $service->seedDefaults();

        $official = PdpTemplate::query()->where('code', 'staff-pdp-dpsm-v4')->firstOrFail();
        $service->activate($official);

        $this->assertTrue($official->fresh()->is_default);
        $this->assertFalse(PdpTemplate::query()->where('code', 'staff-pdp-school-v4')->firstOrFail()->is_default);
        $this->assertSame($official->id, $settings->get('templates.active_template_id'));
        $this->assertSame($official->code, $settings->get('templates.active_template_code'));
    }

    public function test_publish_requires_sections_periods_rating_schemes_and_approval_steps(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot publish a PDP template without sections.');

        $template = PdpTemplate::create([
            'template_family_key' => 'test_family',
            'version' => 1,
            'code' => 'test-draft-template',
            'name' => 'Test Draft Template',
            'status' => PdpTemplate::STATUS_DRAFT,
        ]);

        app(PdpTemplateService::class)->publish($template);
    }

    public function test_published_template_content_is_immutable(): void
    {
        $service = app(PdpTemplateService::class);
        $template = $service->seedDefaults()['school']->fresh();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Published or archived PDP templates are immutable.');

        $template->name = 'Mutated Template Name';
        $template->save();
    }

    public function test_blueprints_expose_all_shared_repeatable_sections_as_template_rows(): void
    {
        $schoolSections = collect(PdpTemplateBlueprints::schoolHalfYearly()['sections'])->keyBy('key');
        $officialSections = collect(PdpTemplateBlueprints::officialDpsm()['sections'])->keyBy('key');
        $blankSections = collect(PdpTemplateBlueprints::blankBounded()['sections'])->keyBy('key');

        foreach ([
            'performance_objectives' => ['objective_category', 'objective', 'output', 'measure', 'target'],
            'coaching' => ['development_objective', 'expected_result'],
            'behavioural_attributes' => ['attribute_name', 'description', 'applicable'],
            'personal_development_goals' => ['performance_gap', 'agreed_action', 'time_frame'],
        ] as $sectionKey => $managedFieldKeys) {
            $this->assertSame('template_section_rows', data_get($schoolSections[$sectionKey], 'layout_config_json.row_source'));
            $this->assertSame($managedFieldKeys, data_get($schoolSections[$sectionKey], 'layout_config_json.template_managed_field_keys'));
            $this->assertFalse((bool) data_get($schoolSections[$sectionKey], 'layout_config_json.allow_custom_entries'));
        }

        $this->assertSame(
            ['objective_category', 'objective'],
            data_get($schoolSections['performance_objectives'], 'layout_config_json.template_parent_field_keys')
        );
        $this->assertSame(
            ['output', 'measure', 'target'],
            data_get($schoolSections['performance_objectives'], 'layout_config_json.template_child_field_keys')
        );

        foreach ([
            'performance_objectives' => ['objective_category', 'objective', 'output', 'measure', 'target'],
            'development_objectives' => ['development_objective', 'expected_result'],
            'personal_attributes' => ['attribute_name', 'description'],
        ] as $sectionKey => $managedFieldKeys) {
            $this->assertSame('template_section_rows', data_get($officialSections[$sectionKey], 'layout_config_json.row_source'));
            $this->assertSame($managedFieldKeys, data_get($officialSections[$sectionKey], 'layout_config_json.template_managed_field_keys'));
            $this->assertFalse((bool) data_get($officialSections[$sectionKey], 'layout_config_json.allow_custom_entries'));
        }

        $this->assertNotEmpty($schoolSections['behavioural_attributes']['rows']);
        $this->assertNotEmpty($officialSections['personal_attributes']['rows']);
        $this->assertSame([], $blankSections['behavioural_attributes']['rows']);
        $this->assertSame([], $blankSections['performance_objectives']['rows']);
        $objectiveCategoryField = collect(data_get($schoolSections['performance_objectives'], 'fields'))
            ->firstWhere('key', 'objective_category');
        $this->assertSame(
            ['Attendance', 'Academic Performance', 'Stakeholder Involvement'],
            collect($objectiveCategoryField['options_json'] ?? [])->pluck('value')->all()
        );
    }
}
