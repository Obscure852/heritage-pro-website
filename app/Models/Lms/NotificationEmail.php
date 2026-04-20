<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationEmail extends Model {
    protected $table = 'lms_notification_emails';

    protected $fillable = [
        'notification_id',
        'student_id',
        'email',
        'subject',
        'body',
        'status',
        'error_message',
        'sent_at',
        'attempts',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    public function notification(): BelongsTo {
        return $this->belongsTo(Notification::class);
    }

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function scopePending($query) {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function markAsSent(): void {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $this->notification->update(['email_sent_at' => now()]);
    }

    public function markAsFailed(string $error): void {
        $this->increment('attempts');
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $error,
        ]);
    }
}
