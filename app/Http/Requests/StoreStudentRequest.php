<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // Required name fields with regex to prevent special characters
            'first_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\-\']+$/'],
            'middle_name' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z\s\-\']+$/'],
            'last_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\-\']+$/'],

            // Required identification
            'gender' => 'required|in:M,F',
            'date_of_birth' => 'required|date|before:today|after:' . now()->subYears(30)->format('Y-m-d'),
            'id_number' => 'nullable|string|max:20|unique:students,id_number,NULL,id,deleted_at,NULL',
            'exam_number' => 'nullable|string|max:50',

            // Contact information
            'email' => 'nullable|email|max:255|unique:students,email,NULL,id,deleted_at,NULL',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',

            // Academic assignment
            'grade_id' => 'required|exists:grades,id',
            'klass_id' => 'nullable|exists:klasses,id',
            'house' => 'nullable|exists:houses,id',

            // Parent/Guardian
            'sponsor_id' => 'required|exists:sponsors,id',

            // Additional information
            'nationality' => 'required|string|max:100',
            'type' => 'nullable|string|max:50',
            'filter' => 'nullable|string|max:50',
            'status' => 'nullable|in:Current,Left,Suspended,Graduated',
            'religion' => 'nullable|string|max:100',
            'place_of_birth' => 'nullable|string|max:255',
            'home_language' => 'nullable|string|max:100',

            // SECURITY: File upload with strict validation
            // 'image' rule validates actual MIME type, not just extension
            'photo_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Name validation messages
            'first_name.required' => 'First name is required.',
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens and apostrophes.',
            'middle_name.regex' => 'Middle name can only contain letters, spaces, hyphens and apostrophes.',
            'last_name.required' => 'Last name is required.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens and apostrophes.',

            // Identification messages
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Gender must be M or F.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'date_of_birth.after' => 'Student cannot be older than 30 years.',
            'id_number.unique' => 'This ID number is already registered to another student.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',

            // Academic messages
            'grade_id.required' => 'Please select a grade.',
            'grade_id.exists' => 'The selected grade is invalid.',
            'klass_id.exists' => 'The selected class is invalid.',
            'house.exists' => 'The selected house is invalid.',

            // Parent/Guardian messages
            'sponsor_id.required' => 'Please select a parent/guardian.',
            'sponsor_id.exists' => 'The selected parent/guardian is invalid.',

            // Additional information messages
            'nationality.required' => 'Nationality is required.',

            // File upload messages
            'photo_path.image' => 'The file must be an image.',
            'photo_path.mimes' => 'Photo must be a JPEG, PNG, or JPG file.',
            'photo_path.max' => 'Photo must not exceed 2MB.',
        ];
    }
}
