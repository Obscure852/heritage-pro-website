<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrmUserFilterUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $filter = $this->route('crmUserFilter');

        return [
            'name' => ['required', 'string', 'max:160', Rule::unique('crm_user_filters', 'name')->ignore($filter?->id)],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
