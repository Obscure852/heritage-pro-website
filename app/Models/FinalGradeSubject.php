<?php

namespace App\Models;

use App\Models\Grade;
use App\Models\Subject;
use App\Models\Term;
use App\Models\Department;
use App\Models\GradeSubject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinalGradeSubject extends Model{
    use HasFactory;

    protected $fillable = [
        'original_grade_subject_id',
        'grade_id',
        'subject_id',
        'graduation_term_id',
        'department_id',
        'graduation_year',
        'type',
        'mandatory',
    ];

    protected $casts = [
        'mandatory' => 'boolean',
    ];

    public function originalGradeSubject(): BelongsTo{
        return $this->belongsTo(GradeSubject::class, 'original_grade_subject_id');
    }

    public function grade(): BelongsTo{
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function subject(): BelongsTo{
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function graduationTerm(): BelongsTo{
        return $this->belongsTo(Term::class, 'graduation_term_id');
    }

    public function department(): BelongsTo{
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function finalKlassSubjects(): HasMany{
        return $this->hasMany(FinalKlassSubject::class);
    }

    public function finalOptionalSubjects(): HasMany{
        return $this->hasMany(FinalOptionalSubject::class);
    }

    public function externalExamSubjectResults(): HasMany{
        return $this->hasMany(ExternalExamSubjectResult::class);
    }

    public function scopeByGraduationYear(Builder $query, int $year): Builder{
        return $query->where('graduation_year', $year);
    }

    public function scopeByGraduationTerm(Builder $query, int $termId): Builder{
        return $query->where('graduation_term_id', $termId);
    }

    public function scopeByGrade(Builder $query, int $gradeId): Builder{
        return $query->where('grade_id', $gradeId);
    }

    public function scopeBySubject(Builder $query, int $subjectId): Builder{
        return $query->where('subject_id', $subjectId);
    }

    public function scopeByDepartment(Builder $query, int $departmentId): Builder{
        return $query->where('department_id', $departmentId);
    }

    public function scopeMandatory(Builder $query): Builder{
        return $query->where('mandatory', true);
    }

    public function scopeOptional(Builder $query): Builder{
        return $query->where('mandatory', false);
    }

    public function scopeCoreSubjects(Builder $query): Builder{
        return $query->where('type', 1);
    }

    public function scopeElectiveSubjects(Builder $query): Builder{
        return $query->where('type', 0);
    }
}