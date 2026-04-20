<?php

namespace App\Http\Requests\Fee;

use App\Models\Fee\FeeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeeTypeRequest extends FormRequest
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
        $feeTypeId = $this->route('fee_type') ?? $this->route('feeType');

        // Handle both model binding and raw ID
        if ($feeTypeId instanceof FeeType) {
            $feeTypeId = $feeTypeId->id;
        }

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('fee_types', 'code')
                    ->ignore($feeTypeId)
                    ->whereNull('deleted_at'),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'category' => [
                'required',
                Rule::in([
                    FeeType::CATEGORY_TUITION,
                    FeeType::CATEGORY_LEVY,
                    FeeType::CATEGORY_OPTIONAL,
                ]),
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_optional' => [
                'boolean',
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
            'code.required' => 'Fee type code is required.',
            'code.max' => 'Fee type code cannot exceed 20 characters.',
            'code.unique' => 'This fee type code already exists.',
            'name.required' => 'Fee type name is required.',
            'name.max' => 'Fee type name cannot exceed 100 characters.',
            'category.required' => 'Please select a fee category.',
            'category.in' => 'The selected category is invalid.',
        ];
    }
}
