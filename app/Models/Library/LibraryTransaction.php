<?php

namespace App\Models\Library;

use App\Models\Copy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryTransaction extends Model {
    use HasFactory;

    protected $fillable = [
        'copy_id',
        'borrower_type',
        'borrower_id',
        'checkout_date',
        'due_date',
        'return_date',
        'status',
        'renewal_count',
        'checked_out_by',
        'checked_in_by',
        'notes',
    ];

    protected $casts = [
        'checkout_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
    ];

    // ==================== RELATIONSHIPS ====================

    public function copy() {
        return $this->belongsTo(Copy::class);
    }

    public function borrower() {
        return $this->morphTo();
    }

    public function checkedOutBy() {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    public function checkedInBy() {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function fines() {
        return $this->hasMany(LibraryFine::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query) {
        return $query->whereIn('status', ['checked_out', 'overdue']);
    }

    public function scopeOverdue($query) {
        return $query->where('status', 'overdue');
    }

    public function scopeForBorrower($query, $borrowerType, $borrowerId) {
        return $query->where('borrower_type', $borrowerType)
                     ->where('borrower_id', $borrowerId);
    }
}
