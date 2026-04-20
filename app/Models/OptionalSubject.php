<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OptionalSubject extends Model{
    use HasFactory,SoftDeletes;

    protected $table = 'optional_subjects';
    protected $fillable = [
        'name',
        'grade_subject_id',
        'user_id',
        'assistant_user_id',
        'term_id',
        'grade_id',
        'grouping',
        'venue_id',
        'active'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'assistant_user_id' => 'integer',
    ];

    public function getSafeNameAttribute(){
        return htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8', false);
    }


    public function gradeSubject(){
        return $this->belongsTo(GradeSubject::class, 'grade_subject_id');
    }

    public function teacher(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assistantTeacher(){
        return $this->belongsTo(User::class, 'assistant_user_id');
    }

    public function students(){
        return $this->belongsToMany(Student::class, 'student_optional_subjects')
                    ->withPivot(['term_id','klass_id'])
                    ->withTimestamps();
    }

    public function venue(){
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function klasses(){
        return $this->belongsToMany(Klass::class, 'student_optional_subjects', 'optional_subject_id', 'klass_id')
                    ->withPivot('student_id')
                    ->withTimestamps();
    }

    public function grade(){
        return $this->belongsTo(Grade::class,'grade_id');
    }

    public function term(){
        return $this->belongsTo(Term::class,'term_id');
    }

    public function klass(){
        return $this->belongsToMany(Klass::class,'student_optional_subjects','klass_id');
    }
}
