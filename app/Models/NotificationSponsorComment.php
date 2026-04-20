<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSponsorComment extends Model{
    protected $fillable = ['notification_id', 'sponsor_id', 'body'];

    public function notification(){
        return $this->belongsTo(Notification::class);
    }

    public function sponsor(){
        return $this->belongsTo(Sponsor::class);
    }
}
