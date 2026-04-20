<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFilter extends Model{
    use HasFactory;
    protected $table = 'student_filters';

    protected $fillable = ['name'];

    public function students(){
        return $this->hasMany(Student::class);
    }
}
