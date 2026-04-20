<?php

namespace App\Http\Requests\Fee;

use App\Models\Fee\StudentDiscount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class StoreStudentDiscountRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'discount_type_id' => ['required', 'integer', 'exists:discount_types,id'],
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->hasDuplicateAssignment()) {
                $validator->errors()->add(
                    'discount_type_id',
                    'This student already has this discount type assigned for the selected year.'
                );
            }
        });
    }

    /**
     * Check if a duplicate assignment already exists.
     */
    protected function hasDuplicateAssignment(): bool
    {
        if (!$this->filled(['student_id', 'discount_type_id', 'year'])) {
            return false;
        }

        return StudentDiscount::query()
            ->where('student_id', $this->student_id)
            ->where('discount_type_id', $this->discount_type_id)
            ->where('year', $this->year)
            ->exists();
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Please select a student.',
            'student_id.exists' => 'The selected student does not exist.',
            'discount_type_id.required' => 'Please select a discount type.',
            'discount_type_id.exists' => 'The selected discount type does not exist.',
            'year.required' => 'Please select a year.',
            'year.integer' => 'Year must be a valid number.',
            'year.min' => 'Year must be 2020 or later.',
            'year.max' => 'Year must be 2099 or earlier.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'student',
            'discount_type_id' => 'discount type',
        ];
    }
}
