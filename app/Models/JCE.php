<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JCE extends Model{
    use HasFactory;

    protected $table = 'jce_grades';
    protected $fillable = [
        'student_id',
        'overall',
        'mathematics',
        'english',
        'science',
        'setswana',
        'design_and_technology',
        'home_economics',
        'agriculture',
        'social_studies',
        'moral_education',
        'religious_education',
        'music',
        'physical_education',
        'art',
        'office_procedures',
        'accounting',
        'french',
    ];

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }
}
