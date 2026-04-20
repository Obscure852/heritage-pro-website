<?php

namespace App\Models\Invigilation;

use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class InvigilationSeries extends Model
{
    use HasFactory;

    public const TYPE_MOCK = 'mock';
    public const TYPE_FINAL = 'final';
    public const TYPE_CUSTOM = 'custom';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    public const POLICY_SUBJECT_ONLY = 'subject_only';
    public const POLICY_EXCLUDE_SUBJECT_TEACHERS = 'exclude_subject_teachers';
    public const POLICY_ANY_TEACHER = 'any_teacher';

    public const TIMETABLE_CHECK = 'check';
    public const TIMETABLE_IGNORE = 'ignore';

    protected $table = 'invigilation_series';

    protected $fillable = [
        'name',
        'type',
        'term_id',
        'status',
        'eligibility_policy',
        'timetable_conflict_policy',
        'balancing_policy',
        'default_required_invigilators',
        'notes',
        'published_at',
        'published_by',
        'created_by',
    ];

    protected $casts = [
        'default_required_invigilators' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $series): void {
            if (!$series->term_id || !Schema::hasColumn($series->getTable(), 'year')) {
                return;
            }

            if (array_key_exists('year', $series->attributes) && $series->attributes['year'] !== null) {
                return;
            }

            $termYear = Term::query()->whereKey($series->term_id)->value('year');

            if ($termYear !== null) {
                $series->setAttribute('year', (int) $termYear);
            }
        });
    }

    public static function types(): array
    {
        return [
            self::TYPE_MOCK => 'Mock',
            self::TYPE_FINAL => 'Final',
            self::TYPE_CUSTOM => 'Custom',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    public static function eligibilityPolicies(): array
    {
        return [
            self::POLICY_SUBJECT_ONLY => 'Subject Teachers Only',
            self::POLICY_EXCLUDE_SUBJECT_TEACHERS => 'Non-subject Teachers Only',
            self::POLICY_ANY_TEACHER => 'Any Teacher',
        ];
    }

    public static function timetableConflictPolicies(): array
    {
        return [
            self::TIMETABLE_CHECK => 'Check Teaching Timetable',
            self::TIMETABLE_IGNORE => 'Ignore Teaching Timetable',
        ];
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function isEditable(): bool
    {
        return $this->isDraft();
    }

    public function getYearAttribute($value): ?int
    {
        if ($value !== null) {
            return (int) $value;
        }

        if ($this->relationLoaded('term')) {
            return $this->term?->year ? (int) $this->term->year : null;
        }

        $termYear = $this->term()->value('year');

        return $termYear !== null ? (int) $termYear : null;
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(InvigilationSession::class, 'series_id')->orderBy('exam_date')->orderBy('start_time');
    }
}
