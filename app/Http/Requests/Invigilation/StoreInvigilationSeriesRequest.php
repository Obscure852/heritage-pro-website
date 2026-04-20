<?php

namespace App\Http\Requests\Invigilation;

use App\Models\Invigilation\InvigilationSeries;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvigilationSeriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-invigilation') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(InvigilationSeries::types()))],
            'term_id' => ['required', 'integer', 'exists:terms,id'],
            'eligibility_policy' => ['required', Rule::in(array_keys(InvigilationSeries::eligibilityPolicies()))],
            'timetable_conflict_policy' => ['required', Rule::in(array_keys(InvigilationSeries::timetableConflictPolicies()))],
            'balancing_policy' => ['required', Rule::in(['balanced'])],
            'default_required_invigilators' => ['required', 'integer', 'min:1', 'max:10'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
