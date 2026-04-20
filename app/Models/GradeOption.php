<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeOption extends Model{
    use HasFactory;

    protected $fillable = [
        'sequence',
        'name',
        'label',
        'description',
    ];

    public function subjects(){
        return $this->belongsToMany(GradeSubject::class, 'subject_grade_options');
    }

    public function gradeOptionSet(){
        return $this->belongsTo(GradeOptionSet::class);
    }
}