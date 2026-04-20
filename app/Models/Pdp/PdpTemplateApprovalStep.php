<?php

namespace App\Models\Pdp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class PdpTemplateApprovalStep extends Model
{
    use HasFactory;

    protected $table = 'pdp_template_approval_steps';

    protected $fillable = [
        'pdp_template_id',
        'key',
        'label',
        'sequence',
        'role_type',
        'required',
        'period_scope',
        'comment_required',
    ];

    protected $casts = [
        'required' => 'boolean',
        'comment_required' => 'boolean',
    ];

    protected static function booted(): void
    {
        $guard = function (self $step): void {
            $templateStatus = $step->relationLoaded('template')
                ? $step->template?->status
                : PdpTemplate::query()->whereKey($step->pdp_template_id)->value('status');

            if ($templateStatus !== null && $templateStatus !== PdpTemplate::STATUS_DRAFT) {
                throw new LogicException('Only draft PDP templates can be modified.');
            }
        };

        static::saving($guard);
        static::deleting($guard);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PdpTemplate::class, 'pdp_template_id');
    }
}
