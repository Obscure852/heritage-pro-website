<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouplingGroupsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return $this->user()->can('manage-timetable');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array {
        return [
            'coupling_groups' => ['required', 'array'],
            'coupling_groups.*.label' => ['required', 'string', 'max:100'],
            'coupling_groups.*.grade_id' => ['required', 'integer', 'exists:grades,id'],
            'coupling_groups.*.optional_subject_ids' => ['required', 'array', 'min:2'],
            'coupling_groups.*.optional_subject_ids.*' => ['integer', 'exists:optional_subjects,id'],
            'coupling_groups.*.singles' => ['required', 'integer', 'min:0', 'max:20'],
            'coupling_groups.*.doubles' => ['required', 'integer', 'min:0', 'max:10'],
            'coupling_groups.*.triples' => ['required', 'integer', 'min:0', 'max:6'],
        ];
    }
}
