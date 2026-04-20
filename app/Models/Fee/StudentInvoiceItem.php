<?php

namespace App\Models\Fee;

use App\Models\Activities\ActivityFeeCharge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentInvoiceItem extends Model
{
    use HasFactory;

    // Item type constants
    const TYPE_FEE = 'fee';
    const TYPE_CARRYOVER = 'carryover';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_CREDIT_NOTE = 'credit_note';
    const TYPE_LATE_FEE = 'late_fee';
    const TYPE_ACTIVITY_FEE = 'activity_fee';

    protected $fillable = [
        'student_invoice_id',
        'activity_fee_charge_id',
        'fee_structure_id',
        'item_type',
        'source_year',
        'description',
        'amount',
        'discount_amount',
        'net_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'source_year' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StudentInvoice::class, 'student_invoice_id');
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function activityFeeCharge(): BelongsTo
    {
        return $this->belongsTo(ActivityFeeCharge::class, 'activity_fee_charge_id');
    }

    /**
     * Scope to get only fee items (not carryovers).
     */
    public function scopeFees(Builder $query): Builder
    {
        return $query->where('item_type', self::TYPE_FEE);
    }

    /**
     * Scope to get only carryover items.
     */
    public function scopeCarryovers(Builder $query): Builder
    {
        return $query->where('item_type', self::TYPE_CARRYOVER);
    }

    /**
     * Check if this item is a carryover.
     */
    public function isCarryover(): bool
    {
        return $this->item_type === self::TYPE_CARRYOVER;
    }

    /**
     * Check if this item is a regular fee.
     */
    public function isFee(): bool
    {
        return $this->item_type === self::TYPE_FEE;
    }

    /**
     * Scope to get only late fee items.
     */
    public function scopeLateFees(Builder $query): Builder
    {
        return $query->where('item_type', self::TYPE_LATE_FEE);
    }

    /**
     * Check if this item is a late fee.
     */
    public function isLateFee(): bool
    {
        return $this->item_type === self::TYPE_LATE_FEE;
    }

    /**
     * Scope to get only activity fee items.
     */
    public function scopeActivityFees(Builder $query): Builder
    {
        return $query->where('item_type', self::TYPE_ACTIVITY_FEE);
    }

    /**
     * Check if this item is an activity fee.
     */
    public function isActivityFee(): bool
    {
        return $this->item_type === self::TYPE_ACTIVITY_FEE;
    }
}
