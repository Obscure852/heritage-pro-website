<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class ClientSetupRevisionCompareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['required', 'integer', 'min:1'],
            'to' => ['required', 'integer', 'min:1', 'different:from'],
        ];
    }
}
