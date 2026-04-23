<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proposed_clock_in' => ['nullable', 'date'],
            'proposed_clock_out' => ['nullable', 'date'],
            'proposed_code_id' => ['nullable', 'exists:crm_attendance_codes,id'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('proposed_clock_in') && ! $this->filled('proposed_clock_out') && ! $this->filled('proposed_code_id')) {
                $validator->errors()->add('proposed_clock_in', 'At least one proposed change (clock in, clock out, or code) is required.');
            }
        });
    }
}
