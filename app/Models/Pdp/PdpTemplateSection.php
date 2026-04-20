<?php

namespace App\Models\Pdp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class PdpTemplateSection extends Model
{
    use HasFactory;

    protected $table = 'pdp_template_sections';

    protected $fillable = [
        'pdp_template_id',
        'key',
        'label',
        'section_type',
        'sequence',
        'is_repeatable',
        'min_items',
        'max_items',
        'applies_when_json',
        'editable_by_json',
        'layout_config_json',
        'print_config_json',
    ];

    protected $casts = [
        'is_repeatable' => 'boolean',
        'applies_when_json' => 'array',
        'editable_by_json' => 'array',
        'layout_config_json' => 'array',
        'print_config_json' => 'array',
    ];

    protected static function booted(): void
    {
        $guard = function (self $section): void {
            $templateStatus = $section->relationLoaded('template')
                ? $section->template?->status
                : PdpTemplate::query()->whereKey($section->pdp_template_id)->value('status');

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

    public function fields(): HasMany
    {
        return $this->hasMany(PdpTemplateField::class, 'pdp_template_section_id')->orderBy('sort_order');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(PdpTemplateSectionRow::class, 'pdp_template_section_id')
            ->whereNull('parent_row_id')
            ->orderBy('sort_order');
    }

    public function allRows(): HasMany
    {
        return $this->hasMany(PdpTemplateSectionRow::class, 'pdp_template_section_id')->orderBy('sort_order');
    }

    public function usesTemplateRows(): bool
    {
        return data_get($this->layout_config_json, 'row_source') === 'template_section_rows';
    }

    public function templateManagedFieldKeys(): array
    {
        return collect(data_get($this->layout_config_json, 'template_managed_field_keys', []))
            ->filter(fn ($key) => is_string($key) && $key !== '')
            ->values()
            ->all();
    }

    public function allowsCustomEntries(): bool
    {
        return (bool) data_get($this->layout_config_json, 'allow_custom_entries', false);
    }

    public function templateParentFieldKeys(): array
    {
        return collect(data_get($this->layout_config_json, 'template_parent_field_keys', []))
            ->filter(fn ($key) => is_string($key) && $key !== '')
            ->values()
            ->all();
    }

    public function templateChildFieldKeys(): array
    {
        return collect(data_get($this->layout_config_json, 'template_child_field_keys', []))
            ->filter(fn ($key) => is_string($key) && $key !== '')
            ->values()
            ->all();
    }

    public function planEvaluationFieldKeys(): array
    {
        return collect(data_get($this->layout_config_json, 'plan_evaluation_field_keys', []))
            ->filter(fn ($key) => is_string($key) && $key !== '')
            ->reject(fn (string $key): bool => $key === 'actual_result')
            ->values()
            ->all();
    }
}
