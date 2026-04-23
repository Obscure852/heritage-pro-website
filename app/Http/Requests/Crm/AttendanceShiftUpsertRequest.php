<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceShiftUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'is_default' => ['nullable'],
            'grace_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'early_out_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'overtime_after_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'earliest_clock_in' => ['nullable', 'date_format:H:i'],
            'latest_clock_in' => ['nullable', 'date_format:H:i'],
            'is_active' => ['nullable'],
            'days' => ['required', 'array', 'size:7'],
            'days.*.start_time' => ['required', 'date_format:H:i'],
            'days.*.end_time' => ['required', 'date_format:H:i'],
            'days.*.is_working_day' => ['nullable'],
        ];
    }
}
