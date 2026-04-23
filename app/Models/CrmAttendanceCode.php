<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmAttendanceCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'label',
        'color',
        'category',
        'counts_as_working',
        'is_system',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'counts_as_working' => 'decimal:2',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(CrmAttendanceRecord::class, 'attendance_code_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
