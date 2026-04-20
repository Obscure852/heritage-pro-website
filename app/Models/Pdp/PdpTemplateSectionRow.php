<?php

namespace App\Models\Pdp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class PdpTemplateSectionRow extends Model
{
    use HasFactory;

    protected $table = 'pdp_template_section_rows';

    protected $fillable = [
        'pdp_template_section_id',
        'parent_row_id',
        'key',
        'values_json',
        'sort_order',
    ];

    protected $casts = [
        'values_json' => 'array',
    ];

    protected static function booted(): void
    {
        $guard = function (self $row): void {
            $templateStatus = $row->relationLoaded('section') && $row->section?->relationLoaded('template')
                ? $row->section?->template?->status
                : PdpTemplateSection::query()
                    ->join('pdp_templates', 'pdp_templates.id', '=', 'pdp_template_sections.pdp_template_id')
                    ->where('pdp_template_sections.id', $row->pdp_template_section_id)
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

    public function parentRow(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_row_id');
    }

    public function childRows(): HasMany
    {
        return $this->hasMany(self::class, 'parent_row_id')->orderBy('sort_order');
    }

    public function planEntries(): HasMany
    {
        return $this->hasMany(PdpPlanSectionEntry::class, 'pdp_template_section_row_id');
    }
}
