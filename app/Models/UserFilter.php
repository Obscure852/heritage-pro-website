<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFilter extends Model{
    use HasFactory;
    protected $table = 'user_filters';

    protected $fillable = [
        'name'
    ];

    public function users(){
        return $this->hasMany(User::class);
    }

}
