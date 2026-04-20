<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $package->title }} - SCORM Player</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }

        .player-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .player-title {
            font-weight: 600;
            font-size: 16px;
        }

        .course-name {
            font-size: 13px;
            opacity: 0.9;
        }

        .player-controls {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .player-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            opacity: 0.9;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fbbf24;
        }

        .status-indicator.connected {
            background: #34d399;
        }

        .status-indicator.error {
            background: #f87171;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-exit {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .btn-exit:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .content-frame {
            flex: 1;
            border: none;
            background: white;
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
            border: 4px solid rgba(78, 115, 223, 0.3);
            border-top-color: #4e73df;
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

        .error-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(31, 41, 55, 0.95);
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .error-icon {
            font-size: 48px;
            color: #f87171;
            margin-bottom: 16px;
        }

        .error-message {
            font-size: 18px;
            margin-bottom: 8px;
        }

        .error-details {
            font-size: 14px;
            opacity: 0.7;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
        <div class="loading-text">Loading SCORM content...</div>
    </div>

    <div class="error-overlay" id="errorOverlay">
        <div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="error-message" id="errorMessage">An error occurred</div>
        <div class="error-details" id="errorDetails"></div>
        <a href="{{ route('student.lms.learn', $course) }}" class="btn btn-exit">
            <i class="fas fa-arrow-left"></i> Return to Course
        </a>
    </div>

    <div class="player-header">
        <div class="player-info">
            <div>
                <div class="player-title">{{ $package->title }}</div>
                <div class="course-name">{{ $course->title }}</div>
            </div>
        </div>
        <div class="player-controls">
            <div class="player-status">
                <div class="status-indicator" id="statusIndicator"></div>
                <span id="statusText">Connecting...</span>
            </div>
            <a href="{{ route('student.lms.learn', $course) }}" class="btn btn-exit" id="exitBtn">
                <i class="fas fa-times"></i> Exit
            </a>
        </div>
    </div>

    <iframe
        id="contentFrame"
        class="content-frame"
        sandbox="allow-scripts allow-same-origin allow-forms allow-popups"
    ></iframe>

    <script>
        // SCORM Configuration
        const SCORM_CONFIG = {
            attemptId: {{ $attempt->id }},
            version: '{{ $package->version }}',
            isScorm12: {{ $package->is_scorm_12 ? 'true' : 'false' }},
            isPreview: false,
            launchUrl: '{{ Storage::disk('public')->url($package->extracted_path . '/' . $package->launch_url) }}',
            apiEndpoints: {
                initialize: '{{ route('lms.scorm.api.initialize', $attempt) }}',
                getValue: '{{ route('lms.scorm.api.getValue', $attempt) }}',
                setValue: '{{ route('lms.scorm.api.setValue', $attempt) }}',
                commit: '{{ route('lms.scorm.api.commit', $attempt) }}',
                terminate: '{{ route('lms.scorm.api.terminate', $attempt) }}',
                batch: '{{ route('lms.scorm.api.batch', $attempt) }}'
            },
            exitUrl: '{{ route('student.lms.learn', $course) }}',
            csrfToken: '{{ csrf_token() }}'
        };

        // SCORM Runtime API
        class ScormAPI {
            constructor(config) {
                this.config = config;
                this.initialized = false;
                this.terminated = false;
                this.cmiData = {};
                this.pendingUpdates = {};
                this.lastError = '0';
                this.commitInterval = null;
            }

            // Common API methods
            async Initialize(param) {
                if (this.initialized) {
                    this.lastError = '101';
                    return 'false';
                }

                try {
                    const response = await this.apiCall('initialize', {});
                    if (response.success) {
                        this.initialized = true;
                        this.cmiData = response.data || {};
                        this.lastError = '0';
                        this.startCommitInterval();
                        this.updateStatus('connected', 'Connected');
                        return 'true';
                    }
                } catch (error) {
                    console.error('SCORM Initialize error:', error);
                    this.lastError = '101';
                }

                return 'false';
            }

            Terminate(param) {
                if (!this.initialized || this.terminated) {
                    this.lastError = '112';
                    return 'false';
                }

                this.stopCommitInterval();
                this.flushPendingUpdates();

                this.apiCall('terminate', {}).then(() => {
                    this.terminated = true;
                    this.updateStatus('', 'Completed');
                }).catch(console.error);

                this.lastError = '0';
                return 'true';
            }

            GetValue(element) {
                if (!this.initialized) {
                    this.lastError = '122';
                    return '';
                }

                if (this.cmiData.hasOwnProperty(element)) {
                    this.lastError = '0';
                    return this.cmiData[element] ?? '';
                }

                this.lastError = '0';
                return '';
            }

            SetValue(element, value) {
                if (!this.initialized) {
                    this.lastError = '132';
                    return 'false';
                }

                this.cmiData[element] = value;
                this.pendingUpdates[element] = value;
                this.lastError = '0';

                return 'true';
            }

            Commit(param) {
                if (!this.initialized) {
                    this.lastError = '142';
                    return 'false';
                }

                this.flushPendingUpdates();
                this.lastError = '0';
                return 'true';
            }

            GetLastError() {
                return this.lastError;
            }

            GetErrorString(errorCode) {
                const errors = {
                    '0': 'No Error',
                    '101': 'Already Initialized',
                    '102': 'Content Instance Terminated',
                    '103': 'Already Terminated',
                    '112': 'Termination Failed',
                    '122': 'Get Value Failed',
                    '132': 'Set Value Failed',
                    '142': 'Commit Failed',
                    '201': 'General Argument Error',
                    '301': 'Not Initialized',
                    '401': 'Unknown Data Model Element',
                    '402': 'Data Model Element Value Not Initialized',
                    '403': 'Data Model Element Is Read Only',
                    '404': 'Data Model Element Is Write Only',
                    '405': 'Data Model Element Type Mismatch'
                };
                return errors[errorCode] || 'Unknown Error';
            }

            GetDiagnostic(errorCode) {
                return this.GetErrorString(errorCode);
            }

            // SCORM 1.2 specific methods
            LMSInitialize(param) { return this.Initialize(param); }
            LMSFinish(param) { return this.Terminate(param); }
            LMSGetValue(element) { return this.GetValue(element); }
            LMSSetValue(element, value) { return this.SetValue(element, value); }
            LMSCommit(param) { return this.Commit(param); }
            LMSGetLastError() { return this.GetLastError(); }
            LMSGetErrorString(errorCode) { return this.GetErrorString(errorCode); }
            LMSGetDiagnostic(errorCode) { return this.GetDiagnostic(errorCode); }

            // Helper methods
            async apiCall(endpoint, data) {
                const response = await fetch(this.config.apiEndpoints[endpoint], {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken
                    },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    throw new Error(`API call failed: ${response.status}`);
                }

                return response.json();
            }

            flushPendingUpdates() {
                if (Object.keys(this.pendingUpdates).length === 0) return;

                const updates = { ...this.pendingUpdates };
                this.pendingUpdates = {};

                this.apiCall('batch', { data: updates }).catch(error => {
                    console.error('Failed to flush updates:', error);
                    Object.assign(this.pendingUpdates, updates);
                });
            }

            startCommitInterval() {
                this.commitInterval = setInterval(() => {
                    this.flushPendingUpdates();
                }, 30000);
            }

            stopCommitInterval() {
                if (this.commitInterval) {
                    clearInterval(this.commitInterval);
                    this.commitInterval = null;
                }
            }

            updateStatus(className, text) {
                const indicator = document.getElementById('statusIndicator');
                const statusText = document.getElementById('statusText');
                if (indicator) indicator.className = `status-indicator ${className}`;
                if (statusText) statusText.textContent = text;
            }
        }

        // Initialize
        const scormAPI = new ScormAPI(SCORM_CONFIG);

        // Expose API globally based on SCORM version
        if (SCORM_CONFIG.isScorm12) {
            window.API = scormAPI;
        } else {
            window.API_1484_11 = scormAPI;
        }

        // Load content
        document.addEventListener('DOMContentLoaded', function() {
            const frame = document.getElementById('contentFrame');
            const loading = document.getElementById('loadingOverlay');

            frame.onload = function() {
                loading.style.display = 'none';
            };

            frame.onerror = function() {
                showError('Failed to load content', 'The SCORM content could not be loaded.');
            };

            frame.src = SCORM_CONFIG.launchUrl;
        });

        // Handle unload
        window.addEventListener('beforeunload', function(e) {
            if (scormAPI.initialized && !scormAPI.terminated) {
                scormAPI.flushPendingUpdates();
                navigator.sendBeacon && navigator.sendBeacon(
                    SCORM_CONFIG.apiEndpoints.terminate,
                    new Blob([JSON.stringify({ _token: SCORM_CONFIG.csrfToken })], { type: 'application/json' })
                );
            }
        });

        function showError(message, details) {
            document.getElementById('loadingOverlay').style.display = 'none';
            document.getElementById('errorOverlay').style.display = 'flex';
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorDetails').textContent = details || '';
        }
    </script>
</body>
</html>
