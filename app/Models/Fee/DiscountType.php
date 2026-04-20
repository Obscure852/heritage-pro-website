<?php

namespace App\Models\Fee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscountType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'percentage',
        'description',
        'applies_to',
        'is_active',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Applies to constants
    const APPLIES_TO_ALL = 'all';
    const APPLIES_TO_TUITION_ONLY = 'tuition_only';

    public static function appliesOptions(): array
    {
        return [
            self::APPLIES_TO_ALL => 'All Fees',
            self::APPLIES_TO_TUITION_ONLY => 'Tuition Only',
        ];
    }

    public function studentDiscounts(): HasMany
    {
        return $this->hasMany(StudentDiscount::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function appliesToTuitionOnly(): bool
    {
        return $this->applies_to === self::APPLIES_TO_TUITION_ONLY;
    }

    public function appliesToAllFees(): bool
    {
        return $this->applies_to === self::APPLIES_TO_ALL;
    }
}
