<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class SavePeriodRestrictionRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('manage-timetable');
    }

    public function rules(): array {
        return [
            'timetable_id' => ['required', 'integer', 'exists:timetables,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'restriction' => ['required', 'string', 'in:fixed_period,first_or_last,afternoon_only,reserved_periods'],
            'allowed_periods' => ['sometimes', 'array'],
            'allowed_periods.*' => ['integer', 'min:1'],
        ];
    }
}
