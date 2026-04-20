<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Leave Reminder</title>
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

        .reminder-highlight {
            background-color: #e7f3ff;
            border-left: 4px solid #1a73e8;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }

        .reminder-highlight p {
            margin: 0;
            font-size: 16px;
            font-weight: 500;
            color: #1a73e8;
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
            <h1>Leave Reminder</h1>
        </div>

        <div class="email-content">
            <p>Dear {{ $userName }},</p>

            <div class="reminder-highlight">
                <p>Your approved {{ $leaveType }} starts in {{ $daysUntilStart }} {{ $daysUntilStart === 1 ? 'day' : 'days' }}.</p>
            </div>

            <p>This is a friendly reminder that your approved leave is about to begin.</p>

            <table class="details-table">
                <tr>
                    <th>Leave Type</th>
                    <td>{{ $leaveType }}</td>
                </tr>
                <tr>
                    <th>Start Date</th>
                    <td>{{ $startDate }}</td>
                </tr>
                <tr>
                    <th>End Date</th>
                    <td>{{ $endDate }}</td>
                </tr>
                <tr>
                    <th>Total Days</th>
                    <td>{{ $totalDays }} day(s)</td>
                </tr>
            </table>

            <p>Please ensure all necessary handover tasks are completed before your leave begins.</p>
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
