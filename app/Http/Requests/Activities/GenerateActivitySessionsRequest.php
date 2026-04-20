<?php

namespace App\Http\Requests\Activities;

use Illuminate\Foundation\Http\FormRequest;

class GenerateActivitySessionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'generate_from' => ['required', 'date'],
            'generate_to' => ['required', 'date', 'after_or_equal:generate_from'],
        ];
    }
}
