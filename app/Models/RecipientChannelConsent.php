<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipientChannelConsent extends Model
{
    use HasFactory;

    public const STATUS_OPTED_IN = 'opted_in';
    public const STATUS_OPTED_OUT = 'opted_out';
    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'recipient_type',
        'recipient_id',
        'channel',
        'status',
        'source',
        'recorded_by',
        'recorded_at',
        'opted_out_at',
        'notes',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'opted_out_at' => 'datetime',
    ];

    public function recipient()
    {
        return $this->morphTo();
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeOptedIn($query)
    {
        return $query->where('status', self::STATUS_OPTED_IN);
    }
}
