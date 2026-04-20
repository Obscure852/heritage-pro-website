<?php

namespace App\Models\Leave;

use App\Models\StaffAttendance\StaffAttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Leave request model.
 *
 * Represents a staff member's request for leave.
 * Tracks the full lifecycle from draft to approval/rejection/cancellation.
 *
 * @property int $id
 * @property string $ulid
 * @property int $user_id
 * @property int $leave_type_id
 * @property int $leave_balance_id
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property string|null $start_half_day
 * @property string|null $end_half_day
 * @property float $total_days
 * @property string|null $reason
 * @property string $status
 * @property \Carbon\Carbon|null $submitted_at
 * @property int|null $approved_by
 * @property \Carbon\Carbon|null $approved_at
 * @property string|null $approver_comments
 * @property \Carbon\Carbon|null $cancelled_at
 * @property int|null $cancelled_by
 * @property string|null $cancellation_reason
 * @property string|null $idempotency_key
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class LeaveRequest extends Model {
    use HasFactory;

    protected $fillable = [
        'ulid',
        'user_id',
        'leave_type_id',
        'leave_balance_id',
        'start_date',
        'end_date',
        'start_half_day',
        'end_half_day',
        'total_days',
        'reason',
        'status',
        'submitted_at',
        'approved_by',
        'approved_at',
        'approver_comments',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'idempotency_key',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    // Half day constants
    public const HALF_DAY_AM = 'am';
    public const HALF_DAY_PM = 'pm';

    // ==================== RELATIONSHIPS ====================

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function leaveType() {
        return $this->belongsTo(LeaveType::class);
    }

    public function balance() {
        return $this->belongsTo(LeaveBalance::class, 'leave_balance_id');
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function cancelledBy() {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function attachments() {
        return $this->hasMany(LeaveAttachment::class);
    }

    /**
     * Get attendance records generated from this leave request.
     */
    public function attendanceRecords(): HasMany {
        return $this->hasMany(StaffAttendanceRecord::class, 'leave_request_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope to pending leave requests.
     */
    public function scopePending(Builder $query): Builder {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to approved leave requests.
     */
    public function scopeApproved(Builder $query): Builder {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope to leave requests for a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to leave requests where the user reports to a specific approver.
     * Joins with users table to check reporting_to field.
     */
    public function scopeForApprover(Builder $query, int $approverId): Builder {
        return $query->whereHas('user', function ($q) use ($approverId) {
            $q->where('reporting_to', $approverId);
        });
    }

    /**
     * Scope to leave requests that overlap with a date range.
     * Two date ranges overlap if: start1 <= end2 AND end1 >= start2
     */
    public function scopeOverlapping(Builder $query, $startDate, $endDate): Builder {
        return $query->where('start_date', '<=', $endDate)
                     ->where('end_date', '>=', $startDate);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if the request is in pending status.
     */
    public function isPending(): bool {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the request is approved.
     */
    public function isApproved(): bool {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the request can be cancelled.
     * A request can be cancelled if approved and start_date is in the future.
     */
    public function canBeCancelled(): bool {
        if (!$this->isApproved()) {
            return false;
        }

        return $this->start_date->isFuture();
    }
}
