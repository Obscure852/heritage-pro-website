<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for creating a leave balance adjustment.
 */
class StoreLeaveBalanceAdjustmentRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'adjustment_type' => ['required', 'string', Rule::in(['credit', 'debit', 'correction'])],
            'days' => ['required', 'numeric', 'min:0.5', 'max:365'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'adjustment_type.required' => 'Please select an adjustment type.',
            'adjustment_type.in' => 'Invalid adjustment type selected.',
            'days.required' => 'The number of days is required.',
            'days.numeric' => 'Days must be a valid number.',
            'days.min' => 'The minimum adjustment is 0.5 days.',
            'days.max' => 'The maximum adjustment is 365 days.',
            'reason.required' => 'A reason for the adjustment is required.',
            'reason.min' => 'Please provide a detailed reason (at least 10 characters).',
            'reason.max' => 'The reason cannot exceed 500 characters.',
        ];
    }
}
