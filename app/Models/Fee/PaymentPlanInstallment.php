<?php

namespace App\Models\Fee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class PaymentPlanInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_plan_id',
        'installment_number',
        'amount',
        'due_date',
        'amount_paid',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'installment_number' => 'integer',
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PARTIAL = 'partial';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';

    public function paymentPlan(): BelongsTo
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class, 'payment_plan_installment_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PARTIAL]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OVERDUE);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeDueBefore(Builder $query, $date): Builder
    {
        return $query->where('due_date', '<=', $date);
    }

    public function scopeDueOn(Builder $query, $date): Builder
    {
        return $query->whereDate('due_date', $date);
    }

    public function getBalanceAttribute(): string
    {
        return bcsub((string) $this->amount, (string) $this->amount_paid, 2);
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === self::STATUS_PAID) {
            return false;
        }

        return Carbon::parse($this->due_date)->isPast() && bccomp($this->balance, '0', 2) > 0;
    }

    public function recordPayment(string $amount): void
    {
        $this->amount_paid = bcadd((string) $this->amount_paid, $amount, 2);

        if (bccomp((string) $this->amount_paid, (string) $this->amount, 2) >= 0) {
            $this->status = self::STATUS_PAID;
            $this->paid_at = now();
        } elseif (bccomp((string) $this->amount_paid, '0', 2) > 0) {
            $this->status = self::STATUS_PARTIAL;
        }

        $this->save();

        // Check if the plan is now complete
        $this->paymentPlan->checkAndUpdateCompletion();
    }

    public function reversePayment(string $amount): void
    {
        $this->amount_paid = bcsub((string) $this->amount_paid, $amount, 2);

        if (bccomp((string) $this->amount_paid, '0', 2) <= 0) {
            $this->amount_paid = 0;
            $this->status = $this->is_overdue ? self::STATUS_OVERDUE : self::STATUS_PENDING;
            $this->paid_at = null;
        } else {
            $this->status = self::STATUS_PARTIAL;
        }

        $this->save();
    }

    public function markAsOverdue(): void
    {
        if ($this->status !== self::STATUS_PAID) {
            $this->status = self::STATUS_OVERDUE;
            $this->save();
        }
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'success',
            self::STATUS_PARTIAL => 'warning',
            self::STATUS_OVERDUE => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'Paid',
            self::STATUS_PARTIAL => 'Partial',
            self::STATUS_OVERDUE => 'Overdue',
            default => 'Pending',
        };
    }
}
