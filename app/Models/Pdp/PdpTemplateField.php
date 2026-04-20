<?php

namespace App\Models\Pdp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class PdpTemplateField extends Model
{
    use HasFactory;

    protected $table = 'pdp_template_fields';

    protected $fillable = [
        'pdp_template_section_id',
        'parent_field_id',
        'key',
        'label',
        'field_type',
        'data_type',
        'input_mode',
        'required',
        'validation_rules_json',
        'mapping_source',
        'mapping_key',
        'default_value_json',
        'options_json',
        'period_scope',
        'rating_scheme_key',
        'sort_order',
    ];

    protected $casts = [
        'required' => 'boolean',
        'validation_rules_json' => 'array',
        'default_value_json' => 'array',
        'options_json' => 'array',
    ];

    protected static function booted(): void
    {
        $guard = function (self $field): void {
            $templateStatus = $field->relationLoaded('section') && $field->section?->relationLoaded('template')
                ? $field->section?->template?->status
                : PdpTemplateSection::query()
                    ->join('pdp_templates', 'pdp_templates.id', '=', 'pdp_template_sections.pdp_template_id')
                    ->where('pdp_template_sections.id', $field->pdp_template_section_id)
                    ->value('pdp_templates.status');

            if ($templateStatus !== null && $templateStatus !== PdpTemplate::STATUS_DRAFT) {
                throw new LogicException('Only draft PDP templates can be modified.');
            }
        };

        static::saving($guard);
        static::deleting($guard);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(PdpTemplateSection::class, 'pdp_template_section_id');
    }

    public function parentField(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_field_id');
    }

    public function childFields(): HasMany
    {
        return $this->hasMany(self::class, 'parent_field_id')->orderBy('sort_order');
    }
}
