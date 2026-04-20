<?php
namespace App\Models;

use App\Models\FinalStudent;
use App\Models\User;
use App\Models\Grade;
use App\Models\Term;
use App\Models\Klass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinalKlass extends Model{
    use HasFactory;

    protected $fillable = [
        'original_klass_id',
        'name',
        'user_id',
        'graduation_term_id',
        'grade_id',
        'type',
        'graduation_year',
    ];

    public function originalKlass(): BelongsTo{
        return $this->belongsTo(Klass::class, 'original_klass_id');
    }

    public function teacher(): BelongsTo{
        return $this->belongsTo(User::class, 'user_id');
    }

    public function graduationTerm(): BelongsTo{
        return $this->belongsTo(Term::class, 'graduation_term_id');
    }

    public function grade(): BelongsTo{
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function finalStudents(): BelongsToMany{
        return $this->belongsToMany(FinalStudent::class, 'final_student_klass')
                    ->withPivot(['graduation_term_id', 'graduation_year', 'grade_id', 'active'])
                    ->withTimestamps();
    }

    public function finalKlassSubjects(): HasMany{
        return $this->hasMany(FinalKlassSubject::class);
    }

    public function finalOptionalSubjects(): BelongsToMany{
        return $this->belongsToMany(FinalOptionalSubject::class, 'final_student_optional_subjects')
                    ->distinct();
    }

    public function scopeByGraduationYear(Builder $query, int $year): Builder{
        return $query->where('graduation_year', $year);
    }

    public function scopeByGraduationTerm(Builder $query, int $termId): Builder{
        return $query->where('graduation_term_id', $termId);
    }

    public function scopeByTeacher(Builder $query, int $userId): Builder{
        return $query->where('user_id', $userId);
    }

    public function scopeByGrade(Builder $query, int $gradeId): Builder{
        return $query->where('grade_id', $gradeId);
    }
}