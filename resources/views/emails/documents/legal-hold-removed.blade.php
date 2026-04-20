<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Legal Hold Removed</title>
    <style>
        body, table, td, p, a { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color: #333333; line-height: 1.5; }
        body { margin: 0; padding: 0; background-color: #ffffff; }
        .email-container { max-width: 580px; margin: 0 auto; padding: 40px 20px; background-color: #ffffff; }
        .email-logo { text-align: center; margin-bottom: 30px; }
        .email-logo img { max-height: 70px; width: auto; }
        .email-header { text-align: left; padding-bottom: 25px; }
        .email-header h1 { margin: 0; color: #111111; font-size: 24px; font-weight: 600; }
        .email-content { padding: 0 0 30px 0; }
        .email-content p { font-size: 15px; margin: 0 0 15px; color: #333333; }
        .info-box { background-color: #f0fdf4; border: 1px solid #22c55e; border-radius: 6px; padding: 20px; margin: 20px 0; }
        .info-box .info-text { font-size: 16px; font-weight: 600; color: #166534; margin: 0 0 8px; }
        .info-box .info-detail { font-size: 14px; color: #15803d; margin: 0; }
        .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .details-table th, .details-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eeeeee; }
        .details-table th { background-color: #f8f9fa; font-weight: 600; color: #555555; width: 40%; }
        .details-table td { color: #333333; }
        .action-button { display: inline-block; padding: 12px 24px; background-color: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: 500; font-size: 14px; margin-top: 10px; }
        .divider { height: 1px; background-color: #eeeeee; margin: 30px 0; border: none; }
        .email-footer { padding: 0; text-align: left; color: #777777; font-size: 13px; }
        .email-footer a { color: #1a73e8; text-decoration: none; }
        .email-footer p { margin: 5px 0; }
        .sign-off { margin-bottom: 20px; }
        .powered-by { margin-top: 20px; text-align: center; font-size: 12px; }
        .heritage-logo { text-align: center; margin-top: 10px; }
        .heritage-logo img { max-height: 30px; width: auto; }
        @media only screen and (max-width: 600px) { .email-container { width: 100% !important; padding: 20px; } }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-logo">
            @if ($school['logo'])
                <img src="{{ $school['logo'] }}" alt="{{ $school['name'] }} Logo">
            @else
                <h2>{{ $school['name'] }}</h2>
            @endif
        </div>

        <div class="email-header">
            <h1>Legal Hold Removed</h1>
        </div>

        <div class="email-content">
            <p>Dear {{ $recipientName }},</p>

            <div class="info-box">
                <p class="info-text">Legal hold has been removed from your document</p>
                <p class="info-detail">"{{ $documentTitle }}" is now subject to normal retention policies.</p>
            </div>

            <p>The legal hold on this document has been lifted. The document is now subject to standard retention policies, including expiration dates and automated archival rules.</p>

            <table class="details-table">
                <tr>
                    <th>Document</th>
                    <td>{{ $documentTitle }}</td>
                </tr>
                <tr>
                    <th>Removed By</th>
                    <td>{{ $removedByName }}</td>
                </tr>
            </table>

            <a href="{{ $documentUrl }}" class="action-button">View Document</a>
        </div>

        <hr class="divider">

        <div class="email-footer">
            <p class="sign-off">The {{ $school['name'] }} Team</p>
            @if ($school['address'])
                <p>{{ $school['address'] }}</p>
            @endif
            <p>&copy; {{ date('Y') }} {{ $school['name'] }}. All rights reserved.</p>
            <p>For questions contact <a href="mailto:{{ $school['email'] }}">{{ $school['email'] }}</a></p>

            <div class="powered-by">Powered by</div>
            <div class="heritage-logo">
                <img src="{{ config('notifications.email.defaults.logo_url') }}" alt="Heritage Pro">
            </div>
        </div>
    </div>
</body>

</html>
