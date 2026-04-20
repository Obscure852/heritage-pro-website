<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GradeRecalculationCompleted extends Notification
{
    use Queueable;

    protected int $classId;
    protected string $subjectTypeName;
    protected int $subjectsProcessed;
    protected int $testsProcessed;
    protected int $studentsAffected;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(int $classId, string $subjectTypeName, int $subjectsProcessed, int $testsProcessed, int $studentsAffected)
    {
        $this->classId = $classId;
        $this->subjectTypeName = $subjectTypeName;
        $this->subjectsProcessed = $subjectsProcessed;
        $this->testsProcessed = $testsProcessed;
        $this->studentsAffected = $studentsAffected;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Grade Recalculation Completed')
                    ->greeting('Hello!')
                    ->line('Grade recalculation has been completed successfully.')
                    ->line('Subject Type: ' . ucfirst($this->subjectTypeName))
                    ->line('Subjects Processed: ' . $this->subjectsProcessed)
                    ->line('Tests Processed: ' . $this->testsProcessed)
                    ->line('Students Affected: ' . $this->studentsAffected)
                    ->action('View Markbook', route('assessment.markbook'))
                    ->line('Thank you for using our system!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Grade Recalculation Completed',
            'message' => 'Successfully recalculated grades for ' . $this->subjectTypeName,
            'class_id' => $this->classId,
            'subject_type' => $this->subjectTypeName,
            'subjects_processed' => $this->subjectsProcessed,
            'tests_processed' => $this->testsProcessed,
            'students_affected' => $this->studentsAffected,
            'icon' => 'bx-check-circle',
            'color' => 'success',
        ];
    }
}
