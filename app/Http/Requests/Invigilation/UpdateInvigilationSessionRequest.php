<?php

namespace App\Http\Requests\Invigilation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvigilationSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-invigilation') ?? false;
    }

    public function rules(): array
    {
        return [
            'grade_subject_id' => ['required', 'integer', 'exists:grade_subject,id'],
            'paper_label' => ['nullable', 'string', 'max:255'],
            'exam_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'day_of_cycle' => ['nullable', 'integer', 'min:1', 'max:6'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
