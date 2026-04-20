<?php

namespace App\Models\Activities;

use App\Models\Grade;
use App\Models\House;
use App\Models\Klass;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityEnrollment extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_WITHDRAWN = 'withdrawn';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_SUSPENDED = 'suspended';

    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_BULK_FILTER = 'bulk_filter';
    public const SOURCE_CARRY_FORWARD = 'carry_forward';

    protected $fillable = [
        'activity_id',
        'student_id',
        'term_id',
        'year',
        'status',
        'joined_at',
        'left_at',
        'joined_by',
        'left_by',
        'exit_reason',
        'source',
        'grade_id_snapshot',
        'klass_id_snapshot',
        'house_id_snapshot',
    ];

    protected $casts = [
        'year' => 'integer',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_WITHDRAWN => 'Withdrawn',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    public static function closableStatuses(): array
    {
        return [
            self::STATUS_WITHDRAWN => 'Withdrawn',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    public static function sources(): array
    {
        return [
            self::SOURCE_MANUAL => 'Manual',
            self::SOURCE_BULK_FILTER => 'Bulk Eligibility',
            self::SOURCE_CARRY_FORWARD => 'Carry Forward',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function joinedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'joined_by');
    }

    public function leftBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'left_by');
    }

    public function gradeSnapshot(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'grade_id_snapshot')->withTrashed();
    }

    public function klassSnapshot(): BelongsTo
    {
        return $this->belongsTo(Klass::class, 'klass_id_snapshot')->withTrashed();
    }

    public function houseSnapshot(): BelongsTo
    {
        return $this->belongsTo(House::class, 'house_id_snapshot');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeHistorical($query)
    {
        return $query->where('status', '!=', self::STATUS_ACTIVE);
    }
}
