<?php

namespace App\Http\Requests\StaffAttendance;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for staff attendance settings update.
 */
class UpdateStaffAttendanceSettingsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return $this->user()->can('manage-staff-attendance-settings');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            // General tab - Working hours
            'work_start_time' => ['sometimes', 'date_format:H:i'],
            'work_end_time' => ['sometimes', 'date_format:H:i'],
            'grace_period_minutes' => ['sometimes', 'integer', 'min:0', 'max:60'],

            // General tab - Hour thresholds
            'half_day_hours' => ['sometimes', 'numeric', 'min:1', 'max:12'],
            'full_day_hours' => ['sometimes', 'numeric', 'min:1', 'max:24'],
            'overtime_threshold_hours' => ['sometimes', 'numeric', 'min:1', 'max:24'],

            // Self-service tab
            'self_clock_in_enabled' => ['sometimes', 'boolean'],

            // Manual attendance tab
            'manual_attendance_enabled' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'work_start_time.date_format' => 'Work start time must be in HH:MM format.',
            'work_end_time.date_format' => 'Work end time must be in HH:MM format.',
            'grace_period_minutes.min' => 'Grace period must be at least 0 minutes.',
            'grace_period_minutes.max' => 'Grace period cannot exceed 60 minutes.',
            'half_day_hours.min' => 'Half day hours must be at least 1 hour.',
            'half_day_hours.max' => 'Half day hours cannot exceed 12 hours.',
            'full_day_hours.min' => 'Full day hours must be at least 1 hour.',
            'full_day_hours.max' => 'Full day hours cannot exceed 24 hours.',
            'overtime_threshold_hours.min' => 'Overtime threshold must be at least 1 hour.',
            'overtime_threshold_hours.max' => 'Overtime threshold cannot exceed 24 hours.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void {
        $booleanFields = ['self_clock_in_enabled', 'manual_attendance_enabled'];

        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => filter_var($this->$field, FILTER_VALIDATE_BOOLEAN),
                ]);
            }
        }
    }
}
