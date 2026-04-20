<?php

namespace App\Models\Pdp;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PdpRollout extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUPERSEDED = 'superseded';

    public const PROVISIONING_COMPLETED = 'completed';
    public const PROVISIONING_RUNNING = 'running';
    public const PROVISIONING_FAILED = 'failed';

    protected $table = 'pdp_rollouts';

    protected $fillable = [
        'pdp_template_id',
        'label',
        'cycle_year',
        'plan_period_start',
        'plan_period_end',
        'status',
        'provisioning_status',
        'auto_provision_new_staff',
        'fallback_supervisor_user_id',
        'provisioned_count',
        'skipped_count',
        'summary_json',
        'exceptions_json',
        'launched_by',
        'launched_at',
        'closed_at',
    ];

    protected $casts = [
        'plan_period_start' => 'date',
        'plan_period_end' => 'date',
        'auto_provision_new_staff' => 'boolean',
        'summary_json' => 'array',
        'exceptions_json' => 'array',
        'launched_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(PdpTemplate::class, 'pdp_template_id');
    }

    public function fallbackSupervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fallback_supervisor_user_id');
    }

    public function launcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'launched_by');
    }

    public function plans(): HasMany
    {
        return $this->hasMany(PdpPlan::class, 'pdp_rollout_id')->orderByDesc('id');
    }
}
