<?php

namespace App\Models;

use App\Helpers\TermHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'sequence',
        'name',
        'promotion',
        'description',
        'level',
        'active',
        'term_id',
        'year',
    ];

    public function classes(){
        return $this->hasMany(Klass::class);
    }

    function klasses(){
        return $this->hasMany(Klass::class)->orderBy('name', 'asc');
    }

    public function currentKlasses($termId, $year){
        return $this->hasMany(Klass::class)->where('term_id', $termId)->where('year', $year)->get();
    }

    public function subjects(){
        return $this->belongsToMany(GradeSubject::class)
                    ->withTimestamps();
    }


    public function optionalSubjects(){
        return $this->hasMany(OptionalSubject::class,'grade_id');
    }

    public function standardSchemes(){
        return $this->hasMany(\App\Models\Schemes\StandardScheme::class, 'grade_id');
    }

}
