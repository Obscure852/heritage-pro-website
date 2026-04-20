<?php

namespace App\Http\Requests\Houses;

use Illuminate\Foundation\Http\FormRequest;

class SyncHouseUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-houses') ?? false;
    }

    public function rules(): array
    {
        return [
            'users' => ['required', 'array', 'min:1'],
            'users.*' => ['integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'users.required' => 'No users were selected for allocation. Please select at least one user.',
            'users.array' => 'The selected users payload is invalid.',
            'users.min' => 'No users were selected for allocation. Please select at least one user.',
            'users.*.integer' => 'Each selected user must be a valid ID.',
            'users.*.exists' => 'One or more selected users do not exist.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $userIds = array_values(array_filter((array) $this->input('users', []), static function ($value) {
            return $value !== '0' && $value !== 0 && $value !== null && $value !== '';
        }));

        $this->merge([
            'users' => $userIds,
        ]);
    }
}
