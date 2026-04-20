<?php

namespace App\Http\Requests\Lms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateDiscussionRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        // Either a student or instructor can create discussions
        return Auth::guard('student')->check() || Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array {
        $isInstructor = Auth::check() && !Auth::guard('student')->check();
        $allowedTypes = $isInstructor ? 'discussion,question,announcement' : 'discussion,question';

        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string|min:10|max:50000',
            'category_id' => 'nullable|exists:lms_discussion_categories,id',
            'type' => 'required|in:' . $allowedTypes,
            'is_anonymous' => 'boolean',
            'is_pinned' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array {
        return [
            'title.required' => 'Please provide a title for your discussion.',
            'title.max' => 'The title cannot exceed 255 characters.',
            'body.required' => 'Please provide content for your discussion.',
            'body.min' => 'The discussion content must be at least 10 characters.',
            'body.max' => 'The discussion content cannot exceed 50,000 characters.',
            'type.required' => 'Please select a discussion type.',
            'type.in' => 'Invalid discussion type selected.',
        ];
    }
}
