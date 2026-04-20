<?php

namespace App\Http\Requests\Activities;

use App\Helpers\TermHelper;
use App\Models\Activities\Activity;
use App\Models\Term;
use App\Services\Activities\ActivitySettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-activities') ?? false;
    }

    public function rules(): array
    {
        $settingsService = app(ActivitySettingsService::class);
        $activity = $this->route('activity');
        $activityId = $activity instanceof Activity ? $activity->id : null;

        $codeRule = Rule::unique('activities', 'code')->ignore($activityId);

        if ($year = $this->resolveYear($activity)) {
            $codeRule = $codeRule->where(fn ($query) => $query->where('year', $year)->whereNull('deleted_at'));
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', $codeRule],
            'category' => ['required', Rule::in($this->allowedOptionKeys($settingsService->activeCategoryOptions(), $activity?->category))],
            'delivery_mode' => ['required', Rule::in($this->allowedOptionKeys($settingsService->activeDeliveryModeOptions(), $activity?->delivery_mode))],
            'participation_mode' => ['required', Rule::in($this->allowedOptionKeys($settingsService->activeParticipationModeOptions(), $activity?->participation_mode))],
            'result_mode' => ['required', Rule::in($this->allowedOptionKeys($settingsService->activeResultModeOptions(), $activity?->result_mode))],
            'description' => ['nullable', 'string'],
            'default_location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'gender_policy' => ['nullable', Rule::in($this->allowedOptionKeys($settingsService->activeGenderPolicyOptions(), $activity?->gender_policy))],
            'attendance_required' => ['nullable', 'boolean'],
            'allow_house_linkage' => ['nullable', 'boolean'],
            'fee_type_id' => ['nullable', 'integer', 'exists:fee_types,id'],
            'default_fee_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper((string) $this->input('code')),
            'attendance_required' => $this->boolean('attendance_required'),
            'allow_house_linkage' => $this->boolean('allow_house_linkage'),
        ]);
    }

    private function resolveYear(mixed $activity): ?int
    {
        if ($activity instanceof Activity) {
            return $activity->year;
        }

        $selectedTermId = session('selected_term_id');

        if ($selectedTermId) {
            return Term::query()->whereKey($selectedTermId)->value('year');
        }

        return TermHelper::getCurrentTerm()?->year;
    }

    private function allowedOptionKeys(array $activeOptions, ?string $currentKey = null): array
    {
        $keys = array_keys($activeOptions);

        if ($currentKey && !in_array($currentKey, $keys, true)) {
            $keys[] = $currentKey;
        }

        return $keys;
    }
}
