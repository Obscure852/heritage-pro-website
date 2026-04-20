<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RubricCriterion extends Model
{
    use HasFactory;

    protected $table = 'lms_rubric_criteria';

    protected $fillable = [
        'rubric_id',
        'title',
        'description',
        'max_points',
        'sequence',
    ];

    protected $casts = [
        'max_points' => 'decimal:2',
    ];

    // Relationships
    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    public function levels(): HasMany
    {
        return $this->hasMany(RubricLevel::class, 'criterion_id')->orderBy('sequence');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(RubricScore::class, 'criterion_id');
    }

    public function getScoreFor(Grade $grade): ?RubricScore
    {
        return $this->scores()->where('grade_id', $grade->id)->first();
    }

    // Boot
    protected static function booted(): void
    {
        static::saved(function ($criterion) {
            $criterion->rubric->calculateTotalPoints();
        });

        static::deleted(function ($criterion) {
            $criterion->rubric->calculateTotalPoints();
        });
    }
}
