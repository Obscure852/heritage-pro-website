<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceCodeUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $codeId = $this->route('attendanceCode')?->id;

        return [
            'code' => ['required', 'string', 'max:8', Rule::unique('crm_attendance_codes', 'code')->ignore($codeId)],
            'label' => ['required', 'string', 'max:100'],
            'color' => ['required', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'category' => ['required', 'string', Rule::in(['presence', 'absence', 'leave', 'holiday', 'duty'])],
            'counts_as_working' => ['required', 'numeric', 'min:0', 'max:1'],
            'is_active' => ['nullable'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => strtoupper(trim($this->input('code')))]);
        }
    }
}
