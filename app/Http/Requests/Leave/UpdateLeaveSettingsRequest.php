<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for leave settings update.
 */
class UpdateLeaveSettingsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return $this->user()->can('manage-leave-settings');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            // General settings
            'leave_year_start_month' => ['sometimes', 'integer', 'min:1', 'max:12'],
            'weekend_days' => ['sometimes', 'nullable', 'array'],
            'weekend_days.*' => ['integer', 'min:0', 'max:6'],
            'default_balance_mode' => ['sometimes', 'string', 'in:allocation,accrual'],
            'default_carry_over_mode' => ['sometimes', 'string', 'in:none,limited,full'],

            // Request settings
            'allow_backdated_requests' => ['sometimes', 'boolean'],
            'backdated_max_days' => ['sometimes', 'integer', 'min:1', 'max:30'],
            'leave_request_approval_required' => ['sometimes', 'boolean'],
            'max_negative_balance' => ['sometimes', 'integer', 'min:0', 'max:30'],
            'auto_cancel_pending_enabled' => ['sometimes', 'boolean'],
            'auto_cancel_pending_days' => ['sometimes', 'integer', 'min:1', 'max:90'],

            // Notification settings
            'leave_reminder_days_before' => ['sometimes', 'integer', 'min:1', 'max:14'],
            'pending_approval_reminder_hours' => ['sometimes', 'integer', 'min:1', 'max:72'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'leave_year_start_month.min' => 'Month must be between 1 (January) and 12 (December).',
            'leave_year_start_month.max' => 'Month must be between 1 (January) and 12 (December).',
            'weekend_days.*.min' => 'Day must be between 0 (Sunday) and 6 (Saturday).',
            'weekend_days.*.max' => 'Day must be between 0 (Sunday) and 6 (Saturday).',
            'default_balance_mode.in' => 'Balance mode must be either allocation or accrual.',
            'default_carry_over_mode.in' => 'Carry-over mode must be none, limited, or full.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void {
        // Convert checkbox values to booleans
        if ($this->has('allow_backdated_requests')) {
            $this->merge([
                'allow_backdated_requests' => filter_var($this->allow_backdated_requests, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('leave_request_approval_required')) {
            $this->merge([
                'leave_request_approval_required' => filter_var($this->leave_request_approval_required, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('auto_cancel_pending_enabled')) {
            $this->merge([
                'auto_cancel_pending_enabled' => filter_var($this->auto_cancel_pending_enabled, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
