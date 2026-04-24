<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExternalDiscussionDirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:180'],
            'recipient_type' => ['required', Rule::in(['user', 'lead', 'customer', 'contact', 'manual'])],
            'recipient_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'lead_id' => ['nullable', 'integer', 'exists:leads,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
            'recipient_email' => ['nullable', 'email', 'max:160'],
            'recipient_phone' => ['nullable', 'string', 'max:60'],
            'recipient_label' => ['nullable', 'string', 'max:160'],
            'integration_id' => ['nullable', 'exists:crm_integrations,id'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'body' => ['required', 'string', 'max:8000'],
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
            $recipientType = (string) $this->input('recipient_type');
            $channel = $this->currentChannel();
            $requiredFields = [
                'user' => 'recipient_user_id',
                'lead' => 'lead_id',
                'customer' => 'customer_id',
                'contact' => 'contact_id',
            ];

            if (isset($requiredFields[$recipientType]) && blank($this->input($requiredFields[$recipientType]))) {
                $validator->errors()->add($requiredFields[$recipientType], 'Select a recipient for this channel.');
            }

            if ($channel === 'email' && filled($this->input('integration_id'))) {
                $validator->errors()->add('integration_id', 'Email discussions do not use CRM integrations.');
            }

            if ($recipientType === 'manual' && $channel === 'email' && blank($this->input('recipient_email'))) {
                $validator->errors()->add('recipient_email', 'Manual email recipients require an email address.');
            }

            if ($recipientType === 'manual' && $channel === 'whatsapp' && blank($this->input('recipient_phone'))) {
                $validator->errors()->add('recipient_phone', 'Manual WhatsApp recipients require a phone number.');
            }

            if (filled($this->input('source_type')) && blank($this->input('source_id'))) {
                $validator->errors()->add('source_id', 'A source record is required when a source type is supplied.');
            }
        });
    }

    private function currentChannel(): string
    {
        if ($this->routeIs('crm.discussions.whatsapp.*')) {
            return 'whatsapp';
        }

        return 'email';
    }
}
