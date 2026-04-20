<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LtiLaunch extends Model {
    protected $table = 'lms_lti_launches';

    protected $fillable = [
        'tool_id',
        'resource_link_id',
        'user_id',
        'course_id',
        'message_type',
        'lti_version',
        'claims_sent',
        'ip_address',
        'user_agent',
        'launched_at',
    ];

    protected $casts = [
        'claims_sent' => 'array',
        'launched_at' => 'datetime',
    ];

    public function tool(): BelongsTo {
        return $this->belongsTo(LtiTool::class, 'tool_id');
    }

    public function resourceLink(): BelongsTo {
        return $this->belongsTo(LtiResourceLink::class, 'resource_link_id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public static function log(
        LtiTool $tool,
        User $user,
        string $messageType,
        ?LtiResourceLink $resourceLink = null,
        ?Course $course = null,
        array $claims = []
    ): self {
        return self::create([
            'tool_id' => $tool->id,
            'resource_link_id' => $resourceLink?->id,
            'user_id' => $user->id,
            'course_id' => $course?->id,
            'message_type' => $messageType,
            'lti_version' => $tool->lti_version,
            'claims_sent' => $claims,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'launched_at' => now(),
        ]);
    }
}
