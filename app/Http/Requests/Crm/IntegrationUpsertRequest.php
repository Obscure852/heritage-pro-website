<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IntegrationUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:160'],
            'kind' => ['required', Rule::in(array_keys(config('heritage_crm.integration_kinds')))],
            'status' => ['required', Rule::in(array_keys(config('heritage_crm.integration_statuses')))],
            'school_code' => ['nullable', 'string', 'max:80'],
            'base_url' => ['nullable', 'url', 'max:255'],
            'auth_type' => ['nullable', 'string', 'max:60'],
            'api_key' => ['nullable', 'string', 'max:4000'],
            'webhook_url' => ['nullable', 'url', 'max:255'],
            'last_synced_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
