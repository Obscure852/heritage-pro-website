<?php

namespace App\Models\Fee;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class LateFeeCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_invoice_id',
        'amount',
        'fee_type',
        'applied_date',
        'days_overdue',
        'waived',
        'waived_at',
        'waived_by',
        'waived_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'applied_date' => 'date',
        'days_overdue' => 'integer',
        'waived' => 'boolean',
        'waived_at' => 'datetime',
    ];

    // Fee type constants
    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENTAGE = 'percentage';

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StudentInvoice::class, 'student_invoice_id');
    }

    public function waivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waived_by');
    }

    public function scopeNotWaived(Builder $query): Builder
    {
        return $query->where('waived', false);
    }

    public function scopeWaived(Builder $query): Builder
    {
        return $query->where('waived', true);
    }

    public function scopeForInvoice(Builder $query, int $invoiceId): Builder
    {
        return $query->where('student_invoice_id', $invoiceId);
    }

    public function scopeAppliedOn(Builder $query, $date): Builder
    {
        return $query->whereDate('applied_date', $date);
    }

    public function isWaived(): bool
    {
        return $this->waived === true;
    }

    public function waive(User $user, string $reason): bool
    {
        $this->waived = true;
        $this->waived_at = now();
        $this->waived_by = $user->id;
        $this->waived_reason = $reason;

        $saved = $this->save();

        // Update invoice totals after waiving
        if ($saved) {
            $this->invoice->recalculateAfterLateFeeChange();
        }

        return $saved;
    }

    public function getEffectiveAmountAttribute(): string
    {
        return $this->waived ? '0.00' : (string) $this->amount;
    }
}
