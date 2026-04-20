<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsDeliveryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'external_id',
        'phone_number',
        'status',
        'status_code',
        'status_message',
        'provider',
        'raw_response',
        'sent_at',
        'delivered_at',
        'failed_at',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Delivery status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';

    /**
     * Create a log entry for a sent message
     */
    public static function logSent(string $messageId, string $phoneNumber, ?string $externalId = null, ?array $rawResponse = null): self
    {
        return self::create([
            'message_id' => $messageId,
            'external_id' => $externalId,
            'phone_number' => $phoneNumber,
            'status' => self::STATUS_SENT,
            'provider' => 'link_sms',
            'raw_response' => $rawResponse,
            'sent_at' => now(),
        ]);
    }

    /**
     * Create a log entry for a delivered message
     */
    public static function logDelivered(string $externalId, ?string $statusCode = null, ?string $statusMessage = null, ?array $rawResponse = null): ?self
    {
        $log = self::where('external_id', $externalId)->latest()->first();

        if ($log) {
            $log->update([
                'status' => self::STATUS_DELIVERED,
                'status_code' => $statusCode,
                'status_message' => $statusMessage,
                'raw_response' => $rawResponse,
                'delivered_at' => now(),
            ]);
            return $log;
        }

        return null;
    }

    /**
     * Create a log entry for a failed message
     */
    public static function logFailed(string $externalId, ?string $statusCode = null, ?string $statusMessage = null, ?array $rawResponse = null): ?self
    {
        $log = self::where('external_id', $externalId)->latest()->first();

        if ($log) {
            $log->update([
                'status' => self::STATUS_FAILED,
                'status_code' => $statusCode,
                'status_message' => $statusMessage,
                'raw_response' => $rawResponse,
                'failed_at' => now(),
            ]);
            return $log;
        }

        return null;
    }

    /**
     * Scope to get logs by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending logs
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get delivered logs
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    /**
     * Scope to get failed logs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Check if message was delivered
     */
    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Check if message failed
     */
    public function isFailed(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_REJECTED, self::STATUS_EXPIRED]);
    }
}
