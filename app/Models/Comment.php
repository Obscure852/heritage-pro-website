<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'student_id',
        'klass_id',
        'user_id',
        'term_id',
        'class_teacher_remarks',
        'school_head_remarks',
        'year',
    ];

    function student() {
        return $this->belongsToMany(Student::class,'student_id');
    }
}
