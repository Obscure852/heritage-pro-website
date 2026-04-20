<?php

namespace App\Mail\Fee;

use App\Models\Fee\FeePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public FeePayment $payment)
    {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Payment Confirmation: Receipt #{$this->payment->receipt_number}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.fee.payment-confirmation',
            with: [
                'payment' => $this->payment,
                'invoice' => $this->payment->invoice,
                'student' => $this->payment->student ?? $this->payment->invoice->student,
                'schoolName' => config('app.name'),
            ]
        );
    }
}
