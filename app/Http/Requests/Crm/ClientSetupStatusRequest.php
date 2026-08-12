<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientSetupStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in([
            'draft', 'academic_submitted', 'supplemental_in_progress', 'complete_submission',
            'under_review', 'changes_requested', 'approved', 'archived',
        ])]];
    }
}
