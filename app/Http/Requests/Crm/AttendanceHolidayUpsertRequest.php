<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceHolidayUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'date' => ['required', 'date'],
            'is_recurring' => ['nullable'],
            'applies_to' => ['required', 'string', Rule::in(['all', 'department', 'shift'])],
            'scope_id' => ['nullable', 'integer', 'required_unless:applies_to,all'],
            'is_active' => ['nullable'],
        ];
    }
}
