<?php

namespace App\Models\Leave;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Leave balance model.
 *
 * Tracks a user's leave balance for a specific leave type and year.
 * Provides computed attribute for available balance calculation.
 *
 * @property int $id
 * @property int $user_id
 * @property int $leave_type_id
 * @property int $leave_year
 * @property float $entitled
 * @property float $carried_over
 * @property float $accrued
 * @property float $used
 * @property float $pending
 * @property float $adjusted
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read float $available
 */
class LeaveBalance extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'leave_year',
        'entitled',
        'carried_over',
        'accrued',
        'used',
        'pending',
        'adjusted',
    ];

    protected $casts = [
        'entitled' => 'decimal:2',
        'carried_over' => 'decimal:2',
        'accrued' => 'decimal:2',
        'used' => 'decimal:2',
        'pending' => 'decimal:2',
        'adjusted' => 'decimal:2',
    ];

    // ==================== RELATIONSHIPS ====================

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function leaveType() {
        return $this->belongsTo(LeaveType::class);
    }

    public function requests() {
        return $this->hasMany(LeaveRequest::class);
    }

    public function adjustments() {
        return $this->hasMany(LeaveBalanceAdjustment::class);
    }

    // ==================== COMPUTED ATTRIBUTES ====================

    /**
     * Get the available leave balance.
     * Calculated as: (entitled + carried_over + accrued + adjusted) - used - pending
     */
    public function getAvailableAttribute(): float {
        $total = ($this->entitled ?? 0)
               + ($this->carried_over ?? 0)
               + ($this->accrued ?? 0)
               + ($this->adjusted ?? 0);

        $deductions = ($this->used ?? 0) + ($this->pending ?? 0);

        return (float) ($total - $deductions);
    }

    // ==================== SCOPES ====================

    /**
     * Scope to balances for a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to balances for a specific year.
     */
    public function scopeForYear(Builder $query, int $year): Builder {
        return $query->where('leave_year', $year);
    }

    /**
     * Scope to balances for a specific leave type.
     */
    public function scopeForType(Builder $query, int $typeId): Builder {
        return $query->where('leave_type_id', $typeId);
    }
}
