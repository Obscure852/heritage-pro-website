<?php

namespace App\Http\Requests\Activities;

use App\Models\Activities\ActivitySession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivitySessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activity_schedule_id' => ['nullable', 'integer', 'exists:activity_schedules,id'],
            'session_type' => ['required', Rule::in(array_keys(ActivitySession::sessionTypes()))],
            'session_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(ActivitySession::statuses()))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
