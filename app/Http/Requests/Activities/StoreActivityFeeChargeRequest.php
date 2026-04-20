<?php

namespace App\Http\Requests\Activities;

use App\Models\Activities\ActivityFeeCharge;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityFeeChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'activity_event_id' => ['nullable', 'integer', 'exists:activity_events,id'],
            'charge_type' => ['required', Rule::in(array_keys(ActivityFeeCharge::chargeTypes()))],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
