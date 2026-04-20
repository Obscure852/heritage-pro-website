<?php

namespace App\Models\Fee;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * Student invoices are generated once per student per year (annual invoice).
 *
 * Contains the full annual fee amount with multiple payments recorded against it.
 * Unique constraint: (student_id, year)
 */
class StudentInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'student_id',
        'year',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'balance',
        'credit_balance',
        'status',
        'issued_at',
        'due_date',
        'notes',
        'created_by',
        'last_reminder_sent_at',
        'reminder_count',
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'credit_balance' => 'decimal:2',
        'issued_at' => 'datetime',
        'due_date' => 'date',
        'year' => 'integer',
        'last_reminder_sent_at' => 'datetime',
        'reminder_count' => 'integer',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_ISSUED = 'issued';
    const STATUS_PARTIAL = 'partial';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get all available invoice statuses.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ISSUED => 'Issued',
            self::STATUS_PARTIAL => 'Partial',
            self::STATUS_PAID => 'Paid',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get the student this invoice belongs to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the invoice line items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StudentInvoiceItem::class);
    }

    /**
     * Get the payments made against this invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class, 'student_invoice_id');
    }

    /**
     * Get the refunds issued against this invoice.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(FeeRefund::class, 'student_invoice_id');
    }

    /**
     * Get payment plans for this invoice.
     */
    public function paymentPlans(): HasMany
    {
        return $this->hasMany(PaymentPlan::class, 'student_invoice_id');
    }

    /**
     * Get the active payment plan for this invoice.
     */
    public function activePaymentPlan()
    {
        return $this->hasOne(PaymentPlan::class, 'student_invoice_id')
            ->where('status', PaymentPlan::STATUS_ACTIVE);
    }

    /**
     * Get late fee charges for this invoice.
     */
    public function lateFeeCharges(): HasMany
    {
        return $this->hasMany(LateFeeCharge::class, 'student_invoice_id');
    }

    /**
     * Get the user who created this invoice.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to filter by student.
     */
    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to filter by year.
     */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    /**
     * Scope to get invoices with outstanding balance.
     */
    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_ISSUED, self::STATUS_PARTIAL, self::STATUS_OVERDUE]);
    }

    /**
     * Scope to get paid invoices.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope to get overdue invoices.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OVERDUE);
    }

    /**
     * Scope to get non-cancelled invoices.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    /**
     * Recalculate balance based on amount paid and update status.
     * Uses bcmath for precise monetary calculations.
     */
    public function recalculateBalance(): void
    {
        $this->balance = bcsub((string) $this->total_amount, (string) $this->amount_paid, 2);

        if (bccomp((string) $this->balance, '0', 2) <= 0) {
            $this->status = self::STATUS_PAID;
            $this->balance = '0.00';
        } elseif (bccomp((string) $this->amount_paid, '0', 2) > 0) {
            $this->status = self::STATUS_PARTIAL;
        }

        $this->save();
    }

    /**
     * Check if invoice is fully paid.
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        if ($this->status === self::STATUS_OVERDUE) {
            return true;
        }

        if ($this->due_date && Carbon::parse($this->due_date)->isPast() && $this->balance > 0) {
            return true;
        }

        return false;
    }

    /**
     * Check if invoice is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Get CSS class for status badge.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'status-paid',
            self::STATUS_PARTIAL => 'status-partial',
            self::STATUS_OVERDUE => 'status-overdue',
            self::STATUS_DRAFT => 'status-draft',
            self::STATUS_CANCELLED => 'status-cancelled',
            default => 'status-outstanding',
        };
    }

    /**
     * Generate invoice number with database locking.
     */
    public static function generateInvoiceNumber(int $year): string
    {
        return FeePaymentSequence::getNextInvoiceNumber($year);
    }

    /**
     * Get only fee items (excludes carryovers).
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFeeItems()
    {
        return $this->items->filter(function ($item) {
            return $item->item_type === StudentInvoiceItem::TYPE_FEE;
        });
    }

    /**
     * Get only carryover items.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCarryoverItems()
    {
        return $this->items->filter(function ($item) {
            return $item->item_type === StudentInvoiceItem::TYPE_CARRYOVER;
        })->sortBy('source_year');
    }

    /**
     * Get total of all carryover items.
     */
    public function getTotalCarryover(): string
    {
        return $this->getCarryoverItems()->sum('net_amount');
    }

    /**
     * Get subtotal of fee items only (excludes carryovers).
     */
    public function getFeeSubtotal(): string
    {
        return $this->getFeeItems()->sum('net_amount');
    }

    /**
     * Check if invoice has any carryover items.
     */
    public function hasCarryovers(): bool
    {
        return $this->getCarryoverItems()->isNotEmpty();
    }

    /**
     * Check if invoice has credit balance (overpayment).
     */
    public function hasCreditBalance(): bool
    {
        return $this->credit_balance > 0;
    }

    /**
     * Add credit to the invoice (from overpayment or credit note).
     */
    public function addCredit(string $amount): void
    {
        $this->credit_balance = bcadd((string) $this->credit_balance, $amount, 2);
        $this->save();
    }

    /**
     * Use credit from the invoice balance.
     */
    public function useCredit(string $amount): void
    {
        $this->credit_balance = bcsub((string) $this->credit_balance, $amount, 2);
        if (bccomp((string) $this->credit_balance, '0', 2) < 0) {
            $this->credit_balance = 0;
        }
        $this->save();
    }

    /**
     * Get total refunded amount for this invoice.
     */
    public function getTotalRefundedAttribute(): string
    {
        return (string) $this->refunds()
            ->where('status', FeeRefund::STATUS_PROCESSED)
            ->sum('amount');
    }

    /**
     * Get total late fees (not waived) for this invoice.
     */
    public function getTotalLateFeeAttribute(): string
    {
        return (string) $this->lateFeeCharges()
            ->where('waived', false)
            ->sum('amount');
    }

    /**
     * Check if invoice has an active payment plan.
     */
    public function hasActivePaymentPlan(): bool
    {
        return $this->paymentPlans()->where('status', PaymentPlan::STATUS_ACTIVE)->exists();
    }

    /**
     * Recalculate totals after late fee is added or waived.
     */
    public function recalculateAfterLateFeeChange(): void
    {
        $lateFees = $this->lateFeeCharges()->where('waived', false)->sum('amount');
        $itemsTotal = $this->items()->sum('net_amount');

        $this->total_amount = bcadd((string) $itemsTotal, (string) $lateFees, 2);
        $this->balance = bcsub((string) $this->total_amount, (string) $this->amount_paid, 2);

        if (bccomp((string) $this->balance, '0', 2) <= 0) {
            $this->status = self::STATUS_PAID;
            $this->balance = 0;
        } elseif (bccomp((string) $this->amount_paid, '0', 2) > 0) {
            $this->status = self::STATUS_PARTIAL;
        }

        $this->save();
    }

    /**
     * Mark that a reminder was sent.
     */
    public function markReminderSent(): void
    {
        $this->last_reminder_sent_at = now();
        $this->reminder_count = ($this->reminder_count ?? 0) + 1;
        $this->save();
    }

    /**
     * Check if a reminder can be sent (based on cooldown).
     */
    public function canSendReminder(int $cooldownDays = 7): bool
    {
        if (!$this->last_reminder_sent_at) {
            return true;
        }

        return $this->last_reminder_sent_at->diffInDays(now()) >= $cooldownDays;
    }
}
