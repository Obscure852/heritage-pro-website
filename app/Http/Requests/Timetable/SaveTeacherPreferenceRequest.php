<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class SaveTeacherPreferenceRequest extends FormRequest {
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
            'preference' => ['required', 'string', 'in:morning,afternoon,none'],
        ];
    }
}
