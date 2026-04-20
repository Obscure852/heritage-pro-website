<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LtiTool extends Model {
    protected $table = 'lms_lti_tools';

    protected $fillable = [
        'name',
        'description',
        'tool_url',
        'login_url',
        'redirect_urls',
        'client_id',
        'deployment_id',
        'public_key',
        'public_key_url',
        'lti_version',
        'custom_parameters',
        'claims',
        'is_active',
        'send_name',
        'send_email',
        'icon_url',
        'privacy_level',
        'created_by',
    ];

    protected $casts = [
        'custom_parameters' => 'array',
        'claims' => 'array',
        'is_active' => 'boolean',
        'send_name' => 'boolean',
        'send_email' => 'boolean',
    ];

    public static array $privacyLevels = [
        'public' => 'Public - Send name and email',
        'name_only' => 'Name Only - Send name, not email',
        'anonymous' => 'Anonymous - No personal information',
    ];

    public static array $ltiVersions = [
        '1.1' => 'LTI 1.1',
        '1.3' => 'LTI 1.3 (Recommended)',
    ];

    protected static function boot() {
        parent::boot();

        static::creating(function ($tool) {
            if (empty($tool->client_id)) {
                $tool->client_id = Str::uuid()->toString();
            }
            if (empty($tool->deployment_id)) {
                $tool->deployment_id = Str::random(32);
            }
        });
    }

    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function placements(): HasMany {
        return $this->hasMany(LtiPlacement::class, 'tool_id');
    }

    public function courseTools(): HasMany {
        return $this->hasMany(CourseLtiTool::class, 'tool_id');
    }

    public function resourceLinks(): HasMany {
        return $this->hasMany(LtiResourceLink::class, 'tool_id');
    }

    public function launches(): HasMany {
        return $this->hasMany(LtiLaunch::class, 'tool_id');
    }

    public function lineItems(): HasMany {
        return $this->hasMany(LtiLineItem::class, 'tool_id');
    }

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public function isLti13(): bool {
        return $this->lti_version === '1.3';
    }

    public function getRedirectUrlsArrayAttribute(): array {
        return $this->redirect_urls ? explode(',', $this->redirect_urls) : [];
    }
}
