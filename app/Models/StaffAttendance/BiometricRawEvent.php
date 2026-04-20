<?php

namespace App\Models\StaffAttendance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for raw biometric events from devices.
 *
 * Stores unprocessed clock events before they are converted to attendance records.
 * Events are immutable logs - no soft deletes.
 *
 * @property int $id
 * @property int $device_id
 * @property string $employee_number
 * @property \Carbon\Carbon $event_timestamp
 * @property string $event_type
 * @property array|null $raw_payload
 * @property bool $processed
 * @property \Carbon\Carbon|null $processed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read AttendanceDevice $device
 */
class BiometricRawEvent extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'device_id',
        'employee_number',
        'event_timestamp',
        'event_type',
        'raw_payload',
        'processed',
        'processed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'event_timestamp' => 'datetime',
        'raw_payload' => 'array',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
    ];

    // ==================== EVENT TYPE CONSTANTS ====================

    /**
     * Clock in event type.
     */
    const CLOCK_IN = 'clock_in';

    /**
     * Clock out event type.
     */
    const CLOCK_OUT = 'clock_out';

    /**
     * Break start event type.
     */
    const BREAK_START = 'break_start';

    /**
     * Break end event type.
     */
    const BREAK_END = 'break_end';

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the device that captured this event.
     *
     * @return BelongsTo
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(AttendanceDevice::class, 'device_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope to filter unprocessed events.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    /**
     * Scope to filter events by employee number.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $employeeNumber
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEmployee($query, string $employeeNumber)
    {
        return $query->where('employee_number', $employeeNumber);
    }

    /**
     * Scope to filter events on a specific date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|\Carbon\Carbon $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('event_timestamp', $date);
    }

    /**
     * Scope to filter events between two dates.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|\Carbon\Carbon $start
     * @param string|\Carbon\Carbon $end
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('event_timestamp', [$start, $end]);
    }
}
