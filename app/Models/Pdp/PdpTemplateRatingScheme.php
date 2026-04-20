<?php

namespace App\Models\Pdp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class PdpTemplateRatingScheme extends Model
{
    use HasFactory;

    protected $table = 'pdp_template_rating_schemes';

    protected $fillable = [
        'pdp_template_id',
        'key',
        'label',
        'input_type',
        'scale_config_json',
        'conversion_config_json',
        'weight',
        'rounding_rule',
        'formula_config_json',
        'band_config_json',
    ];

    protected $casts = [
        'scale_config_json' => 'array',
        'conversion_config_json' => 'array',
        'formula_config_json' => 'array',
        'band_config_json' => 'array',
        'weight' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        $guard = function (self $scheme): void {
            $templateStatus = $scheme->relationLoaded('template')
                ? $scheme->template?->status
                : PdpTemplate::query()->whereKey($scheme->pdp_template_id)->value('status');

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
