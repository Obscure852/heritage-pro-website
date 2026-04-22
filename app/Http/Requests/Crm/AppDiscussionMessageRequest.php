<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class AppDiscussionMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string', 'max:8000'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:15360', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $attachments = $this->file('attachments', []);
            $hasFiles = is_array($attachments) && count($attachments) > 0;

            if (blank($this->input('body')) && ! $hasFiles) {
                $validator->errors()->add('body', 'Write a message or attach at least one file.');
            }
        });
    }
}
