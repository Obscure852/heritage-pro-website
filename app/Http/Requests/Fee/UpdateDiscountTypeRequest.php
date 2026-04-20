<?php

namespace App\Http\Requests\Fee;

use App\Models\Fee\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDiscountTypeRequest extends FormRequest
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
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('discount_types', 'code')
                    ->whereNull('deleted_at')
                    ->ignore($this->discountType),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'percentage' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'applies_to' => [
                'required',
                Rule::in([
                    DiscountType::APPLIES_TO_ALL,
                    DiscountType::APPLIES_TO_TUITION_ONLY,
                ]),
            ],
            'is_active' => [
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Discount type code is required.',
            'code.max' => 'Discount type code cannot exceed 20 characters.',
            'code.unique' => 'This discount type code already exists.',
            'name.required' => 'Discount type name is required.',
            'name.max' => 'Discount type name cannot exceed 100 characters.',
            'percentage.required' => 'Discount percentage is required.',
            'percentage.numeric' => 'Discount percentage must be a number.',
            'percentage.min' => 'Discount percentage cannot be less than 0.',
            'percentage.max' => 'Discount percentage cannot exceed 100.',
            'applies_to.required' => 'Please select what fees this discount applies to.',
            'applies_to.in' => 'The selected applies to option is invalid.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set defaults for boolean fields if not provided
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }
}
