<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class WaiveFineRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('waive-library-fines');
    }

    public function rules(): array {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array {
        return [
            'amount.required' => 'Waiver amount is required.',
            'amount.min' => 'Waiver amount must be at least P0.01.',
            'reason.required' => 'A reason for the waiver is required.',
            'reason.min' => 'Waiver reason must be at least 5 characters.',
        ];
    }
}
