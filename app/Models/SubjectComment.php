<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectComment extends Model{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'student_test_id',
        'student_id',
        'grade_subject_id',
        'remarks',
        'user_id',
        'term_id',
        'year',

    ];

    public function tests() {
        return $this->belongsTo(StudentTest::class,'student_test_id');
    }

    public function students(){
        return $this->belongsToMany(Student::class);
    }

}
