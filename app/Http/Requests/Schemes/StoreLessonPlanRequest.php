<?php

namespace App\Http\Requests\Schemes;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonPlanRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('access-schemes');
    }

    public function rules(): array {
        return [
            'scheme_of_work_id'       => ['nullable', 'integer', 'exists:schemes_of_work,id'],
            'scheme_of_work_entry_id' => ['nullable', 'integer', 'exists:scheme_of_work_entries,id'],
            'date'                    => ['required', 'date'],
            'period'                  => ['nullable', 'string', 'max:30'],
            'topic'                   => ['required', 'string', 'max:255'],
            'sub_topic'               => ['nullable', 'string', 'max:255'],
            'learning_objectives'     => ['nullable', 'string'],
            'content'                 => ['nullable', 'string'],
            'activities'              => ['nullable', 'string'],
            'teaching_learning_aids'  => ['nullable', 'string'],
            'lesson_evaluation'       => ['nullable', 'string'],
            'resources'               => ['nullable', 'string'],
            'homework'                => ['nullable', 'string'],
        ];
    }
}
