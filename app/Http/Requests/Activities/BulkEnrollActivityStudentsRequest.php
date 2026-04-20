<?php

namespace App\Http\Requests\Activities;

use App\Models\Activities\Activity;
use Illuminate\Foundation\Http\FormRequest;

class BulkEnrollActivityStudentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $activity = $this->route('activity');

        return $activity instanceof Activity
            ? ($this->user()?->can('manageRoster', $activity) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
            'joined_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'student_ids' => array_values(array_unique(array_filter(
                array_map(static fn ($id) => is_numeric($id) ? (int) $id : null, (array) $this->input('student_ids', []))
            ))),
        ]);
    }
}
