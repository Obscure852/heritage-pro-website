<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrmUserRoleAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(array_keys(config('heritage_crm.roles', [])))],
            'module_permissions' => ['required', 'array'],
            'module_permissions.*' => ['nullable', Rule::in(array_keys(config('heritage_crm.permission_levels', [])))],
        ];
    }
}
