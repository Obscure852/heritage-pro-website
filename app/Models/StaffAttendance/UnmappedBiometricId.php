<?php

namespace App\Models\StaffAttendance;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for tracking unmapped biometric IDs.
 *
 * Aggregates event counts and timestamps for device employee_numbers
 * that cannot be matched to system users. Used for admin attention queue.
 *
 * @property int $id
 * @property string $employee_number
 * @property \Carbon\Carbon $first_seen_at
 * @property \Carbon\Carbon $last_seen_at
 * @property int $event_count
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UnmappedBiometricId extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_number',
        'first_seen_at',
        'last_seen_at',
        'event_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'event_count' => 'integer',
    ];

    // ==================== SCOPES ====================

    /**
     * Scope to filter IDs with recent events.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days Number of days to look back (default 30)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRecentEvents($query, int $days = 30)
    {
        return $query->where('last_seen_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to order by event count descending.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByEventCount($query)
    {
        return $query->orderBy('event_count', 'desc');
    }
}
