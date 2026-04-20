<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriteriaBasedTest extends Model{
    use HasFactory;

    protected $table = 'criteria_based_tests';
    protected $fillable = [
        'sequence',
        'name',
        'abbrev',
        'grade_subject_id',
        'term_id',
        'grade_id',
        'type',
        'assessment',
        'start_date',
        'end_date',
    ];


    public function subject(){
        return $this->belongsTo(GradeSubject::class,'grade_subject_id');
    }

    public function grade(){
        return $this->belongsTo(Grade::class,'grade_id');
    }
}