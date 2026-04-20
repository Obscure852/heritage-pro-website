<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlockAllocationsRequest extends FormRequest {
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
            'timetable_id' => ['required', 'integer', 'exists:timetables,id'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.klass_subject_id' => ['required', 'integer', 'exists:klass_subject,id'],
            'allocations.*.singles' => ['required', 'integer', 'min:0', 'max:20'],
            'allocations.*.doubles' => ['required', 'integer', 'min:0', 'max:10'],
            'allocations.*.triples' => ['required', 'integer', 'min:0', 'max:6'],
        ];
    }
}
