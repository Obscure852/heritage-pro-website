<?php

namespace App\Http\Requests\Fee;

use App\Models\Fee\FeeStructure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeeStructureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fee_type_id' => [
                'required',
                'integer',
                Rule::exists('fee_types', 'id')->whereNull('deleted_at'),
            ],
            'grade_id' => [
                'required',
                'integer',
                Rule::exists('grades', 'id')->whereNull('deleted_at'),
            ],
            'year' => [
                'required',
                'integer',
                'min:2020',
                'max:2099',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check for historical year (locked)
            if ($this->isHistoricalYear()) {
                $validator->errors()->add(
                    'year',
                    'Cannot modify fee structures for historical years. Past years are locked.'
                );
            }

            // Check unique combination (excluding current record)
            if ($this->isUniqueCombinationViolated()) {
                $validator->errors()->add(
                    'fee_type_id',
                    'A fee structure with this fee type, grade, and year combination already exists.'
                );
            }
        });
    }

    /**
     * Check if the year is historical (in the past).
     */
    protected function isHistoricalYear(): bool
    {
        $feeStructure = $this->route('fee_structure') ?? $this->route('feeStructure');

        // Check if the fee structure's year is in the past
        if ($feeStructure instanceof FeeStructure) {
            return $feeStructure->year < (int) date('Y');
        }

        // If we have a raw ID, load the model
        if ($feeStructure) {
            $structure = FeeStructure::find($feeStructure);
            if ($structure) {
                return $structure->year < (int) date('Y');
            }
        }

        return false;
    }

    /**
     * Check if the combination of fee_type_id, grade_id, and year already exists.
     */
    protected function isUniqueCombinationViolated(): bool
    {
        if (!$this->fee_type_id || !$this->grade_id || !$this->year) {
            return false;
        }

        $feeStructure = $this->route('fee_structure') ?? $this->route('feeStructure');
        $currentId = $feeStructure instanceof FeeStructure ? $feeStructure->id : $feeStructure;

        return FeeStructure::where('fee_type_id', $this->fee_type_id)
            ->where('grade_id', $this->grade_id)
            ->where('year', $this->year)
            ->where('id', '!=', $currentId)
            ->exists();
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'fee_type_id.required' => 'Please select a fee type.',
            'fee_type_id.exists' => 'The selected fee type is invalid.',
            'grade_id.required' => 'Please select a grade.',
            'grade_id.exists' => 'The selected grade is invalid.',
            'year.required' => 'Year is required.',
            'year.integer' => 'Year must be a valid number.',
            'year.min' => 'Year must be 2020 or later.',
            'year.max' => 'Year must be 2099 or earlier.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount cannot be negative.',
            'amount.max' => 'Amount cannot exceed 999,999.99.',
        ];
    }
}
