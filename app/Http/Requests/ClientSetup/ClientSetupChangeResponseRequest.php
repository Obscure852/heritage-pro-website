<?php

namespace App\Http\Requests\ClientSetup;

use Illuminate\Foundation\Http\FormRequest;

class ClientSetupChangeResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['client_response' => ['required', 'string', 'max:5000']];
    }
}
