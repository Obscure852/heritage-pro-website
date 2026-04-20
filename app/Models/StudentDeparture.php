<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDeparture extends Model{

    protected $table = 'student_departures';

    protected $fillable = [
        'student_id',
        'last_day_of_attendance',
        'reason_for_leaving',
        'reason_for_leaving_other',
        'new_school_name',
        'new_school_contact_number',
        'outstanding_fees',
        'property_returned',
        'year',
        'notes',
        'processed_by',
        'processed_at'
    ];

    protected $casts = [
        'last_day_of_attendance' => 'date',
        'outstanding_fees' => 'boolean',
        'property_returned' => 'boolean',
        'year' => 'integer',
        'processed_at' => 'datetime'
    ];

    public const REASONS = [
        'Graduation',
        'Transfer to another school',
        'Relocation',
        'Withdrawal',
        'Dropout - Pregnancy',
        'Illness',
        'Expulsion',
        'Other'
    ];

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function processor(){
        return $this->belongsTo(User::class, 'processed_by');
    }
}
