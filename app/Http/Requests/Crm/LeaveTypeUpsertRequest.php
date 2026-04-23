<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveTypeUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $leaveTypeId = $this->route('leaveType')?->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:10', Rule::unique('crm_leave_types', 'code')->ignore($leaveTypeId)],
            'color' => ['required', 'string', 'max:7', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'default_days_per_year' => ['nullable', 'numeric', 'min:0', 'max:365'],
            'requires_attachment' => ['boolean'],
            'attachment_required_after_days' => ['nullable', 'integer', 'min:1'],
            'max_consecutive_days' => ['nullable', 'integer', 'min:1'],
            'min_notice_days' => ['integer', 'min:0'],
            'allow_half_day' => ['boolean'],
            'is_paid' => ['boolean'],
            'counts_as_working' => ['numeric', 'min:0', 'max:1'],
            'carry_over_limit' => ['nullable', 'numeric', 'min:0'],
            'gender_restriction' => ['nullable', 'in:female,male'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }
}
