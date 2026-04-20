<?php

namespace App\Models\Library;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryFine extends Model {
    use HasFactory;

    protected $fillable = [
        'library_transaction_id',
        'borrower_type',
        'borrower_id',
        'fine_type',
        'amount',
        'amount_paid',
        'amount_waived',
        'status',
        'daily_rate',
        'fine_date',
        'waived_by',
        'waiver_reason',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_waived' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'fine_date' => 'date',
    ];

    // ==================== RELATIONSHIPS ====================

    public function transaction() {
        return $this->belongsTo(LibraryTransaction::class, 'library_transaction_id');
    }

    public function borrower() {
        return $this->morphTo();
    }

    public function waivedBy() {
        return $this->belongsTo(User::class, 'waived_by');
    }

    // ==================== COMPUTED ====================

    public function getOutstandingAttribute(): float {
        return $this->amount - $this->amount_paid - $this->amount_waived;
    }

    // ==================== SCOPES ====================

    public function scopeUnpaid($query) {
        return $query->whereIn('status', ['pending', 'partial']);
    }

    public function scopeForBorrower($query, $borrowerType, $borrowerId) {
        return $query->where('borrower_type', $borrowerType)
                     ->where('borrower_id', $borrowerId);
    }
}
