<?php

namespace App\Models\Pdp;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

class PdpPlan extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'pdp_plans';

    protected $fillable = [
        'pdp_template_id',
        'pdp_rollout_id',
        'user_id',
        'supervisor_id',
        'plan_period_start',
        'plan_period_end',
        'status',
        'current_period_key',
        'calculated_summary_json',
        'created_by',
    ];

    protected $casts = [
        'plan_period_start' => 'date',
        'plan_period_end' => 'date',
        'calculated_summary_json' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $plan): void {
            if (!$plan->isDirty('pdp_template_id')) {
                return;
            }

            throw new LogicException('A PDP plan cannot be rebound to a different template version.');
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PdpTemplate::class, 'pdp_template_id');
    }

    public function rollout(): BelongsTo
    {
        return $this->belongsTo(PdpRollout::class, 'pdp_rollout_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PdpPlanReview::class, 'pdp_plan_id')->orderBy('id');
    }

    public function sectionEntries(): HasMany
    {
        return $this->hasMany(PdpPlanSectionEntry::class, 'pdp_plan_id')->orderBy('section_key')->orderBy('sort_order');
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(PdpPlanSignature::class, 'pdp_plan_id')->orderBy('id');
    }
}
