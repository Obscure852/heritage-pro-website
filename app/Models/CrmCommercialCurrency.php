<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmCommercialCurrency extends Model
{
    use HasFactory;

    protected $table = 'crm_commercial_currencies';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'symbol_position',
        'precision',
        'is_active',
    ];

    protected $casts = [
        'precision' => 'integer',
        'is_active' => 'boolean',
    ];
}
