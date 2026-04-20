<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationAttachment extends Model{
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'original_name',
        'file_path',
        'file_type',
    ];

    public function notification(){
        return $this->belongsTo(Notification::class);
    }
}
