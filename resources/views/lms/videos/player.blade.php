<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $video->contentItemMorph?->title ?? $video->original_filename }} - Video Player</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f0f0f;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .player-header {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            color: white;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }
        .player-title { font-weight: 600; font-size: 16px; }
        .player-subtitle { font-size: 12px; opacity: 0.9; }
        .player-controls { display: flex; align-items: center; gap: 16px; }
        .progress-info { font-size: 13px; opacity: 0.9; }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-exit { background: rgba(255, 255, 255, 0.2); color: white; }
        .btn-exit:hover { background: rgba(255, 255, 255, 0.3); }
        .video-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #000;
            position: relative;
        }
        .video-wrapper {
            width: 100%;
            max-width: 1280px;
            aspect-ratio: 16 / 9;
            position: relative;
        }
        video { width: 100%; height: 100%; background: #000; }
        .youtube-embed { width: 100%; height: 100%; border: none; }
        .quality-selector {
            position: absolute;
            bottom: 60px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 3px;
            padding: 8px 0;
            display: none;
        }
        .quality-selector.active { display: block; }
        .quality-option {
            padding: 8px 16px;
            color: white;
            cursor: pointer;
            font-size: 13px;
        }
        .quality-option:hover { background: rgba(255, 255, 255, 0.1); }
        .quality-option.active { color: #ec4899; }
        .transcoding-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
        }
        .transcoding-overlay i { font-size: 48px; margin-bottom: 16px; color: #ec4899; }
        .transcoding-overlay h4 { margin-bottom: 8px; }
        .transcoding-overlay p { opacity: 0.7; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="player-header">
        <div>
            <div class="player-title">{{ $video->contentItemMorph?->title ?? $video->original_filename }}</div>
            @if ($contentItem)
                <div class="player-subtitle">{{ $contentItem->module->course->title }}</div>
            @endif
        </div>
        <div class="player-controls">
            <div class="progress-info">
                @if ($progress)
                    <i class="fas fa-check-circle" style="color: #34d399;"></i>
                    {{ $progress->watch_percentage }}% watched
                @endif
            </div>
            <button class="btn btn-exit" id="exitBtn">Exit</button>
        </div>
    </div>

    <div class="video-container">
        <div class="video-wrapper">
            @if ($video->isYouTube())
                <iframe
                    class="youtube-embed"
                    src="{{ $video->getYouTubeEmbedUrl() }}?enablejsapi=1&rel=0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                ></iframe>
            @elseif ($video->isTranscoding())
                <div class="transcoding-overlay">
                    <i class="fas fa-cog fa-spin"></i>
                    <h4>Video Processing</h4>
                    <p>This video is being processed. Please check back later.</p>
                </div>
            @else
                <video id="videoPlayer" controls poster="{{ $video->thumbnail_path ? Storage::disk('public')->url($video->thumbnail_path) : '' }}">
                    @if ($video->qualities->count())
                        @foreach ($video->qualities->sortByDesc('height') as $quality)
                            <source src="{{ $quality->url }}" type="video/mp4" data-quality="{{ $quality->label }}">
                        @endforeach
                    @else
                        <source src="{{ Storage::disk('public')->url($video->file_path) }}" type="{{ $video->mime_type }}">
                    @endif
                    Your browser does not support the video tag.
                </video>

                @if ($video->qualities->count() > 1)
                    <div class="quality-selector" id="qualitySelector">
                        @foreach ($video->qualities->sortByDesc('height') as $quality)
                            <div class="quality-option" data-quality="{{ $quality->label }}" data-url="{{ $quality->url }}">
                                {{ $quality->label }}
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>

    <script>
        const CONFIG = {
            videoId: {{ $video->id }},
            progressUrl: '{{ route("lms.videos.progress", $video) }}',
            eventUrl: '{{ route("lms.videos.event", $video) }}',
            exitUrl: '{{ $contentItem ? route("lms.courses.learn", $contentItem->module->course) : route("lms.courses.index") }}',
            csrfToken: '{{ csrf_token() }}',
            lastPosition: {{ $progress?->last_position_seconds ?? 0 }},
        };

        const video = document.getElementById('videoPlayer');
        let progressInterval = null;
        let isResumed = false;

        if (video) {
            video.addEventListener('loadedmetadata', function() {
                if (CONFIG.lastPosition > 0 && !isResumed) {
                    video.currentTime = CONFIG.lastPosition;
                    isResumed = true;
                }
            });

            video.addEventListener('play', function() {
                sendEvent('play');
                progressInterval = setInterval(updateProgress, 10000);
            });

            video.addEventListener('pause', function() {
                sendEvent('pause');
                updateProgress();
                if (progressInterval) clearInterval(progressInterval);
            });

            video.addEventListener('ended', function() {
                sendEvent('complete');
                if (progressInterval) clearInterval(progressInterval);
            });

            video.addEventListener('seeked', function() {
                sendEvent('seek', { time: video.currentTime });
            });

            video.addEventListener('error', function() {
                sendEvent('error', { code: video.error?.code });
            });
        }

        async function updateProgress() {
            if (!video) return;
            try {
                await fetch(CONFIG.progressUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CONFIG.csrfToken
                    },
                    body: JSON.stringify({
                        current_time: video.currentTime,
                        duration: video.duration
                    })
                });
            } catch (error) {
                console.error('Failed to update progress:', error);
            }
        }

        async function sendEvent(event, metadata = {}) {
            try {
                await fetch(CONFIG.eventUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CONFIG.csrfToken
                    },
                    body: JSON.stringify({
                        event: event,
                        current_time: video?.currentTime,
                        metadata: metadata
                    })
                });
            } catch (error) {
                console.error('Failed to send event:', error);
            }
        }

        const qualitySelector = document.getElementById('qualitySelector');
        if (qualitySelector) {
            document.querySelectorAll('.quality-option').forEach(option => {
                option.addEventListener('click', function() {
                    const url = this.dataset.url;
                    const currentTime = video.currentTime;
                    const wasPlaying = !video.paused;

                    video.src = url;
                    video.currentTime = currentTime;
                    if (wasPlaying) video.play();

                    document.querySelectorAll('.quality-option').forEach(o => o.classList.remove('active'));
                    this.classList.add('active');
                    qualitySelector.classList.remove('active');
                });
            });
        }

        document.getElementById('exitBtn').addEventListener('click', function() {
            if (video) updateProgress();
            window.location.href = CONFIG.exitUrl;
        });

        window.addEventListener('beforeunload', function() {
            if (video && video.currentTime > 0) {
                navigator.sendBeacon(CONFIG.progressUrl, JSON.stringify({
                    current_time: video.currentTime,
                    duration: video.duration,
                    _token: CONFIG.csrfToken
                }));
            }
        });
    </script>
</body>
</html>
