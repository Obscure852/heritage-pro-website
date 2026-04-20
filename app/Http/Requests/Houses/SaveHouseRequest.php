<?php

namespace App\Http\Requests\Houses;

use Illuminate\Foundation\Http\FormRequest;

class SaveHouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-houses') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'color_code' => ['required', 'regex:/^#[0-9A-F]{6}$/'],
            'head' => ['required', 'integer', 'exists:users,id'],
            'assistant' => ['required', 'integer', 'exists:users,id'],
            'year' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The house name is required.',
            'name.string' => 'The house name must be a valid string.',
            'name.max' => 'The house name must not exceed 255 characters.',
            'color_code.required' => 'The house color is required.',
            'color_code.regex' => 'The house color must be a valid hex color in the format #RRGGBB.',
            'head.required' => 'The head user ID is required.',
            'head.integer' => 'The head of house ID must be a valid integer.',
            'head.exists' => 'The selected head user does not exist in the database.',
            'assistant.required' => 'The assistant user ID is required.',
            'assistant.integer' => 'The assistant user ID must be a valid integer.',
            'assistant.exists' => 'The selected assistant user does not exist in the database.',
            'year.required' => 'The year is required.',
            'year.integer' => 'The year must be a valid integer.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('color_code')) {
            $this->merge([
                'color_code' => strtoupper((string) $this->input('color_code')),
            ]);
        }
    }
}
