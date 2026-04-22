<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CrmCommercialSetting extends Model
{
    use HasFactory;

    protected $table = 'crm_commercial_settings';

    protected $fillable = [
        'default_currency_id',
        'company_name',
        'company_email',
        'company_phone',
        'company_website',
        'company_address_line_1',
        'company_address_line_2',
        'company_city',
        'company_state',
        'company_country',
        'company_postal_code',
        'quote_prefix',
        'quote_next_sequence',
        'invoice_prefix',
        'invoice_next_sequence',
        'default_tax_rate',
        'allow_line_discounts',
        'allow_document_discounts',
        'company_logo_path',
        'login_image_path',
    ];

    protected $casts = [
        'quote_next_sequence' => 'integer',
        'invoice_next_sequence' => 'integer',
        'default_tax_rate' => 'decimal:2',
        'allow_line_discounts' => 'boolean',
        'allow_document_discounts' => 'boolean',
    ];

    public function defaultCurrency(): BelongsTo
    {
        return $this->belongsTo(CrmCommercialCurrency::class, 'default_currency_id');
    }

    protected function companyLogoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->company_logo_path ? Storage::disk('public')->url($this->company_logo_path) : null
        );
    }

    protected function loginImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->login_image_path ? Storage::disk('public')->url($this->login_image_path) : null
        );
    }
}
