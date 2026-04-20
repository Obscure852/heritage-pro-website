<?php
namespace App\Models;

use App\Models\FinalStudent;
use App\Models\User;
use App\Models\Grade;
use App\Models\Term;
use App\Models\Venue;
use App\Models\OptionalSubject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FinalOptionalSubject extends Model{
    use HasFactory;

    protected $fillable = [
        'original_optional_subject_id',
        'name',
        'final_grade_subject_id',
        'user_id',
        'graduation_term_id',
        'grade_id',
        'grouping',
        'venue_id',
        'active',
        'graduation_year',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function getSafeNameAttribute(): string{
        return htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8', false);
    }

    public function originalOptionalSubject(): BelongsTo{
        return $this->belongsTo(OptionalSubject::class, 'original_optional_subject_id');
    }

    public function finalGradeSubject(): BelongsTo{
        return $this->belongsTo(FinalGradeSubject::class, 'final_grade_subject_id');
    }

    public function teacher(): BelongsTo{
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grade(): BelongsTo{
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function graduationTerm(): BelongsTo{
        return $this->belongsTo(Term::class, 'graduation_term_id');
    }

    public function venue(): BelongsTo{
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function finalStudents(): BelongsToMany{
        return $this->belongsToMany(FinalStudent::class, 'final_student_optional_subjects')
                    ->withPivot(['graduation_term_id', 'final_klass_id', 'graduation_year'])
                    ->withTimestamps();
    }

    public function finalKlasses(): BelongsToMany{
        return $this->belongsToMany(FinalKlass::class, 'final_student_optional_subjects', 'final_optional_subject_id', 'final_klass_id')
                    ->withPivot('final_student_id')
                    ->distinct();
    }

    public function scopeActive(Builder $query): Builder{
        return $query->where('active', true);
    }

    public function scopeByTeacher(Builder $query, int $userId): Builder{
        return $query->where('user_id', $userId);
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
}