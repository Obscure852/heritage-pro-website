<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Unavailable - Heritage Junior Secondary School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: #f3f4f6;
        }

        .expired-page {
            max-width: 480px;
            width: 100%;
            padding: 16px;
        }

        /* Branding */
        .branding {
            text-align: center;
            margin-bottom: 28px;
        }
        .branding .logo {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            color: white;
            font-size: 24px;
        }
        .branding .school-name {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }

        /* Card */
        .expired-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.08);
            padding: 44px 36px;
            text-align: center;
        }

        .expired-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #fef2f2;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .expired-icon i {
            font-size: 32px;
            color: #ef4444;
        }

        .expired-card h1 {
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .expired-card .message {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .portal-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 24px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .portal-btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        /* Footer */
        .expired-footer {
            text-align: center;
            margin-top: 24px;
            color: #9ca3af;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="expired-page">
        {{-- School Branding --}}
        <div class="branding">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="school-name">Heritage Junior Secondary School</div>
        </div>

        {{-- Expired Card --}}
        <div class="expired-card">
            <div class="expired-icon">
                <i class="fas fa-link-slash"></i>
            </div>

            <h1>This Link is No Longer Available</h1>

            <p class="message">
                This link has expired, been disabled, or has reached its maximum number of views.
                Please contact the document owner for a new link.
            </p>

            <a href="{{ route('documents.public.portal') }}" class="portal-btn">
                <i class="fas fa-folder-open"></i>
                Browse Public Documents
            </a>
        </div>

        <div class="expired-footer">
            Heritage Junior Secondary School &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
