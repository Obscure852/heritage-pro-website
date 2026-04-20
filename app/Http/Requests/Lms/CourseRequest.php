<?php

namespace App\Http\Requests\Lms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CourseRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return Gate::allows('manage-lms-courses');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array {
        $courseId = $this->route('course')?->id;

        return [
            'code' => 'required|string|max:50|unique:lms_courses,code,' . $courseId,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'grade_id' => 'required|exists:grades,id',
            'grade_subject_id' => 'required|exists:grade_subject,id',
            'term_id' => 'required|exists:terms,id',
            'instructor_id' => 'required|exists:users,id',
            'thumbnail' => 'nullable|image|max:2048',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'max_students' => 'nullable|integer|min:1|max:1000',
            'self_enrollment' => 'boolean',
            'enrollment_key' => 'nullable|string|max:50',
            'passing_grade' => 'nullable|numeric|min:0|max:100',
            'learning_objectives' => 'nullable|string|max:5000',
            'prerequisites_text' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array {
        return [
            'code.required' => 'Please provide a course code.',
            'code.unique' => 'This course code is already in use.',
            'code.max' => 'Course code cannot exceed 50 characters.',
            'title.required' => 'Please provide a course title.',
            'title.max' => 'Course title cannot exceed 255 characters.',
            'grade_id.required' => 'Please select a grade level.',
            'grade_id.exists' => 'The selected grade is invalid.',
            'grade_subject_id.required' => 'Please select a subject.',
            'grade_subject_id.exists' => 'The selected subject is invalid.',
            'term_id.required' => 'Please select a term.',
            'term_id.exists' => 'The selected term is invalid.',
            'instructor_id.required' => 'Please assign an instructor.',
            'instructor_id.exists' => 'The selected instructor is invalid.',
            'thumbnail.image' => 'The thumbnail must be an image.',
            'thumbnail.max' => 'The thumbnail cannot exceed 2MB.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
            'max_students.min' => 'Maximum students must be at least 1.',
            'max_students.max' => 'Maximum students cannot exceed 1000.',
            'passing_grade.min' => 'Passing grade cannot be negative.',
            'passing_grade.max' => 'Passing grade cannot exceed 100.',
        ];
    }
}
