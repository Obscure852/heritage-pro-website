<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $content->title }} - H5P Player</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #1f2937;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .player-header {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }

        .player-title {
            font-weight: 600;
            font-size: 16px;
        }

        .player-subtitle {
            font-size: 12px;
            opacity: 0.9;
        }

        .player-controls {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-exit {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .btn-exit:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .content-wrapper {
            flex: 1;
            background: white;
            overflow: auto;
            display: flex;
            flex-direction: column;
        }

        .h5p-container {
            flex: 1;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .h5p-content {
            max-width: 1200px;
            width: 100%;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(31, 41, 55, 0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .loading-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid rgba(6, 182, 212, 0.3);
            border-top-color: #06b6d4;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            color: white;
            margin-top: 16px;
            font-size: 14px;
        }

        .error-message {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 16px;
            border-radius: 3px;
            text-align: center;
            margin: 20px;
        }

        /* H5P iframe styling */
        .h5p-iframe-wrapper {
            width: 100%;
            max-width: 1200px;
        }

        .h5p-iframe-wrapper iframe {
            width: 100%;
            min-height: 600px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
        <div class="loading-text">Loading H5P content...</div>
    </div>

    <div class="player-header">
        <div>
            <div class="player-title">{{ $content->title }}</div>
            <div class="player-subtitle">{{ $content->library_display_name }}</div>
        </div>
        <div class="player-controls">
            <button class="btn btn-exit" id="exitBtn">Exit</button>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="h5p-container">
            <div class="h5p-content" id="h5pContent">
                @if ($content->content_path)
                    <div class="h5p-iframe-wrapper">
                        <iframe
                            id="h5pFrame"
                            src="{{ Storage::disk('public')->url($content->content_path . '/content/index.html') }}"
                            frameborder="0"
                            allowfullscreen
                        ></iframe>
                    </div>
                @else
                    <div class="error-message">
                        <strong>Content Not Found</strong><br>
                        The H5P content files could not be loaded.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        const H5P_CONFIG = {
            contentId: {{ $content->id }},
            resultId: {{ $result->id }},
            xapiEndpoint: '{{ route('lms.h5p.xapi', $content) }}',
            exitUrl: '{{ $item ? route('lms.courses.learn', $item->module->course) : route('lms.courses.index') }}',
            csrfToken: '{{ csrf_token() }}'
        };

        // Handle iframe load
        document.addEventListener('DOMContentLoaded', function() {
            const frame = document.getElementById('h5pFrame');
            const loadingOverlay = document.getElementById('loadingOverlay');

            if (frame) {
                frame.onload = function() {
                    loadingOverlay.style.display = 'none';
                };

                frame.onerror = function() {
                    loadingOverlay.style.display = 'none';
                    document.getElementById('h5pContent').innerHTML =
                        '<div class="error-message"><strong>Error</strong><br>Failed to load H5P content.</div>';
                };
            } else {
                loadingOverlay.style.display = 'none';
            }
        });

        // Exit button handler
        document.getElementById('exitBtn').addEventListener('click', function() {
            window.location.href = H5P_CONFIG.exitUrl;
        });

        // Listen for xAPI events from H5P content
        window.addEventListener('message', function(event) {
            // Handle H5P xAPI messages
            if (event.data && event.data.context === 'h5p') {
                const statement = event.data.statement;
                if (statement) {
                    sendXapiEvent(statement);
                }
            }
        });

        async function sendXapiEvent(statement) {
            try {
                const verb = statement.verb?.id?.split('/').pop() || 'unknown';
                const result = statement.result || {};

                await fetch(H5P_CONFIG.xapiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': H5P_CONFIG.csrfToken
                    },
                    body: JSON.stringify({
                        verb: verb,
                        object_type: statement.object?.definition?.type,
                        object_id: statement.object?.id,
                        result: {
                            score: result.score,
                            success: result.success,
                            completion: result.completion,
                            response: result.response,
                            duration: result.duration
                        },
                        context: statement.context
                    })
                });
            } catch (error) {
                console.error('Failed to send xAPI event:', error);
            }
        }

        // Handle page visibility change (track time)
        let startTime = Date.now();

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // User left the page - could track time spent
                const timeSpent = Math.floor((Date.now() - startTime) / 1000);
                console.log('Time spent on content:', timeSpent, 'seconds');
            } else {
                startTime = Date.now();
            }
        });

        // Resize handler for responsive iframe
        function resizeH5PFrame() {
            const frame = document.getElementById('h5pFrame');
            if (frame) {
                const container = document.querySelector('.content-wrapper');
                const headerHeight = document.querySelector('.player-header').offsetHeight;
                const availableHeight = window.innerHeight - headerHeight - 40; // 40px padding
                frame.style.minHeight = availableHeight + 'px';
            }
        }

        window.addEventListener('resize', resizeH5PFrame);
        resizeH5PFrame();
    </script>
</body>
</html>
