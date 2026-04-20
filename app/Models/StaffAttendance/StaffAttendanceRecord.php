<?php

namespace App\Models\StaffAttendance;

use App\Models\Leave\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for daily staff attendance records.
 *
 * Represents one user's attendance for one day. Has unique constraint on (user_id, date).
 *
 * @property int $id
 * @property int $user_id
 * @property \Carbon\Carbon $date
 * @property \Carbon\Carbon|null $clock_in
 * @property \Carbon\Carbon|null $clock_out
 * @property int|null $clock_in_device_id
 * @property int|null $clock_out_device_id
 * @property float|null $hours_worked
 * @property string $status
 * @property string|null $leave_type
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read User $user
 * @property-read AttendanceDevice|null $clockInDevice
 * @property-read AttendanceDevice|null $clockOutDevice
 * @property-read bool $is_late
 */
class StaffAttendanceRecord extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'clock_in_device_id',
        'clock_out_device_id',
        'hours_worked',
        'status',
        'late_minutes',
        'leave_type',
        'notes',
        'attendance_code_id',
        'entry_type',
        'recorded_by',
        'leave_request_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'hours_worked' => 'decimal:2',
    ];

    // ==================== STATUS CONSTANTS ====================

    /**
     * Present status.
     */
    const STATUS_PRESENT = 'present';

    /**
     * Absent status.
     */
    const STATUS_ABSENT = 'absent';

    /**
     * Late status.
     */
    const STATUS_LATE = 'late';

    /**
     * Half day status.
     */
    const STATUS_HALF_DAY = 'half_day';

    /**
     * On leave status.
     */
    const STATUS_ON_LEAVE = 'on_leave';

    /**
     * Holiday status (public holiday).
     */
    const STATUS_HOLIDAY = 'holiday';

    // ==================== ENTRY TYPE CONSTANTS ====================

    /**
     * Entry recorded by biometric device.
     */
    const ENTRY_BIOMETRIC = 'biometric';

    /**
     * Entry recorded manually by HR.
     */
    const ENTRY_MANUAL = 'manual';

    /**
     * Entry recorded by staff via self-service.
     */
    const ENTRY_SELF_SERVICE = 'self_service';

    /**
     * Entry synced from leave system.
     */
    const ENTRY_LEAVE_SYNC = 'leave_sync';

    /**
     * Entry created automatically by system (public holidays, etc.).
     */
    const ENTRY_SYSTEM = 'system';

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the user this attendance record belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the device used for clock in.
     *
     * @return BelongsTo
     */
    public function clockInDevice(): BelongsTo
    {
        return $this->belongsTo(AttendanceDevice::class, 'clock_in_device_id');
    }

    /**
     * Get the device used for clock out.
     *
     * @return BelongsTo
     */
    public function clockOutDevice(): BelongsTo
    {
        return $this->belongsTo(AttendanceDevice::class, 'clock_out_device_id');
    }

    /**
     * Get the attendance code for this record.
     *
     * @return BelongsTo
     */
    public function attendanceCode(): BelongsTo
    {
        return $this->belongsTo(StaffAttendanceCode::class, 'attendance_code_id');
    }

    /**
     * Get the user who recorded this attendance (for manual/self-service entries).
     *
     * @return BelongsTo
     */
    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the leave request that generated this attendance record.
     *
     * @return BelongsTo
     */
    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope to filter records by user ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter records by date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|\Carbon\Carbon $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope to filter records between two dates.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|\Carbon\Carbon $start
     * @param string|\Carbon\Carbon $end
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    /**
     * Scope to filter records by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter records for a specific month.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $year
     * @param int $month
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    /**
     * Scope to filter records by entry type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $entryType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByEntryType($query, string $entryType)
    {
        return $query->where('entry_type', $entryType);
    }

    /**
     * Scope to filter records by leave request.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $leaveRequestId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLeaveRequest($query, int $leaveRequestId)
    {
        return $query->where('leave_request_id', $leaveRequestId);
    }

    // ==================== ACCESSORS ====================

    /**
     * Check if the employee was late.
     *
     * Stub for now - will implement in Phase 5 with configured start time.
     *
     * @return bool
     */
    public function getIsLateAttribute(): bool
    {
        // TODO: Implement with configured start time from settings
        // For now, return based on status
        return $this->status === self::STATUS_LATE;
    }
}
