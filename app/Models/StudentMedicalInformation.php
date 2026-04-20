<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentMedicalInformation extends Model{
    use HasFactory,SoftDeletes;

    protected $fillable =[
        'student_id',
        'health_history',

        'a_positive',
        'a_negative',
        'b_positive',
        'b_negative',
        'ab_positive',
        'ab_negative',
        'o_positive',
        'o_negative',

        'immunization_records',
        'other_allergies',
        'other_disabilities',
        'medical_conditioins',
        'peanuts',
        'red_meat',
        'vegetarian',
        'left_leg',
        'right_leg',
        'left_hand',
        'right_hand',
        'left_eye',
        'right_eye',
        'left_ear',
        'right_ear',
    ];


    public function student(){
        return $this->belongsTo(Student::class);
    }
}
