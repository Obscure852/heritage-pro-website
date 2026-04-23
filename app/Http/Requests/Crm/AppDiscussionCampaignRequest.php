<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppDiscussionCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:8000'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'recipient_user_ids' => ['nullable', 'array', 'max:100'],
            'recipient_user_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('active', true)
                        ->whereIn('role', array_keys(config('heritage_crm.roles', [])));
                }),
            ],
            'department_ids' => ['nullable', 'array', 'max:25'],
            'department_ids.*' => ['integer', 'exists:crm_user_departments,id'],
            'intent' => ['required', Rule::in(['draft', 'send'])],
            'source_type' => ['nullable', Rule::in(['quote', 'invoice'])],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:15360', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $recipientIds = collect($this->input('recipient_user_ids', []))
                ->filter(fn ($value) => filled($value));
            $departmentIds = collect($this->input('department_ids', []))
                ->filter(fn ($value) => filled($value));

            if ($recipientIds->isEmpty() && $departmentIds->isEmpty()) {
                $validator->errors()->add('recipient_user_ids', 'Select at least one user or department for the group chat.');
            }

            if (filled($this->input('source_type')) && blank($this->input('source_id'))) {
                $validator->errors()->add('source_id', 'A source record is required when a source type is supplied.');
            }
        });
    }
}
