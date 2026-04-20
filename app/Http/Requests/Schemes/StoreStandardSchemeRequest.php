<?php

namespace App\Http\Requests\Schemes;

use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Schemes\StandardScheme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreStandardSchemeRequest extends FormRequest {
    private bool $resolvedGradeSubjectLoaded = false;

    private ?GradeSubject $resolvedGradeSubject = null;

    public function authorize(): bool {
        return $this->user()->can('create', StandardScheme::class);
    }

    public function rules(): array {
        return [
            'subject_id'  => ['required', 'integer', 'exists:subjects,id'],
            'grade_id'    => ['required', 'integer', 'exists:grades,id'],
            'term_id'     => ['required', 'integer', 'exists:terms,id'],
            'total_weeks' => ['sometimes', 'integer', 'min:1', 'max:52'],
        ];
    }

    public function withValidator(Validator $validator): void {
        $validator->after(function (Validator $v) {
            $subjectId = $this->input('subject_id');
            $gradeId   = $this->input('grade_id');
            $termId    = $this->input('term_id');

            if (!$subjectId || !$gradeId || !$termId) {
                return;
            }

            $gradeSubject = $this->resolveGradeSubject((int) $subjectId, (int) $gradeId, (int) $termId);
            $resolvedGradeId = $gradeSubject?->grade_id ?? $gradeId;

            // Check for existing standard scheme for this subject+grade+term
            $duplicate = StandardScheme::where('subject_id', $subjectId)
                ->where('grade_id', $resolvedGradeId)
                ->where('term_id', $termId)
                ->exists();

            if ($duplicate) {
                $v->errors()->add('subject_id', 'A standard scheme already exists for this subject, grade, and term combination.');
            }

            // Resolve department_id from GradeSubject
            if (!$gradeSubject || is_null($gradeSubject->department_id)) {
                $v->errors()->add('subject_id', 'Could not resolve a department for this subject and grade combination.');
            }
        });
    }

    /**
     * Add the resolved department_id to validated data.
     */
    public function validated($key = null, $default = null): mixed {
        $data = parent::validated($key, $default);

        if ($key !== null) {
            return $data;
        }

        $gradeSubject = $this->resolveGradeSubject(
            (int) $data['subject_id'],
            (int) $data['grade_id'],
            (int) $data['term_id']
        );

        if ($gradeSubject) {
            $data['grade_id'] = $gradeSubject->grade_id;
        }

        $data['department_id'] = $gradeSubject?->department_id;

        return $data;
    }

    private function resolveGradeSubject(int $subjectId, int $gradeId, int $termId): ?GradeSubject
    {
        if ($this->resolvedGradeSubjectLoaded) {
            return $this->resolvedGradeSubject;
        }

        $this->resolvedGradeSubjectLoaded = true;

        $gradeSubject = GradeSubject::query()
            ->where('subject_id', $subjectId)
            ->where('term_id', $termId)
            ->where('grade_id', $gradeId)
            ->first();

        if ($gradeSubject) {
            return $this->resolvedGradeSubject = $gradeSubject;
        }

        $gradeName = Grade::query()
            ->whereKey($gradeId)
            ->value('name');

        if (!$gradeName) {
            return $this->resolvedGradeSubject = null;
        }

        return $this->resolvedGradeSubject = GradeSubject::query()
            ->where('subject_id', $subjectId)
            ->where('term_id', $termId)
            ->whereHas('grade', function ($q) use ($gradeName) {
                $q->where('name', $gradeName);
            })
            ->orderBy('id')
            ->first();
    }
}
