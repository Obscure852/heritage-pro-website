<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model {
    protected $table = 'lms_notification_preferences';

    protected $fillable = [
        'student_id',
        'type',
        'in_app',
        'email',
        'push',
    ];

    protected $casts = [
        'in_app' => 'boolean',
        'email' => 'boolean',
        'push' => 'boolean',
    ];

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public static function getPreference(int $studentId, string $type): self {
        return self::firstOrCreate(
            ['student_id' => $studentId, 'type' => $type],
            ['in_app' => true, 'email' => true, 'push' => false]
        );
    }

    public static function shouldSendInApp(int $studentId, string $type): bool {
        $pref = self::getPreference($studentId, $type);
        return $pref->in_app;
    }

    public static function shouldSendEmail(int $studentId, string $type): bool {
        $pref = self::getPreference($studentId, $type);
        return $pref->email;
    }

    public static function updatePreferences(int $studentId, array $preferences): void {
        foreach ($preferences as $type => $settings) {
            self::updateOrCreate(
                ['student_id' => $studentId, 'type' => $type],
                [
                    'in_app' => $settings['in_app'] ?? true,
                    'email' => $settings['email'] ?? true,
                    'push' => $settings['push'] ?? false,
                ]
            );
        }
    }
}
