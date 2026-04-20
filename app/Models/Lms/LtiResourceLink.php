<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LtiResourceLink extends Model {
    protected $table = 'lms_lti_resource_links';

    protected $fillable = [
        'tool_id',
        'content_id',
        'course_id',
        'resource_link_id',
        'title',
        'description',
        'launch_url',
        'custom_parameters',
    ];

    protected $casts = [
        'custom_parameters' => 'array',
    ];

    protected static function boot() {
        parent::boot();

        static::creating(function ($link) {
            if (empty($link->resource_link_id)) {
                $link->resource_link_id = Str::uuid()->toString();
            }
        });
    }

    public function tool(): BelongsTo {
        return $this->belongsTo(LtiTool::class, 'tool_id');
    }

    public function content(): BelongsTo {
        return $this->belongsTo(ContentItem::class, 'content_id');
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function launches(): HasMany {
        return $this->hasMany(LtiLaunch::class, 'resource_link_id');
    }

    public function lineItems(): HasMany {
        return $this->hasMany(LtiLineItem::class, 'resource_link_id');
    }

    public function getLaunchUrlAttribute($value): string {
        return $value ?: $this->tool->tool_url;
    }
}
