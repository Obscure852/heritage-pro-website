<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\PersonalAccessToken;

class CrmAttendanceDevice extends Model
{
    protected $fillable = [
        'name',
        'brand',
        'model',
        'device_identifier',
        'serial_number',
        'ip_address',
        'port',
        'communication_key',
        'protocol',
        'direction',
        'timezone',
        'heartbeat_interval',
        'push_interval',
        'firmware_version',
        'user_capacity',
        'fingerprint_capacity',
        'face_capacity',
        'supported_verify_methods',
        'device_options',
        'location',
        'api_token_id',
        'min_confidence',
        'is_active',
        'last_heartbeat_at',
    ];

    protected $casts = [
        'port' => 'integer',
        'min_confidence' => 'decimal:2',
        'heartbeat_interval' => 'integer',
        'push_interval' => 'integer',
        'user_capacity' => 'integer',
        'fingerprint_capacity' => 'integer',
        'face_capacity' => 'integer',
        'supported_verify_methods' => 'array',
        'device_options' => 'array',
        'is_active' => 'boolean',
        'last_heartbeat_at' => 'datetime',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(CrmAttendanceDeviceLog::class, 'device_id')->latest('created_at');
    }

    public function apiToken()
    {
        return $this->belongsTo(PersonalAccessToken::class, 'api_token_id');
    }

    public function isOnline(): bool
    {
        if (! $this->last_heartbeat_at) {
            return false;
        }

        $timeout = (int) config('heritage_crm.attendance.biometric_heartbeat_timeout_minutes', 30);

        return $this->last_heartbeat_at->gte(now()->subMinutes($timeout));
    }

    public function isZkteco(): bool
    {
        return $this->brand === 'zkteco';
    }

    public function isHikvision(): bool
    {
        return $this->brand === 'hikvision';
    }

    public function brandLabel(): string
    {
        return config('heritage_crm.attendance.device_brands.' . $this->brand . '.label', ucfirst($this->brand));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
