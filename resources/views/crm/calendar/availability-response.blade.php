<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Availability Recorded | {{ config('app.name', 'Heritage Pro') }}</title>
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700,800" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: linear-gradient(180deg, #eff6ff 0%, #f8fafc 100%);
            color: #0f172a;
            font-family: Nunito, Arial, sans-serif;
        }

        .response-card {
            width: min(100%, 560px);
            background: #ffffff;
            border: 1px solid #dbe5f0;
            border-radius: 6px;
            padding: 28px;
        }

        .response-kicker {
            margin: 0 0 10px;
            color: #2563eb;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 28px;
            line-height: 1.15;
        }

        p {
            margin: 0 0 16px;
            color: #475569;
            font-size: 15px;
            line-height: 1.6;
        }

        .status {
            display: inline-flex;
            margin: 8px 0 20px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(37, 99, 235, .1);
            color: #1d4ed8;
            font-size: 14px;
            font-weight: 800;
        }

        .meta {
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <main class="response-card">
        <p class="response-kicker">Availability Recorded</p>
        <h1>{{ $event?->title ?: 'Calendar event' }}</h1>
        <p>Thanks {{ $attendee->display_name ?: 'there' }}. Your response has been recorded.</p>
        <span class="status">{{ $responseLabel }}</span>
        <p class="meta">
            @if ($event?->all_day)
                {{ $event->starts_at?->format('M d, Y') }} (all day)
            @else
                {{ $event?->starts_at?->format('M d, Y H:i') }} - {{ $event?->ends_at?->format('H:i') }}
            @endif
        </p>
    </main>
</body>
</html>
