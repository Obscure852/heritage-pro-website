<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentBehaviour extends Model{
    use HasFactory,SoftDeletes;

    protected $fillable =[
        'student_id',
        'term_id',
        'date',
        'behaviour_type',
        'description',
        'action_taken',
        'remarks',
        'reported_by',
        'year'
    ];

    public function student(){
        return $this->belongsTo(Student::class);
    }

}
