<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Storage Quota Warning</title>
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

        .warning-box .warning-icon {
            font-size: 24px;
            margin-bottom: 8px;
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

        .progress-bar-container {
            background-color: #e5e7eb;
            border-radius: 8px;
            height: 12px;
            margin: 15px 0;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 8px;
            transition: width 0.3s ease;
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
            <h1>Storage Quota Warning</h1>
        </div>

        <div class="email-content">
            <p>Dear {{ $recipientName }},</p>

            <div class="warning-box">
                <p class="warning-text">Your document storage is {{ number_format($usagePercent, 0) }}% full</p>
                <p class="warning-detail">{{ $usedFormatted }} of {{ $quotaFormatted }} used</p>

                @php
                    $barColor = $usagePercent >= 100 ? '#ef4444' : '#f59e0b';
                    $barWidth = min($usagePercent, 100);
                @endphp
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: {{ $barWidth }}%; background-color: {{ $barColor }};"></div>
                </div>
            </div>

            <p>Your document storage usage has reached <strong>{{ number_format($usagePercent, 0) }}%</strong> of your allocated quota. Please review and remove unused documents to free up space.</p>

            <div class="suggestions">
                <h3>Suggestions to free up space:</h3>
                <ul>
                    <li>Review and delete documents you no longer need</li>
                    <li>Check your trash folder and permanently delete old items</li>
                    <li>Remove duplicate files or outdated versions</li>
                    <li>Contact an administrator if you need a larger quota</li>
                </ul>
            </div>

            <table class="details-table">
                <tr>
                    <th>Storage Used</th>
                    <td>{{ $usedFormatted }}</td>
                </tr>
                <tr>
                    <th>Total Quota</th>
                    <td>{{ $quotaFormatted }}</td>
                </tr>
                <tr>
                    <th>Usage</th>
                    <td>{{ number_format($usagePercent, 1) }}%</td>
                </tr>
            </table>

            <a href="{{ $manageUrl }}" class="action-button">Manage Documents</a>
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
