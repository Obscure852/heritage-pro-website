<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmQuoteItem extends Model
{
    use HasFactory;

    protected $table = 'crm_quote_items';

    protected $fillable = [
        'quote_id',
        'product_id',
        'source_type',
        'position',
        'item_name',
        'item_description',
        'unit_label',
        'quantity',
        'unit_price',
        'gross_amount',
        'discount_type',
        'discount_value',
        'discount_amount',
        'net_amount',
        'tax_rate',
        'tax_amount',
        'total_amount',
    ];

    protected $casts = [
        'position' => 'integer',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(CrmQuote::class, 'quote_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(CrmProduct::class, 'product_id');
    }
}
