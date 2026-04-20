<?php

namespace App\Models\Activities;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivitySessionAttendance extends Model
{
    use HasFactory;

    protected $table = 'activity_session_attendance';

    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_EXCUSED = 'excused';
    public const STATUS_LATE = 'late';
    public const STATUS_INJURED = 'injured';

    protected $fillable = [
        'activity_session_id',
        'activity_enrollment_id',
        'student_id',
        'status',
        'remarks',
        'marked_by',
        'marked_at',
    ];

    protected $casts = [
        'marked_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_PRESENT => 'Present',
            self::STATUS_ABSENT => 'Absent',
            self::STATUS_EXCUSED => 'Excused',
            self::STATUS_LATE => 'Late',
            self::STATUS_INJURED => 'Injured',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ActivitySession::class, 'activity_session_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(ActivityEnrollment::class, 'activity_enrollment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
