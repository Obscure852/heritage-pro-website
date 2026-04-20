<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Qualification extends Model{
    use HasFactory,SoftDeletes;


    protected $fillable = [
        'qualification',
        'qualification_code',
    ];

    public function users(){
        return $this->belongsToMany(User::class)
                    ->using(QualificationUser::class)->withPivot('level','college','start_date','completion_date')
                    ->withTimestamps();
                    
    }


}
