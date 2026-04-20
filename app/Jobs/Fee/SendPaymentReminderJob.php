<?php

namespace App\Jobs\Fee;

use App\Mail\Fee\PaymentConfirmationMail;
use App\Mail\Fee\PaymentOverdueMail;
use App\Mail\Fee\PaymentReminderMail;
use App\Models\Fee\FeePayment;
use App\Models\Fee\PaymentPlanInstallment;
use App\Models\Fee\StudentInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public StudentInvoice $invoice,
        public string $notificationType,
        public ?PaymentPlanInstallment $installment = null,
        public ?int $daysOverdue = null
    ) {}

    public function handle(): void
    {
        $invoice = $this->invoice->load('student');
        $student = $invoice->student;

        if (!$student) {
            Log::warning('Cannot send payment reminder - student not found', [
                'invoice_id' => $invoice->id,
            ]);
            return;
        }

        // Get recipient email - student's email or sponsor email
        $recipientEmail = $this->getRecipientEmail($student);

        if (!$recipientEmail) {
            Log::warning('Cannot send payment reminder - no email address', [
                'invoice_id' => $invoice->id,
                'student_id' => $student->id,
            ]);
            return;
        }

        // Send appropriate email based on notification type
        switch ($this->notificationType) {
            case 'invoice_due':
            case 'installment_due':
                $mail = new PaymentReminderMail($invoice, $this->installment);
                break;

            case 'overdue':
            case 'installment_overdue':
                $mail = new PaymentOverdueMail($invoice, $this->daysOverdue, $this->installment);
                break;

            default:
                Log::warning('Unknown notification type', [
                    'type' => $this->notificationType,
                    'invoice_id' => $invoice->id,
                ]);
                return;
        }

        try {
            Mail::to($recipientEmail)->send($mail);

            Log::info('Payment reminder sent', [
                'invoice_id' => $invoice->id,
                'type' => $this->notificationType,
                'recipient' => $recipientEmail,
            ]);

            // Also send to admin if configured
            $adminEmail = settings('fee.admin_notification_email');
            if ($adminEmail) {
                Mail::to($adminEmail)->send($mail);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send payment reminder email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get the recipient email address.
     */
    protected function getRecipientEmail($student): ?string
    {
        // First try student's email
        if (!empty($student->email)) {
            return $student->email;
        }

        // Try sponsor/parent email if available
        if ($student->sponsor && !empty($student->sponsor->email)) {
            return $student->sponsor->email;
        }

        return null;
    }

    /**
     * Static method to send payment confirmation.
     */
    public static function sendConfirmation(FeePayment $payment): void
    {
        $payment->load(['invoice.student', 'receivedBy']);

        $student = $payment->student ?? $payment->invoice->student;

        if (!$student) {
            return;
        }

        $recipientEmail = $student->email;

        // Try sponsor email if student email not available
        if (empty($recipientEmail) && $student->sponsor) {
            $recipientEmail = $student->sponsor->email ?? null;
        }

        if (empty($recipientEmail)) {
            Log::info('Cannot send payment confirmation - no email address', [
                'payment_id' => $payment->id,
            ]);
            return;
        }

        try {
            $mail = new PaymentConfirmationMail($payment);
            Mail::to($recipientEmail)->send($mail);

            Log::info('Payment confirmation sent', [
                'payment_id' => $payment->id,
                'receipt_number' => $payment->receipt_number,
                'recipient' => $recipientEmail,
            ]);

            // Also send to admin if configured
            $adminEmail = settings('fee.admin_notification_email');
            if ($adminEmail) {
                Mail::to($adminEmail)->send($mail);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send payment confirmation', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
