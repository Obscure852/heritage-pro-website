<?php

namespace App\Services\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpPlanSectionEntry;
use App\Models\Pdp\PdpPlanReview;
use App\Models\Pdp\PdpPlanSignature;
use App\Models\Pdp\PdpRollout;
use App\Models\Pdp\PdpTemplate;
use App\Models\Pdp\PdpTemplateField;
use App\Models\Pdp\PdpTemplateSection;
use App\Models\Pdp\PdpTemplateSectionRow;
use App\Models\SchoolSetup;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class PdpPlanService
{
    public function __construct(
        private readonly PdpTemplateService $templateService,
        private readonly PdpSettingsService $pdpSettingsService,
        private readonly SettingsService $settingsService
    ) {
    }

    public function createPlan(User $employee, array $attributes = [], ?PdpTemplate $template = null): PdpPlan
    {
        $template = $template
            ? $template->loadMissing(['sections.fields', 'sections.rows.childRows', 'periods', 'approvalSteps'])
            : $this->templateService->getActiveTemplate();

        if (!$template) {
            throw new RuntimeException('Cannot create a PDP plan without an active or provided template.');
        }

        if ($template->status !== PdpTemplate::STATUS_PUBLISHED) {
            throw new RuntimeException('PDP plans can only be created from a published template version.');
        }

        $startDate = $this->normalizeDate($attributes['plan_period_start'] ?? null, 'plan_period_start');
        $endDate = $this->normalizeDate($attributes['plan_period_end'] ?? null, 'plan_period_end');

        if ($endDate->lt($startDate)) {
            throw new InvalidArgumentException('The plan period end date must be on or after the start date.');
        }

        $status = $attributes['status'] ?? PdpPlan::STATUS_DRAFT;
        $this->assertValidPlanStatus($status);
        $this->assertNoOverlappingPlan($employee->id, $template->template_family_key, $startDate, $endDate);

        return DB::transaction(function () use ($employee, $attributes, $template, $startDate, $endDate, $status): PdpPlan {
            $periods = $template->periods->sortBy('sequence')->values();
            $currentPeriodKey = $attributes['current_period_key'] ?? $periods->first()?->key;

            if ($currentPeriodKey !== null && !$periods->contains(fn ($period): bool => $period->key === $currentPeriodKey)) {
                throw new InvalidArgumentException('The current period key must match a configured template period.');
            }

            /** @var PdpPlan $plan */
            $plan = PdpPlan::create([
                'pdp_template_id' => $template->id,
                'pdp_rollout_id' => $attributes['pdp_rollout_id'] ?? null,
                'user_id' => $employee->id,
                'supervisor_id' => $attributes['supervisor_id'] ?? $employee->reporting_to,
                'plan_period_start' => $startDate->toDateString(),
                'plan_period_end' => $endDate->toDateString(),
                'status' => $status,
                'current_period_key' => $currentPeriodKey,
                'calculated_summary_json' => $attributes['calculated_summary_json'] ?? null,
                'created_by' => $attributes['created_by'] ?? null,
            ]);

            $reviewsByPeriod = $this->initializeReviews($plan, $periods, $status, $currentPeriodKey);
            $this->initializeSectionEntries($plan, $template);
            $this->initializeSignatures($plan, $template, $reviewsByPeriod);

            return $this->loadPlan($plan);
        });
    }

    public function createPlanForRollout(User $employee, PdpRollout $rollout, array $attributes = []): PdpPlan
    {
        return $this->createPlan($employee, array_merge([
            'pdp_rollout_id' => $rollout->id,
            'plan_period_start' => $rollout->plan_period_start?->toDateString(),
            'plan_period_end' => $rollout->plan_period_end?->toDateString(),
            'status' => PdpPlan::STATUS_DRAFT,
        ], $attributes), $rollout->template);
    }

    public function updatePlan(PdpPlan $plan, array $attributes): PdpPlan
    {
        $plan = $this->loadPlan($plan);

        $startDate = array_key_exists('plan_period_start', $attributes)
            ? $this->normalizeDate($attributes['plan_period_start'], 'plan_period_start')
            : $plan->plan_period_start->copy()->startOfDay();

        $endDate = array_key_exists('plan_period_end', $attributes)
            ? $this->normalizeDate($attributes['plan_period_end'], 'plan_period_end')
            : $plan->plan_period_end->copy()->startOfDay();

        if ($endDate->lt($startDate)) {
            throw new InvalidArgumentException('The plan period end date must be on or after the start date.');
        }

        $status = $attributes['status'] ?? $plan->status;
        $this->assertValidPlanStatus($status);
        $this->assertNoOverlappingPlan($plan->user_id, $plan->template->template_family_key, $startDate, $endDate, $plan->id);

        $currentPeriodKey = $attributes['current_period_key'] ?? $plan->current_period_key;
        if ($currentPeriodKey !== null && !$plan->template->periods->contains(fn ($period): bool => $period->key === $currentPeriodKey)) {
            throw new InvalidArgumentException('The current period key must match a configured template period.');
        }

        $plan->fill([
            'supervisor_id' => $attributes['supervisor_id'] ?? $plan->supervisor_id,
            'plan_period_start' => $startDate->toDateString(),
            'plan_period_end' => $endDate->toDateString(),
            'status' => $status,
            'current_period_key' => $currentPeriodKey,
        ]);
        $plan->save();

        foreach ($plan->reviews as $review) {
            $reviewState = $this->resolveInitialReviewState($status, $currentPeriodKey, $review->period_key);
            $review->fill([
                'status' => $reviewState['status'],
                'opened_at' => $reviewState['opened_at'],
                'closed_at' => $reviewState['closed_at'],
            ])->save();
        }

        return $this->loadPlan($plan);
    }

    public function resolveMappedPlanValues(PdpPlan $plan, ?string $periodKey = null, array $computedValues = []): array
    {
        $plan = $this->loadPlan($plan);
        $resolved = [];

        foreach ($plan->template->sections as $section) {
            $resolved[$section->key] = $this->resolveMappedSectionValues($plan, $section->key, $periodKey, $computedValues);
        }

        return $resolved;
    }

    public function resolveMappedSectionValues(PdpPlan $plan, string $sectionKey, ?string $periodKey = null, array $computedValues = []): array
    {
        $plan = $this->loadPlan($plan);
        $section = $plan->template->sections->firstWhere('key', $sectionKey);

        if (!$section) {
            throw new InvalidArgumentException("Unknown PDP template section [{$sectionKey}].");
        }

        $resolved = [];

        foreach ($section->fields->whereNull('parent_field_id')->sortBy('sort_order') as $field) {
            $resolved[$field->key] = $this->resolveFieldValue($plan, $field, $periodKey, $computedValues);
        }

        return $resolved;
    }

    public function resolveFieldValue(PdpPlan $plan, PdpTemplateField $field, ?string $periodKey = null, array $computedValues = [])
    {
        $plan->loadMissing(['user.profileMetadata', 'supervisor']);

        if ($periodKey !== null && $field->period_scope !== null && $field->period_scope !== $periodKey) {
            return null;
        }

        $mappingSource = $field->mapping_source ?: $this->inferMappingSource($field);
        $defaultValue = $field->default_value_json;

        return match ($mappingSource) {
            'user' => $this->resolveUserValue($plan, (string) $field->mapping_key, $defaultValue),
            'settings' => $this->resolveSettingsValue((string) $field->mapping_key, $defaultValue),
            'profile_metadata' => $plan->user?->getProfileMetadataValue((string) $field->mapping_key, $defaultValue),
            'plan' => data_get($plan, (string) $field->mapping_key, $defaultValue),
            'computed' => $this->resolveComputedValue($computedValues, (string) $field->mapping_key, $defaultValue),
            null => $defaultValue,
            default => throw new InvalidArgumentException("Unsupported PDP mapping source [{$mappingSource}]."),
        };
    }

    private function inferMappingSource(PdpTemplateField $field): ?string
    {
        return match ($field->input_mode) {
            'mapped_user_field' => 'user',
            'mapped_setting' => 'settings',
            'mapped_profile_metadata' => 'profile_metadata',
            'computed' => 'computed',
            default => null,
        };
    }

    private function resolveUserValue(PdpPlan $plan, string $mappingKey, $default = null)
    {
        if ($mappingKey === '') {
            return $default;
        }

        $sentinel = new \stdClass();
        $resolved = data_get($plan->user, $mappingKey, $sentinel);

        if ($resolved !== $sentinel && $resolved !== null && $resolved !== '') {
            return $resolved;
        }

        $legacyValue = $this->resolveLegacyProfileMetadataValue($plan, $mappingKey, $sentinel);

        if ($legacyValue !== $sentinel && $legacyValue !== null && $legacyValue !== '') {
            return $legacyValue;
        }

        return $resolved === $sentinel ? $default : $resolved;
    }

    private function resolveLegacyProfileMetadataValue(PdpPlan $plan, string $mappingKey, object $sentinel)
    {
        $metadataKeys = match ($mappingKey) {
            'personal_payroll_number' => ['payroll_no'],
            'dpsm_personal_file_number' => ['dpsm_file_no', 'dpsm_personal_file_no'],
            'earning_band' => ['grade'],
            'date_of_appointment' => ['date_of_appointment'],
            default => [],
        };

        if ($metadataKeys === [] || !$plan->user) {
            return $sentinel;
        }

        $profileMetadata = $plan->user->relationLoaded('profileMetadata')
            ? $plan->user->profileMetadata
            : $plan->user->profileMetadata()->get();

        foreach ($metadataKeys as $metadataKey) {
            $record = $profileMetadata->firstWhere('key', $metadataKey);
            if ($record && $record->value !== null && $record->value !== '') {
                return $record->value;
            }
        }

        return $sentinel;
    }

    private function normalizeDate(mixed $value, string $field): Carbon
    {
        if ($value === null || $value === '') {
            throw new InvalidArgumentException("The {$field} field is required when creating a PDP plan.");
        }

        return Carbon::parse($value)->startOfDay();
    }

    private function assertValidPlanStatus(string $status): void
    {
        if (!in_array($status, [
            PdpPlan::STATUS_DRAFT,
            PdpPlan::STATUS_ACTIVE,
            PdpPlan::STATUS_COMPLETED,
            PdpPlan::STATUS_CANCELLED,
        ], true)) {
            throw new InvalidArgumentException("Unsupported PDP plan status [{$status}].");
        }
    }

    private function assertNoOverlappingPlan(int $userId, string $templateFamilyKey, Carbon $startDate, Carbon $endDate, ?int $ignorePlanId = null): void
    {
        $query = PdpPlan::query()
            ->where('user_id', $userId)
            ->where('status', '!=', PdpPlan::STATUS_CANCELLED)
            ->whereDate('plan_period_start', '<=', $endDate->toDateString())
            ->whereDate('plan_period_end', '>=', $startDate->toDateString())
            ->whereHas('template', fn ($query) => $query->where('template_family_key', $templateFamilyKey));

        if ($ignorePlanId !== null) {
            $query->whereKeyNot($ignorePlanId);
        }

        $overlapExists = $query->exists();

        if ($overlapExists) {
            throw new RuntimeException('An overlapping PDP plan already exists for this employee and template family.');
        }
    }

    private function initializeReviews(PdpPlan $plan, Collection $periods, string $planStatus, ?string $currentPeriodKey): Collection
    {
        $reviewsByPeriod = collect();

        foreach ($periods as $period) {
            $reviewState = $this->resolveInitialReviewState($planStatus, $currentPeriodKey, $period->key);

            $reviewsByPeriod->put(
                $period->key,
                $plan->reviews()->create([
                    'period_key' => $period->key,
                    'status' => $reviewState['status'],
                    'opened_at' => $reviewState['opened_at'],
                    'closed_at' => $reviewState['closed_at'],
                    'score_summary_json' => null,
                    'narrative_summary' => null,
                ])
            );
        }

        return $reviewsByPeriod;
    }

    private function initializeSignatures(PdpPlan $plan, PdpTemplate $template, Collection $reviewsByPeriod): void
    {
        foreach ($template->approvalSteps->sortBy('sequence') as $step) {
            $reviews = $this->signatureTargetReviews($step->period_scope, $reviewsByPeriod);

            foreach ($reviews as $review) {
                $plan->signatures()->create([
                    'pdp_plan_review_id' => $review?->id,
                    'approval_step_key' => $step->key,
                    'role_type' => $step->role_type,
                    'status' => PdpPlanSignature::STATUS_PENDING,
                ]);
            }
        }
    }

    private function initializeSectionEntries(PdpPlan $plan, PdpTemplate $template): void
    {
        foreach ($template->sections as $section) {
            if ($section->usesTemplateRows() && $section->rows->isNotEmpty()) {
                $this->initializeTemplateSectionRows($plan, $section);
                continue;
            }

            $seedRows = data_get($section->layout_config_json, 'seed_rows');
            if (!$section->is_repeatable || !is_array($seedRows) || $seedRows === []) {
                continue;
            }

            foreach (array_values($seedRows) as $index => $row) {
                if (!is_array($row)) {
                    continue;
                }

                $this->createSectionEntrySnapshot(
                    $plan,
                    $section,
                    $index + 1,
                    $row,
                    PdpPlanSectionEntry::ORIGIN_CUSTOM,
                    null,
                    null
                );
            }
        }
    }

    private function initializeTemplateSectionRows(PdpPlan $plan, PdpTemplateSection $section): void
    {
        foreach ($section->rows->sortBy('sort_order')->values() as $index => $row) {
            $parentEntry = $this->createSectionEntrySnapshot(
                $plan,
                $section,
                $index + 1,
                $row->values_json ?? [],
                PdpPlanSectionEntry::ORIGIN_TEMPLATE_SNAPSHOT,
                $row,
                null
            );

            foreach ($row->childRows->sortBy('sort_order')->values() as $childIndex => $childRow) {
                $this->createSectionEntrySnapshot(
                    $plan,
                    $section,
                    $childIndex + 1,
                    $childRow->values_json ?? [],
                    PdpPlanSectionEntry::ORIGIN_TEMPLATE_SNAPSHOT,
                    $childRow,
                    $parentEntry
                );
            }
        }
    }

    private function createSectionEntrySnapshot(
        PdpPlan $plan,
        PdpTemplateSection $section,
        int $sortOrder,
        array $seedValues,
        string $originType,
        ?PdpTemplateSectionRow $templateRow,
        ?PdpPlanSectionEntry $parentEntry
    ): PdpPlanSectionEntry {
        return $plan->sectionEntries()->create([
            'pdp_template_section_row_id' => $templateRow?->id,
            'parent_entry_id' => $parentEntry?->id,
            'section_key' => $section->key,
            'entry_group_key' => $section->key,
            'origin_type' => $originType,
            'sort_order' => $sortOrder,
            'values_json' => $this->snapshotEntryValues($section, $seedValues),
            'computed_values_json' => null,
        ]);
    }

    private function snapshotEntryValues(PdpTemplateSection $section, array $seedValues): array
    {
        $fieldKeys = $section->fields
            ->whereNull('parent_field_id')
            ->where('input_mode', 'manual_entry')
            ->pluck('key')
            ->all();

        $values = array_fill_keys($fieldKeys, null);

        foreach ($seedValues as $key => $value) {
            if (in_array($key, $fieldKeys, true)) {
                $values[$key] = $value;
            }
        }

        return $values;
    }

    private function signatureTargetReviews(?string $periodScope, Collection $reviewsByPeriod): Collection
    {
        if ($periodScope === null || $periodScope === '') {
            return collect([null]);
        }

        if (in_array($periodScope, ['all_periods', 'per_period', 'each_period'], true)) {
            return $reviewsByPeriod->values();
        }

        $review = $reviewsByPeriod->get($periodScope);

        return $review ? collect([$review]) : collect([null]);
    }

    private function resolveInitialReviewState(string $planStatus, ?string $currentPeriodKey, string $periodKey): array
    {
        if ($planStatus === PdpPlan::STATUS_COMPLETED) {
            return [
                'status' => PdpPlanReview::STATUS_CLOSED,
                'opened_at' => now(),
                'closed_at' => now(),
            ];
        }

        if ($planStatus === PdpPlan::STATUS_ACTIVE && $currentPeriodKey === $periodKey) {
            return [
                'status' => PdpPlanReview::STATUS_OPEN,
                'opened_at' => now(),
                'closed_at' => null,
            ];
        }

        return [
            'status' => PdpPlanReview::STATUS_PENDING,
            'opened_at' => null,
            'closed_at' => null,
        ];
    }

    private function resolveSettingsValue(string $mappingKey, $default = null)
    {
        if ($mappingKey === '') {
            return $default;
        }

        if (str_starts_with($mappingKey, 'school_setup.')) {
            return data_get(SchoolSetup::current(), substr($mappingKey, strlen('school_setup.')), $default);
        }

        if (str_starts_with($mappingKey, 'pdp.')) {
            return $this->pdpSettingsService->get(substr($mappingKey, strlen('pdp.')), $default);
        }

        if (str_starts_with($mappingKey, 'system.')) {
            return $this->settingsService->get(substr($mappingKey, strlen('system.')), $default);
        }

        $pdpValue = $this->pdpSettingsService->get($mappingKey);
        if ($pdpValue !== null) {
            return $pdpValue;
        }

        return data_get(SchoolSetup::current(), $mappingKey, $default);
    }

    private function resolveComputedValue(array $computedValues, string $mappingKey, $default = null)
    {
        $sentinel = new \stdClass();
        $resolved = data_get($computedValues, $mappingKey, $sentinel);

        if ($resolved !== $sentinel) {
            return $resolved;
        }

        return array_key_exists($mappingKey, $computedValues)
            ? $computedValues[$mappingKey]
            : $default;
    }

    private function loadPlan(PdpPlan $plan): PdpPlan
    {
        return $plan->fresh([
            'template.sections.fields.childFields',
            'template.sections.rows.childRows',
            'template.periods',
            'template.ratingSchemes',
            'template.approvalSteps',
            'rollout.fallbackSupervisor',
            'user.profileMetadata',
            'supervisor',
            'reviews',
            'sectionEntries.childEntries',
            'sectionEntries.templateSectionRow',
            'signatures.signer',
        ]);
    }
}
