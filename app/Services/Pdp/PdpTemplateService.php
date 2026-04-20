<?php

namespace App\Services\Pdp;

use App\Models\Pdp\PdpTemplate;
use App\Models\Pdp\PdpTemplateApprovalStep;
use App\Models\Pdp\PdpTemplateField;
use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpPlanReview;
use App\Models\Pdp\PdpPlanSectionEntry;
use App\Models\Pdp\PdpPlanSignature;
use App\Models\Pdp\PdpRollout;
use App\Models\Pdp\PdpTemplatePeriod;
use App\Models\Pdp\PdpTemplateRatingScheme;
use App\Models\Pdp\PdpTemplateSection;
use App\Models\Pdp\PdpTemplateSectionRow;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

class PdpTemplateService
{
    public function __construct(
        private readonly PdpSettingsService $settingsService
    ) {
    }

    public function createDraftFromDefinition(array $definition, ?int $createdBy = null): PdpTemplate
    {
        $definition = PdpTemplateBlueprints::normalizeSharedRowSections($definition);
        $templateData = $definition['template'] ?? null;

        if (
            !is_array($templateData)
            || empty($templateData['code'])
            || empty($templateData['template_family_key'])
        ) {
            throw new InvalidArgumentException('Template definition must include template metadata with a family key and code.');
        }

        return DB::transaction(function () use ($definition, $templateData, $createdBy): PdpTemplate {
            $template = PdpTemplate::create([
                'template_family_key' => $templateData['template_family_key'],
                'version' => $templateData['version'] ?? 1,
                'code' => $templateData['code'],
                'name' => $templateData['name'],
                'source_reference' => $templateData['source_reference'] ?? null,
                'description' => $templateData['description'] ?? null,
                'status' => PdpTemplate::STATUS_DRAFT,
                'is_default' => false,
                'settings_json' => $templateData['settings_json'] ?? null,
                'created_by' => $createdBy,
            ]);

            foreach ($definition['sections'] ?? [] as $sectionDefinition) {
                $this->createSectionDefinition($template, $sectionDefinition);
            }

            foreach ($definition['periods'] ?? [] as $periodDefinition) {
                $template->periods()->create($periodDefinition);
            }

            foreach ($definition['rating_schemes'] ?? [] as $ratingSchemeDefinition) {
                $template->ratingSchemes()->create($ratingSchemeDefinition);
            }

            foreach ($definition['approval_steps'] ?? [] as $approvalStepDefinition) {
                $template->approvalSteps()->create($approvalStepDefinition);
            }

            return $this->loadDefinition($template);
        });
    }

    public function publish(PdpTemplate $template): PdpTemplate
    {
        if ($template->status !== PdpTemplate::STATUS_DRAFT) {
            throw new RuntimeException('Only draft templates can be published.');
        }

        if (!$template->sections()->exists()) {
            throw new RuntimeException('Cannot publish a PDP template without sections.');
        }

        if (!$template->periods()->exists()) {
            throw new RuntimeException('Cannot publish a PDP template without periods.');
        }

        if (!$template->ratingSchemes()->exists()) {
            throw new RuntimeException('Cannot publish a PDP template without rating schemes.');
        }

        if (!$template->approvalSteps()->exists()) {
            throw new RuntimeException('Cannot publish a PDP template without approval steps.');
        }

        $template->status = PdpTemplate::STATUS_PUBLISHED;
        $template->published_at = now();
        $template->save();

        return $this->loadDefinition($template);
    }

    public function activate(PdpTemplate $template): PdpTemplate
    {
        if ($template->status !== PdpTemplate::STATUS_PUBLISHED) {
            throw new RuntimeException('Only published templates can be activated.');
        }

        return DB::transaction(function () use ($template): PdpTemplate {
            PdpTemplate::query()
                ->where('is_default', true)
                ->where('id', '!=', $template->id)
                ->update(['is_default' => false]);

            if (!$template->is_default) {
                $template->is_default = true;
                $template->save();
            }

            $this->settingsService->set(
                'templates.active_template_id',
                $template->id,
                null,
                'Currently active Staff PDP template version'
            );

            $this->settingsService->set(
                'templates.active_template_code',
                $template->code,
                null,
                'Currently active Staff PDP template code'
            );

            return $this->loadDefinition($template);
        });
    }

    public function seedIfMissing(array $definition, bool $activate = false, ?int $createdBy = null): PdpTemplate
    {
        $code = $definition['template']['code'] ?? null;
        if (!$code) {
            throw new InvalidArgumentException('Cannot seed a PDP template definition without a template code.');
        }

        $template = PdpTemplate::query()->where('code', $code)->first();

        if (!$template) {
            $template = $this->createDraftFromDefinition($definition, $createdBy);
        }

        if ($template->status === PdpTemplate::STATUS_DRAFT) {
            $template = $this->publish($template);
        }

        if ($activate) {
            $template = $this->activate($template);
        }

        return $this->loadDefinition($template);
    }

    public function seedDefaults(?int $createdBy = null): array
    {
        $schoolTemplate = $this->seedIfMissing(PdpTemplateBlueprints::schoolHalfYearly(), true, $createdBy);
        $officialTemplate = $this->seedIfMissing(PdpTemplateBlueprints::officialDpsm(), false, $createdBy);

        return [
            'school' => $schoolTemplate,
            'official' => $officialTemplate,
        ];
    }

    public function blueprintCatalog(): array
    {
        return PdpTemplateBlueprints::catalog();
    }

    public function createDraftFromBlueprint(string $blueprintKey, array $overrides = [], ?int $createdBy = null): PdpTemplate
    {
        $definition = PdpTemplateBlueprints::definitionByKey($blueprintKey);
        $templateData = $definition['template'];
        $familyKey = $overrides['template_family_key'] ?? $templateData['template_family_key'];
        $version = $this->nextVersionForFamily($familyKey);

        $definition['template'] = array_merge($templateData, [
            'template_family_key' => $familyKey,
            'version' => $version,
            'code' => $overrides['code'] ?? $this->suggestCode($templateData['code'], $familyKey, $version),
            'name' => $overrides['name'] ?? $this->suggestName($templateData['name'], $version),
            'source_reference' => $overrides['source_reference'] ?? $templateData['source_reference'] ?? null,
            'description' => $overrides['description'] ?? $templateData['description'] ?? null,
        ]);

        return $this->createDraftFromDefinition($definition, $createdBy);
    }

    public function updateDraftTemplateMetadata(PdpTemplate $template, array $attributes): PdpTemplate
    {
        $template = $this->assertDraftTemplate($template);

        $template->fill(Arr::only($attributes, [
            'template_family_key',
            'code',
            'name',
            'source_reference',
            'description',
        ]));
        $template->save();

        return $this->loadDefinition($template);
    }

    public function updateDraftSections(PdpTemplate $template, array $sectionPayloads): PdpTemplate
    {
        $template = $this->assertDraftTemplate($template)->loadMissing('sections');

        foreach ($sectionPayloads as $sectionId => $payload) {
            $section = $template->sections->firstWhere('id', (int) $sectionId);
            if (!$section) {
                continue;
            }

            $section->fill(Arr::only((array) $payload, [
                'label',
                'sequence',
                'min_items',
                'max_items',
            ]));
            $section->save();
        }

        return $this->loadDefinition($template);
    }

    public function updateDraftSectionFieldMappings(PdpTemplateSection $section, array $fieldPayloads): PdpTemplateSection
    {
        $section->loadMissing('template', 'fields');
        $this->assertDraftTemplate($section->template);

        foreach ($fieldPayloads as $fieldId => $payload) {
            $field = $section->fields->firstWhere('id', (int) $fieldId);
            if (!$field || $field->parent_field_id !== null) {
                continue;
            }

            $field->fill(Arr::only((array) $payload, [
                'label',
                'mapping_source',
                'mapping_key',
                'required',
                'sort_order',
            ]));
            $field->save();
        }

        return $section->fresh(['template', 'fields.childFields', 'rows']);
    }

    public function updateDraftPeriods(PdpTemplate $template, array $periodPayloads): PdpTemplate
    {
        $template = $this->assertDraftTemplate($template)->loadMissing('periods');

        foreach ($periodPayloads as $periodId => $payload) {
            $period = $template->periods->firstWhere('id', (int) $periodId);
            if (!$period) {
                continue;
            }

            $period->fill(Arr::only((array) $payload, [
                'key',
                'label',
                'sequence',
                'window_type',
                'summary_label',
                'include_in_final_score',
                'due_rule_json',
                'open_rule_json',
                'close_rule_json',
            ]));
            $period->save();
        }

        return $this->loadDefinition($template);
    }

    public function updateDraftRatingSchemes(PdpTemplate $template, array $schemePayloads): PdpTemplate
    {
        $template = $this->assertDraftTemplate($template)->loadMissing('ratingSchemes');

        foreach ($schemePayloads as $schemeId => $payload) {
            $scheme = $template->ratingSchemes->firstWhere('id', (int) $schemeId);
            if (!$scheme) {
                continue;
            }

            $scheme->fill(Arr::only((array) $payload, [
                'label',
                'input_type',
                'weight',
                'rounding_rule',
                'scale_config_json',
                'conversion_config_json',
                'formula_config_json',
                'band_config_json',
            ]));
            $scheme->save();
        }

        return $this->loadDefinition($template);
    }

    public function updateDraftApprovalSteps(PdpTemplate $template, array $stepPayloads): PdpTemplate
    {
        $template = $this->assertDraftTemplate($template)->loadMissing('approvalSteps');

        foreach ($stepPayloads as $stepId => $payload) {
            $step = $template->approvalSteps->firstWhere('id', (int) $stepId);
            if (!$step) {
                continue;
            }

            $step->fill(Arr::only((array) $payload, [
                'key',
                'label',
                'sequence',
                'role_type',
                'required',
                'comment_required',
                'period_scope',
            ]));
            $step->save();
        }

        return $this->loadDefinition($template);
    }

    public function updateDraftPerformanceObjectiveCategories(PdpTemplateSection $section, array $categories): PdpTemplateSection
    {
        $section->loadMissing('template', 'fields');
        $this->assertDraftTemplate($section->template);

        if ($section->key !== 'performance_objectives') {
            throw new InvalidArgumentException('Only the performance objectives section supports objective categories.');
        }

        $field = $this->ensurePerformanceObjectiveCategoryField($section);
        $field->fill([
            'options_json' => $this->normalizeSelectOptions($categories),
        ])->save();

        return $section->fresh(['template', 'fields.childFields', 'rows.childRows']);
    }

    public function upgradeDraftTemplateToFullBuilder(PdpTemplate $template): PdpTemplate
    {
        $template = $this->assertDraftTemplate($template)->loadMissing('sections.fields.childFields', 'sections.rows.childRows');

        foreach ($template->sections as $section) {
            $config = PdpTemplateBlueprints::sharedRowSectionConfig($section->key);
            if ($config === null) {
                continue;
            }

            $layout = is_array($section->layout_config_json) ? $section->layout_config_json : [];
            $seedRows = data_get($layout, 'seed_rows');
            $normalizedLayout = array_merge(
                collect($layout)->except('seed_rows')->all(),
                [
                    'display' => $layout['display'] ?? 'accordion',
                    'row_source' => 'template_section_rows',
                    'template_managed_field_keys' => $config['template_managed_field_keys'],
                    'template_parent_field_keys' => $config['template_parent_field_keys'] ?? data_get($layout, 'template_parent_field_keys', []),
                    'template_child_field_keys' => $config['template_child_field_keys'] ?? data_get($layout, 'template_child_field_keys', []),
                    'plan_evaluation_field_keys' => $config['plan_evaluation_field_keys'] ?? data_get($layout, 'plan_evaluation_field_keys', []),
                    'allow_custom_entries' => false,
                ]
            );

            if ($section->layout_config_json !== $normalizedLayout) {
                $section->fill(['layout_config_json' => $normalizedLayout])->save();
            }

            if ($section->key === 'performance_objectives') {
                $this->ensurePerformanceObjectiveCategoryField($section);
                $this->syncPerformanceObjectiveFieldOrder($section);
                $this->upgradeLegacyPerformanceObjectiveRows($section);
            }

            if ($section->rows->isEmpty() && is_array($seedRows) && $seedRows !== []) {
                foreach (array_values($seedRows) as $index => $rowDefinition) {
                    if (!is_array($rowDefinition)) {
                        continue;
                    }

                    $this->createSectionRowDefinition($section, $rowDefinition, $index + 1);
                }
            }
        }

        return $this->loadDefinition($template);
    }

    public function cloneAsNextDraft(PdpTemplate $template, ?int $createdBy = null, array $overrides = []): PdpTemplate
    {
        $definition = $this->exportDefinition($template);
        $familyKey = $overrides['template_family_key'] ?? $template->template_family_key;
        $version = $this->nextVersionForFamily($familyKey);

        $definition['template'] = array_merge($definition['template'], [
            'template_family_key' => $familyKey,
            'version' => $version,
            'code' => $overrides['code'] ?? $this->suggestCode($template->code, $familyKey, $version),
            'name' => $overrides['name'] ?? $this->suggestName($template->name, $version),
            'source_reference' => $overrides['source_reference'] ?? $template->source_reference,
            'description' => $overrides['description'] ?? $template->description,
        ]);

        return $this->createDraftFromDefinition($definition, $createdBy);
    }

    public function archive(PdpTemplate $template): PdpTemplate
    {
        if ($template->status === PdpTemplate::STATUS_ARCHIVED) {
            throw new RuntimeException('This PDP template is already archived.');
        }

        if ($template->is_default) {
            throw new RuntimeException('The active default PDP template cannot be archived.');
        }

        $template->fill([
            'status' => PdpTemplate::STATUS_ARCHIVED,
            'archived_at' => now(),
        ])->save();

        return $this->loadDefinition($template);
    }

    public function templateDeletionImpact(PdpTemplate $template): array
    {
        $plansQuery = PdpPlan::withTrashed()->where('pdp_template_id', $template->id);

        return [
            'rollouts' => PdpRollout::query()->where('pdp_template_id', $template->id)->count(),
            'plans' => (clone $plansQuery)->count(),
            'reviews' => PdpPlanReview::query()
                ->whereIn('pdp_plan_id', (clone $plansQuery)->select('id'))
                ->count(),
            'section_entries' => PdpPlanSectionEntry::query()
                ->whereIn('pdp_plan_id', (clone $plansQuery)->select('id'))
                ->count(),
            'signatures' => PdpPlanSignature::query()
                ->whereIn('pdp_plan_id', (clone $plansQuery)->select('id'))
                ->count(),
        ];
    }

    public function deleteTemplate(PdpTemplate $template, bool $confirmed = false): void
    {
        $impact = $this->templateDeletionImpact($template);
        $isUsed = collect($impact)->contains(fn (int $count): bool => $count > 0);

        if ($isUsed && !$confirmed) {
            throw new RuntimeException('This PDP template is already used by rollout or plan records. Review the deletion impact and confirm before continuing.');
        }

        DB::transaction(function () use ($template): void {
            $activeTemplateId = (int) $this->settingsService->get('templates.active_template_id', 0);

            PdpPlan::withTrashed()
                ->where('pdp_template_id', $template->id)
                ->get()
                ->each(fn (PdpPlan $plan) => $plan->forceDelete());

            PdpRollout::query()
                ->where('pdp_template_id', $template->id)
                ->delete();

            if ($activeTemplateId === (int) $template->id) {
                $this->settingsService->forget('templates.active_template_id');
                $this->settingsService->forget('templates.active_template_code');
            }

            $template->delete();
        });
    }

    public function exportDefinition(PdpTemplate $template): array
    {
        $template = $this->loadDefinition($template);

        return [
            'template' => [
                'template_family_key' => $template->template_family_key,
                'version' => $template->version,
                'code' => $template->code,
                'name' => $template->name,
                'source_reference' => $template->source_reference,
                'description' => $template->description,
                'settings_json' => $template->settings_json,
            ],
            'sections' => $template->sections
                ->sortBy('sequence')
                ->values()
                ->map(fn (PdpTemplateSection $section): array => $this->exportSectionDefinition($section))
                ->all(),
            'periods' => $template->periods
                ->sortBy('sequence')
                ->values()
                ->map(fn (PdpTemplatePeriod $period): array => [
                    'key' => $period->key,
                    'label' => $period->label,
                    'sequence' => $period->sequence,
                    'window_type' => $period->window_type,
                    'open_rule_json' => $period->open_rule_json,
                    'close_rule_json' => $period->close_rule_json,
                    'summary_label' => $period->summary_label,
                ])
                ->all(),
            'rating_schemes' => $template->ratingSchemes
                ->values()
                ->map(fn (PdpTemplateRatingScheme $scheme): array => [
                    'key' => $scheme->key,
                    'label' => $scheme->label,
                    'input_type' => $scheme->input_type,
                    'weight' => $scheme->weight,
                    'rounding_rule' => $scheme->rounding_rule,
                    'scale_config_json' => $scheme->scale_config_json,
                    'conversion_config_json' => $scheme->conversion_config_json,
                    'formula_config_json' => $scheme->formula_config_json,
                    'band_config_json' => $scheme->band_config_json,
                ])
                ->all(),
            'approval_steps' => $template->approvalSteps
                ->sortBy('sequence')
                ->values()
                ->map(fn (PdpTemplateApprovalStep $step): array => [
                    'key' => $step->key,
                    'label' => $step->label,
                    'sequence' => $step->sequence,
                    'role_type' => $step->role_type,
                    'required' => $step->required,
                    'comment_required' => $step->comment_required,
                    'period_scope' => $step->period_scope,
                    'settings_json' => $step->settings_json,
                ])
                ->all(),
        ];
    }

    public function getActiveTemplate(): ?PdpTemplate
    {
        $activeTemplateId = $this->settingsService->get('templates.active_template_id');

        if ($activeTemplateId) {
            $template = PdpTemplate::query()->find($activeTemplateId);
            if ($template) {
                return $this->loadDefinition($template);
            }
        }

        $template = PdpTemplate::query()
            ->where('is_default', true)
            ->first();

        return $template ? $this->loadDefinition($template) : null;
    }

    public function nextVersionForFamily(string $templateFamilyKey): int
    {
        $currentMax = (int) PdpTemplate::query()
            ->where('template_family_key', $templateFamilyKey)
            ->max('version');

        return $currentMax + 1;
    }

    private function createSectionDefinition(PdpTemplate $template, array $sectionDefinition): PdpTemplateSection
    {
        $fieldDefinitions = $sectionDefinition['fields'] ?? [];
        $rowDefinitions = $sectionDefinition['rows'] ?? [];
        unset($sectionDefinition['fields']);
        unset($sectionDefinition['rows']);

        /** @var PdpTemplateSection $section */
        $section = $template->sections()->create($sectionDefinition);

        foreach ($fieldDefinitions as $fieldDefinition) {
            $this->createFieldDefinition($section, $fieldDefinition);
        }

        foreach ($rowDefinitions as $index => $rowDefinition) {
            $this->createSectionRowDefinition($section, $rowDefinition, $index + 1);
        }

        return $section;
    }

    private function createFieldDefinition(PdpTemplateSection $section, array $fieldDefinition, ?PdpTemplateField $parentField = null): PdpTemplateField
    {
        $childFields = $fieldDefinition['child_fields'] ?? [];
        unset($fieldDefinition['child_fields']);

        $fieldDefinition['parent_field_id'] = $parentField?->id;

        /** @var PdpTemplateField $field */
        $field = $section->fields()->create($fieldDefinition);

        foreach ($childFields as $childFieldDefinition) {
            $this->createFieldDefinition($section, $childFieldDefinition, $field);
        }

        return $field;
    }

    private function loadDefinition(PdpTemplate $template): PdpTemplate
    {
        return $template->fresh([
            'sections.fields.childFields',
            'sections.rows.childRows',
            'periods',
            'ratingSchemes',
            'approvalSteps',
            'createdBy',
        ]);
    }

    private function exportSectionDefinition(PdpTemplateSection $section): array
    {
        return [
            'key' => $section->key,
            'label' => $section->label,
            'section_type' => $section->section_type,
            'sequence' => $section->sequence,
            'is_repeatable' => $section->is_repeatable,
            'min_items' => $section->min_items,
            'max_items' => $section->max_items,
            'applies_when_json' => $section->applies_when_json,
            'editable_by_json' => $section->editable_by_json,
            'layout_config_json' => $section->layout_config_json,
            'print_config_json' => $section->print_config_json,
            'rows' => $section->rows
                ->sortBy('sort_order')
                ->values()
                ->map(fn (PdpTemplateSectionRow $row): array => $this->exportSectionRowDefinition($row))
                ->all(),
            'fields' => $section->fields
                ->whereNull('parent_field_id')
                ->sortBy('sort_order')
                ->values()
                ->map(fn (PdpTemplateField $field): array => $this->exportFieldDefinition($field))
                ->all(),
        ];
    }

    private function exportFieldDefinition(PdpTemplateField $field): array
    {
        return [
            'key' => $field->key,
            'label' => $field->label,
            'field_type' => $field->field_type,
            'data_type' => $field->data_type,
            'input_mode' => $field->input_mode,
            'required' => $field->required,
            'validation_rules_json' => $field->validation_rules_json,
            'mapping_source' => $field->mapping_source,
            'mapping_key' => $field->mapping_key,
            'default_value_json' => $field->default_value_json,
            'options_json' => $field->options_json,
            'period_scope' => $field->period_scope,
            'rating_scheme_key' => $field->rating_scheme_key,
            'sort_order' => $field->sort_order,
            'child_fields' => $field->childFields
                ->sortBy('sort_order')
                ->values()
                ->map(fn (PdpTemplateField $child): array => $this->exportFieldDefinition($child))
                ->all(),
        ];
    }

    private function suggestCode(string $seedCode, string $templateFamilyKey, int $version): string
    {
        $baseCode = preg_replace('/-v\d+$/', '', $seedCode) ?: $templateFamilyKey;

        return strtolower($baseCode . '-v' . $version);
    }

    private function suggestName(string $seedName, int $version): string
    {
        return preg_replace('/\s+v\d+$/i', '', $seedName) . ' v' . $version;
    }

    public function createSectionRow(
        PdpTemplateSection $section,
        array $values,
        ?int $sortOrder = null,
        ?PdpTemplateSectionRow $parentRow = null
    ): PdpTemplateSectionRow {
        $section->loadMissing('template', 'fields', 'rows.childRows', 'allRows');
        $this->assertSectionSupportsTemplateRows($section);
        $this->assertParentRowBelongsToSection($section, $parentRow);
        $isChildRow = $parentRow !== null;

        $relation = $parentRow ? $parentRow->childRows() : $section->allRows();
        $maxSortOrder = $relation->max('sort_order');

        return $relation->create([
            'pdp_template_section_id' => $section->id,
            'parent_row_id' => $parentRow?->id,
            'key' => $this->uniqueSectionRowKey($section, $values, $isChildRow, $parentRow),
            'values_json' => $this->normalizeSectionRowValues($section, $values, $isChildRow),
            'sort_order' => $sortOrder ?: (((int) $maxSortOrder) + 1),
        ]);
    }

    public function updateSectionRow(
        PdpTemplateSection $section,
        PdpTemplateSectionRow $row,
        array $values,
        ?int $sortOrder = null
    ): PdpTemplateSectionRow {
        $section->loadMissing('template', 'fields', 'rows.childRows', 'allRows');
        $this->assertSectionSupportsTemplateRows($section);

        if ($row->pdp_template_section_id !== $section->id) {
            throw new InvalidArgumentException('The PDP template row does not belong to the requested section.');
        }

        $isChildRow = $row->parent_row_id !== null;
        $mergedValues = array_merge($row->values_json ?? [], $values);

        $row->fill([
            'values_json' => $this->normalizeSectionRowValues($section, $mergedValues, $isChildRow),
            'sort_order' => $sortOrder ?: $row->sort_order,
        ])->save();

        return $row->fresh('childRows');
    }

    public function deleteSectionRow(PdpTemplateSection $section, PdpTemplateSectionRow $row): void
    {
        $section->loadMissing('template');
        $this->assertSectionSupportsTemplateRows($section);

        if ($row->pdp_template_section_id !== $section->id) {
            throw new InvalidArgumentException('The PDP template row does not belong to the requested section.');
        }

        $row->delete();
    }

    public function templateRowFields(PdpTemplateSection $section)
    {
        return $this->templateRowFieldsForContext($section, false);
    }

    public function templateChildRowFields(PdpTemplateSection $section)
    {
        return $this->templateRowFieldsForContext($section, true);
    }

    public function templateParentRowFields(PdpTemplateSection $section)
    {
        return $this->templateRowFieldsForContext($section, false);
    }

    public function templateEvaluationFields(PdpTemplateSection $section)
    {
        $evaluationKeys = $section->planEvaluationFieldKeys();

        return $section->fields
            ->whereNull('parent_field_id')
            ->filter(fn (PdpTemplateField $field): bool => in_array($field->key, $evaluationKeys, true))
            ->sortBy('sort_order')
            ->values();
    }

    private function templateRowFieldsForContext(PdpTemplateSection $section, bool $isChildRow)
    {
        $managedKeys = $this->templateManagedFieldKeysForContext($section, $isChildRow);

        return $section->fields
            ->whereNull('parent_field_id')
            ->filter(fn (PdpTemplateField $field): bool => in_array($field->key, $managedKeys, true))
            ->sortBy('sort_order')
            ->values();
    }

    private function createSectionRowDefinition(
        PdpTemplateSection $section,
        array $rowDefinition,
        int $defaultSortOrder,
        ?PdpTemplateSectionRow $parentRow = null
    ): PdpTemplateSectionRow {
        $childDefinitions = $rowDefinition['child_rows'] ?? [];
        unset($rowDefinition['child_rows']);

        /** @var PdpTemplateSectionRow $row */
        $row = $section->allRows()->create([
            'parent_row_id' => $parentRow?->id,
            'key' => (string) ($rowDefinition['key'] ?? $this->uniqueSectionRowKey(
                $section,
                $rowDefinition['values_json'] ?? $rowDefinition,
                $parentRow !== null,
                $parentRow
            )),
            'values_json' => $this->normalizeSectionRowValues(
                $section,
                (array) ($rowDefinition['values_json'] ?? $rowDefinition),
                $parentRow !== null
            ),
            'sort_order' => (int) ($rowDefinition['sort_order'] ?? $defaultSortOrder),
        ]);

        foreach (array_values($childDefinitions) as $index => $childDefinition) {
            if (!is_array($childDefinition)) {
                continue;
            }

            $this->createSectionRowDefinition($section, $childDefinition, $index + 1, $row);
        }

        return $row;
    }

    private function assertDraftTemplate(PdpTemplate $template): PdpTemplate
    {
        if ($template->status !== PdpTemplate::STATUS_DRAFT) {
            throw new RuntimeException('Only draft PDP templates can be edited.');
        }

        return $template;
    }

    private function assertSectionSupportsTemplateRows(PdpTemplateSection $section): void
    {
        if (!$section->usesTemplateRows()) {
            throw new InvalidArgumentException('This PDP template section does not support template-managed rows.');
        }
    }

    private function normalizeSectionRowValues(PdpTemplateSection $section, array $values, bool $isChildRow = false): array
    {
        $fields = $this->templateRowFieldsForContext($section, $isChildRow);
        $rules = [];
        $attributeNames = [];

        foreach ($fields as $field) {
            $rules[$field->key] = $this->templateRowValidationRules($field);
            $attributeNames[$field->key] = $field->label;
        }

        $validator = Validator::make($values, $rules, [], $attributeNames);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        return $fields
            ->mapWithKeys(fn (PdpTemplateField $field): array => [
                $field->key => $this->normalizeTemplateRowValue($field, Arr::get($validated, $field->key)),
            ])
            ->all();
    }

    private function templateRowValidationRules(PdpTemplateField $field): array
    {
        $rules = [$field->required ? 'required' : 'nullable'];

        $rules[] = match ($field->data_type) {
            'date' => 'date',
            'integer' => 'integer',
            'decimal' => 'numeric',
            'boolean' => 'boolean',
            'array' => 'array',
            default => 'string',
        };

        if (is_array($field->validation_rules_json)) {
            $rules = array_merge($rules, $field->validation_rules_json);
        }

        return $rules;
    }

    private function normalizeTemplateRowValue(PdpTemplateField $field, mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($field->data_type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'integer' => (int) $value,
            'decimal' => (float) $value,
            'array' => is_array($value) ? $value : [$value],
            default => $value,
        };
    }

    private function uniqueSectionRowKey(
        PdpTemplateSection $section,
        array $values,
        bool $isChildRow = false,
        ?PdpTemplateSectionRow $parentRow = null
    ): string
    {
        $preferredFieldKey = data_get(
            PdpTemplateBlueprints::sharedRowSectionConfig($section->key),
            $isChildRow ? 'child_row_identity_key' : 'row_identity_key'
        );
        $preferredValue = $preferredFieldKey ? Arr::get($values, $preferredFieldKey) : null;
        $primaryValue = is_scalar($preferredValue) && trim((string) $preferredValue) !== ''
            ? (string) $preferredValue
            : (string) collect($values)
                ->filter(fn ($value) => is_scalar($value) && trim((string) $value) !== '')
                ->first();

        $baseKey = Str::limit(Str::slug($primaryValue ?: $section->key . '-row', '-'), 60, '');
        $baseKey = trim($baseKey, '-');

        if ($baseKey === '') {
            $baseKey = $section->key . '-row';
        }

        $key = $baseKey;
        $suffix = 2;
        $query = $section->allRows()->where('key', $key);
        if ($isChildRow) {
            $query->where('parent_row_id', $parentRow?->id);
        } else {
            $query->whereNull('parent_row_id');
        }

        while ($query->exists()) {
            $key = $baseKey . '-' . $suffix;
            $suffix++;
            $query = $section->allRows()->where('key', $key);
            if ($isChildRow) {
                $query->where('parent_row_id', $parentRow?->id);
            } else {
                $query->whereNull('parent_row_id');
            }
        }

        return $key;
    }

    private function ensurePerformanceObjectiveCategoryField(PdpTemplateSection $section): PdpTemplateField
    {
        $section->loadMissing('template', 'fields');

        $field = $section->fields->firstWhere('key', 'objective_category');
        $attributes = [
            'key' => 'objective_category',
            'label' => 'Category',
            'field_type' => 'select',
            'data_type' => 'string',
            'input_mode' => 'manual_entry',
            'required' => true,
            'options_json' => $this->defaultPerformanceObjectiveCategoryOptions(),
            'sort_order' => 1,
        ];

        if (!$field) {
            /** @var PdpTemplateField $field */
            $field = $section->fields()->create($attributes);
            $section->unsetRelation('fields');

            return $field;
        }

        if (!is_array($field->options_json) || $field->options_json === []) {
            $field->options_json = $this->defaultPerformanceObjectiveCategoryOptions();
        }

        $field->label = $attributes['label'];
        $field->field_type = $attributes['field_type'];
        $field->data_type = $attributes['data_type'];
        $field->input_mode = $attributes['input_mode'];
        $field->required = $attributes['required'];
        $field->sort_order = $attributes['sort_order'];
        $field->save();

        return $field;
    }

    private function syncPerformanceObjectiveFieldOrder(PdpTemplateSection $section): void
    {
        $section->loadMissing('fields');

        $sortOrderMap = [
            'objective_category' => 1,
            'objective' => 2,
            'output' => 3,
            'measure' => 4,
            'target' => 5,
            'score_out_of_10' => 6,
            'supervisee_comment' => 7,
            'supervisor_comment' => 8,
        ];

        foreach ($section->fields as $field) {
            if ($field->parent_field_id !== null || !isset($sortOrderMap[$field->key])) {
                continue;
            }

            if ((int) $field->sort_order === $sortOrderMap[$field->key]) {
                continue;
            }

            $field->sort_order = $sortOrderMap[$field->key];
            $field->save();
        }
    }

    private function normalizeSelectOptions(array $labels): array
    {
        return collect($labels)
            ->map(fn ($label) => trim((string) $label))
            ->filter()
            ->unique()
            ->values()
            ->map(fn (string $label): array => ['value' => $label, 'label' => $label])
            ->all();
    }

    private function defaultPerformanceObjectiveCategoryOptions(): array
    {
        return PdpTemplateBlueprints::defaultPerformanceObjectiveCategoryOptions();
    }

    private function upgradeLegacyPerformanceObjectiveRows(PdpTemplateSection $section): void
    {
        $section->loadMissing('rows.childRows');

        foreach ($section->rows as $row) {
            $values = is_array($row->values_json) ? $row->values_json : [];
            $detailValues = collect(['output', 'measure', 'target'])
                ->filter(fn (string $key): bool => array_key_exists($key, $values) && trim((string) $values[$key]) !== '')
                ->mapWithKeys(fn (string $key): array => [$key => $values[$key]])
                ->all();

            if ($detailValues !== [] && $row->childRows->isEmpty()) {
                $row->childRows()->create([
                    'pdp_template_section_id' => $section->id,
                    'key' => $this->uniqueSectionRowKey($section, $detailValues, true, $row),
                    'values_json' => $this->normalizeSectionRowValues($section, $detailValues, true),
                    'sort_order' => 1,
                ]);
            }

            if ($detailValues === []) {
                continue;
            }

            foreach (['output', 'measure', 'target'] as $key) {
                unset($values[$key]);
            }

            $row->values_json = $values;
            $row->save();
        }
    }

    private function exportSectionRowDefinition(PdpTemplateSectionRow $row): array
    {
        return [
            'key' => $row->key,
            'values_json' => $row->values_json,
            'sort_order' => $row->sort_order,
            'child_rows' => $row->childRows
                ->sortBy('sort_order')
                ->values()
                ->map(fn (PdpTemplateSectionRow $child): array => $this->exportSectionRowDefinition($child))
                ->all(),
        ];
    }

    private function templateManagedFieldKeysForContext(PdpTemplateSection $section, bool $isChildRow): array
    {
        if ($section->key !== 'performance_objectives') {
            return $section->templateManagedFieldKeys();
        }

        return $isChildRow
            ? ($section->templateChildFieldKeys() ?: ['output', 'measure', 'target'])
            : ($section->templateParentFieldKeys() ?: ['objective_category', 'objective']);
    }

    private function assertParentRowBelongsToSection(PdpTemplateSection $section, ?PdpTemplateSectionRow $parentRow): void
    {
        if ($parentRow === null) {
            return;
        }

        if ($parentRow->pdp_template_section_id !== $section->id || $parentRow->parent_row_id !== null) {
            throw new InvalidArgumentException('The parent PDP template row does not belong to the requested section.');
        }
    }
}
