<?php

namespace App\Http\Requests\Activities;

use App\Models\Activities\Activity;
use Illuminate\Foundation\Http\FormRequest;

class SyncActivityEligibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        $activity = $this->route('activity');

        return $activity instanceof Activity
            ? ($this->user()?->can('manageEligibility', $activity) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'grades' => ['nullable', 'array'],
            'grades.*' => ['integer', 'exists:grades,id'],
            'klasses' => ['nullable', 'array'],
            'klasses.*' => ['integer', 'exists:klasses,id'],
            'houses' => ['nullable', 'array'],
            'houses.*' => ['integer', 'exists:houses,id'],
            'student_filters' => ['nullable', 'array'],
            'student_filters.*' => ['integer', 'exists:student_filters,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'grades' => array_values(array_unique(array_filter((array) $this->input('grades', [])))),
            'klasses' => array_values(array_unique(array_filter((array) $this->input('klasses', [])))),
            'houses' => array_values(array_unique(array_filter((array) $this->input('houses', [])))),
            'student_filters' => array_values(array_unique(array_filter((array) $this->input('student_filters', [])))),
        ]);
    }
}
