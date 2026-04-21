<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'position',
        'is_active',
        'is_won',
        'is_lost',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
    ];

    public function requests(): HasMany
    {
        return $this->hasMany(CrmRequest::class, 'sales_stage_id');
    }
}
