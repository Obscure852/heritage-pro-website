<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriteriaBasedStudentTest extends Model{
    use HasFactory;
    protected $table = 'criteria_based_student_tests';

    protected $fillable = [
        'grade_subject_id',
        'component_id',
        'criteria_based_test_id',
        'student_id',
        'grade_option_id',
        'klass_id',
        'term_id',
        'grade_id',
    ];


    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }
}