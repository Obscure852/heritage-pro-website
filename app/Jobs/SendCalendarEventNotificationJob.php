<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\CalendarEventNotification;
use App\Models\Grade;
use App\Models\Klass;
use App\Models\Lms\CalendarEvent;
use App\Models\Lms\Course;
use App\Models\Lms\Enrollment;
use App\Models\SchoolSetup;
use App\Models\SMSApiSetting;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCalendarEventNotificationJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 300;

    public function __construct(
        protected CalendarEvent $event,
        protected array $studentIds
    ) {
        $queueName = SMSApiSetting::where('key', 'lms_calendar_notification_queue')
            ->value('value') ?? 'calendar-notifications';
        $this->onQueue($queueName);
    }

    public function handle(): void {
        if (empty($this->studentIds)) {
            Log::info('SendCalendarEventNotificationJob: No students to notify', [
                'event_id' => $this->event->id,
            ]);
            return;
        }

        $schoolSetup = SchoolSetup::first();
        $schoolDetails = $this->buildSchoolDetails($schoolSetup);

        $students = Student::whereIn('id', $this->studentIds)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where('status', 'current')
            ->get();

        $sentCount = 0;
        $failedCount = 0;

        foreach ($students as $student) {
            try {
                Mail::to($student->email)->send(
                    new CalendarEventNotification($this->event, $schoolDetails)
                );
                $sentCount++;

                Log::debug('Calendar event notification sent', [
                    'event_id' => $this->event->id,
                    'student_id' => $student->id,
                    'email' => $student->email,
                ]);
            } catch (\Throwable $e) {
                $failedCount++;
                Log::error('Failed to send calendar event notification', [
                    'event_id' => $this->event->id,
                    'student_id' => $student->id,
                    'email' => $student->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('SendCalendarEventNotificationJob completed', [
            'event_id' => $this->event->id,
            'sent' => $sentCount,
            'failed' => $failedCount,
            'total_students' => count($this->studentIds),
        ]);
    }

    protected function buildSchoolDetails(?SchoolSetup $schoolSetup): array {
        return [
            'schoolName' => $schoolSetup?->school_name ?? config('app.name'),
            'schoolLogo' => $schoolSetup?->logo_path ? asset('storage/' . $schoolSetup->logo_path) : null,
            'address' => $schoolSetup?->physical_address ?? '',
            'supportEmail' => $schoolSetup?->email_address ?? config('mail.from.address'),
        ];
    }

    public function failed(\Throwable $exception): void {
        Log::error('SendCalendarEventNotificationJob failed permanently', [
            'event_id' => $this->event->id,
            'student_count' => count($this->studentIds),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
