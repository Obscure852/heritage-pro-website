<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for updating an existing leave type.
 */
class UpdateLeaveTypeRequest extends FormRequest {
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
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('leave_types', 'code')->ignore($this->leaveType->id),
            ],
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'default_entitlement' => 'required|numeric|min:0|max:365',
            'requires_attachment' => 'required|boolean',
            'attachment_required_after_days' => 'nullable|integer|min:1|max:30',
            'gender_restriction' => 'nullable|in:male,female',
            'is_paid' => 'required|boolean',
            'allow_negative_balance' => 'required|boolean',
            'allow_half_day' => 'required|boolean',
            'min_notice_days' => 'nullable|integer|min:0|max:90',
            'max_consecutive_days' => 'nullable|integer|min:1|max:365',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'code.required' => 'The leave type code is required.',
            'code.unique' => 'This leave type code already exists.',
            'code.max' => 'The code cannot exceed 20 characters.',
            'name.required' => 'The leave type name is required.',
            'name.max' => 'The name cannot exceed 100 characters.',
            'default_entitlement.required' => 'The default entitlement is required.',
            'default_entitlement.numeric' => 'The entitlement must be a number.',
            'default_entitlement.min' => 'The entitlement cannot be negative.',
            'default_entitlement.max' => 'The entitlement cannot exceed 365 days.',
            'color.regex' => 'The color must be a valid hex color code (e.g., #FF5733).',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void {
        $this->merge([
            'requires_attachment' => $this->boolean('requires_attachment'),
            'is_paid' => $this->boolean('is_paid'),
            'allow_negative_balance' => $this->boolean('allow_negative_balance'),
            'allow_half_day' => $this->boolean('allow_half_day'),
            'is_active' => $this->boolean('is_active'),
            // Set defaults for nullable fields that have database defaults
            'min_notice_days' => $this->input('min_notice_days') ?? 0,
        ]);
    }

    /**
     * Get the validated data with null values filtered for database defaults.
     *
     * @return array
     */
    public function validated($key = null, $default = null) {
        $validated = parent::validated($key, $default);

        if ($key === null) {
            // Filter out null values for fields that should use database defaults
            $fieldsWithDefaults = ['attachment_required_after_days', 'gender_restriction', 'max_consecutive_days', 'color'];
            foreach ($fieldsWithDefaults as $field) {
                if (array_key_exists($field, $validated) && $validated[$field] === null) {
                    unset($validated[$field]);
                }
            }
        }

        return $validated;
    }
}
