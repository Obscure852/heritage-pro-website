<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class CrmUserCompanyInformationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:160'],
            'company_email' => ['nullable', 'email', 'max:160'],
            'company_phone' => ['nullable', 'string', 'max:40'],
            'company_website' => ['nullable', 'url', 'max:160'],
            'company_address_line_1' => ['nullable', 'string', 'max:160'],
            'company_address_line_2' => ['nullable', 'string', 'max:160'],
            'company_city' => ['nullable', 'string', 'max:120'],
            'company_state' => ['nullable', 'string', 'max:120'],
            'company_country' => ['nullable', 'string', 'max:120'],
            'company_postal_code' => ['nullable', 'string', 'max:40'],
        ];
    }
}
