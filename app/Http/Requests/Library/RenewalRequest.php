<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class RenewalRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('manage-library');
    }

    public function rules(): array {
        return [
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
