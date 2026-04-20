<?php

namespace App\Models\StaffAttendance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for device synchronization logs.
 *
 * Tracks sync operations with devices including success/failure status,
 * error details, and retry counts per DEV-07 requirements.
 *
 * @property int $id
 * @property int $device_id
 * @property string $sync_type
 * @property string $status
 * @property \Carbon\Carbon $started_at
 * @property \Carbon\Carbon|null $completed_at
 * @property int $records_processed
 * @property int $records_failed
 * @property string|null $error_message
 * @property array|null $error_details
 * @property int $retry_count
 * @property \Carbon\Carbon|null $last_retry_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read AttendanceDevice $device
 */
class AttendanceSyncLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'device_id',
        'sync_type',
        'status',
        'started_at',
        'completed_at',
        'records_processed',
        'records_failed',
        'error_message',
        'error_details',
        'retry_count',
        'last_retry_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'error_details' => 'array',
        'last_retry_at' => 'datetime',
        'records_processed' => 'integer',
        'records_failed' => 'integer',
        'retry_count' => 'integer',
    ];

    // ==================== STATUS CONSTANTS ====================

    /**
     * Successful sync status.
     */
    const STATUS_SUCCESS = 'success';

    /**
     * Failed sync status.
     */
    const STATUS_FAILED = 'failed';

    /**
     * Partial sync status (some records failed).
     */
    const STATUS_PARTIAL = 'partial';

    /**
     * Running sync status.
     */
    const STATUS_RUNNING = 'running';

    // ==================== SYNC TYPE CONSTANTS ====================

    /**
     * Pull events from device sync type.
     */
    const SYNC_PULL_EVENTS = 'pull_events';

    /**
     * Push users to device sync type.
     */
    const SYNC_PUSH_USERS = 'push_users';

    /**
     * Full sync (both directions) sync type.
     */
    const SYNC_FULL = 'full_sync';

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the device this sync log belongs to.
     *
     * @return BelongsTo
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(AttendanceDevice::class, 'device_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope to filter logs by device ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $deviceId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDevice($query, int $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    /**
     * Scope to filter failed sync logs.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope to filter logs from recent days.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days Number of days to look back
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
