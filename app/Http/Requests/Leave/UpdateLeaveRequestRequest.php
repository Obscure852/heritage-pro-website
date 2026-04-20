<?php

namespace App\Http\Requests\Leave;

use App\Models\Leave\LeaveSetting;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for updating an existing leave request.
 *
 * Allows partial updates - only provided fields are validated.
 * Only pending leave requests can be updated.
 * Note: Business rules are validated in LeaveRequestService::validateRequest().
 */
class UpdateLeaveRequestRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        // Authorization is handled by policy in controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        $rules = [
            'leave_type_id' => 'sometimes|required|exists:leave_types,id',
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'start_half_day' => 'nullable|in:am,pm',
            'end_half_day' => 'nullable|in:am,pm',
            'reason' => 'sometimes|required|string|max:1000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
            // idempotency_key is not updatable
        ];

        // Add date validation based on backdated setting (only if start_date is being updated)
        if ($this->has('start_date')) {
            $allowBackdated = LeaveSetting::get('allow_backdated_requests', false);
            if (!$allowBackdated) {
                $rules['start_date'][] = 'after_or_equal:today';
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'leave_type_id.required' => 'Please select a leave type.',
            'leave_type_id.exists' => 'The selected leave type is invalid.',
            'start_date.required' => 'Please select a start date.',
            'start_date.date' => 'Please enter a valid start date.',
            'start_date.after_or_equal' => 'Leave start date cannot be in the past.',
            'end_date.required' => 'Please select an end date.',
            'end_date.date' => 'Please enter a valid end date.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
            'start_half_day.in' => 'Invalid half-day selection for start date.',
            'end_half_day.in' => 'Invalid half-day selection for end date.',
            'reason.required' => 'Please provide a reason for your leave request.',
            'reason.max' => 'Reason cannot exceed 1000 characters.',
            'attachments.array' => 'Attachments must be provided as an array.',
            'attachments.*.file' => 'Each attachment must be a valid file.',
            'attachments.*.mimes' => 'Attachments must be PDF, JPG, JPEG, or PNG files.',
            'attachments.*.max' => 'Each attachment must be less than 5MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array {
        return [
            'leave_type_id' => 'leave type',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'start_half_day' => 'start half-day',
            'end_half_day' => 'end half-day',
        ];
    }
}
