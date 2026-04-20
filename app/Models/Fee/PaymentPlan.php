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

class PaymentPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_invoice_id',
        'student_id',
        'year',
        'name',
        'total_amount',
        'number_of_installments',
        'frequency',
        'start_date',
        'status',
        'created_by',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'number_of_installments' => 'integer',
        'year' => 'integer',
        'start_date' => 'date',
        'cancelled_at' => 'datetime',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Frequency constants
    const FREQ_MONTHLY = 'monthly';
    const FREQ_TERMLY = 'termly';
    const FREQ_CUSTOM = 'custom';

    public static function frequencies(): array
    {
        return [
            self::FREQ_MONTHLY => 'Monthly',
            self::FREQ_TERMLY => 'Termly (3 Installments)',
            self::FREQ_CUSTOM => 'Custom',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StudentInvoice::class, 'student_invoice_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(PaymentPlanInstallment::class)->orderBy('installment_number');
    }

    public function pendingInstallments(): HasMany
    {
        return $this->hasMany(PaymentPlanInstallment::class)
            ->whereIn('status', [PaymentPlanInstallment::STATUS_PENDING, PaymentPlanInstallment::STATUS_PARTIAL, PaymentPlanInstallment::STATUS_OVERDUE])
            ->orderBy('installment_number');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function scopeForInvoice(Builder $query, int $invoiceId): Builder
    {
        return $query->where('student_invoice_id', $invoiceId);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getTotalPaidAttribute(): string
    {
        return (string) $this->installments->sum('amount_paid');
    }

    public function getRemainingBalanceAttribute(): string
    {
        return bcsub((string) $this->total_amount, $this->total_paid, 2);
    }

    public function checkAndUpdateCompletion(): void
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return;
        }

        $allPaid = $this->installments()
            ->where('status', '!=', PaymentPlanInstallment::STATUS_PAID)
            ->doesntExist();

        if ($allPaid) {
            $this->status = self::STATUS_COMPLETED;
            $this->save();
        }
    }

    public function cancel(User $user, string $reason): bool
    {
        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_by = $user->id;
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;

        return $this->save();
    }

    public function getNextDueInstallment(): ?PaymentPlanInstallment
    {
        return $this->installments()
            ->whereIn('status', [PaymentPlanInstallment::STATUS_PENDING, PaymentPlanInstallment::STATUS_PARTIAL, PaymentPlanInstallment::STATUS_OVERDUE])
            ->orderBy('due_date')
            ->first();
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'secondary',
            default => 'secondary',
        };
    }
}
