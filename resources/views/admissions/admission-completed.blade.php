<!DOCTYPE html>
<html>

<head>
    <title>Admission Completed</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .success-container {
            background: white;
            border-radius: 3px;
            padding: 48px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .success-icon i {
            font-size: 40px;
            color: white;
        }

        .school-logo {
            width: 100px;
            height: auto;
            margin-bottom: 24px;
        }

        .success-title {
            font-size: 24px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
        }

        .success-message {
            font-size: 16px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .contact-info {
            background: #f9fafb;
            border-radius: 3px;
            padding: 24px;
            border-left: 4px solid #3b82f6;
        }

        .contact-info p {
            margin: 0 0 8px 0;
            color: #374151;
            font-size: 14px;
        }

        .contact-info p:last-child {
            margin-bottom: 0;
        }

        .contact-info strong {
            color: #1f2937;
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .success-container {
                padding: 32px 24px;
                margin: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="container" style="min-height: 100vh; display: flex; justify-content: center; align-items: center;">
        <div class="success-container">
            <img src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo" class="school-logo">

            <div class="success-icon">
                <i class="bx bx-check"></i>
            </div>

            <h1 class="success-title">Thank You, {{ $admission->full_name }}</h1>
            <p class="success-message">
                Your application has been received successfully. Our admissions team will review your application and get in touch with you soon.
            </p>

            <div class="contact-info">
                <p><strong>{{ $school_data->school_name ?? '' }}</strong></p>
                <p>Email: {{ $school_data->email_address ?? '' }}</p>
                <p>Phone: {{ $school_data->telephone ?? '' }}</p>
            </div>
        </div>
    </div>
</body>

</html>
