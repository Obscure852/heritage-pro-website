<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeniorAdmissionPlacementCriteria extends Model
{
    use HasFactory;

    protected $table = 'senior_admission_placement_criteria';

    public const PATHWAY_TRIPLE = 'triple';
    public const PATHWAY_DOUBLE = 'double';
    public const PATHWAY_SINGLE = 'single';

    public const PATHWAYS = [
        self::PATHWAY_TRIPLE,
        self::PATHWAY_DOUBLE,
        self::PATHWAY_SINGLE,
    ];

    protected $fillable = [
        'school_setup_id',
        'pathway',
        'priority',
        'science_best_grade',
        'science_worst_grade',
        'mathematics_best_grade',
        'mathematics_worst_grade',
        'science_ceiling_grade',
        'promotion_pathway',
        'target_count',
        'is_active',
    ];

    protected $casts = [
        'priority' => 'integer',
        'target_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function schoolSetup()
    {
        return $this->belongsTo(SchoolSetup::class, 'school_setup_id');
    }
}
