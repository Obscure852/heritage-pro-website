<?php

namespace App\Http\Requests\Invigilation;

use App\Models\Invigilation\InvigilationSeries;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvigilationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-invigilation') ?? false;
    }

    public function rules(): array
    {
        return [
            'default_type' => ['required', Rule::in(array_keys(InvigilationSeries::types()))],
            'default_required_invigilators' => ['required', 'integer', 'min:1', 'max:10'],
            'default_eligibility_policy' => ['required', Rule::in(array_keys(InvigilationSeries::eligibilityPolicies()))],
            'default_timetable_conflict_policy' => ['required', Rule::in(array_keys(InvigilationSeries::timetableConflictPolicies()))],
        ];
    }
}
