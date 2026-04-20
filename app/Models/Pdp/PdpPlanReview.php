<?php

namespace App\Models\Pdp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PdpPlanReview extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    protected $table = 'pdp_plan_reviews';

    protected $fillable = [
        'pdp_plan_id',
        'period_key',
        'status',
        'opened_at',
        'closed_at',
        'score_summary_json',
        'narrative_summary',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'score_summary_json' => 'array',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PdpPlan::class, 'pdp_plan_id');
    }

    public function sectionEntries(): HasMany
    {
        return $this->hasMany(PdpPlanSectionEntry::class, 'pdp_plan_review_id')->orderBy('section_key')->orderBy('sort_order');
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(PdpPlanSignature::class, 'pdp_plan_review_id')->orderBy('id');
    }
}
