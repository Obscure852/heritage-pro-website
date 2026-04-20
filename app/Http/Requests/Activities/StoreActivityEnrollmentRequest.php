<?php

namespace App\Http\Requests\Activities;

use App\Models\Activities\Activity;
use Illuminate\Foundation\Http\FormRequest;

class StoreActivityEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $activity = $this->route('activity');

        return $activity instanceof Activity
            ? ($this->user()?->can('manageRoster', $activity) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'joined_at' => ['nullable', 'date'],
        ];
    }
}
