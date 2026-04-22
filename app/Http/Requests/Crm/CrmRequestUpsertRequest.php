<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrmRequestUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->routeIs('crm.requests.sales.store')) {
            $this->merge(['type' => 'sales']);
        }

        if ($this->routeIs('crm.requests.support.store')) {
            $this->merge(['type' => 'support']);
        }
    }

    public function rules(): array
    {
        return [
            'owner_id' => ['nullable', 'exists:users,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'sales_stage_id' => ['nullable', 'exists:sales_stages,id'],
            'type' => ['required', Rule::in(['sales', 'support'])],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'support_status' => ['nullable', Rule::in(['open', 'in_progress', 'resolved', 'closed'])],
            'outcome' => ['nullable', Rule::in(['pending', 'won', 'lost'])],
            'next_action' => ['nullable', 'string', 'max:255'],
            'next_action_at' => ['nullable', 'date'],
            'last_contact_at' => ['nullable', 'date'],
            'closed_at' => ['nullable', 'date'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:15360', 'mimes:pdf,doc,docx'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');

            if ($type === 'sales' && blank($this->input('sales_stage_id'))) {
                $validator->errors()->add('sales_stage_id', 'Sales requests require a sales stage.');
            }

            if ($type === 'sales' && blank($this->input('lead_id'))) {
                $validator->errors()->add('lead_id', 'Sales requests must be linked to a lead.');
            }

            if ($type === 'support' && blank($this->input('support_status'))) {
                $validator->errors()->add('support_status', 'Support requests require a support status.');
            }

            if ($type === 'support' && blank($this->input('customer_id'))) {
                $validator->errors()->add('customer_id', 'Support requests must be linked to a customer.');
            }
        });
    }
}
