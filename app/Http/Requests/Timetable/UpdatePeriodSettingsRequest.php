<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePeriodSettingsRequest extends FormRequest {
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
            'period_definitions' => ['sometimes', 'array', 'min:1', 'max:12'],
            'period_definitions.*.period' => ['required', 'integer', 'min:1', 'max:12'],
            'period_definitions.*.start_time' => ['required', 'date_format:H:i'],
            'period_definitions.*.end_time' => ['required', 'date_format:H:i', 'after:period_definitions.*.start_time'],
            'period_definitions.*.duration' => ['required', 'integer', 'min:20', 'max:120'],
            'break_intervals' => ['sometimes', 'array'],
            'break_intervals.*.after_period' => ['required', 'integer', 'min:1'],
            'break_intervals.*.duration' => ['required', 'integer', 'min:5', 'max:90'],
            'break_intervals.*.label' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array {
        return [
            'period_definitions.*.start_time.date_format' => 'Start time must be in HH:MM format.',
            'period_definitions.*.end_time.date_format' => 'End time must be in HH:MM format.',
            'period_definitions.*.end_time.after' => 'End time must be after start time.',
            'period_definitions.*.duration.min' => 'Period duration must be at least 20 minutes.',
            'period_definitions.*.duration.max' => 'Period duration cannot exceed 120 minutes.',
            'break_intervals.*.duration.min' => 'Break duration must be at least 5 minutes.',
            'break_intervals.*.duration.max' => 'Break duration cannot exceed 90 minutes.',
        ];
    }
}
