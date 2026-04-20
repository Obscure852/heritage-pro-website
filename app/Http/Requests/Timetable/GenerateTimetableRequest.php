<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for timetable generation trigger request.
 */
class GenerateTimetableRequest extends FormRequest {
    public function authorize(): bool {
        return true; // Authorization handled by route middleware
    }

    public function rules(): array {
        return [
            'timetable_id' => ['required', 'integer', 'exists:timetables,id'],
        ];
    }
}
