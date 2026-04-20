<?php

namespace App\Models;

use App\Models\FinalStudent;
use App\Models\User;
use App\Models\Term;
use App\Models\House;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinalHouse extends Model{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'original_house_id',
        'name',
        'color_code',
        'head',
        'assistant',
        'graduation_term_id',
        'graduation_year',
    ];

    public function originalHouse(): BelongsTo{
        return $this->belongsTo(House::class, 'original_house_id');
    }

    public function houseHead(): BelongsTo{
        return $this->belongsTo(User::class, 'head');
    }

    public function houseAssistant(): BelongsTo{
        return $this->belongsTo(User::class, 'assistant');
    }

    public function graduationTerm(): BelongsTo{
        return $this->belongsTo(Term::class, 'graduation_term_id');
    }

    public function finalStudents(): BelongsToMany{
        return $this->belongsToMany(FinalStudent::class, 'final_student_houses')->withPivot(['graduation_term_id', 'graduation_year'])->withTimestamps();
    }

    public function scopeByGraduationYear(Builder $query, int $year): Builder{
        return $query->where('graduation_year', $year);
    }

    public function scopeByGraduationTerm(Builder $query, int $termId): Builder{
        return $query->where('graduation_term_id', $termId);
    }

    public function scopeByHead(Builder $query, int $userId): Builder{
        return $query->where('head', $userId);
    }

    public function scopeByAssistant(Builder $query, int $userId): Builder{
        return $query->where('assistant', $userId);
    }

    public function getContrastTextColorAttribute(): string
    {
        $color = ltrim((string) $this->color_code, '#');

        if (strlen($color) !== 6 || !ctype_xdigit($color)) {
            return '#111827';
        }

        $red = hexdec(substr($color, 0, 2));
        $green = hexdec(substr($color, 2, 2));
        $blue = hexdec(substr($color, 4, 2));
        $luminance = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

        return $luminance >= 160 ? '#111827' : '#FFFFFF';
    }
}
