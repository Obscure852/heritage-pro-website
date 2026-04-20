<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Pending Leave Requests</title>
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

        .alert-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }

        .alert-box p {
            margin: 0;
            font-size: 15px;
            color: #856404;
        }

        .alert-box strong {
            font-weight: 600;
        }

        .requests-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }

        .requests-table th,
        .requests-table td {
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid #eeeeee;
        }

        .requests-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #555555;
        }

        .requests-table td {
            color: #333333;
        }

        .requests-table tr:hover {
            background-color: #fafafa;
        }

        .cta-button {
            display: inline-block;
            background-color: #1a73e8;
            color: #ffffff !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            margin: 20px 0;
        }

        .cta-button:hover {
            background-color: #1557b0;
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

            .requests-table {
                font-size: 12px;
            }

            .requests-table th,
            .requests-table td {
                padding: 8px 4px;
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
            <h1>Pending Leave Requests</h1>
        </div>

        <div class="email-content">
            <p>Dear {{ $managerName }},</p>

            <div class="alert-box">
                <p>You have <strong>{{ $pendingCount }}</strong> leave {{ $pendingCount === 1 ? 'request' : 'requests' }} awaiting your approval.</p>
                @if ($oldestRequestHours > 24)
                    <p style="margin-top: 10px;">The oldest request has been pending for <strong>{{ round($oldestRequestHours / 24) }}</strong> day(s).</p>
                @endif
            </div>

            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Leave Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendingRequests as $request)
                    <tr>
                        <td>{{ $request->user->name ?? 'N/A' }}</td>
                        <td>{{ $request->leaveType->name ?? 'N/A' }}</td>
                        <td>{{ $request->start_date->format('d M') }} - {{ $request->end_date->format('d M') }}</td>
                        <td>{{ $request->total_days }}</td>
                        <td>{{ $request->submitted_at?->format('d M H:i') ?? $request->created_at->format('d M H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p style="text-align: center;">
                <a href="{{ url('/leave/requests/pending') }}" class="cta-button">Review Pending Requests</a>
            </p>

            <p>Please review these requests at your earliest convenience.</p>
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
