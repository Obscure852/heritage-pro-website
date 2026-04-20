<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualAttendanceEntry extends Model{
    use HasFactory;

    protected $table = 'manual_attendance_entries';

    protected $fillable = [
        'student_id',
        'term_id',
        'days_absent',
        'school_fees_owing',
        'other_info'
    ];

    protected $casts = [
        'days_absent' => 'integer',
        'school_fees_owing' => 'decimal:2',
    ];

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function term(){
        return $this->belongsTo(Term::class);
    }
}