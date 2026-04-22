<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiscussionThreadStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:180'],
            'channel' => ['required', Rule::in(array_keys(config('heritage_crm.discussion_channels')))],
            'recipient_user_id' => ['nullable', 'exists:users,id'],
            'recipient_email' => ['nullable', 'email', 'max:160'],
            'recipient_phone' => ['nullable', 'string', 'max:60'],
            'integration_id' => ['nullable', 'exists:crm_integrations,id'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'body' => ['required', 'string', 'max:8000'],
            'source_type' => ['nullable', Rule::in(['quote', 'invoice'])],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:15360', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $channel = $this->input('channel');

            if ($channel === 'app' && blank($this->input('recipient_user_id'))) {
                $validator->errors()->add('recipient_user_id', 'An in-app discussion requires a recipient user.');
            }

            if ($channel === 'email' && blank($this->input('recipient_email'))) {
                $validator->errors()->add('recipient_email', 'An email discussion requires a recipient email address.');
            }

            if ($channel === 'whatsapp' && blank($this->input('recipient_phone'))) {
                $validator->errors()->add('recipient_phone', 'A WhatsApp discussion requires a phone number.');
            }

            if (filled($this->input('source_type')) && blank($this->input('source_id'))) {
                $validator->errors()->add('source_id', 'A source record is required when a source type is supplied.');
            }
        });
    }
}
