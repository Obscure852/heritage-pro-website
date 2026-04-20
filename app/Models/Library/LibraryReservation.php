<?php

namespace App\Models\Library;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryReservation extends Model {
    use HasFactory;

    protected $fillable = [
        'book_id',
        'borrower_type',
        'borrower_id',
        'status',
        'queue_position',
        'notified_at',
        'expires_at',
        'fulfilled_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'expires_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function book() {
        return $this->belongsTo(Book::class);
    }

    public function borrower() {
        return $this->morphTo();
    }

    // ==================== SCOPES ====================

    public function scopeActive($query) {
        return $query->whereIn('status', ['pending', 'ready']);
    }

    public function scopeForBook($query, $bookId) {
        return $query->where('book_id', $bookId)->orderBy('queue_position');
    }
}
