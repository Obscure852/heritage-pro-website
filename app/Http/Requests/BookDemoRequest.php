<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookDemoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:120'],
            'role' => ['required', 'string', 'max:120'],
            'institution' => ['required', 'string', 'max:160'],
            'work_email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:40'],
            'edition' => ['required', 'string', 'max:80'],
            'learner_band' => ['required', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
