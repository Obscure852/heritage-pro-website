<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationDeliveryEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'channel',
        'provider',
        'external_message_id',
        'event_type',
        'status',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
