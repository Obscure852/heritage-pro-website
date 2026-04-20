<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class SaveSubjectPairRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('manage-timetable');
    }

    public function rules(): array {
        return [
            'timetable_id' => ['required', 'integer', 'exists:timetables,id'],
            'subject_id_a' => ['required', 'integer', 'exists:subjects,id'],
            'subject_id_b' => ['required', 'integer', 'exists:subjects,id', 'different:subject_id_a'],
            'klass_id' => ['nullable', 'integer', 'exists:klasses,id'],
            'rule' => ['required', 'string', 'in:not_same_day,not_consecutive,must_same_day,must_follow'],
        ];
    }
}
