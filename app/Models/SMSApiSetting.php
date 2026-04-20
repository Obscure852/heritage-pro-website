<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMSApiSetting extends Model
{
    use HasFactory;

    protected $table = 's_m_s_api_settings';

    protected $fillable = [
        'key',
        'value',
        'category',
        'type',
        'description',
        'display_name',
        'validation_rules',
        'is_editable',
        'display_order',
    ];

    protected $casts = [
        'is_editable' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Scope to get settings by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category)
            ->orderBy('display_order');
    }

    /**
     * Scope to get editable settings only
     */
    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }

    /**
     * Get the typed value
     *
     * @return mixed
     */
    public function getTypedValueAttribute()
    {
        return match($this->type) {
            'integer', 'int' => (int) $this->value,
            'decimal', 'float', 'double' => (float) $this->value,
            'boolean', 'bool' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => json_decode($this->value, true),
            default => (string) $this->value,
        };
    }
}
