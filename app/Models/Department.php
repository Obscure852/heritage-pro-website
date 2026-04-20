<?php

namespace App\Models;

use App\Helpers\TermHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model{
    use HasFactory;

    protected $table = 'departments';

    protected $fillable = [
        'name',
        'department_head',
        'assistant'
    ];

    public function departmentHead(){
        return $this->belongsTo(User::class, 'department_head');
    }

    public function assistant(){
        return $this->belongsTo(User::class, 'assistant');
    }

    public function gradeSubjects(){
        return $this->hasMany(GradeSubject::class, 'department_id')
                    ->where('term_id', TermHelper::getCurrentTerm()->id);
    }

    public function allGradeSubjects(){
        return $this->hasMany(GradeSubject::class, 'department_id');
    }

    public function gradeSubjectsByTerm($termId = null){
        if ($termId) {
            return $this->hasMany(GradeSubject::class, 'department_id')
                        ->where('term_id', $termId);
        }
        return $this->gradeSubjects();
    }
}
