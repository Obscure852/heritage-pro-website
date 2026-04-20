<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionHealthInformation extends Model{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'admission_id',
        'health_history',
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

    public function admission(){
        return $this->belongsTo(Admission::class);
    }
}
