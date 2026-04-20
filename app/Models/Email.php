<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model{
    use HasFactory;

    protected $fillable = [
         'term_id',
         'user_id',
         'sponsor_id',
         'sender_id',
         'receiver_id',
         'receiver_type',
         'subject',
         'body',
         'attachment_path',
         'num_of_recipients',
         'status',
         'type',
         'error_message',
         'filters'
    ];

    protected $casts = [
        'filters' => 'array',
    ];

    public function sender(){
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sponsor(){
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }
    
}