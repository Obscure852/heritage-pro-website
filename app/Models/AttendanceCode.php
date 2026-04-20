<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'order',
        'code',
        'description',
        'color',
        'is_present',
        'is_active',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_present' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get only active codes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get codes ordered by order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Get all active codes as an array of code strings
     */
    public static function getActiveCodes()
    {
        return self::active()->ordered()->pluck('code')->toArray();
    }

    /**
     * Get all active codes with their descriptions
     */
    public static function getActiveCodesWithDescriptions()
    {
        return self::active()->ordered()->pluck('description', 'code')->toArray();
    }

    /**
     * Get all active codes with their colors
     */
    public static function getActiveCodesWithColors()
    {
        return self::active()->ordered()->get()->mapWithKeys(function ($item) {
            return [$item->code => [
                'description' => $item->description,
                'color' => $item->color,
                'is_present' => $item->is_present,
            ]];
        })->toArray();
    }
}
