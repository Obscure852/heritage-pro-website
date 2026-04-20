<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->title }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-body {
            padding: 30px;
        }
        .notification-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .notification-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #1f2937;
        }
        .notification-message {
            font-size: 16px;
            color: #4b5563;
            margin-bottom: 25px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #4f46e5;
            color: white !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
        }
        .btn:hover {
            background: #4338ca;
        }
        .email-footer {
            padding: 20px 30px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .email-footer a {
            color: #4f46e5;
            text-decoration: none;
        }
        .timestamp {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-card">
            <div class="email-header">
                <h1>{{ config('app.name') }} LMS</h1>
            </div>

            <div class="email-body">
                <div class="notification-icon" style="background-color: {{ $notification->color }}20; color: {{ $notification->color }};">
                    <i class="{{ $notification->icon }}"></i>
                </div>

                <div class="notification-title">
                    {{ $notification->title }}
                </div>

                <div class="notification-message">
                    {{ $notification->message }}
                </div>

                @if($notification->action_url)
                    <div style="text-align: center;">
                        <a href="{{ $notification->action_url }}" class="btn">
                            {{ $notification->action_text ?? 'View Details' }}
                        </a>
                    </div>
                @endif

                <div class="timestamp">
                    {{ $notification->created_at->format('F j, Y \a\t g:i A') }}
                </div>
            </div>

            <div class="email-footer">
                <p>
                    You received this email because you have notifications enabled for this type of activity.
                </p>
                <p>
                    <a href="{{ route('lms.notifications.preferences') }}">Manage notification preferences</a>
                    &nbsp;|&nbsp;
                    <a href="{{ route('lms.notifications.index') }}">View all notifications</a>
                </p>
                <p style="margin-top: 15px;">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
