<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $package->title }} - SCORM Player</title>
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
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
        }

        .btn-exit {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .btn-exit:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .preview-banner {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 10px 20px;
            text-align: center;
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .preview-banner i {
            font-size: 16px;
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
            border: 4px solid rgba(139, 92, 246, 0.3);
            border-top-color: #8b5cf6;
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
        <div class="error-icon">!</div>
        <div class="error-message" id="errorMessage">An error occurred</div>
        <div class="error-details" id="errorDetails"></div>
        <button class="btn btn-exit" onclick="window.location.href='{{ isset($preview) && $preview ? route('lms.scorm.show', $package) : ($content && $content->module && $content->module->course ? route('student.lms.learn', $content->module->course) : route('lms.scorm.index')) }}'">
            Return to Course
        </button>
    </div>

    @if(isset($preview) && $preview)
        <div class="preview-banner">
            <i class="fas fa-eye"></i>
            Preview Mode - Progress will not be saved
        </div>
    @endif

    <div class="player-header">
        <div class="player-title">{{ $package->title }}</div>
        <div class="player-controls">
            @if(!isset($preview) || !$preview)
                <div class="player-status">
                    <div class="status-indicator" id="statusIndicator"></div>
                    <span id="statusText">Connecting...</span>
                </div>
            @else
                <div class="player-status">
                    <div class="status-indicator" style="background: #f59e0b;"></div>
                    <span>Preview Mode</span>
                </div>
            @endif
            <button class="btn btn-exit" id="exitBtn">Exit</button>
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
            attemptId: {{ $attempt ? $attempt->id : 'null' }},
            version: '{{ $package->version }}',
            isScorm12: {{ $package->is_scorm_12 ? 'true' : 'false' }},
            isPreview: {{ (isset($preview) && $preview) ? 'true' : 'false' }},
            launchUrl: '{{ Storage::disk('public')->url($package->extracted_path . '/' . $package->launch_url) }}',
            @if($attempt)
            apiEndpoints: {
                initialize: '{{ route('lms.scorm.api.initialize', $attempt) }}',
                getValue: '{{ route('lms.scorm.api.getValue', $attempt) }}',
                setValue: '{{ route('lms.scorm.api.setValue', $attempt) }}',
                commit: '{{ route('lms.scorm.api.commit', $attempt) }}',
                terminate: '{{ route('lms.scorm.api.terminate', $attempt) }}',
                batch: '{{ route('lms.scorm.api.batch', $attempt) }}'
            },
            @else
            apiEndpoints: null,
            @endif
            exitUrl: '{{ isset($preview) && $preview ? route('lms.scorm.show', $package) : ($content && $content->module && $content->module->course ? route('student.lms.learn', $content->module->course) : route('lms.scorm.index')) }}',
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
                    this.lastError = '101'; // Already initialized
                    return 'false';
                }

                try {
                    const response = await this.apiCall('initialize', {});
                    if (response.success) {
                        this.initialized = true;
                        this.cmiData = response.data || {};
                        this.lastError = '0';

                        // Start periodic commit
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
                    this.lastError = '112'; // Not initialized or already terminated
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

                // Return from cache if available
                if (this.cmiData.hasOwnProperty(element)) {
                    this.lastError = '0';
                    return this.cmiData[element] ?? '';
                }

                // For elements not in cache, return empty string (SCORM allows this)
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
                // In preview mode, return mock responses without calling backend
                if (this.config.isPreview || !this.config.apiEndpoints) {
                    return this.mockApiResponse(endpoint, data);
                }

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

            mockApiResponse(endpoint, data) {
                // Return mock responses for preview mode
                switch (endpoint) {
                    case 'initialize':
                        return { success: true, data: {}, version: this.config.version };
                    case 'getValue':
                        return { success: true, value: this.cmiData[data.element] || '' };
                    case 'setValue':
                    case 'commit':
                    case 'terminate':
                    case 'batch':
                        return { success: true };
                    default:
                        return { success: true };
                }
            }

            flushPendingUpdates() {
                if (Object.keys(this.pendingUpdates).length === 0) return;

                const updates = { ...this.pendingUpdates };
                this.pendingUpdates = {};

                this.apiCall('batch', { data: updates }).catch(error => {
                    console.error('Failed to flush updates:', error);
                    // Restore pending updates on failure
                    this.pendingUpdates = { ...updates, ...this.pendingUpdates };
                });
            }

            startCommitInterval() {
                // Commit every 30 seconds
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

            updateStatus(state, text) {
                const indicator = document.getElementById('statusIndicator');
                const statusText = document.getElementById('statusText');

                indicator.className = 'status-indicator';
                if (state) {
                    indicator.classList.add(state);
                }
                statusText.textContent = text;
            }
        }

        // Initialize the API
        const scormAPI = new ScormAPI(SCORM_CONFIG);

        // Expose to iframe content (both SCORM 1.2 and 2004 naming conventions)
        if (SCORM_CONFIG.isScorm12) {
            // SCORM 1.2 API
            window.API = {
                LMSInitialize: (p) => scormAPI.LMSInitialize(p),
                LMSFinish: (p) => scormAPI.LMSFinish(p),
                LMSGetValue: (e) => scormAPI.LMSGetValue(e),
                LMSSetValue: (e, v) => scormAPI.LMSSetValue(e, v),
                LMSCommit: (p) => scormAPI.LMSCommit(p),
                LMSGetLastError: () => scormAPI.LMSGetLastError(),
                LMSGetErrorString: (e) => scormAPI.LMSGetErrorString(e),
                LMSGetDiagnostic: (e) => scormAPI.LMSGetDiagnostic(e)
            };
        } else {
            // SCORM 2004 API
            window.API_1484_11 = {
                Initialize: (p) => scormAPI.Initialize(p),
                Terminate: (p) => scormAPI.Terminate(p),
                GetValue: (e) => scormAPI.GetValue(e),
                SetValue: (e, v) => scormAPI.SetValue(e, v),
                Commit: (p) => scormAPI.Commit(p),
                GetLastError: () => scormAPI.GetLastError(),
                GetErrorString: (e) => scormAPI.GetErrorString(e),
                GetDiagnostic: (e) => scormAPI.GetDiagnostic(e)
            };
        }

        // Load content
        document.addEventListener('DOMContentLoaded', function() {
            const frame = document.getElementById('contentFrame');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const errorOverlay = document.getElementById('errorOverlay');

            frame.onload = function() {
                loadingOverlay.style.display = 'none';
            };

            frame.onerror = function() {
                showError('Failed to load content', 'The SCORM package could not be loaded.');
            };

            // Load the SCORM content
            try {
                frame.src = SCORM_CONFIG.launchUrl;
            } catch (error) {
                showError('Launch Error', error.message);
            }
        });

        function showError(message, details) {
            document.getElementById('loadingOverlay').style.display = 'none';
            document.getElementById('errorOverlay').style.display = 'flex';
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorDetails').textContent = details || '';
        }

        // Exit button handler
        document.getElementById('exitBtn').addEventListener('click', function() {
            if (scormAPI.initialized && !scormAPI.terminated) {
                scormAPI.Terminate('');
            }
            window.location.href = SCORM_CONFIG.exitUrl;
        });

        // Handle page unload
        window.addEventListener('beforeunload', function() {
            if (scormAPI.initialized && !scormAPI.terminated && SCORM_CONFIG.apiEndpoints) {
                scormAPI.flushPendingUpdates();
                // Use synchronous call for unload
                navigator.sendBeacon(
                    SCORM_CONFIG.apiEndpoints.terminate,
                    JSON.stringify({ _token: SCORM_CONFIG.csrfToken })
                );
            }
        });
    </script>
</body>
</html>
