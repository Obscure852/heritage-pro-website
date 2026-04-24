<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CrmSector extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public static function activeNames(): array
    {
        return self::query()
            ->active()
            ->ordered()
            ->pluck('name')
            ->all();
    }

    public static function normalizeName(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $sector = self::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($value)])
            ->first();

        return $sector?->name ?: $value;
    }
}
