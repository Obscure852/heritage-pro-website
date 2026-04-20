<?php

namespace App\Models\Pdp;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdpPlanSignature extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SIGNED = 'signed';
    public const STATUS_SKIPPED = 'skipped';

    protected $table = 'pdp_plan_signatures';

    protected $fillable = [
        'pdp_plan_id',
        'pdp_plan_review_id',
        'approval_step_key',
        'role_type',
        'signer_user_id',
        'signed_at',
        'comment',
        'status',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    protected $appends = [
        'resolved_signature_path',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PdpPlan::class, 'pdp_plan_id');
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(PdpPlanReview::class, 'pdp_plan_review_id');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_user_id');
    }

    public function getResolvedSignaturePathAttribute(): ?string
    {
        return $this->signer?->signature_path;
    }
}
