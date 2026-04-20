<?php

namespace App\Http\Requests\Fee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class GrantClearanceOverrideRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('manage-fee-setup');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'A student must be selected.',
            'student_id.exists' => 'The selected student does not exist.',
            'year.required' => 'A year must be selected.',
            'year.integer' => 'Year must be a valid number.',
            'year.min' => 'Year must be 2020 or later.',
            'year.max' => 'Year must be 2099 or earlier.',
            'reason.required' => 'A reason for the override is required.',
            'reason.min' => 'Please provide a more detailed reason (at least 10 characters).',
            'reason.max' => 'The reason cannot exceed 500 characters.',
            'notes.max' => 'The notes cannot exceed 1000 characters.',
        ];
    }
}
