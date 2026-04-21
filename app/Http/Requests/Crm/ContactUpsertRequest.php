<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class ContactUpsertRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:160'],
            'job_title' => ['nullable', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:60'],
            'is_primary' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $leadId = $this->input('lead_id');
            $customerId = $this->input('customer_id');

            if (blank($leadId) && blank($customerId)) {
                $validator->errors()->add('lead_id', 'A contact must be linked to a lead or a customer.');
            }

            if (!blank($leadId) && !blank($customerId)) {
                $validator->errors()->add('customer_id', 'A contact cannot be linked to both a lead and a customer.');
            }
        });
    }
}
