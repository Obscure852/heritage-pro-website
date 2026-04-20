<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Overdue Notice</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .amount-box { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; text-align: center; margin: 20px 0; border-radius: 5px; }
        .amount { font-size: 24px; font-weight: bold; color: #721c24; }
        .overdue-notice { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px 15px; margin: 15px 0; }
        .details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .details p { margin: 5px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $schoolName }}</h1>
        <h2>Payment Overdue Notice</h2>
    </div>

    <div class="content">
        <p>Dear Parent/Guardian,</p>

        <div class="overdue-notice">
            <strong>Important:</strong> A fee payment is now overdue
            @if($daysOverdue)
                by <strong>{{ $daysOverdue }} days</strong>
            @endif
            and requires immediate attention.
        </div>

        <div class="details">
            <p><strong>Student:</strong> {{ $student->full_name ?? 'N/A' }}</p>
            <p><strong>Student Number:</strong> {{ $student->student_number ?? '-' }}</p>
            <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
            @if($installment)
                <p><strong>Installment:</strong> #{{ $installment->installment_number }}</p>
                <p><strong>Original Due Date:</strong> {{ $installment->due_date->format('d M Y') }}</p>
            @else
                <p><strong>Original Due Date:</strong> {{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</p>
            @endif
        </div>

        <div class="amount-box">
            <p>Overdue Amount</p>
            @if($installment)
                <p class="amount">P{{ number_format($installment->balance, 2) }}</p>
            @else
                <p class="amount">P{{ number_format($invoice->balance, 2) }}</p>
            @endif
        </div>

        <p>Please make payment as soon as possible to avoid:</p>
        <ul>
            <li>Additional late fees</li>
            <li>Service restrictions</li>
            <li>Impact on student clearance</li>
        </ul>

        <p>If you are experiencing financial difficulties, please contact the finance office to discuss payment arrangements.</p>

        <p>If you have already made this payment, please forward your proof of payment to the finance office.</p>
    </div>

    <div class="footer">
        <p>This is an automated message from {{ $schoolName }}.</p>
        <p>Please do not reply directly to this email.</p>
    </div>
</body>
</html>
