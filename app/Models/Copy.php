<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Copy extends Model{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'accession_number',
        'status',
    ];

    public function book(){
        return $this->belongsTo(Book::class);
    }

    public function allocations(){
        return $this->hasMany(BookAllocation::class);
    }

    public function currentAllocation(){
        return $this->hasOne(BookAllocation::class)->whereNull('return_date');
    }

    public function transactions() {
        return $this->hasMany(\App\Models\Library\LibraryTransaction::class);
    }

    public function currentTransaction() {
        return $this->hasOne(\App\Models\Library\LibraryTransaction::class)
            ->whereIn('status', ['checked_out', 'overdue']);
    }

    public function isAvailable(){
        return $this->status === 'available';
    }
}