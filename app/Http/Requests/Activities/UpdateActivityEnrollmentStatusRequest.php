<?php

namespace App\Http\Requests\Activities;

use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEnrollment;
use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityEnrollmentStatusRequest extends FormRequest
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
            'status' => ['required', 'in:' . implode(',', array_keys(ActivityEnrollment::closableStatuses()))],
            'left_at' => ['nullable', 'date'],
            'exit_reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
