<?php

namespace App\Models\StaffAttendance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model for staff attendance codes.
 *
 * Represents configurable attendance status codes (P, A, L, HD, OL, SL, WFH, H).
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $color
 * @property bool $counts_as_present
 * @property bool $is_active
 * @property int $order
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|StaffAttendanceRecord[] $attendanceRecords
 */
class StaffAttendanceCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'color',
        'counts_as_present',
        'is_active',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'counts_as_present' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get attendance records using this code.
     *
     * @return HasMany
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(StaffAttendanceRecord::class, 'attendance_code_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope to filter only active codes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by the order column.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // ==================== HELPERS ====================

    /**
     * Check if this code is in use by any attendance records.
     *
     * @return bool
     */
    public function isInUse(): bool
    {
        return $this->attendanceRecords()->exists();
    }
}
