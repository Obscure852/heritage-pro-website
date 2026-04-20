<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorFilter extends Model{
    use HasFactory;

    protected $table = 'sponsor_filters';

    protected $fillable = [
        'name'
    ];


    public function sponsors(){
        return $this->hasMany(Sponsor::class);
    }
}
