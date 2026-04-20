<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RubricLevel extends Model
{
    use HasFactory;

    protected $table = 'lms_rubric_levels';

    protected $fillable = [
        'criterion_id',
        'title',
        'description',
        'points',
        'sequence',
    ];

    protected $casts = [
        'points' => 'decimal:2',
    ];

    // Relationships
    public function criterion(): BelongsTo
    {
        return $this->belongsTo(RubricCriterion::class, 'criterion_id');
    }
}
