<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmProduct extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'crm_products';

    protected $fillable = [
        'code',
        'name',
        'type',
        'description',
        'billing_frequency',
        'default_unit_label',
        'default_unit_price',
        'default_tax_rate',
        'active',
        'notes',
    ];

    protected $casts = [
        'default_unit_price' => 'decimal:2',
        'default_tax_rate' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function quoteItems(): HasMany
    {
        return $this->hasMany(CrmQuoteItem::class, 'product_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(CrmInvoiceItem::class, 'product_id');
    }
}
