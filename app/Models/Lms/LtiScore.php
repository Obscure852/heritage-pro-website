<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LtiScore extends Model {
    protected $table = 'lms_lti_scores';

    protected $fillable = [
        'line_item_id',
        'user_id',
        'score_given',
        'score_maximum',
        'comment',
        'activity_progress',
        'grading_progress',
        'timestamp',
    ];

    protected $casts = [
        'score_given' => 'decimal:2',
        'score_maximum' => 'decimal:2',
        'timestamp' => 'datetime',
    ];

    public static array $activityProgress = [
        'Initialized' => 'Initialized',
        'Started' => 'Started',
        'InProgress' => 'In Progress',
        'Submitted' => 'Submitted',
        'Completed' => 'Completed',
    ];

    public static array $gradingProgress = [
        'FullyGraded' => 'Fully Graded',
        'Pending' => 'Pending',
        'PendingManual' => 'Pending Manual',
        'Failed' => 'Failed',
        'NotReady' => 'Not Ready',
    ];

    public function lineItem(): BelongsTo {
        return $this->belongsTo(LtiLineItem::class, 'line_item_id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function getPercentageAttribute(): ?float {
        if ($this->score_given === null || $this->score_maximum <= 0) {
            return null;
        }
        return round(($this->score_given / $this->score_maximum) * 100, 2);
    }
}
