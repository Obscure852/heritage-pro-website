<?php

namespace App\Http\Requests\Lms;

use App\Models\Lms\Assignment;
use App\Models\Lms\Enrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SubmitAssignmentRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return false;
        }

        $assignment = $this->route('assignment');

        if (!$assignment instanceof Assignment) {
            return false;
        }

        // Check if student can submit to this assignment
        return $assignment->canStudentSubmit($student->id);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array {
        $assignment = $this->route('assignment');
        $rules = [];

        if ($assignment && in_array($assignment->submission_type, ['text', 'both'])) {
            $textRule = $assignment->require_submission_text ? 'required' : 'nullable';
            $rules['submission_text'] = $textRule . '|string|max:50000';
        }

        if ($assignment && in_array($assignment->submission_type, ['file', 'both'])) {
            $fileTypes = implode(',', $assignment->allowed_file_types ?? Assignment::DEFAULT_FILE_TYPES);
            $maxSize = ($assignment->max_file_size_mb ?? 10) * 1024;
            $rules['files'] = 'nullable|array|max:' . ($assignment->max_files ?? 5);
            $rules['files.*'] = "file|mimes:{$fileTypes}|max:{$maxSize}";
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array {
        return [
            'submission_text.required' => 'A text submission is required for this assignment.',
            'submission_text.max' => 'The submission text cannot exceed 50,000 characters.',
            'files.max' => 'You can only upload a limited number of files.',
            'files.*.max' => 'Each file cannot exceed the maximum file size.',
            'files.*.mimes' => 'The file type is not allowed for this assignment.',
        ];
    }
}
