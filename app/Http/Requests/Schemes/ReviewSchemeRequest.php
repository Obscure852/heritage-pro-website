<?php

namespace App\Http\Requests\Schemes;

use Illuminate\Foundation\Http\FormRequest;

class ReviewSchemeRequest extends FormRequest {
    /**
     * Authorization is handled in the controller via policy.
     */
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'comments' => 'required|string|min:5|max:2000',
        ];
    }

    public function messages(): array {
        return [
            'comments.required' => 'Please provide revision comments explaining what needs to be changed.',
            'comments.min'      => 'Comments must be at least 5 characters.',
        ];
    }
}
