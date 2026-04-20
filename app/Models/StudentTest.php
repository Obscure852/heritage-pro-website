<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentTest extends Pivot{
    use HasFactory,SoftDeletes;

    protected $table = 'student_tests';

    protected $fillable = [
        'student_id',
        'test_id',
        'score',
        'percentage',
        'grade',
        'points',
        'avg_score',
        'avg_grade',
    ];

    function test(){
        return $this->belongsTo(Test::class,'test_id');
    }

    function student(){
        return $this->belongsTo(Student::class,'student_id');
    }


    function subjectComment() {
        return $this->hasMany(SubjectComment::class);
    }

    // Note: Use test() relationship instead - tests() was a duplicate
}
