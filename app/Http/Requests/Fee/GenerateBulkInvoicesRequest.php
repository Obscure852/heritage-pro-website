<?php

namespace App\Http\Requests\Fee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class GenerateBulkInvoicesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('collect-fees');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'grade_id' => ['required', 'integer', 'exists:grades,id'],
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'due_date' => ['nullable', 'date', 'after:today'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'grade_id.required' => 'Please select a grade.',
            'grade_id.exists' => 'The selected grade does not exist.',
            'year.required' => 'Please select a year.',
            'year.integer' => 'Year must be a valid number.',
            'year.min' => 'Year must be 2020 or later.',
            'year.max' => 'Year must be 2099 or earlier.',
            'due_date.date' => 'Please enter a valid due date.',
            'due_date.after' => 'The due date must be in the future.',
        ];
    }
}
