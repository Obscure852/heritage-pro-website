<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationComment extends Model{

    use HasFactory;
    protected $table = 'notification_comments';

    protected $fillable = [
        'notification_id',
        'user_id',
        'body',
    ];

    public function notification(){
        return $this->belongsTo(Notification::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}