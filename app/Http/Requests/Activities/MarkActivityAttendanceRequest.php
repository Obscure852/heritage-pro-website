<?php

namespace App\Http\Requests\Activities;

use App\Models\Activities\ActivitySessionAttendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarkActivityAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance' => ['required', 'array', 'min:1'],
            'attendance.*.status' => ['required', Rule::in(array_keys(ActivitySessionAttendance::statuses()))],
            'attendance.*.remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
