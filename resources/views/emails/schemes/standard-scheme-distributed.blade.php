@php
    $subjectName = $standardScheme->subject?->name ?? 'Subject';
    $gradeName = $standardScheme->grade?->name ?? 'Grade';
    $termLabel = 'Term ' . ($standardScheme->term?->term ?? '—') . ', ' . ($standardScheme->term?->year ?? '—');
    $schoolName = $schoolSetup?->school_name ?? config('notifications.email.defaults.school_name', config('app.name'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Scheme of Work Available</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f5f7fb;
            color: #1f2937;
            font-family: "Segoe UI", Arial, sans-serif;
            line-height: 1.55;
        }

        .container {
            max-width: 720px;
            margin: 0 auto;
            padding: 32px 20px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }

        .header {
            padding: 28px 28px 20px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }

        .header img {
            width: 72px;
            height: auto;
            margin-bottom: 14px;
        }

        .header h1 {
            margin: 0 0 6px;
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }

        .header p {
            margin: 0;
            font-size: 14px;
            color: #6b7280;
        }

        .content {
            padding: 24px 28px 32px;
        }

        .content p {
            margin: 0 0 14px;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin: 18px 0 24px;
        }

        .summary td {
            padding: 10px 0;
            border-bottom: 1px solid #eef2f7;
            vertical-align: top;
            font-size: 14px;
        }

        .summary td:first-child {
            width: 140px;
            color: #6b7280;
            font-weight: 600;
        }

        .scheme-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .scheme-list li {
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 14px 16px;
            margin-bottom: 12px;
            background: #f8fafc;
        }

        .scheme-list strong {
            display: block;
            margin-bottom: 6px;
            font-size: 15px;
            color: #111827;
        }

        .scheme-list a {
            color: #2563eb;
            text-decoration: none;
            word-break: break-word;
        }

        .footer {
            margin-top: 24px;
            font-size: 13px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <img src="{{ asset('assets/images/coat_of_arms.jpg') }}" alt="Botswana Coat of Arms">
                <h1>New Scheme of Work Available</h1>
                <p>{{ $schoolName }}</p>
            </div>

            <div class="content">
                <p>Dear {{ $recipient->full_name ?: 'Teacher' }},</p>
                <p>{{ $publisher->full_name ?: 'A staff member' }} has shared the standard scheme and new schemes of work have been created for your subject allocation.</p>

                <table class="summary" role="presentation">
                    <tr>
                        <td>Subject</td>
                        <td>{{ $subjectName }}</td>
                    </tr>
                    <tr>
                        <td>Grade</td>
                        <td>{{ $gradeName }}</td>
                    </tr>
                    <tr>
                        <td>Term</td>
                        <td>{{ $termLabel }}</td>
                    </tr>
                    <tr>
                        <td>Shared By</td>
                        <td>{{ $publisher->full_name ?: '—' }}</td>
                    </tr>
                </table>

                <p>The following schemes are now available in the app:</p>

                <ul class="scheme-list">
                    @foreach ($schemeItems as $item)
                        <li>
                            <strong>{{ $item['label'] }}</strong>
                            <a href="{{ $item['url'] }}">{{ $item['url'] }}</a>
                        </li>
                    @endforeach
                </ul>

                <div class="footer">
                    <p>{{ $schoolName }}</p>
                    @if ($schoolSetup?->email_address)
                        <p>For assistance contact {{ $schoolSetup->email_address }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
