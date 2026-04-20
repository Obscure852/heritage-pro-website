<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class SaveConsecutiveLimitRequest extends FormRequest {
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
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'max_consecutive_periods' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }
}
