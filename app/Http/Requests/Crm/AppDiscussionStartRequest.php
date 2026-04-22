<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class AppDiscussionStartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient_user_id' => ['required', 'exists:users,id'],
            'subject' => ['nullable', 'string', 'max:180'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'body' => ['nullable', 'string', 'max:8000'],
            'source_type' => ['nullable', 'in:quote,invoice'],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:15360', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ((int) $this->input('recipient_user_id') === (int) optional($this->user())->id) {
                $validator->errors()->add('recipient_user_id', 'You cannot start a direct message with yourself.');
            }

            if (filled($this->input('source_type')) && blank($this->input('source_id'))) {
                $validator->errors()->add('source_id', 'A source record is required when a source type is supplied.');
            }
        });
    }
}
