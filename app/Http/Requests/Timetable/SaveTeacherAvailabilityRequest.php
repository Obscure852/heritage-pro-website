<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class SaveTeacherAvailabilityRequest extends FormRequest {
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
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'unavailable_slots' => ['present', 'array'],
            'unavailable_slots.*.day_of_cycle' => ['required', 'integer', 'min:1', 'max:6'],
            'unavailable_slots.*.period_number' => ['required', 'integer', 'min:1'],
        ];
    }
}
