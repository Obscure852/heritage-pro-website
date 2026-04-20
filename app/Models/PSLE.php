<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class PSLE extends Model{
    use HasFactory;

    protected $table = 'psle_grades';

    protected $fillable = [
        'student_id',
        'overall_grade',
        'agriculture_grade',
        'mathematics_grade',
        'english_grade',
        'science_grade',
        'social_studies_grade',
        'setswana_grade',
        'capa_grade',
        'religious_and_moral_education_grade'
    ];

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }
    
    public static function getSubjects(){
        return [
            'agriculture',
            'mathematics',
            'english',
            'science',
            'social_studies',
            'setswana',
            'capa',
            'religious_and_moral_education'
        ];
    }

}
