<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'external_id',
        'name',
        'language',
        'category',
        'status',
        'body_preview',
        'variables',
        'content',
        'last_synced_at',
    ];

    protected $casts = [
        'variables' => 'array',
        'content' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function scopeApproved($query)
    {
        return $query->whereIn('status', ['approved', 'active']);
    }
}
