<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrmUserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            CrmUserProfileRules::identity(),
            CrmUserProfileRules::work(),
            [
            'role' => ['required', Rule::in(array_keys(config('heritage_crm.roles', [])))],
            'active' => ['nullable', 'boolean'],
            'custom_filter_ids' => ['nullable', 'array'],
            'custom_filter_ids.*' => ['integer', 'exists:crm_user_filters,id'],
            ]
        );
    }
}
