<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class LeaveApplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'exists:crm_leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_half' => ['sometimes', 'in:full,first_half,second_half'],
            'end_half' => ['sometimes', 'in:full,first_half,second_half'],
            'reason' => ['required', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ];
    }

    public function messages(): array
    {
        return [
            'attachments.*.max' => 'Each attachment must not exceed 5MB.',
            'attachments.max' => 'You can upload a maximum of 5 attachments.',
        ];
    }
}
