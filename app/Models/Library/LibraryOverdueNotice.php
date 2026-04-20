<?php

namespace App\Models\Library;

use Illuminate\Database\Eloquent\Model;

class LibraryOverdueNotice extends Model {
    protected $fillable = [
        'library_transaction_id',
        'borrower_type',
        'borrower_id',
        'notice_type',
        'channel',
        'days_overdue',
        'escalated_to',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function transaction() {
        return $this->belongsTo(LibraryTransaction::class, 'library_transaction_id');
    }

    public function borrower() {
        return $this->morphTo();
    }

    // ==================== SCOPES ====================

    public function scopeForTransaction($query, int $transactionId) {
        return $query->where('library_transaction_id', $transactionId);
    }

    public function scopeOfType($query, string $noticeType) {
        return $query->where('notice_type', $noticeType);
    }

    public function scopeAtDays($query, int $days) {
        return $query->where('days_overdue', $days);
    }

    // ==================== STATIC HELPERS ====================

    /**
     * Check if a notice has already been sent for this transaction+type+days combo.
     * Used for deduplication to prevent sending the same notice twice.
     */
    public static function alreadySent(int $transactionId, string $noticeType, int $daysOverdue): bool {
        return static::forTransaction($transactionId)
            ->ofType($noticeType)
            ->atDays($daysOverdue)
            ->exists();
    }
}
