<?php

namespace App\Models\Pdp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class PdpTemplatePeriod extends Model
{
    use HasFactory;

    protected $table = 'pdp_template_periods';

    protected $fillable = [
        'pdp_template_id',
        'key',
        'label',
        'sequence',
        'window_type',
        'due_rule_json',
        'open_rule_json',
        'close_rule_json',
        'include_in_final_score',
        'summary_label',
    ];

    protected $casts = [
        'due_rule_json' => 'array',
        'open_rule_json' => 'array',
        'close_rule_json' => 'array',
        'include_in_final_score' => 'boolean',
    ];

    protected static function booted(): void
    {
        $guard = function (self $period): void {
            $templateStatus = $period->relationLoaded('template')
                ? $period->template?->status
                : PdpTemplate::query()->whereKey($period->pdp_template_id)->value('status');

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
