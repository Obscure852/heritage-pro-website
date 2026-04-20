<?php

namespace App\Http\Requests\Lms;

use App\Models\Lms\Enrollment;
use App\Models\Lms\Quiz;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SubmitQuizRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return false;
        }

        $quiz = $this->route('quiz');

        if (!$quiz instanceof Quiz) {
            return false;
        }

        // Check if student is enrolled in the course
        $course = $quiz->contentItem?->module?->course;

        if (!$course) {
            return false;
        }

        return Enrollment::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array {
        return [
            'answers' => 'nullable|array',
            'answers.*' => 'nullable',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array {
        return [
            'answers.array' => 'Answers must be submitted as an array.',
        ];
    }
}
