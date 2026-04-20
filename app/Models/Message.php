<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'term_id',
        'user_id',
        'author',
        'sponsor_id',
        'body',
        'channel',
        'provider',
        'recipient_address',
        'template_name',
        'template_external_id',
        'metadata',
        'sms_count',
        'type',
        'num_recipients',
        'status',
        'external_message_id',
        'delivery_status',
        'delivered_at',
        'price',
        'price_unit'
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function sponsor(){
        return $this->belongsTo(Sponsor::class);
    }

    public function author(){
        return $this->belongsTo(User::class, 'author');
    }

    public function deliveryEvents()
    {
        return $this->hasMany(CommunicationDeliveryEvent::class);
    }

}
