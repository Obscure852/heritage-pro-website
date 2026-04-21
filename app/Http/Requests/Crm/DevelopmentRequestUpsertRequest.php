<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DevelopmentRequestUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_id' => ['nullable', 'exists:users,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:8000'],
            'requested_by' => ['nullable', 'string', 'max:160'],
            'priority' => ['required', Rule::in(array_keys(config('heritage_crm.development_priorities')))],
            'status' => ['required', Rule::in(array_keys(config('heritage_crm.development_statuses')))],
            'target_module' => ['nullable', 'string', 'max:120'],
            'business_value' => ['nullable', 'string', 'max:4000'],
            'next_step' => ['nullable', 'string', 'max:255'],
            'due_at' => ['nullable', 'date'],
        ];
    }
}
