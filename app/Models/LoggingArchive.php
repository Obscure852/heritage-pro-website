<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoggingArchive extends Model{
    use HasFactory;
    protected $table = 'logging_archive';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'input',
        'changes',
        'archived_at'
    ];


    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
}
