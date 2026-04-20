<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Pivot;

class QualificationUser extends Pivot{
    use HasFactory,SoftDeletes;
    protected $table = 'qualification_user';

    protected $fillable = [
        'user_id',
        'qualification_id',
        'level',
        'college',
        'start_date',
        'completion_date',
    ];


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function qualification(){
        return $this->belongsTo(Qualification::class);
    }
    


}
