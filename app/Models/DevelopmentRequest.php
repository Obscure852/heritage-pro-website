<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DevelopmentRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'crm_development_requests';

    protected $fillable = [
        'owner_id',
        'lead_id',
        'customer_id',
        'contact_id',
        'title',
        'description',
        'requested_by',
        'priority',
        'status',
        'target_module',
        'business_value',
        'next_step',
        'due_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
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
}
