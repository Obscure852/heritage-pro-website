<?php

namespace App\Http\Requests\ClientSetup;

use Illuminate\Foundation\Http\FormRequest;

class ClientSetupVerificationCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'digits:6'],
        ];
    }
}
