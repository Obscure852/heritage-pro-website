<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Required - Heritage Junior Secondary School</title>
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

        .password-page {
            max-width: 440px;
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
        .password-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.08);
            padding: 36px;
        }

        .lock-icon {
            text-align: center;
            margin-bottom: 20px;
        }
        .lock-icon i {
            font-size: 36px;
            color: #d1d5db;
        }

        .password-card h1 {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            text-align: center;
            margin-bottom: 6px;
        }

        .password-card .subtitle {
            color: #6b7280;
            font-size: 14px;
            text-align: center;
            margin-bottom: 6px;
        }

        .doc-title-preview {
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            color: #4e73df;
            margin-bottom: 24px;
            padding: 8px 12px;
            background: #f0f4ff;
            border-radius: 6px;
        }

        /* Form */
        .password-card label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .password-card input[type="password"] {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .password-card input[type="password"]:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-verify {
            display: block;
            width: 100%;
            padding: 11px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 16px;
            transition: all 0.2s;
        }
        .btn-verify:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* Error */
        .error-box {
            background: #fef2f2;
            color: #dc2626;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            border: 1px solid #fecaca;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Lockout */
        .lockout-box {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
        }
        .lockout-box .lockout-icon {
            font-size: 32px;
            color: #f59e0b;
            margin-bottom: 12px;
        }
        .lockout-box .lockout-title {
            font-size: 16px;
            font-weight: 600;
            color: #92400e;
            margin-bottom: 8px;
        }
        .lockout-box .lockout-message {
            font-size: 13px;
            color: #b45309;
            margin-bottom: 16px;
        }
        .lockout-box .countdown {
            font-size: 28px;
            font-weight: 700;
            color: #d97706;
            font-variant-numeric: tabular-nums;
        }
        .lockout-box .countdown-label {
            font-size: 12px;
            color: #b45309;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="password-page">
        {{-- School Branding --}}
        <div class="branding">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="school-name">Heritage Junior Secondary School</div>
        </div>

        {{-- Password Card --}}
        <div class="password-card">
            <div class="lock-icon">
                <i class="fas fa-lock"></i>
            </div>

            <h1>Password Required</h1>
            <p class="subtitle">This document is password protected.</p>

            @if(isset($document) && $document)
                <div class="doc-title-preview">
                    <i class="fas fa-file me-1"></i> {{ $document->title }}
                </div>
            @endif

            @if($locked ?? false)
                {{-- Lockout State --}}
                <div class="lockout-box">
                    <div class="lockout-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="lockout-title">Too Many Attempts</div>
                    <div class="lockout-message">
                        {{ $error ?? 'Too many failed attempts. Please try again later.' }}
                    </div>
                    <div class="countdown" id="countdown">--:--</div>
                    <div class="countdown-label">Time remaining</div>
                </div>
            @else
                {{-- Error Message --}}
                @if(isset($error))
                    <div class="error-box">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $error }}
                    </div>
                @endif

                {{-- Password Form --}}
                <form method="POST" action="{{ route('documents.public.password.verify', ['token' => $token]) }}">
                    @csrf
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autofocus placeholder="Enter document password">
                    <button type="submit" class="btn-verify">
                        <i class="fas fa-unlock me-1"></i> Verify Password
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($locked ?? false)
    <script>
        (function() {
            var expiry = {{ $lockoutExpiry ?? 'null' }};
            if (expiry) {
                var countdownEl = document.getElementById('countdown');
                var interval = setInterval(function() {
                    var remaining = Math.max(0, Math.ceil((expiry * 1000 - Date.now()) / 1000));
                    if (remaining <= 0) {
                        clearInterval(interval);
                        location.reload();
                        return;
                    }
                    var mins = Math.floor(remaining / 60);
                    var secs = remaining % 60;
                    countdownEl.textContent = mins + ':' + secs.toString().padStart(2, '0');
                }, 1000);
            }
        })();
    </script>
    @endif
</body>
</html>
