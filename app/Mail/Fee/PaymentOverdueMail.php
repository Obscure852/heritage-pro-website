<?php

namespace App\Mail\Fee;

use App\Models\Fee\PaymentPlanInstallment;
use App\Models\Fee\StudentInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentOverdueMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public StudentInvoice $invoice,
        public ?int $daysOverdue = null,
        public ?PaymentPlanInstallment $installment = null
    ) {}

    public function envelope(): Envelope
    {
        $daysText = $this->daysOverdue ? " ({$this->daysOverdue} days)" : '';

        $subject = $this->installment
            ? "Payment Overdue{$daysText}: Installment #{$this->installment->installment_number}"
            : "Payment Overdue{$daysText}: Invoice #{$this->invoice->invoice_number}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.fee.payment-overdue',
            with: [
                'invoice' => $this->invoice,
                'student' => $this->invoice->student,
                'daysOverdue' => $this->daysOverdue,
                'installment' => $this->installment,
                'schoolName' => config('app.name'),
            ]
        );
    }
}
