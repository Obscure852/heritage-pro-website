<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookAllocation extends Model{
    use HasFactory;

    protected $table = 'book_allocations';

    protected $fillable = [
        'student_id',
        'copy_id',
        'grade_id',
        'accession_number',
        'allocation_date',
        'due_date',
        'return_date',
        'condition_on_allocation',
        'condition_on_return',
        'notes',
    ];

    protected $casts = [
        'allocation_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
    ];

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function book(){
        return $this->belongsTo(Book::class);
    }

    public function grade(){
        return $this->belongsTo(Grade::class);
    }

    public function copy(){
        return $this->belongsTo(Copy::class);
    }
}