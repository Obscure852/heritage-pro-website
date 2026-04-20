<?php

namespace App\Http\Requests\Leave;

use App\Models\Leave\PublicHoliday;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for creating a new public holiday.
 */
class StorePublicHolidayRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            'name' => ['required', 'string', 'max:100'],
            'date' => ['required', 'date'],
            'is_recurring' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator): void {
        $validator->after(function ($validator) {
            if ($this->hasDuplicateHoliday()) {
                $validator->errors()->add('date', 'A holiday already exists on this date.');
            }
        });
    }

    /**
     * Check if a holiday already exists on the given date.
     *
     * @return bool
     */
    protected function hasDuplicateHoliday(): bool {
        if (!$this->date) {
            return false;
        }

        return PublicHoliday::whereDate('date', $this->date)->exists();
    }

    /**
     * Get custom attribute names.
     *
     * @return array
     */
    public function attributes(): array {
        return [
            'name' => 'holiday name',
            'date' => 'holiday date',
            'is_recurring' => 'recurring status',
            'description' => 'description',
            'is_active' => 'active status',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages(): array {
        return [
            'name.required' => 'Please enter a name for the holiday.',
            'name.max' => 'The holiday name cannot exceed 100 characters.',
            'date.required' => 'Please select a date for the holiday.',
            'date.date' => 'Please enter a valid date.',
            'is_recurring.required' => 'Please specify if this holiday is recurring.',
            'is_active.required' => 'Please specify if this holiday is active.',
            'description.max' => 'The description cannot exceed 500 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void {
        $this->merge([
            'is_recurring' => $this->boolean('is_recurring'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
