<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Integration extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'crm_integrations';

    protected $fillable = [
        'owner_id',
        'name',
        'kind',
        'status',
        'school_code',
        'base_url',
        'auth_type',
        'api_key',
        'webhook_url',
        'last_synced_at',
        'notes',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function discussionThreads(): HasMany
    {
        return $this->hasMany(DiscussionThread::class, 'integration_id');
    }
}
