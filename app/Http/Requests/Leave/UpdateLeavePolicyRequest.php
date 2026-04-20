<?php

namespace App\Http\Requests\Leave;

use App\Models\Leave\LeavePolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for updating an existing leave policy.
 */
class UpdateLeavePolicyRequest extends FormRequest {
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
            'leave_year' => [
                'required',
                'integer',
                'min:2020',
                'max:2099',
                Rule::unique('leave_policies')->where(function ($query) {
                    return $query->where('leave_type_id', $this->route('leaveType')->id);
                })->ignore($this->route('policy')->id),
            ],
            'balance_mode' => [
                'required',
                'string',
                Rule::in([LeavePolicy::MODE_ALLOCATION, LeavePolicy::MODE_ACCRUAL]),
            ],
            'accrual_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:31',
                'required_if:balance_mode,' . LeavePolicy::MODE_ACCRUAL,
            ],
            'carry_over_mode' => [
                'required',
                'string',
                Rule::in([LeavePolicy::CARRY_NONE, LeavePolicy::CARRY_LIMITED, LeavePolicy::CARRY_FULL]),
            ],
            'carry_over_limit' => [
                'nullable',
                'numeric',
                'min:0',
                'max:365',
                'required_if:carry_over_mode,' . LeavePolicy::CARRY_LIMITED,
            ],
            'carry_over_expiry_months' => [
                'nullable',
                'integer',
                'min:1',
                'max:12',
            ],
            'prorate_new_employees' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'leave_year.required' => 'The leave year is required.',
            'leave_year.unique' => 'A policy for this year already exists for this leave type.',
            'leave_year.min' => 'The leave year must be 2020 or later.',
            'balance_mode.required' => 'Please select a balance mode.',
            'balance_mode.in' => 'Invalid balance mode selected.',
            'accrual_rate.required_if' => 'Accrual rate is required when using accrual mode.',
            'accrual_rate.max' => 'Accrual rate cannot exceed 31 days per month.',
            'carry_over_mode.required' => 'Please select a carry-over mode.',
            'carry_over_mode.in' => 'Invalid carry-over mode selected.',
            'carry_over_limit.required_if' => 'Carry-over limit is required when using limited carry-over mode.',
            'carry_over_limit.max' => 'Carry-over limit cannot exceed 365 days.',
            'carry_over_expiry_months.max' => 'Carry-over expiry cannot exceed 12 months.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void {
        $this->merge([
            'prorate_new_employees' => $this->boolean('prorate_new_employees'),
        ]);
    }
}
