<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $lead = $this->route('lead');
        $allowedStatuses = ['active', 'qualified', 'lost'];

        if ($lead && ($lead->converted_at !== null || $lead->status === 'converted')) {
            $allowedStatuses[] = 'converted';
        }

        return [
            'owner_id' => ['nullable', 'exists:users,id'],
            'company_name' => ['required', 'string', 'max:160'],
            'industry' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:60'],
            'country' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in($allowedStatuses)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $lead = $this->route('lead');

            if (
                $this->input('status') === 'converted'
                && (! $lead || ($lead->converted_at === null && $lead->status !== 'converted'))
            ) {
                $validator->errors()->add('status', 'Leads can only be marked converted through the conversion workflow.');
            }
        });
    }
}
