<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Document Expiring Soon</title>
    <style>
        body,
        table,
        td,
        p,
        a {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #333333;
            line-height: 1.5;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .email-container {
            max-width: 580px;
            margin: 0 auto;
            padding: 40px 20px;
            background-color: #ffffff;
        }

        .email-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .email-logo img {
            max-height: 70px;
            width: auto;
        }

        .email-header {
            text-align: left;
            padding-bottom: 25px;
        }

        .email-header h1 {
            margin: 0;
            color: #111111;
            font-size: 24px;
            font-weight: 600;
        }

        .email-content {
            padding: 0 0 30px 0;
        }

        .email-content p {
            font-size: 15px;
            margin: 0 0 15px;
            color: #333333;
        }

        .warning-box {
            background-color: #fffbeb;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }

        .warning-box .warning-text {
            font-size: 16px;
            font-weight: 600;
            color: #92400e;
            margin: 0 0 8px;
        }

        .warning-box .warning-detail {
            font-size: 14px;
            color: #78350f;
            margin: 0;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .details-table th,
        .details-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eeeeee;
        }

        .details-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #555555;
            width: 40%;
        }

        .details-table td {
            color: #333333;
        }

        .action-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3b82f6;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
            margin-top: 10px;
        }

        .suggestions {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 15px 20px;
            margin: 20px 0;
        }

        .suggestions h3 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 10px;
        }

        .suggestions ul {
            margin: 0;
            padding-left: 20px;
        }

        .suggestions li {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 6px;
        }

        .divider {
            height: 1px;
            background-color: #eeeeee;
            margin: 30px 0;
            border: none;
        }

        .email-footer {
            padding: 0;
            text-align: left;
            color: #777777;
            font-size: 13px;
        }

        .email-footer a {
            color: #1a73e8;
            text-decoration: none;
        }

        .email-footer p {
            margin: 5px 0;
        }

        .sign-off {
            margin-bottom: 20px;
        }

        .powered-by {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
        }

        .heritage-logo {
            text-align: center;
            margin-top: 10px;
        }

        .heritage-logo img {
            max-height: 30px;
            width: auto;
        }

        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                padding: 20px;
            }
        }
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
            <h1>Document Expiring Soon</h1>
        </div>

        <div class="email-content">
            <p>Dear {{ $recipientName }},</p>

            <div class="warning-box">
                <p class="warning-text">Your document expires in {{ $daysRemaining }} day{{ $daysRemaining !== 1 ? 's' : '' }}</p>
                <p class="warning-detail">"{{ $documentTitle }}" will expire on {{ $expiryDate }}</p>
            </div>

            <p>This is a reminder that your document is approaching its expiration date. After expiry, a grace period will begin, after which the document will be automatically archived.</p>

            <table class="details-table">
                <tr>
                    <th>Document</th>
                    <td>{{ $documentTitle }}</td>
                </tr>
                <tr>
                    <th>Expiry Date</th>
                    <td>{{ $expiryDate }}</td>
                </tr>
                <tr>
                    <th>Days Remaining</th>
                    <td>{{ $daysRemaining }}</td>
                </tr>
            </table>

            <div class="suggestions">
                <h3>What you can do:</h3>
                <ul>
                    <li>Renew the expiry date if the document is still needed</li>
                    <li>Download a copy for your records</li>
                    <li>Contact an administrator if you need assistance</li>
                </ul>
            </div>

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
