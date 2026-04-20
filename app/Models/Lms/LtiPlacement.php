<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LtiPlacement extends Model {
    protected $table = 'lms_lti_placements';

    protected $fillable = [
        'tool_id',
        'placement_type',
        'label',
        'icon_url',
        'is_enabled',
        'message_type',
        'display_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'message_type' => 'array',
    ];

    public static array $placementTypes = [
        'course_navigation' => 'Course Navigation',
        'assignment_selection' => 'Assignment Selection',
        'link_selection' => 'Link Selection',
        'editor_button' => 'Editor Button',
        'homework_submission' => 'Homework Submission',
        'migration_selection' => 'Migration Selection',
        'tool_configuration' => 'Tool Configuration',
        'resource_selection' => 'Resource Selection',
    ];

    public function tool(): BelongsTo {
        return $this->belongsTo(LtiTool::class, 'tool_id');
    }

    public function scopeEnabled($query) {
        return $query->where('is_enabled', true);
    }

    public function getDisplayLabelAttribute(): string {
        return $this->label ?: $this->tool->name;
    }
}
