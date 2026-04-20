<?php

namespace App\Models\StaffAttendance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for attendance devices (biometric readers).
 *
 * Represents physical biometric devices used for staff attendance tracking.
 * Supports Hikvision and ZKTeco device types with multiple connectivity modes
 * for cloud-based deployment.
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $ip_address
 * @property int $port
 * @property string|null $username
 * @property string|null $password
 * @property string|null $serial_number
 * @property string|null $location
 * @property string $timezone
 * @property bool $is_active
 * @property string $connectivity_mode
 * @property string|null $webhook_secret
 * @property string|null $public_url
 * @property \Carbon\Carbon|null $last_sync_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|BiometricRawEvent[] $rawEvents
 * @property-read \Illuminate\Database\Eloquent\Collection|AttendanceSyncLog[] $syncLogs
 */
class AttendanceDevice extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'ip_address',
        'port',
        'username',
        'password',
        'serial_number',
        'location',
        'timezone',
        'is_active',
        'connectivity_mode',
        'webhook_secret',
        'public_url',
        'last_sync_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
        'port' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'webhook_secret',
    ];

    // ==================== TYPE CONSTANTS ====================

    /**
     * Hikvision device type.
     */
    const TYPE_HIKVISION = 'hikvision';

    /**
     * ZKTeco device type.
     */
    const TYPE_ZKTECO = 'zkteco';

    // ==================== CONNECTIVITY MODE CONSTANTS ====================

    /**
     * Pull mode: Server polls the device for events.
     * Requires network access from server to device (VPN/port forwarding).
     */
    const MODE_PULL = 'pull';

    /**
     * Push mode: Device pushes events to a webhook URL.
     * Best for Hikvision devices with ISUP/webhook support.
     * Device must be able to reach the server's public URL.
     */
    const MODE_PUSH = 'push';

    /**
     * Agent mode: On-premise sync agent pushes events to cloud API.
     * Most flexible - works with any device type.
     * Requires a local agent running at the school.
     */
    const MODE_AGENT = 'agent';

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the raw biometric events captured by this device.
     *
     * @return HasMany
     */
    public function rawEvents(): HasMany
    {
        return $this->hasMany(BiometricRawEvent::class, 'device_id');
    }

    /**
     * Get the sync logs for this device.
     *
     * @return HasMany
     */
    public function syncLogs(): HasMany
    {
        return $this->hasMany(AttendanceSyncLog::class, 'device_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope to filter only active devices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter devices by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type Device type (use TYPE_* constants)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter devices by connectivity mode.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $mode Connectivity mode (use MODE_* constants)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfMode($query, string $mode)
    {
        return $query->where('connectivity_mode', $mode);
    }

    /**
     * Scope to filter devices that use pull mode (server polls device).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePullMode($query)
    {
        return $query->where('connectivity_mode', self::MODE_PULL);
    }

    /**
     * Scope to filter devices that use push mode (device posts to webhook).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePushMode($query)
    {
        return $query->where('connectivity_mode', self::MODE_PUSH);
    }

    /**
     * Scope to filter devices that use agent mode (local agent posts to API).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAgentMode($query)
    {
        return $query->where('connectivity_mode', self::MODE_AGENT);
    }

    // ==================== HELPERS ====================

    /**
     * Generate a unique webhook secret for this device.
     *
     * @return string
     */
    public static function generateWebhookSecret(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Get the webhook URL for this device.
     *
     * @return string|null
     */
    public function getWebhookUrl(): ?string
    {
        if ($this->connectivity_mode !== self::MODE_PUSH) {
            return null;
        }

        $baseUrl = $this->public_url ?: config('app.url');

        return rtrim($baseUrl, '/') . '/api/attendance/webhook/hikvision/' . $this->id;
    }

    /**
     * Get the agent API URL for this device.
     *
     * @return string|null
     */
    public function getAgentApiUrl(): ?string
    {
        if ($this->connectivity_mode !== self::MODE_AGENT) {
            return null;
        }

        return rtrim(config('app.url'), '/') . '/api/attendance/webhook/agent/' . $this->id;
    }

    /**
     * Check if this device requires network access from the server.
     *
     * @return bool
     */
    public function requiresServerNetworkAccess(): bool
    {
        return $this->connectivity_mode === self::MODE_PULL;
    }

    /**
     * Verify a webhook signature from this device.
     *
     * @param string $payload The raw request body
     * @param string $signature The signature from the request header
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (empty($this->webhook_secret)) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $this->webhook_secret);

        return hash_equals($expected, $signature);
    }
}
