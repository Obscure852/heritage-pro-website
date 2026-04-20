<?php

namespace App\Http\Requests\Lms;

use App\Models\Lms\AssignmentSubmission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class SaveGradeRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return Gate::allows('grade-lms-content');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array {
        $submission = $this->route('submission');
        $maxPoints = 100;

        if ($submission instanceof AssignmentSubmission && $submission->assignment) {
            $maxPoints = $submission->assignment->max_points ?? 100;
        }

        return [
            'score' => "required|numeric|min:0|max:{$maxPoints}",
            'feedback' => 'nullable|string|max:10000',
            'rubric_scores' => 'nullable|array',
            'rubric_scores.*.level_id' => 'nullable|exists:lms_rubric_levels,id',
            'rubric_scores.*.points' => 'nullable|numeric|min:0',
            'rubric_scores.*.comment' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array {
        return [
            'score.required' => 'A score is required.',
            'score.numeric' => 'The score must be a number.',
            'score.min' => 'The score cannot be negative.',
            'score.max' => 'The score cannot exceed the maximum points for this assignment.',
            'feedback.max' => 'Feedback cannot exceed 10,000 characters.',
            'rubric_scores.*.points.min' => 'Rubric points cannot be negative.',
            'rubric_scores.*.comment.max' => 'Rubric comments cannot exceed 1,000 characters.',
        ];
    }
}
