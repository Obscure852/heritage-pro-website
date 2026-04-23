<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class LeaveBalanceAdjustRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'adjustment' => ['required', 'numeric', 'min:-365', 'max:365'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }
}
