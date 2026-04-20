<?php

namespace App\Mail\Fee;

use App\Models\Fee\PaymentPlanInstallment;
use App\Models\Fee\StudentInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public StudentInvoice $invoice,
        public ?PaymentPlanInstallment $installment = null
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->installment
            ? "Payment Reminder: Installment #{$this->installment->installment_number} Due Soon"
            : "Payment Reminder: Invoice #{$this->invoice->invoice_number} Due Soon";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.fee.payment-reminder',
            with: [
                'invoice' => $this->invoice,
                'student' => $this->invoice->student,
                'installment' => $this->installment,
                'schoolName' => config('app.name'),
            ]
        );
    }
}
