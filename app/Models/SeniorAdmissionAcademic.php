<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeniorAdmissionAcademic extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_id',
        'overall',
        'english',
        'setswana',
        'science',
        'mathematics',
        'agriculture',
        'social_studies',
        'moral_education',
        'design_and_technology',
        'home_economics',
        'office_procedures',
        'accounting',
        'french',
        'art',
        'music',
        'physical_education',
        'religious_education',
        'private_agriculture',
    ];

    public function admission() {
        return $this->belongsTo(Admission::class);
    }
}
