<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmInvoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'crm_invoices';

    protected $fillable = [
        'owner_id',
        'lead_id',
        'customer_id',
        'contact_id',
        'request_id',
        'invoice_number',
        'status',
        'subject',
        'invoice_date',
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
        'issued_at',
        'cancelled_at',
        'voided_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'currency_precision' => 'integer',
        'document_tax_rate' => 'decimal:2',
        'document_discount_value' => 'decimal:2',
        'document_discount_amount' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shared_at' => 'datetime',
        'issued_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'voided_at' => 'datetime',
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
        return $this->hasMany(CrmInvoiceItem::class, 'invoice_id')->orderBy('position');
    }

    public function artifact(): HasOne
    {
        return $this->hasOne(CrmCommercialDocumentArtifact::class, 'invoice_id');
    }
}
