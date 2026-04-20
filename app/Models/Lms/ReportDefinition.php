<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportDefinition extends Model {
    protected $table = 'lms_report_definitions';

    protected $fillable = [
        'name',
        'description',
        'type',
        'filters',
        'columns',
        'chart_config',
        'schedule',
        'is_public',
        'created_by',
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'chart_config' => 'array',
        'is_public' => 'boolean',
    ];

    public static array $reportTypes = [
        'course_progress' => 'Course Progress',
        'engagement' => 'Student Engagement',
        'grades' => 'Grade Report',
        'completion' => 'Completion Report',
        'quiz_performance' => 'Quiz Performance',
        'content_usage' => 'Content Usage',
        'time_tracking' => 'Time Tracking',
        'at_risk' => 'At-Risk Students',
        'custom' => 'Custom Report',
    ];

    public static array $schedules = [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
    ];

    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function generatedReports(): HasMany {
        return $this->hasMany(GeneratedReport::class, 'definition_id');
    }

    public function scopePublic($query) {
        return $query->where('is_public', true);
    }

    public function scopeScheduled($query) {
        return $query->whereNotNull('schedule');
    }
}
