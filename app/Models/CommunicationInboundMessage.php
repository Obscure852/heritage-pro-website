<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationInboundMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel',
        'provider',
        'external_message_id',
        'from_address',
        'to_address',
        'body',
        'payload',
        'received_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'received_at' => 'datetime',
    ];
}
