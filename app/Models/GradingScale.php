<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradingScale extends Model{
    use HasFactory,SoftDeletes;

    protected $table = 'grading_scales';

    protected $fillable = [
        'grade_subject_id',
        'term_id',
        'grade_id',
        'year',
        'description',
        'min_score',
        'max_score',
        'grade',
        'points',
        'description',
        
    ];

    public function gradeSubject(){
        return $this->belongsTo(GradeSubject::class, 'grade_subject_id');
    }

    public function grade(){
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function term(){
        return $this->belongsTo(Term::class, 'term_id');
    }
}