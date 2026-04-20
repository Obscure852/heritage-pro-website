<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LtiLineItem extends Model {
    protected $table = 'lms_lti_line_items';

    protected $fillable = [
        'tool_id',
        'course_id',
        'resource_link_id',
        'label',
        'score_maximum',
        'tag',
        'resource_id',
        'start_date_time',
        'end_date_time',
        'grades_released',
    ];

    protected $casts = [
        'score_maximum' => 'decimal:2',
        'start_date_time' => 'datetime',
        'end_date_time' => 'datetime',
        'grades_released' => 'boolean',
    ];

    public function tool(): BelongsTo {
        return $this->belongsTo(LtiTool::class, 'tool_id');
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function resourceLink(): BelongsTo {
        return $this->belongsTo(LtiResourceLink::class, 'resource_link_id');
    }

    public function scores(): HasMany {
        return $this->hasMany(LtiScore::class, 'line_item_id');
    }
}
