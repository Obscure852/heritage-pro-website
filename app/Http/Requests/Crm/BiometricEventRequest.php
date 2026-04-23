<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BiometricEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_id' => ['required', 'string', 'max:50'],
            'employee_identifier' => ['required', 'string', 'max:50'],
            'event_type' => ['required', 'string', Rule::in(['clock_in', 'clock_out'])],
            'captured_at' => ['required', 'date'],
            'verification_method' => ['nullable', 'string', 'max:20'],
            'confidence_score' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ];
    }
}
