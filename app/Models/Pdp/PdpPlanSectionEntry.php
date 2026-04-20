<?php

namespace App\Models\Pdp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PdpPlanSectionEntry extends Model
{
    use HasFactory;

    protected $table = 'pdp_plan_section_entries';

    protected $fillable = [
        'pdp_plan_id',
        'pdp_plan_review_id',
        'parent_entry_id',
        'pdp_template_section_row_id',
        'section_key',
        'entry_group_key',
        'origin_type',
        'sort_order',
        'values_json',
        'computed_values_json',
    ];

    protected $casts = [
        'values_json' => 'array',
        'computed_values_json' => 'array',
    ];

    public const ORIGIN_CUSTOM = 'custom';
    public const ORIGIN_TEMPLATE_SNAPSHOT = 'template_snapshot';

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PdpPlan::class, 'pdp_plan_id');
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(PdpPlanReview::class, 'pdp_plan_review_id');
    }

    public function parentEntry(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_entry_id');
    }

    public function templateSectionRow(): BelongsTo
    {
        return $this->belongsTo(PdpTemplateSectionRow::class, 'pdp_template_section_row_id');
    }

    public function childEntries(): HasMany
    {
        return $this->hasMany(self::class, 'parent_entry_id')->orderBy('sort_order');
    }
}
