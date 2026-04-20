<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>New Calendar Event: {{ $event->title }}</title>
    <style>
        body,
        table,
        td,
        p,
        a {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #333333;
            line-height: 1.6;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .email-wrapper {
            background-color: #f5f5f5;
            padding: 30px 20px;
        }

        .email-container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .email-header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            padding: 30px;
            text-align: center;
        }

        .email-header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }

        .email-logo {
            margin-bottom: 20px;
        }

        .email-logo img {
            max-height: 60px;
            width: auto;
        }

        .event-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .email-body {
            padding: 30px;
        }

        .event-details {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .event-description {
            color: #4b5563;
            margin-bottom: 25px;
            font-size: 15px;
        }

        .event-description p {
            margin: 0 0 10px;
        }

        .action-button {
            display: inline-block;
            background: #3b82f6;
            color: #ffffff !important;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            text-align: center;
        }

        .action-button:hover {
            background: #2563eb;
        }

        .meeting-link-section {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 25px;
        }

        .meeting-link-section a {
            color: #059669;
            font-weight: 600;
        }

        .email-footer {
            background: #f9fafb;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }

        .email-footer p {
            margin: 5px 0;
            font-size: 13px;
            color: #6b7280;
        }

        .email-footer a {
            color: #3b82f6;
            text-decoration: none;
        }

        .powered-by {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }

        .powered-by img {
            max-height: 25px;
            width: auto;
        }

        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                border-radius: 0;
            }

            .email-header,
            .email-body,
            .email-footer {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                @if (!empty($schoolDetails['schoolLogo']))
                    <div class="email-logo">
                        <img src="{{ $schoolDetails['schoolLogo'] }}" alt="{{ $schoolDetails['schoolName'] ?? 'School' }} Logo">
                    </div>
                @endif

                <div class="event-badge">
                    {{ ucfirst($event->type) }} Event
                </div>

                <h1>{{ $event->title }}</h1>
            </div>

            <div class="email-body">
                <div class="event-details">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="30" valign="top" style="color: #3b82f6; font-size: 16px;">
                                <strong>&#128197;</strong>
                            </td>
                            <td width="90" valign="top" style="font-weight: 600; color: #374151;">Date:</td>
                            <td style="color: #4b5563;">
                                @if($event->all_day)
                                    {{ $event->start_date->format('l, F j, Y') }}
                                    <span style="color: #9ca3af;">(All Day)</span>
                                @else
                                    {{ $event->start_date->format('l, F j, Y') }}
                                @endif
                            </td>
                        </tr>
                        @if(!$event->all_day)
                        <tr>
                            <td width="30" valign="top" style="color: #3b82f6; font-size: 16px; padding-top: 10px;">
                                <strong>&#128336;</strong>
                            </td>
                            <td width="90" valign="top" style="font-weight: 600; color: #374151; padding-top: 10px;">Time:</td>
                            <td style="color: #4b5563; padding-top: 10px;">
                                {{ $event->start_date->format('g:i A') }}
                                @if($event->end_date)
                                    - {{ $event->end_date->format('g:i A') }}
                                @endif
                            </td>
                        </tr>
                        @endif
                        @if($event->location)
                        <tr>
                            <td width="30" valign="top" style="color: #3b82f6; font-size: 16px; padding-top: 10px;">
                                <strong>&#128205;</strong>
                            </td>
                            <td width="90" valign="top" style="font-weight: 600; color: #374151; padding-top: 10px;">Location:</td>
                            <td style="color: #4b5563; padding-top: 10px;">
                                {{ $event->location }}
                            </td>
                        </tr>
                        @endif
                        @if($event->course)
                        <tr>
                            <td width="30" valign="top" style="color: #3b82f6; font-size: 16px; padding-top: 10px;">
                                <strong>&#128218;</strong>
                            </td>
                            <td width="90" valign="top" style="font-weight: 600; color: #374151; padding-top: 10px;">Course:</td>
                            <td style="color: #4b5563; padding-top: 10px;">
                                {{ $event->course->title }}
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>

                @if($event->meeting_url)
                <div class="meeting-link-section">
                    <p style="margin: 0;"><strong>Virtual Meeting:</strong></p>
                    <a href="{{ $event->meeting_url }}">{{ $event->meeting_url }}</a>
                </div>
                @endif

                @if($event->description)
                <div class="event-description">
                    <p><strong>Details:</strong></p>
                    <p>{!! nl2br(e($event->description)) !!}</p>
                </div>
                @endif

                <div style="text-align: center;">
                    <a href="{{ route('lms.calendar.index') }}" class="action-button">
                        View Calendar
                    </a>
                </div>
            </div>

            <div class="email-footer">
                <p>{{ $schoolDetails['schoolName'] ?? 'Heritage Pro' }}</p>
                @if(!empty($schoolDetails['address']))
                <p>{{ $schoolDetails['address'] }}</p>
                @endif
                <p>&copy; {{ date('Y') }} {{ $schoolDetails['schoolName'] ?? 'Heritage Pro' }}. All rights reserved.</p>
                @if(!empty($schoolDetails['supportEmail']))
                <p>Questions? Contact us at <a href="mailto:{{ $schoolDetails['supportEmail'] }}">{{ $schoolDetails['supportEmail'] }}</a></p>
                @endif

                <div class="powered-by">
                    <p style="color: #9ca3af; font-size: 11px;">Powered by</p>
                    <img src="https://bw-syllabus.s3.us-east-1.amazonaws.com/heritage-pro-logo.jpg" alt="Heritage Pro">
                </div>
            </div>
        </div>
    </div>
</body>

</html>
