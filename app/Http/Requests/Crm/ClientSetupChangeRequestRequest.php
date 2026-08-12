<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class ClientSetupChangeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stage_key' => ['nullable', 'string', 'max:80'],
            'field_key' => ['nullable', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }
}
