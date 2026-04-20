<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendBulkEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('access-communications');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $maxSizeInMB = settings('email.max_attachment_size', 10); // In MB
        $maxSizeInKB = $maxSizeInMB * 1024; // Convert MB to KB for Laravel validation

        $rules = [
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'recipient_type' => ['required', 'string', 'in:sponsor,user'],
            'attachment' => ['nullable', 'file', 'max:' . $maxSizeInKB],

            // Optional filters for sponsors
            'grade' => ['nullable', 'integer', 'exists:grades,id'],
            'sponsorFilter' => ['nullable', 'string', 'exists:sponsor_filters,id'],

            // Optional filters for users
            'department' => ['nullable', 'string', 'max:100'],
            'area_of_work' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'filter' => ['nullable', 'string', 'exists:user_filters,id'],
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'subject.required' => 'Please enter an email subject.',
            'subject.max' => 'The subject must not exceed 255 characters.',
            'message.required' => 'Please enter the email content.',
            'recipient_type.required' => 'Please select a recipient type.',
            'recipient_type.in' => 'Invalid recipient type. Must be either sponsor or user.',
            'grade.exists' => 'The selected grade does not exist.',
            'sponsorFilter.exists' => 'The selected sponsor filter does not exist.',
            'filter.exists' => 'The selected user filter does not exist.',
            'attachment.file' => 'The attachment must be a valid file.',
            'attachment.max' => 'The attachment must not exceed ' . settings('email.max_attachment_size', 10) . 'MB.',
            'attachment.mimetypes' => 'The attachment type is not allowed.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'message' => 'email content',
            'recipient_type' => 'recipient type',
            'sponsorFilter' => 'sponsor filter',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if attachments are enabled
            if ($this->hasFile('attachment') && !settings('features.allow_attachments', true)) {
                $validator->errors()->add('attachment', 'File attachments are currently disabled.');
            }

            // Check if email sending is enabled
            if (!settings('features.email_enabled', true)) {
                $validator->errors()->add('email', 'Email sending is currently disabled.');
            }

            // Validate that at least one filter is provided when filtering
            if ($this->input('recipient_type') === 'sponsor') {
                if (!$this->filled('grade') && !$this->filled('sponsorFilter')) {
                    // No validation error - will send to all sponsors
                }
            } elseif ($this->input('recipient_type') === 'user') {
                if (!$this->filled('department') && !$this->filled('area_of_work') &&
                    !$this->filled('position') && !$this->filled('filter')) {
                    // No validation error - will send to all users
                }
            }
        });
    }
}
