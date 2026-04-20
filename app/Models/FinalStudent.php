<?php

namespace App\Models;

use App\Models\ExternalExamResult;
use App\Models\FinalKlass;
use App\Models\FinalOptionalSubject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinalStudent extends Model{
    use HasFactory;

    protected $fillable = [
        'original_student_id',
        'connect_id',
        'sponsor_id',
        'photo_path',
        'first_name',
        'last_name',
        'exam_number',
        'gender',
        'date_of_birth',
        'email',
        'nationality',
        'id_number',
        'status',
        'credit',
        'parent_is_staff',
        'student_filter_id',
        'student_type_id',
        'graduation_term_id',
        'graduation_year',
        'graduation_grade_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'parent_is_staff' => 'boolean',
        'credit' => 'decimal:2',
    ];

    public function getFullNameAttribute(): string{
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getFormattedIdNumberAttribute(): string{
        if (empty($this->id_number)) {
            return '';
        }

        $idNumber = preg_replace('/\s+/', '', $this->id_number);
        $length = strlen($idNumber);

        if ($length <= 3) {
            return $idNumber;
        }

        $groups = [];
        $remainder = $length % 3;

        if ($remainder > 0) {
            $groups[] = substr($idNumber, 0, $remainder);
            $idNumber = substr($idNumber, $remainder);
        }

        $groups = array_merge($groups, str_split($idNumber, 3));
        return implode(' ', $groups);
    }

    public function originalStudent(): BelongsTo{
        return $this->belongsTo(Student::class, 'original_student_id');
    }

    public function sponsor(): BelongsTo{
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }

    public function graduationTerm(): BelongsTo{
        return $this->belongsTo(Term::class, 'graduation_term_id');
    }

    public function graduationGrade(): BelongsTo{
        return $this->belongsTo(Grade::class, 'graduation_grade_id');
    }

    public function filter(): BelongsTo{
        return $this->belongsTo(StudentFilter::class, 'student_filter_id');
    }

    public function type(): BelongsTo{
        return $this->belongsTo(StudentType::class, 'student_type_id');
    }

    public function finalKlasses(): BelongsToMany{
        return $this->belongsToMany(FinalKlass::class, 'final_student_klass')
                    ->withPivot(['graduation_term_id', 'graduation_year', 'grade_id', 'active'])
                    ->withTimestamps();
    }

    public function finalOptionalSubjects(): BelongsToMany{
        return $this->belongsToMany(FinalOptionalSubject::class, 'final_student_optional_subjects')
                    ->withPivot(['graduation_term_id', 'final_klass_id', 'graduation_year'])
                    ->withTimestamps();
    }

    public function finalHouses(): BelongsToMany{
        return $this->belongsToMany(FinalHouse::class, 'final_student_houses')
                    ->withPivot(['graduation_term_id', 'graduation_year'])
                    ->withTimestamps();
    }

    public function externalExamResults(): HasMany{
        return $this->hasMany(ExternalExamResult::class);
    }

    public function scopeByGraduationYear(Builder $query, int $year): Builder{
        return $query->where('graduation_year', $year);
    }

    public function scopeByGraduationTerm(Builder $query, int $termId): Builder{
        return $query->where('graduation_term_id', $termId);
    }

    public function scopeByGender(Builder $query, string $gender): Builder{
        return $query->where('gender', $gender);
    }

    public function scopeWithExternalResults(Builder $query): Builder{
        return $query->whereHas('externalExamResults');
    }

    public function scopeWithoutExternalResults(Builder $query): Builder{
        return $query->whereDoesntHave('externalExamResults');
    }

    public function scopeByClass(Builder $query, int $klassId): Builder{
        return $query->whereHas('finalKlasses', function ($q) use ($klassId) {
            $q->where('final_klasses.id', $klassId);
        });
    }
}
