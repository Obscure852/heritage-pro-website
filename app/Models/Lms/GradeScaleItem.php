<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeScaleItem extends Model {
    protected $table = 'lms_grade_scale_items';

    protected $fillable = [
        'grade_scale_id',
        'grade',
        'label',
        'min_percentage',
        'max_percentage',
        'grade_points',
        'color',
        'position',
    ];

    protected $casts = [
        'min_percentage' => 'decimal:2',
        'max_percentage' => 'decimal:2',
        'grade_points' => 'decimal:2',
    ];

    // Relationships
    public function scale(): BelongsTo {
        return $this->belongsTo(GradeScale::class, 'grade_scale_id');
    }

    // Methods
    public function matchesPercentage(float $percentage): bool {
        return $percentage >= $this->min_percentage && $percentage <= $this->max_percentage;
    }
}
