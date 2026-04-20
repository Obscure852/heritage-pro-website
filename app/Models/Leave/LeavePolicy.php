<?php

namespace App\Models\Leave;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Leave policy model.
 *
 * Defines yearly policies for each leave type including balance allocation mode,
 * accrual rates, and carry-over rules.
 *
 * @property int $id
 * @property int $leave_type_id
 * @property int $leave_year
 * @property string $balance_mode
 * @property float|null $accrual_rate
 * @property string $carry_over_mode
 * @property float|null $carry_over_limit
 * @property int|null $carry_over_expiry_months
 * @property bool $prorate_new_employees
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class LeavePolicy extends Model {
    use HasFactory;

    protected $fillable = [
        'leave_type_id',
        'leave_year',
        'balance_mode',
        'accrual_rate',
        'carry_over_mode',
        'carry_over_limit',
        'carry_over_expiry_months',
        'prorate_new_employees',
    ];

    protected $casts = [
        'accrual_rate' => 'decimal:2',
        'carry_over_limit' => 'decimal:2',
        'prorate_new_employees' => 'boolean',
    ];

    // Balance mode constants
    public const MODE_ALLOCATION = 'allocation';
    public const MODE_ACCRUAL = 'accrual';

    // Carry over mode constants
    public const CARRY_NONE = 'none';
    public const CARRY_LIMITED = 'limited';
    public const CARRY_FULL = 'full';

    // ==================== RELATIONSHIPS ====================

    public function leaveType() {
        return $this->belongsTo(LeaveType::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope to policies for a specific year.
     */
    public function scopeForYear(Builder $query, int $year): Builder {
        return $query->where('leave_year', $year);
    }

    /**
     * Scope to policies for a specific leave type.
     */
    public function scopeForType(Builder $query, int $typeId): Builder {
        return $query->where('leave_type_id', $typeId);
    }
}
