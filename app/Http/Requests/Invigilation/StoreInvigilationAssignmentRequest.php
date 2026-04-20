<?php

namespace App\Http\Requests\Invigilation;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvigilationAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-invigilation') ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'locked' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'locked' => $this->boolean('locked'),
        ]);
    }
}
