<?php

namespace App\Models\Fee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'is_optional',
        'is_active',
    ];

    protected $casts = [
        'is_optional' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Category constants
    const CATEGORY_TUITION = 'tuition';
    const CATEGORY_LEVY = 'levy';
    const CATEGORY_OPTIONAL = 'optional';

    public static function categories(): array
    {
        return [
            self::CATEGORY_TUITION => 'Tuition',
            self::CATEGORY_LEVY => 'Levy',
            self::CATEGORY_OPTIONAL => 'Optional',
        ];
    }

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOptional($query)
    {
        return $query->where('is_optional', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
