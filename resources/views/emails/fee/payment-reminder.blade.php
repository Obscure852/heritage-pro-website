<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Reminder</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%); color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .amount-box { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; text-align: center; margin: 20px 0; border-radius: 5px; }
        .amount { font-size: 24px; font-weight: bold; color: #856404; }
        .details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .details p { margin: 5px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .btn { display: inline-block; background: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $schoolName }}</h1>
        <h2>Payment Reminder</h2>
    </div>

    <div class="content">
        <p>Dear Parent/Guardian,</p>

        <p>This is a friendly reminder that a fee payment is due soon for:</p>

        <div class="details">
            <p><strong>Student:</strong> {{ $student->full_name ?? 'N/A' }}</p>
            <p><strong>Student Number:</strong> {{ $student->student_number ?? '-' }}</p>
            <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
            @if($installment)
                <p><strong>Installment:</strong> #{{ $installment->installment_number }}</p>
                <p><strong>Due Date:</strong> {{ $installment->due_date->format('d M Y') }}</p>
            @else
                <p><strong>Due Date:</strong> {{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</p>
            @endif
        </div>

        <div class="amount-box">
            <p>Amount Due</p>
            @if($installment)
                <p class="amount">P{{ number_format($installment->balance, 2) }}</p>
            @else
                <p class="amount">P{{ number_format($invoice->balance, 2) }}</p>
            @endif
        </div>

        <p>Please ensure payment is made by the due date to avoid any late fees or service interruptions.</p>

        <p>If you have already made this payment, please disregard this reminder.</p>

        <p>For any queries, please contact the school's finance office.</p>

        <p>Thank you for your prompt attention to this matter.</p>
    </div>

    <div class="footer">
        <p>This is an automated message from {{ $schoolName }}.</p>
        <p>Please do not reply directly to this email.</p>
    </div>
</body>
</html>
