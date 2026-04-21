<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'requests';

    protected $fillable = [
        'owner_id',
        'lead_id',
        'customer_id',
        'contact_id',
        'sales_stage_id',
        'type',
        'title',
        'description',
        'support_status',
        'outcome',
        'next_action',
        'next_action_at',
        'last_contact_at',
        'closed_at',
    ];

    protected $casts = [
        'next_action_at' => 'datetime',
        'last_contact_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function salesStage(): BelongsTo
    {
        return $this->belongsTo(SalesStage::class, 'sales_stage_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(RequestActivity::class, 'request_id')->orderByDesc('occurred_at');
    }
}
