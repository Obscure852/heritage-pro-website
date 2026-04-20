<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendDirectEmailRequest extends FormRequest
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
            'recipient_email' => ['required', 'email:rfc,dns'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'receiver_id' => ['required', 'integer'],
            'receiver_type' => ['required', 'string', 'in:user,sponsor'],
            'attachment' => ['nullable', 'file', 'max:' . $maxSizeInKB],
        ];

        // Validate receiver_id exists in the appropriate table
        if ($this->input('receiver_type') === 'user') {
            $rules['receiver_id'][] = 'exists:users,id';
        } elseif ($this->input('receiver_type') === 'sponsor') {
            $rules['receiver_id'][] = 'exists:sponsors,id';
        }

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
            'recipient_email.required' => 'Please enter the recipient\'s email address.',
            'recipient_email.email' => 'Please enter a valid email address.',
            'subject.required' => 'Please enter an email subject.',
            'subject.max' => 'The subject must not exceed 255 characters.',
            'body.required' => 'Please enter the email content.',
            'receiver_id.required' => 'Receiver ID is required.',
            'receiver_id.exists' => 'The selected receiver does not exist.',
            'receiver_type.required' => 'Please specify the receiver type.',
            'receiver_type.in' => 'Invalid receiver type. Must be either user or sponsor.',
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
            'recipient_email' => 'email address',
            'receiver_id' => 'recipient',
            'receiver_type' => 'recipient type',
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
        });
    }
}
