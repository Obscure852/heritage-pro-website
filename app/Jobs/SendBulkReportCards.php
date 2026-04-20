<?php

namespace App\Jobs;

use App\Http\Controllers\AssessmentController;
use App\Models\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBulkReportCards implements ShouldQueue{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $studentId;
    protected $subject;
    protected $message;
    protected $termId;
    protected $senderId;

    public function __construct($studentId, $subject, $message, $termId, $senderId){
        $this->studentId = $studentId;
        $this->subject = $subject;
        $this->message = $message;
        $this->termId = $termId;
        $this->senderId = $senderId;
    }

    public function handle(AssessmentController $assessmentController){
        try {
            $student = Student::with(['sponsor', 'currentGrade'])->find($this->studentId);
            $term = Term::find($this->termId);

            if (!$student || !$student->sponsor || !$student->sponsor->email) {
                Log::warning("Unable to send report card for student ID {$this->studentId}: Student not found or no valid email.");
                return;
            }

            $reportCard = $assessmentController->prepareReportCardPdfForStudent($student, $term);

            Mail::send('emails.report-card-email', ['messageContent' => $this->message], function ($mail) use ($student, $reportCard) {
                $mail->to($student->sponsor->email)
                    ->subject($this->subject)
                    ->attachData($reportCard['pdf']->output(), $reportCard['filename']);
            });
            
            $email = new Email([
                'term_id' => $this->termId,
                'sender_id' => $this->senderId,
                'sponsor_id' => $student->sponsor_id,
                'receiver_type' => 'sponsor',
                'subject' => $this->subject,
                'body' => $this->message,
                'attachment_path' => $reportCard['filename'],
                'status' => 'sent',
                'num_of_recipients' => 1,
                'type' => 'Bulk'
            ]);
            
            $email->save();

            Log::info("Report card sent successfully for student ID {$this->studentId}");
        } catch (\Exception $e) {
            Log::error("Failed to send report card for student ID {$this->studentId}: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }
}
