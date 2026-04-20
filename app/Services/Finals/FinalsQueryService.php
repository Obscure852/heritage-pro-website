<?php

namespace App\Services\Finals;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class FinalsQueryService
{
    public function applyToFinalStudents(Builder|Relation $query, FinalsContextDefinition $definition): Builder|Relation
    {
        return $query->whereHas('graduationGrade', function ($gradeQuery) use ($definition) {
            $gradeQuery->whereIn('grades.name', $definition->graduationGradeNames);
        });
    }

    public function applyToFinalKlasses(Builder|Relation $query, FinalsContextDefinition $definition): Builder|Relation
    {
        return $query->whereHas('grade', function ($gradeQuery) use ($definition) {
            $gradeQuery->whereIn('grades.name', $definition->graduationGradeNames);
        });
    }

    public function applyToFinalGradeSubjects(Builder|Relation $query, FinalsContextDefinition $definition): Builder|Relation
    {
        return $query->whereHas('grade', function ($gradeQuery) use ($definition) {
            $gradeQuery->whereIn('grades.name', $definition->graduationGradeNames);
        });
    }

    public function applyToFinalKlassSubjects(Builder|Relation $query, FinalsContextDefinition $definition): Builder|Relation
    {
        return $query->whereHas('grade', function ($gradeQuery) use ($definition) {
            $gradeQuery->whereIn('grades.name', $definition->graduationGradeNames);
        });
    }

    public function applyToFinalOptionalSubjects(Builder|Relation $query, FinalsContextDefinition $definition): Builder|Relation
    {
        return $query->whereHas('grade', function ($gradeQuery) use ($definition) {
            $gradeQuery->whereIn('grades.name', $definition->graduationGradeNames);
        });
    }

    public function applyToFinalHouses(Builder|Relation $query, FinalsContextDefinition $definition): Builder|Relation
    {
        return $query->whereHas('finalStudents.graduationGrade', function ($gradeQuery) use ($definition) {
            $gradeQuery->whereIn('grades.name', $definition->graduationGradeNames);
        });
    }

    public function applyToExternalExamResults(Builder|Relation $query, FinalsContextDefinition $definition): Builder|Relation
    {
        return $query->whereHas('externalExam', function ($examQuery) use ($definition) {
            $examQuery->where('exam_type', $definition->examType);
        });
    }
}
