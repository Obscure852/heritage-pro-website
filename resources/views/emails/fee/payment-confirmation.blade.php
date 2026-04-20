<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .amount-box { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; text-align: center; margin: 20px 0; border-radius: 5px; }
        .amount { font-size: 24px; font-weight: bold; color: #155724; }
        .receipt-number { background: white; padding: 10px 20px; display: inline-block; border-radius: 5px; font-size: 18px; font-weight: bold; margin: 10px 0; }
        .details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .details p { margin: 5px 0; }
        .balance-info { background: #e2e3e5; padding: 10px 15px; border-radius: 5px; margin: 15px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $schoolName }}</h1>
        <h2>Payment Confirmation</h2>
    </div>

    <div class="content">
        <p>Dear Parent/Guardian,</p>

        <p>Thank you for your payment. This email confirms that we have received the following payment:</p>

        <div style="text-align: center;">
            <div class="receipt-number">Receipt #{{ $payment->receipt_number }}</div>
        </div>

        <div class="amount-box">
            <p>Amount Received</p>
            <p class="amount">P{{ number_format($payment->amount, 2) }}</p>
        </div>

        <div class="details">
            <p><strong>Student:</strong> {{ $student->full_name ?? 'N/A' }}</p>
            <p><strong>Student Number:</strong> {{ $student->student_number ?? '-' }}</p>
            <p><strong>Invoice #:</strong> {{ $invoice->invoice_number ?? 'N/A' }}</p>
            <p><strong>Payment Date:</strong> {{ $payment->payment_date->format('d M Y') }}</p>
            <p><strong>Payment Method:</strong> {{ $payment->payment_method_label }}</p>
            @if($payment->reference_number)
                <p><strong>Reference:</strong> {{ $payment->reference_number }}</p>
            @endif
        </div>

        @if($invoice)
            <div class="balance-info">
                <p><strong>Invoice Total:</strong> P{{ number_format($invoice->total_amount, 2) }}</p>
                <p><strong>Total Paid:</strong> P{{ number_format($invoice->amount_paid, 2) }}</p>
                <p><strong>Remaining Balance:</strong> P{{ number_format($invoice->balance, 2) }}</p>
            </div>
        @endif

        <p>Please keep this email as your receipt for records. You may also print this confirmation for your reference.</p>

        <p>If you have any questions about this payment, please contact the school's finance office.</p>

        <p>Thank you for your continued support.</p>
    </div>

    <div class="footer">
        <p>This is an automated confirmation from {{ $schoolName }}.</p>
        <p>Payment received on {{ $payment->created_at->format('d M Y H:i') }}</p>
    </div>
</body>
</html>
