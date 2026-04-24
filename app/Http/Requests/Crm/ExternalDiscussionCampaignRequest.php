<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExternalDiscussionCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:8000'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'integration_id' => ['nullable', 'exists:crm_integrations,id'],
            'recipient_user_ids' => ['nullable', 'array', 'max:100'],
            'recipient_user_ids.*' => ['integer', 'exists:users,id'],
            'lead_ids' => ['nullable', 'array', 'max:100'],
            'lead_ids.*' => ['integer', 'exists:leads,id'],
            'customer_ids' => ['nullable', 'array', 'max:100'],
            'customer_ids.*' => ['integer', 'exists:customers,id'],
            'contact_ids' => ['nullable', 'array', 'max:100'],
            'contact_ids.*' => ['integer', 'exists:contacts,id'],
            'intent' => ['required', Rule::in(['draft', 'send'])],
            'source_type' => ['nullable', Rule::in(['quote', 'invoice'])],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:15360', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->routeIs('crm.discussions.email.*') && filled($this->input('integration_id'))) {
                $validator->errors()->add('integration_id', 'Email campaigns do not use CRM integrations.');
            }

            $hasRecipients = collect([
                $this->input('recipient_user_ids', []),
                $this->input('lead_ids', []),
                $this->input('customer_ids', []),
                $this->input('contact_ids', []),
            ])->flatten()->filter()->isNotEmpty();

            if (! $hasRecipients) {
                $validator->errors()->add('recipient_user_ids', 'Select at least one recipient for the bulk message.');
            }

            if (filled($this->input('source_type')) && blank($this->input('source_id'))) {
                $validator->errors()->add('source_id', 'A source record is required when a source type is supplied.');
            }
        });
    }
}
