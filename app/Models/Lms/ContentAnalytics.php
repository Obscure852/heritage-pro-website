<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentAnalytics extends Model {
    protected $table = 'lms_content_analytics';

    protected $fillable = [
        'content_id',
        'date',
        'views',
        'unique_views',
        'completions',
        'total_time_seconds',
        'avg_time_seconds',
        'completion_rate',
        'avg_score',
        'drop_off_count',
    ];

    protected $casts = [
        'date' => 'date',
        'avg_time_seconds' => 'decimal:2',
        'completion_rate' => 'decimal:2',
        'avg_score' => 'decimal:2',
    ];

    public function content(): BelongsTo {
        return $this->belongsTo(ContentItem::class, 'content_id');
    }

    public function getDropOffRateAttribute(): float {
        if ($this->views === 0) return 0;
        return round(($this->drop_off_count / $this->views) * 100, 2);
    }

    public function getAvgTimeFormattedAttribute(): string {
        $minutes = floor($this->avg_time_seconds / 60);
        $seconds = $this->avg_time_seconds % 60;
        return "{$minutes}m {$seconds}s";
    }
}
