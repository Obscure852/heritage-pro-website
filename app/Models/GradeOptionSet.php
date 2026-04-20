<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeOptionSet extends Model{
    use HasFactory;

    protected $fillable = ['name'];
    
    public function gradeOptions(){
        return $this->hasMany(GradeOption::class);
    }

    public function subjects(){
        return $this->belongsToMany(GradeSubject::class, 'subject_grade_option_set');
    }
}