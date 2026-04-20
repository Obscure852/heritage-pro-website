<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rubric extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lms_rubrics';

    protected $fillable = [
        'title',
        'description',
        'created_by',
        'is_template',
        'total_points',
    ];

    protected $casts = [
        'is_template' => 'boolean',
        'total_points' => 'decimal:2',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(RubricCriterion::class)->orderBy('sequence');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    // Scopes
    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    // Methods
    public function calculateTotalPoints(): void
    {
        $this->total_points = $this->criteria()->sum('max_points');
        $this->save();
    }

    public function duplicate(): self
    {
        $newRubric = $this->replicate();
        $newRubric->title = $this->title . ' (Copy)';
        $newRubric->is_template = false;
        $newRubric->save();

        foreach ($this->criteria as $criterion) {
            $newCriterion = $criterion->replicate();
            $newCriterion->rubric_id = $newRubric->id;
            $newCriterion->save();

            foreach ($criterion->levels as $level) {
                $newLevel = $level->replicate();
                $newLevel->criterion_id = $newCriterion->id;
                $newLevel->save();
            }
        }

        return $newRubric;
    }
}
