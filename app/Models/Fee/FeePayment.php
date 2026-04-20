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

/**
 * Fee payments are recorded against annual invoices.
 *
 * Each payment is linked to a student invoice and includes the year
 * (denormalized from invoice for easier reporting).
 */
class FeePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'receipt_number',
        'student_invoice_id',
        'payment_plan_installment_id',
        'student_id',
        'year',
        'amount',
        'payment_method',
        'payment_date',
        'reference_number',
        'cheque_number',
        'bank_name',
        'notes',
        'received_by',
        'voided',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'year' => 'integer',
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'voided' => 'boolean',
        'voided_at' => 'datetime',
    ];

    // Payment method constants
    const METHOD_CASH = 'cash';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_MOBILE_MONEY = 'mobile_money';
    const METHOD_CHEQUE = 'cheque';

    /**
     * Get all available payment methods.
     */
    public static function paymentMethods(): array
    {
        return [
            self::METHOD_CASH => 'Cash',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_MOBILE_MONEY => 'Mobile Money',
            self::METHOD_CHEQUE => 'Cheque',
        ];
    }

    /**
     * Get the invoice this payment is for.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StudentInvoice::class, 'student_invoice_id');
    }

    /**
     * Get the student who made this payment.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who received this payment.
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the user who voided this payment.
     */
    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * Get refunds associated with this payment.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(FeeRefund::class, 'fee_payment_id');
    }

    /**
     * Get the installment this payment is allocated to (if any).
     */
    public function installment(): BelongsTo
    {
        return $this->belongsTo(PaymentPlanInstallment::class, 'payment_plan_installment_id');
    }

    /**
     * Get total refunded amount for this payment.
     */
    public function getTotalRefundedAttribute(): string
    {
        return (string) $this->refunds()
            ->where('status', FeeRefund::STATUS_PROCESSED)
            ->sum('amount');
    }

    /**
     * Get remaining refundable amount (original amount minus refunds).
     */
    public function getRefundableAmountAttribute(): string
    {
        return bcsub((string) $this->amount, $this->total_refunded, 2);
    }

    /**
     * Check if payment can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return !$this->isVoided() && bccomp($this->refundable_amount, '0', 2) > 0;
    }

    /**
     * Scope to get non-voided payments.
     */
    public function scopeNotVoided(Builder $query): Builder
    {
        return $query->where('fee_payments.voided', false);
    }

    /**
     * Scope to get voided payments.
     */
    public function scopeVoided(Builder $query): Builder
    {
        return $query->where('fee_payments.voided', true);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeForDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('fee_payments.payment_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by payment method.
     */
    public function scopeForMethod(Builder $query, string $method): Builder
    {
        return $query->where('fee_payments.payment_method', $method);
    }

    /**
     * Scope to filter by year.
     */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('fee_payments.year', $year);
    }

    /**
     * Scope to filter by student.
     */
    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('fee_payments.student_id', $studentId);
    }

    /**
     * Scope to filter by collector (received_by).
     */
    public function scopeForCollector(Builder $query, int $userId): Builder
    {
        return $query->where('fee_payments.received_by', $userId);
    }

    /**
     * Void this payment.
     */
    public function void(User $user, string $reason): bool
    {
        $this->voided = true;
        $this->voided_at = now();
        $this->voided_by = $user->id;
        $this->void_reason = $reason;

        return $this->save();
    }

    /**
     * Check if this payment is voided.
     */
    public function isVoided(): bool
    {
        return $this->voided === true;
    }

    /**
     * Generate receipt number with database locking.
     */
    public static function generateReceiptNumber(int $year): string
    {
        return FeePaymentSequence::getNextReceiptNumber($year);
    }

    /**
     * Get human-readable payment method label.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return self::paymentMethods()[$this->payment_method] ?? $this->payment_method;
    }
}
