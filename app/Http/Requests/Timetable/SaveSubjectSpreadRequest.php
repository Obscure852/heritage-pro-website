<?php

namespace App\Http\Requests\Timetable;

use App\Models\Timetable\TimetableSetting;
use Illuminate\Foundation\Http\FormRequest;

class SaveSubjectSpreadRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return $this->user()->can('manage-timetable');
    }

    protected function prepareForValidation(): void {
        if (!$this->filled('max_lessons_per_day') && $this->filled('max_periods_per_day')) {
            $this->merge([
                'max_lessons_per_day' => $this->input('max_periods_per_day'),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array {
        $periodsPerDay = max(1, (int) TimetableSetting::get('periods_per_day', 10));

        return [
            'timetable_id' => ['required', 'integer', 'exists:timetables,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'max_lessons_per_day' => ['required', 'integer', 'min:1', 'max:' . $periodsPerDay],
            'max_periods_per_day' => ['sometimes', 'integer', 'min:1', 'max:' . $periodsPerDay],
            'distribute_across_cycle' => ['sometimes', 'boolean'],
        ];
    }
}
