<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KlassSubject extends Model {
    use HasFactory,SoftDeletes;

    protected $table = 'klass_subject';
    public $incrementing = true;
    public $timestamps = true;


    protected $fillable = [
        'klass_id',
        'grade_subject_id',
        'user_id',
        'assistant_user_id',
        'term_id',
        'grade_id',
        'venue_id',
        'year',
        'active'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'assistant_user_id' => 'integer',
    ];

    function teacher(){
        return $this->belongsTo(User::class,'user_id');
    }

    function assistantTeacher(){
        return $this->belongsTo(User::class,'assistant_user_id');
    }

    public function term(){
        return $this->belongsTo(Term::class,'term_id');
    }

    function subject(){
        return $this->belongsTo(GradeSubject::class,'grade_subject_id');
    }


    function gradeSubject(){
        return $this->belongsTo(GradeSubject::class,'grade_subject_id');
    }

    function grade() {
        return $this->belongsTo(Grade::class,'grade_id');
    }

    function klass(){
        return $this->belongsTo(Klass::class,'klass_id');
    }

    function venue(){
        return $this->belongsTo(Venue::class,'venue_id');
    }
    
}