<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmQuote extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'crm_quotes';

    protected $fillable = [
        'owner_id',
        'lead_id',
        'customer_id',
        'contact_id',
        'request_id',
        'quote_number',
        'status',
        'subject',
        'quote_date',
        'valid_until',
        'currency_code',
        'currency_symbol',
        'currency_position',
        'currency_precision',
        'tax_scope',
        'document_tax_rate',
        'document_discount_type',
        'document_discount_value',
        'document_discount_amount',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'notes',
        'terms',
        'shared_at',
        'cancelled_at',
        'accepted_at',
        'rejected_at',
        'expired_at',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'valid_until' => 'date',
        'currency_precision' => 'integer',
        'document_tax_rate' => 'decimal:2',
        'document_discount_value' => 'decimal:2',
        'document_discount_amount' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shared_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expired_at' => 'datetime',
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

    public function request(): BelongsTo
    {
        return $this->belongsTo(CrmRequest::class, 'request_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CrmQuoteItem::class, 'quote_id')->orderBy('position');
    }

    public function artifact(): HasOne
    {
        return $this->hasOne(CrmCommercialDocumentArtifact::class, 'quote_id');
    }
}
