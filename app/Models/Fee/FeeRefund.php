<?php

namespace App\Models\Fee;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Fee refunds track refunds issued against invoices/payments.
 *
 * Supports full refunds, partial refunds, and credit notes.
 */
class FeeRefund extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'refund_number',
        'student_invoice_id',
        'student_id',
        'fee_payment_id',
        'year',
        'amount',
        'refund_type',
        'refund_method',
        'refund_date',
        'reference_number',
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'processed_by',
        'processed_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'amount' => 'decimal:2',
        'refund_date' => 'date',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    // Refund type constants
    const TYPE_FULL = 'full';
    const TYPE_PARTIAL = 'partial';
    const TYPE_CREDIT_NOTE = 'credit_note';

    // Refund method constants
    const METHOD_CASH = 'cash';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_MOBILE_MONEY = 'mobile_money';
    const METHOD_CHEQUE = 'cheque';
    const METHOD_CREDIT_TO_ACCOUNT = 'credit_to_account';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_PROCESSED = 'processed';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get all available refund types.
     */
    public static function refundTypes(): array
    {
        return [
            self::TYPE_FULL => 'Full Refund',
            self::TYPE_PARTIAL => 'Partial Refund',
            self::TYPE_CREDIT_NOTE => 'Credit Note',
        ];
    }

    /**
     * Get all available refund methods.
     */
    public static function refundMethods(): array
    {
        return [
            self::METHOD_CASH => 'Cash',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_MOBILE_MONEY => 'Mobile Money',
            self::METHOD_CHEQUE => 'Cheque',
            self::METHOD_CREDIT_TO_ACCOUNT => 'Credit to Account',
        ];
    }

    /**
     * Get all available statuses.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_PROCESSED => 'Processed',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    /**
     * Get the invoice this refund is for.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StudentInvoice::class, 'student_invoice_id');
    }

    /**
     * Get the original payment being refunded (if any).
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(FeePayment::class, 'fee_payment_id');
    }

    /**
     * Get the student this refund is for.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who requested this refund.
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved this refund.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who processed this refund.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeForStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending refunds.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get approved refunds awaiting processing.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope to get processed refunds.
     */
    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PROCESSED);
    }

    /**
     * Scope to filter by year.
     */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    /**
     * Scope to filter by student.
     */
    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Check if refund is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if refund is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if refund is processed.
     */
    public function isProcessed(): bool
    {
        return $this->status === self::STATUS_PROCESSED;
    }

    /**
     * Check if refund is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if this is a credit note (credit applied to account).
     */
    public function isCreditNote(): bool
    {
        return $this->refund_type === self::TYPE_CREDIT_NOTE ||
               $this->refund_method === self::METHOD_CREDIT_TO_ACCOUNT;
    }

    /**
     * Generate refund number with database locking.
     */
    public static function generateRefundNumber(int $year): string
    {
        return FeePaymentSequence::getNextRefundNumber($year);
    }

    /**
     * Get human-readable refund type label.
     */
    public function getRefundTypeLabelAttribute(): string
    {
        return self::refundTypes()[$this->refund_type] ?? $this->refund_type;
    }

    /**
     * Get human-readable refund method label.
     */
    public function getRefundMethodLabelAttribute(): string
    {
        return self::refundMethods()[$this->refund_method] ?? $this->refund_method;
    }

    /**
     * Get human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    /**
     * Get CSS class for status badge.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_APPROVED => 'bg-info',
            self::STATUS_PROCESSED => 'bg-success',
            self::STATUS_REJECTED => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
