<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class StoreSlotRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'timetable_id' => ['required', 'integer', 'exists:timetables,id'],
            'klass_subject_id' => ['required_without_all:grade_subject_id,optional_subject_id', 'nullable', 'integer', 'exists:klass_subject,id'],
            'grade_subject_id' => ['required_without_all:klass_subject_id,optional_subject_id', 'nullable', 'integer', 'exists:grade_subject,id'],
            'optional_subject_id' => ['required_without_all:klass_subject_id,grade_subject_id', 'nullable', 'integer', 'exists:optional_subjects,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'klass_id' => ['nullable', 'integer', 'exists:klasses,id'],
            'day_of_cycle' => ['required', 'integer', 'min:1', 'max:6'],
            'period_number' => ['required', 'integer', 'min:1'],
            'block_size' => ['sometimes', 'integer', 'in:1,2,3'],
        ];
    }

    public function messages(): array {
        return [
            'timetable_id.exists' => 'The selected timetable does not exist.',
            'klass_subject_id.exists' => 'The selected class-subject does not exist.',
            'grade_subject_id.exists' => 'The selected grade-subject does not exist.',
            'optional_subject_id.exists' => 'The selected optional subject does not exist.',
            'day_of_cycle.max' => 'Day of cycle must be between 1 and 6.',
            'block_size.in' => 'Block size must be 1 (single), 2 (double), or 3 (triple).',
        ];
    }
}
