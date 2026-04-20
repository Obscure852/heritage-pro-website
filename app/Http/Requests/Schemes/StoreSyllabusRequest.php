<?php

namespace App\Http\Requests\Schemes;
use App\Models\Grade;
use App\Models\Schemes\Syllabus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSyllabusRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('manage-syllabi');
    }

    public function rules(): array {
        return [
            'subject_id'  => [
                'required',
                'integer',
                Rule::exists('subjects', 'id'),
            ],
            'grades'      => ['required', 'array', 'min:1'],
            'grades.*'    => [
                'required',
                'string',
                Rule::in(Grade::pluck('name')->all()),
            ],
            'level'       => [
                'required',
                'string',
                Rule::in(Grade::distinct()->pluck('level')->all()),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active'   => ['sometimes', 'boolean'],
            'document_id' => ['nullable', 'integer', Rule::exists('documents', 'id')],
            'source_url'  => ['nullable', 'url', 'max:2048'],
        ];
    }

    public function withValidator($validator): void {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $subjectId = $this->input('subject_id');
            $level     = $this->input('level');
            $grades    = $this->input('grades', []);

            foreach ($grades as $grade) {
                $exists = Syllabus::where('subject_id', $subjectId)
                    ->where('level', $level)
                    ->whereNull('deleted_at')
                    ->forGrade($grade)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        'grades',
                        "An active syllabus already covers grade \"{$grade}\" for this subject and level."
                    );
                    break;
                }
            }
        });
    }

    public function messages(): array {
        return [
            'grades.required' => 'Please select at least one grade.',
            'grades.min'      => 'Please select at least one grade.',
        ];
    }
}
