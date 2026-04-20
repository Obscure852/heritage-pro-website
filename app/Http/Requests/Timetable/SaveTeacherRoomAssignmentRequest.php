<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class SaveTeacherRoomAssignmentRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('manage-timetable');
    }

    public function rules(): array {
        return [
            'timetable_id' => ['required', 'integer', 'exists:timetables,id'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'venue_id' => ['required', 'integer', 'exists:venues,id'],
        ];
    }
}
