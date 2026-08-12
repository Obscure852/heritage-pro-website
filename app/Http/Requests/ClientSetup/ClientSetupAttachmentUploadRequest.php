<?php

namespace App\Http\Requests\ClientSetup;

use Illuminate\Foundation\Http\FormRequest;

class ClientSetupAttachmentUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:100'],
            'requirement' => ['nullable', 'in:required,optional,if_migrating,if_applicable'],
            'attachment' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg', 'max:20480'],
        ];
    }
}
