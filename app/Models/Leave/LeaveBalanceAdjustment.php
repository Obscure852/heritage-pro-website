<?php

namespace App\Models\Leave;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Leave balance adjustment model.
 *
 * Records manual adjustments to leave balances with audit trail.
 *
 * @property int $id
 * @property int $leave_balance_id
 * @property string $adjustment_type
 * @property float $days
 * @property string|null $reason
 * @property int $adjusted_by
 * @property \Carbon\Carbon $created_at
 */
class LeaveBalanceAdjustment extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'leave_balance_id',
        'adjustment_type',
        'days',
        'reason',
        'adjusted_by',
    ];

    protected $casts = [
        'days' => 'decimal:2',
    ];

    // Adjustment type constants
    public const TYPE_CREDIT = 'credit';
    public const TYPE_DEBIT = 'debit';
    public const TYPE_CORRECTION = 'correction';

    /**
     * Boot the model.
     */
    protected static function boot(): void {
        parent::boot();

        static::creating(function (self $model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function balance() {
        return $this->belongsTo(LeaveBalance::class, 'leave_balance_id');
    }

    public function adjustedBy() {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
