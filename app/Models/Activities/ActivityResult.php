<?php

namespace App\Models\Activities;

use App\Models\House;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityResult extends Model
{
    use HasFactory;

    public const PARTICIPANT_STUDENT = 'student';
    public const PARTICIPANT_HOUSE = 'house';

    public const METRIC_LABEL = 'label';
    public const METRIC_PLACEMENT = 'placement';
    public const METRIC_POINTS = 'points';
    public const METRIC_AWARD = 'award';
    public const METRIC_SCORE = 'score';
    public const METRIC_MIXED = 'mixed';

    protected $fillable = [
        'activity_event_id',
        'participant_type',
        'participant_id',
        'metric_type',
        'score_value',
        'placement',
        'points',
        'award_name',
        'result_label',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'score_value' => 'decimal:2',
        'placement' => 'integer',
        'points' => 'integer',
    ];

    public static function participantTypes(): array
    {
        return [
            self::PARTICIPANT_STUDENT => 'Student',
            self::PARTICIPANT_HOUSE => 'House',
        ];
    }

    public static function metricTypes(): array
    {
        return [
            self::METRIC_LABEL => 'Label',
            self::METRIC_PLACEMENT => 'Placement',
            self::METRIC_POINTS => 'Points',
            self::METRIC_AWARD => 'Award',
            self::METRIC_SCORE => 'Score',
            self::METRIC_MIXED => 'Mixed',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(ActivityEvent::class, 'activity_event_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'participant_id');
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class, 'participant_id');
    }
}
