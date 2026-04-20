<?php

namespace App\Models\Pdp;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class PdpTemplate extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $table = 'pdp_templates';

    protected $fillable = [
        'template_family_key',
        'version',
        'code',
        'name',
        'source_reference',
        'description',
        'status',
        'is_default',
        'settings_json',
        'published_at',
        'archived_at',
        'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'settings_json' => 'array',
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $template): void {
            if ($template->getOriginal('status') === self::STATUS_DRAFT) {
                return;
            }

            $allowed = ['status', 'is_default', 'published_at', 'archived_at', 'updated_at'];
            $dirty = array_keys($template->getDirty());
            $blocked = array_diff($dirty, $allowed);

            if ($blocked !== []) {
                throw new LogicException('Published or archived PDP templates are immutable.');
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PdpTemplateSection::class, 'pdp_template_id')->orderBy('sequence');
    }

    public function rollouts(): HasMany
    {
        return $this->hasMany(PdpRollout::class, 'pdp_template_id')->orderByDesc('id');
    }

    public function periods(): HasMany
    {
        return $this->hasMany(PdpTemplatePeriod::class, 'pdp_template_id')->orderBy('sequence');
    }

    public function ratingSchemes(): HasMany
    {
        return $this->hasMany(PdpTemplateRatingScheme::class, 'pdp_template_id')->orderBy('id');
    }

    public function approvalSteps(): HasMany
    {
        return $this->hasMany(PdpTemplateApprovalStep::class, 'pdp_template_id')->orderBy('sequence');
    }

    public function plans(): HasMany
    {
        return $this->hasMany(PdpPlan::class, 'pdp_template_id')->orderByDesc('id');
    }
}
