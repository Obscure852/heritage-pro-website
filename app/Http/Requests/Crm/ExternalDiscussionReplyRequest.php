<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class ExternalDiscussionReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:8000'],
            'mention_user_ids' => ['prohibited'],
            'mention_user_ids.*' => ['prohibited'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:15360', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp'],
        ];
    }
}
