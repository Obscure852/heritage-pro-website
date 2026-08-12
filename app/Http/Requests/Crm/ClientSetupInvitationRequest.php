<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class ClientSetupInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:180'],
            'contact_name' => ['nullable', 'string', 'max:180'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'primary_contact_id' => ['nullable', 'exists:contacts,id'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
