<?php

namespace App\Models\Activities;

use App\Models\Fee\FeeType;
use App\Models\Fee\StudentInvoice;
use App\Models\Fee\StudentInvoiceItem;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityFeeCharge extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_POSTED = 'posted';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_CANCELLED = 'cancelled';

    public const CHARGE_TYPE_PARTICIPATION = 'participation_fee';
    public const CHARGE_TYPE_EVENT = 'event_fee';
    public const CHARGE_TYPE_SUPPLEMENTAL = 'supplemental_fee';

    protected $fillable = [
        'activity_id',
        'activity_enrollment_id',
        'activity_event_id',
        'student_id',
        'fee_type_id',
        'term_id',
        'year',
        'charge_type',
        'amount',
        'billing_status',
        'student_invoice_id',
        'student_invoice_item_id',
        'generated_by',
        'generated_at',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'amount' => 'decimal:2',
        'generated_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending Invoice',
            self::STATUS_POSTED => 'Posted to Invoice',
            self::STATUS_BLOCKED => 'Blocked',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function chargeTypes(): array
    {
        return [
            self::CHARGE_TYPE_PARTICIPATION => 'Participation Fee',
            self::CHARGE_TYPE_EVENT => 'Event Fee',
            self::CHARGE_TYPE_SUPPLEMENTAL => 'Supplemental Fee',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(ActivityEnrollment::class, 'activity_enrollment_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(ActivityEvent::class, 'activity_event_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StudentInvoice::class, 'student_invoice_id');
    }

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(StudentInvoiceItem::class, 'student_invoice_item_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function scopePending($query)
    {
        return $query->where('billing_status', self::STATUS_PENDING);
    }

    public function scopePosted($query)
    {
        return $query->where('billing_status', self::STATUS_POSTED);
    }

    public function scopeBlocked($query)
    {
        return $query->where('billing_status', self::STATUS_BLOCKED);
    }
}
