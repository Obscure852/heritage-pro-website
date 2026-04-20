<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ $details['subject'] }}</title>
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

        .email-button {
            display: inline-block;
            padding: 10px 0;
            color: #1a73e8;
            font-weight: 500;
            text-decoration: none;
            font-size: 15px;
            margin: 15px 0;
        }

        .validation-link {
            word-break: break-all;
            color: #1a73e8;
            text-decoration: none;
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
            @if (isset($details['schoolLogo']) && $details['schoolLogo'])
                <img src="{{ $details['schoolLogo'] }}" alt="{{ $details['schoolName'] ?? 'School' }} Logo">
            @else
                <h2>{{ $details['schoolName'] ?? 'Heritage Pro' }}</h2>
            @endif
        </div>

        <div class="email-header">
            <h1>{{ $details['subject'] }}</h1>
        </div>

        <div class="email-content">
            {!! strip_tags($details['body'], '<p><br><strong><b><em><i><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><span><div>') !!}
        </div>

        <hr class="divider">

        <div class="email-footer">
            <p class="sign-off">The {{ $details['schoolName'] ?? 'Heritage Pro' }} Team</p>
            <p>{{ $details['address'] ?? '1450 Example Road, City, State 12345' }}</p>
            <p>&copy; {{ date('Y') }} {{ $details['schoolName'] ?? 'Heritage Pro' }}. All rights reserved.</p>
            <p>For questions contact <a
                    href="mailto:{{ $details['supportEmail'] ?? 'support@heritagepro.com' }}">{{ $details['supportEmail'] ?? 'support@heritagepro.com' }}</a>
            </p>


            <div class="powered-by">Powered by</div>
            <div class="heritage-logo">
                <img src="{{ $details['heritageLogo'] ?? config('notifications.email.defaults.logo_url') }}"
                    alt="Heritage Pro">
            </div>
        </div>
    </div>
</body>

</html>
