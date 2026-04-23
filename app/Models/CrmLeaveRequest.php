<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmLeaveRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'start_half',
        'end_half',
        'total_days',
        'reason',
        'status',
        'submitted_at',
        'current_approver_id',
        'escalation_level',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'cancelled_at',
        'cancellation_reason',
        'attendance_synced',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'decimal:1',
        'submitted_at' => 'datetime',
        'escalation_level' => 'integer',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'attendance_synced' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(CrmLeaveType::class, 'leave_type_id');
    }

    public function currentApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CrmLeaveRequestAttachment::class, 'leave_request_id');
    }

    public function approvalTrail(): HasMany
    {
        return $this->hasMany(CrmLeaveApprovalTrail::class, 'leave_request_id')->orderBy('created_at');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(CrmAttendanceRecord::class, 'leave_request_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'pending', 'approved']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForApprover($query, int $approverId)
    {
        return $query->where('current_approver_id', $approverId)->pending();
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOverlapping($query, $startDate, $endDate)
    {
        return $query->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->whereNotIn('status', ['cancelled', 'rejected']);
    }
}
