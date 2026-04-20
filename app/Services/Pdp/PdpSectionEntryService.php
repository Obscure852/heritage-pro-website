<?php

namespace App\Services\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpPlanSectionEntry;
use App\Models\Pdp\PdpTemplateField;
use App\Models\Pdp\PdpTemplateSection;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PdpSectionEntryService
{
    public function __construct(
        private readonly PdpReviewService $reviewService,
        private readonly PdpPlanViewService $viewService
    ) {
    }

    public function createEntry(PdpPlan $plan, string $sectionKey, User $actor, array $values): PdpPlanSectionEntry
    {
        $section = $this->section($plan, $sectionKey);

        if (!$this->reviewService->canManageSectionEntries($plan, $section, $actor)) {
            throw new InvalidArgumentException('This PDP section is currently locked for editing.');
        }

        $editableFieldKeys = $this->reviewService->editableFieldKeysForEntry($plan, $section, null, $actor);

        $normalizedValues = $this->validatedValues($plan, $section, $values, $editableFieldKeys, []);

        return $plan->sectionEntries()->create([
            'section_key' => $section->key,
            'entry_group_key' => $section->key,
            'origin_type' => PdpPlanSectionEntry::ORIGIN_CUSTOM,
            'sort_order' => ((int) $plan->sectionEntries()
                ->where('section_key', $section->key)
                ->whereNull('parent_entry_id')
                ->max('sort_order')) + 1,
            'values_json' => $normalizedValues,
            'computed_values_json' => null,
        ]);
    }

    public function updateEntry(PdpPlan $plan, string $sectionKey, PdpPlanSectionEntry $entry, User $actor, array $values): PdpPlanSectionEntry
    {
        $section = $this->section($plan, $sectionKey);
        $this->assertEntryBelongsToPlanSection($plan, $section, $entry);
        $editableFieldKeys = $this->reviewService->editableFieldKeysForEntry($plan, $section, $entry, $actor);

        if ($editableFieldKeys === []) {
            throw new InvalidArgumentException('This PDP section is currently locked for editing.');
        }

        $mergedValues = array_merge($entry->values_json ?? [], Arr::only($values, $editableFieldKeys));

        $entry->update([
            'values_json' => $this->validatedValues($plan, $section, $mergedValues, $editableFieldKeys, $entry->values_json ?? []),
        ]);

        return $entry->fresh();
    }

    public function deleteEntry(PdpPlan $plan, string $sectionKey, PdpPlanSectionEntry $entry, User $actor): void
    {
        $section = $this->section($plan, $sectionKey);
        $this->assertEntryBelongsToPlanSection($plan, $section, $entry);

        if (!$this->reviewService->canDeleteSectionEntry($plan, $section, $entry, $actor)) {
            throw new InvalidArgumentException('This PDP section is currently locked for editing.');
        }

        $entry->delete();
    }

    private function section(PdpPlan $plan, string $sectionKey): PdpTemplateSection
    {
        $plan->loadMissing('template.sections.fields', 'template.ratingSchemes');
        $section = $plan->template->sections->firstWhere('key', $sectionKey);

        if (!$section) {
            throw new InvalidArgumentException("Unknown PDP template section [{$sectionKey}].");
        }

        return $section;
    }

    private function validatedValues(
        PdpPlan $plan,
        PdpTemplateSection $section,
        array $values,
        array $editableFieldKeys,
        array $existingValues
    ): array
    {
        $rules = [];
        $attributeNames = [];

        foreach ($section->fields->whereNull('parent_field_id')->sortBy('sort_order') as $field) {
            if ($field->input_mode !== 'manual_entry') {
                continue;
            }

            if (!in_array($field->key, $editableFieldKeys, true)) {
                continue;
            }

            $rules['values.' . $field->key] = $this->validationRulesForField($plan, $field);
            $attributeNames['values.' . $field->key] = $field->label;
        }

        $validator = Validator::make(
            ['values' => $values],
            $rules,
            [],
            $attributeNames
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = Arr::get($validator->validated(), 'values', []);
        $normalized = [];

        foreach ($section->fields->whereNull('parent_field_id')->sortBy('sort_order') as $field) {
            if ($field->input_mode !== 'manual_entry') {
                continue;
            }

            if (!in_array($field->key, $editableFieldKeys, true)) {
                $normalized[$field->key] = $existingValues[$field->key] ?? null;
                continue;
            }

            $normalized[$field->key] = $this->normalizeValue($field, $validated[$field->key] ?? null);
        }

        return $normalized;
    }

    private function validationRulesForField(PdpPlan $plan, PdpTemplateField $field): array
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

        if (in_array($field->field_type, ['select', 'radio_scale'], true)) {
            $options = app(PdpPlanViewService::class)->fieldOptions($plan, $field);
            $allowedValues = collect($options)
                ->pluck('value')
                ->map(fn ($value) => $this->viewService->optionValue($value))
                ->all();

            if ($allowedValues !== []) {
                $rules[] = Rule::in($allowedValues);
            }
        }

        if (is_array($field->validation_rules_json)) {
            $rules = array_merge($rules, $field->validation_rules_json);
        }

        return $rules;
    }

    private function normalizeValue(PdpTemplateField $field, mixed $value): mixed
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

    private function assertEntryBelongsToPlanSection(PdpPlan $plan, PdpTemplateSection $section, PdpPlanSectionEntry $entry): void
    {
        if ($entry->section_key !== $section->key || $entry->pdp_plan_id !== $plan->id) {
            throw new InvalidArgumentException('The PDP section entry does not belong to the requested section.');
        }
    }
}
