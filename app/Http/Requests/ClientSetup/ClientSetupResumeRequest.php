<?php

namespace App\Http\Requests\ClientSetup;

use Illuminate\Foundation\Http\FormRequest;

class ClientSetupResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['email' => ['required', 'email:rfc', 'max:255']];
    }
}
