<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Launching {{ $tool->name }}...</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            color: white;
        }
        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 24px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        h1 {
            font-size: 24px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        p {
            opacity: 0.8;
            font-size: 14px;
        }
        .manual-launch {
            margin-top: 32px;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
            animation-delay: 3s;
        }
        @keyframes fadeIn {
            to { opacity: 1; }
        }
        .btn {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
            border: 1px solid rgba(255,255,255,0.3);
            cursor: pointer;
        }
        .btn:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <h1>Launching {{ $tool->name }}</h1>
        <p>Please wait while we connect you to the external tool...</p>

        <div class="manual-launch">
            <p style="margin-bottom: 12px;">Taking too long?</p>
            <button type="submit" form="lti-launch-form" class="btn">
                Launch Manually
            </button>
        </div>
    </div>

    <form id="lti-launch-form" action="{{ $launchUrl }}" method="POST" style="display: none;">
        @foreach($params as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
    </form>

    <script>
        // Auto-submit the form after a short delay
        window.onload = function() {
            setTimeout(function() {
                document.getElementById('lti-launch-form').submit();
            }, 500);
        };
    </script>
</body>
</html>
