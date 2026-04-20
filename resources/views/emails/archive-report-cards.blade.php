<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Report Cards</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 200px;
            height: auto;
        }

        .content {
            margin-bottom: 30px;
        }

        .signature {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="{{ $schoolLogo }}" alt="School Logo">
        </div>

        <div class="content">
            <h2>Archived Report Cards</h2>

            {!! $messageContent !!}

            <p>Please find the archived report cards attached to this email.</p>
        </div>

        <div class="signature">
            <p>Best regards,</p>
            <p>{{ $schoolName ?? 'School Administration' }}</p>
            <p>{{ $schoolAddress ?? '' }}</p>
            <p>{{ $schoolContact ?? '' }}</p>
        </div>
    </div>
</body>

</html>
