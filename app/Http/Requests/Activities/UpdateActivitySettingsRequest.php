<?php

namespace App\Http\Requests\Activities;

use App\Services\Activities\ActivitySettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateActivitySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-activity-settings') ?? false;
    }

    public function rules(): array
    {
        $settingsService = app(ActivitySettingsService::class);
        $tab = $settingsService->normalizeTab($this->input('tab'));

        $allowedTabs = array_merge(array_keys($settingsService->tabs()), [ActivitySettingsService::TAB_ALL]);

        $rules = [
            'tab' => ['required', Rule::in($allowedTabs)],
        ];

        $defaultsRules = [
            'default_category' => ['nullable', Rule::in(array_keys($settingsService->activeCategoryOptions()))],
            'default_delivery_mode' => ['nullable', Rule::in(array_keys($settingsService->activeDeliveryModeOptions()))],
            'default_participation_mode' => ['nullable', Rule::in(array_keys($settingsService->activeParticipationModeOptions()))],
            'default_result_mode' => ['nullable', Rule::in(array_keys($settingsService->activeResultModeOptions()))],
            'default_gender_policy' => ['nullable', Rule::in(array_keys($settingsService->activeGenderPolicyOptions()))],
            'default_capacity' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'default_attendance_required' => ['nullable', 'boolean'],
            'default_allow_house_linkage' => ['nullable', 'boolean'],
            'default_event_type' => ['nullable', Rule::in(array_keys($settingsService->activeEventTypeOptions()))],
            'default_publish_to_calendar' => ['nullable', 'boolean'],
            'default_house_linked' => ['nullable', 'boolean'],
        ];

        if ($tab === ActivitySettingsService::TAB_ACTIVITY_FIELDS) {
            return $rules + $this->optionGroupRules([
                'categories',
                'delivery_modes',
                'participation_modes',
                'result_modes',
                'gender_policies',
            ]);
        }

        if ($tab === ActivitySettingsService::TAB_EVENT_FIELDS) {
            return $rules + $this->optionGroupRules(['event_types']);
        }

        if ($tab === ActivitySettingsService::TAB_ALL) {
            return $rules + $this->optionGroupRules([
                'categories',
                'delivery_modes',
                'participation_modes',
                'result_modes',
                'gender_policies',
                'event_types',
            ]) + $defaultsRules;
        }

        return $rules + $defaultsRules;
    }

    protected function prepareForValidation(): void
    {
        $tab = $this->input('tab', $this->query('tab'));

        $payload = [
            'tab' => $tab,
            'default_attendance_required' => $this->boolean('default_attendance_required'),
            'default_allow_house_linkage' => $this->boolean('default_allow_house_linkage'),
            'default_publish_to_calendar' => $this->boolean('default_publish_to_calendar'),
            'default_house_linked' => $this->boolean('default_house_linked'),
        ];

        foreach (['categories', 'delivery_modes', 'participation_modes', 'result_modes', 'gender_policies', 'event_types'] as $group) {
            if ($this->has($group)) {
                $payload[$group] = $this->filterOptionRows($this->input($group, []));
            }
        }

        $this->merge($payload);
    }

    private function optionGroupRules(array $groups): array
    {
        $rules = [];

        foreach ($groups as $group) {
            $rules[$group] = ['required', 'array', 'min:1'];
            $rules[$group . '.*.key'] = ['required', 'string', 'max:50', 'alpha_dash'];
            $rules[$group . '.*.label'] = ['required', 'string', 'max:120'];
            $rules[$group . '.*.active'] = ['nullable', 'boolean'];
            $rules[$group . '.*.system'] = ['nullable', 'boolean'];
        }

        return $rules;
    }

    private function filterOptionRows(mixed $rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        return array_values(array_filter($rows, function (mixed $row): bool {
            if (!is_array($row)) {
                return false;
            }

            return trim((string) ($row['key'] ?? '')) !== ''
                || trim((string) ($row['label'] ?? '')) !== '';
        }));
    }
}
