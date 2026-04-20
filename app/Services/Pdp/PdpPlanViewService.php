<?php

namespace App\Services\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpTemplateField;
use App\Models\Pdp\PdpPlanSectionEntry;
use App\Models\Pdp\PdpTemplateSection;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PdpPlanViewService
{
    public function __construct(
        private readonly PdpPlanService $planService,
        private readonly PdpReviewService $reviewService
    ) {
    }

    public function buildPlanViewModel(PdpPlan $plan, ?User $actor = null): array
    {
        $plan->loadMissing([
            'template.sections.fields.childFields',
            'template.sections.rows.childRows',
            'template.periods',
            'template.ratingSchemes',
            'reviews',
            'sectionEntries.childEntries',
            'sectionEntries.templateSectionRow',
            'signatures.signer',
            'user',
            'supervisor',
        ]);

        $sections = $plan->template->sections
            ->sortBy('sequence')
            ->values()
            ->map(fn (PdpTemplateSection $section): array => $this->buildSectionViewModel($plan, $section, $actor))
            ->all();

        return [
            'plan' => $plan,
            'periods' => $plan->template->periods->sortBy('sequence')->values(),
            'reviews' => $this->orderedReviews($plan),
            'sections' => $sections,
            'review_permissions' => [
                'can_manage_reviews' => $actor ? $this->reviewService->canManageReview($plan, $actor) : false,
            ],
        ];
    }

    public function buildSectionViewModel(PdpPlan $plan, PdpTemplateSection $section, ?User $actor = null): array
    {
        $fields = $section->fields
            ->whereNull('parent_field_id')
            ->sortBy('sort_order')
            ->values();

        $mappedValues = $this->planService->resolveMappedSectionValues(
            $plan,
            $section->key,
            null,
            $plan->calculated_summary_json ?? []
        );

        [$fields, $mappedValues] = $this->augmentSectionFields($plan, $section, $fields, $mappedValues);

        $manualFields = $fields
            ->filter(fn (PdpTemplateField $field): bool => $field->input_mode === 'manual_entry')
            ->values();

        $entries = $plan->sectionEntries
            ->where('section_key', $section->key)
            ->whereNull('pdp_plan_review_id')
            ->whereNull('parent_entry_id')
            ->sortBy('sort_order')
            ->values();

        $editableFieldKeys = $actor ? $this->reviewService->editableFieldKeys($plan, $section, $actor) : [];
        $entryEditableFieldKeys = $entries->mapWithKeys(function (PdpPlanSectionEntry $entry) use ($actor, $plan, $section): array {
            return [
                $entry->id => $actor ? $this->reviewService->editableFieldKeysForEntry($plan, $section, $entry, $actor) : [],
            ];
        })->all();
        $entryCanDelete = $entries->mapWithKeys(function (PdpPlanSectionEntry $entry) use ($actor, $plan, $section): array {
            return [
                $entry->id => $actor ? $this->reviewService->canDeleteSectionEntry($plan, $section, $entry, $actor) : false,
            ];
        })->all();
        $canCreateEntries = $actor ? $this->reviewService->canManageSectionEntries($plan, $section, $actor) : false;
        $canEditExistingEntries = collect($entryEditableFieldKeys)->contains(fn (array $keys): bool => $keys !== []);
        $fieldOptions = $fields->mapWithKeys(fn (PdpTemplateField $field): array => [
            $field->key => $this->fieldOptions($plan, $field),
        ])->all();
        $objectiveFieldKeys = $section->templateParentFieldKeys();
        $detailFieldKeys = $section->templateChildFieldKeys();
        $evaluationFieldKeys = $section->planEvaluationFieldKeys();

        return [
            'section' => $section,
            'fields' => $fields,
            'manual_fields' => $manualFields,
            'objective_fields' => $fields
                ->filter(fn (PdpTemplateField $field): bool => in_array($field->key, $objectiveFieldKeys, true))
                ->values(),
            'detail_fields' => $fields
                ->filter(fn (PdpTemplateField $field): bool => in_array($field->key, $detailFieldKeys, true))
                ->values(),
            'evaluation_fields' => $fields
                ->filter(fn (PdpTemplateField $field): bool => in_array($field->key, $evaluationFieldKeys, true))
                ->values(),
            'field_options' => $fieldOptions,
            'mapped_values' => $mappedValues,
            'entries' => $entries,
            'grouped_entries' => $section->key === 'performance_objectives'
                ? $this->groupPerformanceObjectivesByCategory($entries, $fieldOptions['objective_category'] ?? [])
                : collect(),
            'supports_entry_crud' => $section->is_repeatable && $manualFields->isNotEmpty(),
            'single_entry' => $section->is_repeatable ? null : $entries->first(),
            'editable_field_keys' => $editableFieldKeys,
            'entry_editable_field_keys' => $entryEditableFieldKeys,
            'entry_can_delete' => $entryCanDelete,
            'entry_origin_labels' => $entries->mapWithKeys(fn (PdpPlanSectionEntry $entry): array => [
                $entry->id => $entry->origin_type === PdpPlanSectionEntry::ORIGIN_TEMPLATE_SNAPSHOT
                    ? ($section->key === 'performance_objectives' ? 'Template Objective' : 'Template Row')
                    : 'Custom Entry',
            ])->all(),
            'template_managed_field_keys' => $section->templateManagedFieldKeys(),
            'can_create_entries' => $canCreateEntries,
            'can_manage_entries' => $canCreateEntries || $canEditExistingEntries,
        ];
    }

    public function fieldOptions(PdpPlan $plan, PdpTemplateField $field): array
    {
        if (is_array($field->options_json) && $field->options_json !== []) {
            return $field->options_json;
        }

        if (!$field->rating_scheme_key) {
            return [];
        }

        $ratingScheme = $plan->template->ratingSchemes->firstWhere('key', $field->rating_scheme_key);
        if (!$ratingScheme) {
            return [];
        }

        $scaleLabels = data_get($ratingScheme->scale_config_json, 'labels', []);
        if (is_array($scaleLabels) && $scaleLabels !== []) {
            return collect($scaleLabels)
                ->map(fn ($label, $value): array => ['value' => (string) $value, 'label' => $label])
                ->values()
                ->all();
        }

        $bandConfig = $ratingScheme->band_config_json;
        if (is_array($bandConfig)) {
            return collect($bandConfig)
                ->map(function (array $band): array {
                    if (array_key_exists('value', $band)) {
                        return [
                            'value' => (string) $band['value'],
                            'label' => $band['label'] ?? (string) $band['value'],
                        ];
                    }

                    $range = trim(($band['min'] ?? '') . ' - ' . ($band['max'] ?? ''));

                    return [
                        'value' => (string) ($band['label'] ?? $range),
                        'label' => $band['label'] ?? $range,
                    ];
                })
                ->values()
                ->all();
        }

        return [];
    }

    public function displayValue(PdpTemplateField $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'Not provided';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($field->field_type === 'date' && $value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if ($field->field_type === 'metric_pair' && is_array($value)) {
            $label = $value['metric'] ?? $value['label'] ?? 'Metric';
            $metricValue = $value['value'] ?? $value['result'] ?? null;

            return trim($label . ': ' . $metricValue);
        }

        if ($field->field_type === 'structured_table' && is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '[]';
        }

        if ($field->field_type === 'attachment' && is_array($value)) {
            return implode(', ', array_filter($value));
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '[]';
        }

        return (string) $value;
    }

    public function optionValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    public function inputValue(PdpTemplateField $field, ?array $values = null): mixed
    {
        $resolved = data_get($values ?? [], $field->key);

        if ($resolved === null) {
            return $field->default_value_json;
        }

        return $resolved;
    }

    public function sectionFieldValue(PdpTemplateField $field, array $sectionData, ?PdpPlanSectionEntry $entry = null): mixed
    {
        $entryValues = $entry?->values_json ?? data_get($sectionData, 'single_entry.values_json', []);

        if (is_array($entryValues) && array_key_exists($field->key, $entryValues)) {
            return $entryValues[$field->key];
        }

        return data_get($sectionData['mapped_values'] ?? [], $field->key);
    }

    public function sectionPartial(PdpTemplateSection $section): string
    {
        if ($section->key === 'performance_objectives') {
            return 'pdp.sections.performance-objectives';
        }

        return match ($section->section_type) {
            'profile_summary' => 'pdp.sections.profile-summary',
            'review_summary' => 'pdp.sections.review-summary',
            'comments_block' => 'pdp.sections.comments-block',
            'signature_block' => 'pdp.sections.signature-block',
            default => 'pdp.sections.repeatable',
        };
    }

    public function periodLabel(?string $periodKey): string
    {
        if (!$periodKey) {
            return 'Plan Level';
        }

        return Str::headline(str_replace('_', ' ', $periodKey));
    }

    private function orderedReviews(PdpPlan $plan): Collection
    {
        return $plan->reviews
            ->sortBy(function ($review) use ($plan): int {
                return (int) $plan->template->periods->search(fn ($period): bool => $period->key === $review->period_key);
            })
            ->values();
    }

    private function groupPerformanceObjectivesByCategory(Collection $entries, array $options): Collection
    {
        if ($entries->isEmpty()) {
            return collect();
        }

        $labelsByValue = collect($options)
            ->mapWithKeys(fn ($option): array => [
                $this->optionValue(data_get($option, 'value')) => data_get($option, 'label', $this->optionValue(data_get($option, 'value'))),
            ]);

        $groups = collect();

        foreach ($entries as $entry) {
            $rawCategory = data_get($entry->values_json, 'objective_category');
            $categoryKey = trim((string) $rawCategory);
            $groupKey = $categoryKey !== '' ? $categoryKey : '__uncategorized';

            if (!$groups->has($groupKey)) {
                $groups->put($groupKey, collect());
            }

            $groups->get($groupKey)->push($entry);
        }

        $orderedKeys = collect($options)
            ->map(fn ($option) => $this->optionValue(data_get($option, 'value')))
            ->filter(fn ($value) => $groups->has($value))
            ->values()
            ->all();

        $remainingKeys = $groups->keys()
            ->reject(fn ($key) => in_array($key, $orderedKeys, true))
            ->values()
            ->all();

        return collect(array_merge($orderedKeys, $remainingKeys))
            ->map(function (string $groupKey) use ($groups, $labelsByValue): array {
                return [
                    'key' => $groupKey,
                    'label' => $groupKey === '__uncategorized'
                        ? 'Uncategorised'
                        : ($labelsByValue[$groupKey] ?? $groupKey),
                    'entries' => $groups->get($groupKey, collect())->values(),
                ];
            })
            ->values();
    }

    private function augmentSectionFields(PdpPlan $plan, PdpTemplateSection $section, Collection $fields, array $mappedValues): array
    {
        if ($section->key !== 'employee_information') {
            return [$fields, $mappedValues];
        }

        $compatibilityFields = collect($this->employeeInformationCompatibilityDefinitions($plan, $fields));
        if ($compatibilityFields->isEmpty()) {
            return [$fields, $mappedValues];
        }

        $compatibilityKeys = $compatibilityFields->pluck('key')->all();
        $existingFields = $fields->keyBy('key');
        $normalisedFields = collect();

        foreach ($compatibilityFields as $definition) {
            $field = $this->makeCompatibilityField($existingFields->get($definition['key']), $definition);
            $normalisedFields->push($field);

            if (!array_key_exists($field->key, $mappedValues) || $mappedValues[$field->key] === null || $mappedValues[$field->key] === '') {
                $mappedValues[$field->key] = $this->planService->resolveFieldValue(
                    $plan,
                    $field,
                    null,
                    $plan->calculated_summary_json ?? []
                );
            }
        }

        $fields = $normalisedFields
            ->concat($fields->reject(fn (PdpTemplateField $field): bool => in_array($field->key, $compatibilityKeys, true)))
            ->sortBy('sort_order')
            ->values();

        return [$fields, $mappedValues];
    }

    private function employeeInformationCompatibilityDefinitions(PdpPlan $plan, Collection $fields): array
    {
        $profile = $this->employeeInformationCompatibilityProfile($plan);
        if ($profile === null) {
            return [];
        }

        $definitions = $profile === 'official'
            ? $this->officialDpsmEmployeeInformationFields()
            : $this->schoolEmployeeInformationFields();

        $existingKeys = $fields->pluck('key')->all();
        $missingKeys = array_diff(array_column($definitions, 'key'), $existingKeys);

        if ($missingKeys === []) {
            return [];
        }

        return $definitions;
    }

    private function employeeInformationCompatibilityProfile(PdpPlan $plan): ?string
    {
        $baseline = (string) data_get($plan->template->settings_json, 'baseline', '');
        $code = (string) $plan->template->code;
        $familyKey = (string) $plan->template->template_family_key;

        return match (true) {
            $baseline === 'official_dpsm',
            $code === 'staff-pdp-dpsm-v4',
            $familyKey === 'staff_pdp_dpsm' => 'official',
            in_array($baseline, ['school_half_yearly', 'blank_bounded'], true),
            in_array($code, ['001', 'staff-pdp-school-v4'], true),
            in_array($familyKey, ['default', 'staff_pdp_school', 'staff_pdp_custom'], true) => 'school',
            default => null,
        };
    }

    private function makeCompatibilityField(?PdpTemplateField $existingField, array $definition): PdpTemplateField
    {
        if ($existingField) {
            $field = clone $existingField;
            $field->fill($definition);

            return $field;
        }

        $field = new PdpTemplateField($definition);
        $field->exists = false;

        return $field;
    }

    private function schoolEmployeeInformationFields(): array
    {
        return [
            [
                'key' => 'employee_name',
                'label' => 'Name of Employee',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'full_name',
                'sort_order' => 1,
            ],
            [
                'key' => 'payroll_no',
                'label' => 'Personal Payroll Number',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'personal_payroll_number',
                'sort_order' => 2,
            ],
            [
                'key' => 'dpsm_file_no',
                'label' => 'DPSM Personal File No',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'dpsm_personal_file_number',
                'sort_order' => 3,
            ],
            [
                'key' => 'plan_period_start',
                'label' => 'Plan Period From',
                'field_type' => 'date',
                'data_type' => 'date',
                'input_mode' => 'computed',
                'mapping_source' => 'plan',
                'mapping_key' => 'plan_period_start',
                'sort_order' => 4,
            ],
            [
                'key' => 'plan_period_end',
                'label' => 'Plan Period To',
                'field_type' => 'date',
                'data_type' => 'date',
                'input_mode' => 'computed',
                'mapping_source' => 'plan',
                'mapping_key' => 'plan_period_end',
                'sort_order' => 5,
            ],
            [
                'key' => 'ministry_department',
                'label' => 'Ministry / Department',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_setting',
                'mapping_source' => 'settings',
                'mapping_key' => 'pdp.general.part_a_ministry_department',
                'sort_order' => 6,
            ],
            [
                'key' => 'school_name',
                'label' => 'Division / Unit',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_setting',
                'mapping_source' => 'settings',
                'mapping_key' => 'school_setup.school_name',
                'sort_order' => 7,
            ],
            [
                'key' => 'position_title',
                'label' => 'Position Title',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'position',
                'sort_order' => 8,
            ],
            [
                'key' => 'grade',
                'label' => 'Grade (Earning Band)',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'earning_band',
                'sort_order' => 9,
            ],
            [
                'key' => 'date_of_appointment',
                'label' => 'Date of Appointment',
                'field_type' => 'date',
                'data_type' => 'date',
                'input_mode' => 'mapped_user_field',
                'mapping_source' => 'user',
                'mapping_key' => 'date_of_appointment',
                'sort_order' => 10,
            ],
            [
                'key' => 'duty_station',
                'label' => 'Duty Station',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_setting',
                'mapping_source' => 'settings',
                'mapping_key' => 'school_setup.school_name',
                'sort_order' => 11,
            ],
            [
                'key' => 'supervisor_name',
                'label' => 'Supervisor Name',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'computed',
                'mapping_source' => 'plan',
                'mapping_key' => 'supervisor.full_name',
                'sort_order' => 12,
            ],
            [
                'key' => 'supervisor_position',
                'label' => 'Supervisor Position',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'computed',
                'mapping_source' => 'plan',
                'mapping_key' => 'supervisor.position',
                'sort_order' => 13,
            ],
            [
                'key' => 'supervisor_grade',
                'label' => 'Supervisor Grade',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'computed',
                'mapping_source' => 'plan',
                'mapping_key' => 'supervisor.earning_band',
                'sort_order' => 14,
            ],
            [
                'key' => 'supervisor_duty_station',
                'label' => 'Supervisor Duty Station',
                'field_type' => 'text',
                'data_type' => 'string',
                'input_mode' => 'mapped_setting',
                'mapping_source' => 'settings',
                'mapping_key' => 'school_setup.school_name',
                'sort_order' => 15,
            ],
        ];
    }
}
