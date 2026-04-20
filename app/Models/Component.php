<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Component extends Model{
    use HasFactory;

    protected $fillable = [
        'term_id',
        'grade_subject_id',
        'grade_id',
        'name',
        'description',
    ];

    public function gradeSubject(){
        return $this->belongsTo(GradeSubject::class, 'grade_subject_id');
    }
    
}