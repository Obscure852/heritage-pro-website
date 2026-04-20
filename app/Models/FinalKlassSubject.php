<?php
namespace App\Models;

use App\Models\User;
use App\Models\Grade;
use App\Models\Term;
use App\Models\Venue;
use App\Models\KlassSubject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalKlassSubject extends Model{
    use HasFactory;

    protected $fillable = [
        'original_klass_subject_id',
        'final_klass_id',
        'final_grade_subject_id',
        'user_id',
        'graduation_term_id',
        'grade_id',
        'venue_id',
        'graduation_year',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function originalKlassSubject(): BelongsTo{
        return $this->belongsTo(KlassSubject::class, 'original_klass_subject_id');
    }

    public function finalKlass(): BelongsTo{
        return $this->belongsTo(FinalKlass::class, 'final_klass_id');
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
}