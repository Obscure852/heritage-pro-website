<?php

namespace App\Models\Schemes;

use App\Models\Subject;
use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Syllabus extends Model {
    use HasFactory, SoftDeletes;

    protected $table = 'syllabi';

    protected $fillable = [
        'subject_id',
        'grades',
        'level',
        'document_id',
        'is_active',
        'description',
        'source_url',
        'cached_structure',
        'cached_at',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'grades'           => 'array',
        'cached_structure' => 'array',
        'cached_at'        => 'datetime',
    ];

    public function getGradesLabelAttribute(): string {
        return implode(', ', $this->grades ?? []);
    }

    public function scopeForGrade(Builder $query, ?string $gradeName): Builder
    {
        if (blank($gradeName)) {
            return $query;
        }

        if ($query->getConnection()->getDriverName() === 'sqlite') {
            return $query->where('grades', 'like', '%"'.$gradeName.'"%');
        }

        return $query->whereJsonContains('grades', $gradeName);
    }

    public function topics(): HasMany {
        return $this->hasMany(SyllabusTopic::class)->orderBy('sequence', 'asc');
    }

    public function subject(): BelongsTo {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function document(): BelongsTo {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
