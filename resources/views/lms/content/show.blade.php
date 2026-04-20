@extends('layouts.master')

@section('title')
    {{ $content->title }}
@endsection

@section('css')
    <style>
        @php $typeColors =[ 'video_youtube'=>['from'=>'#dc2626', 'to'=>'#b91c1c'],
        'video_upload'=>['from'=>'#dc2626', 'to'=>'#b91c1c'],
        'document'=>['from'=>'#2563eb', 'to'=>'#1d4ed8'],
        'quiz'=>['from'=>'#f59e0b', 'to'=>'#d97706'],
        'text'=>['from'=>'#10b981', 'to'=>'#059669'],
        'audio'=>['from'=>'#8b5cf6', 'to'=>'#7c3aed'],
        'image'=>['from'=>'#ec4899', 'to'=>'#db2777'],
        'external_link'=>['from'=>'#6366f1', 'to'=>'#4f46e5'],
        ];
        $colors =$typeColors[$content->type] ?? ['from'=>'#6b7280',
        'to'=>'#4b5563'];
        @endphp

        .content-header {
            background: linear-gradient(135deg, {{ $colors['from'] }} 0%, {{ $colors['to'] }} 100%);
            color: white;
            padding: 32px;
            border-radius: 3px;
            margin-bottom: 24px;
        }

        .content-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .content-meta {
            display: flex;
            gap: 24px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .content-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            opacity: 0.9;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .card-header {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
            border-radius: 3px 3px 0 0 !important;
        }

        .card-title {
            color: #1f2937;
            font-weight: 600;
            font-size: 16px;
            margin: 0;
        }

        .card-body {
            padding: 20px;
        }

        .description-content {
            color: #374151;
            line-height: 1.8;
        }

        .preview-info {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .preview-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: #f9fafb;
            border-radius: 3px;
        }

        .preview-item-icon {
            width: 48px;
            height: 48px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: {{ $colors['from'] }};
            color: white;
        }

        .preview-item-info h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #1f2937;
        }

        .preview-item-info p {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
        }

        .video-thumbnail {
            position: relative;
            background: #1f2937;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .video-thumbnail img {
            width: 100%;
            display: block;
        }

        .video-thumbnail .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 72px;
            height: 72px;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
        }

        .sidebar-card {
            border-left: 4px solid {{ $colors['from'] }};
        }

        .progress-section {
            text-align: center;
            padding: 20px;
            background: #f9fafb;
            border-radius: 3px;
            margin-bottom: 16px;
        }

        .progress-status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .progress-status.not-started {
            color: #6b7280;
        }

        .progress-status.in-progress {
            color: #f59e0b;
        }

        .progress-status.completed {
            color: #10b981;
        }

        .btn {
            padding: 12px 20px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, {{ $colors['from'] }} 0%, {{ $colors['to'] }} 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .btn-outline-secondary {
            border: 1px solid #d1d5db;
            color: #374151;
            background: white;
        }

        .btn-outline-secondary:hover {
            background: #f9fafb;
            color: #1f2937;
        }

        .required-badge {
            background: #fef3c7;
            color: #92400e;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .file-download {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 3px;
            border: 1px dashed #d1d5db;
        }

        .file-download-icon {
            width: 56px;
            height: 56px;
            background: #e5e7eb;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #6b7280;
        }

        .file-download-info h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #1f2937;
        }

        .file-download-info p {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
        }

        .text-preview {
            max-height: 200px;
            overflow: hidden;
            position: relative;
        }

        .text-preview::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: linear-gradient(transparent, white);
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('title')
            {{ $content->title }}
        @endslot
    @endcomponent

    <div class="content-header">
        <span class="content-type-badge">
            @switch($content->type)
                @case('video_youtube')
                @case('video_upload')
                    <i class="fas fa-play-circle"></i> Video
                @break

                @case('document')
                    <i class="fas fa-file-alt"></i> Document
                @break

                @case('quiz')
                    <i class="fas fa-question-circle"></i> Quiz
                @break

                @case('text')
                    <i class="fas fa-align-left"></i> Text Content
                @break

                @case('audio')
                    <i class="fas fa-headphones"></i> Audio
                @break

                @case('image')
                    <i class="fas fa-image"></i> Image
                @break

                @case('external_link')
                    <i class="fas fa-external-link-alt"></i> External Link
                @break

                @default
                    <i class="fas fa-file"></i> Content
            @endswitch
        </span>

        <h2 style="margin: 0 0 8px 0;">{{ $content->title }}</h2>

        @if ($content->module)
            <p style="margin: 0; opacity: 0.9;">{{ $content->module->title }} &bull; {{ $content->module->course->title }}
            </p>
        @endif

        <div class="content-meta">
            @if ($content->estimated_duration)
                <div class="content-meta-item">
                    <i class="fas fa-clock"></i>
                    {{ $content->estimated_duration }} min
                </div>
            @endif
            @if ($content->is_required)
                <div class="content-meta-item">
                    <i class="fas fa-asterisk"></i>
                    Required
                </div>
            @endif
            @if ($content->type === 'quiz' && $content->contentable)
                <div class="content-meta-item">
                    <i class="fas fa-question-circle"></i>
                    {{ $content->contentable->questions()->count() }} questions
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            @if ($content->description)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Description</h5>
                    </div>
                    <div class="card-body">
                        <div class="description-content">
                            {!! nl2br(e($content->description)) !!}
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-eye me-2"></i>Preview</h5>
                </div>
                <div class="card-body">
                    @switch($content->type)
                        @case('video_youtube')
                            @if ($content->contentable && $content->contentable->source_id)
                                <div class="video-thumbnail">
                                    <img src="https://img.youtube.com/vi/{{ $content->contentable->source_id }}/maxresdefault.jpg"
                                        alt="{{ $content->title }}"
                                        onerror="this.src='https://img.youtube.com/vi/{{ $content->contentable->source_id }}/hqdefault.jpg'">
                                    <div class="play-overlay">
                                        <i class="fas fa-play"></i>
                                    </div>
                                </div>
                                <div class="preview-item">
                                    <div class="preview-item-icon">
                                        <i class="fab fa-youtube"></i>
                                    </div>
                                    <div class="preview-item-info">
                                        <h6>YouTube Video</h6>
                                        <p>Watch this video to learn the content</p>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">Video not configured</p>
                            @endif
                        @break

                        @case('video_upload')
                            <div class="preview-item">
                                <div class="preview-item-icon">
                                    <i class="fas fa-video"></i>
                                </div>
                                <div class="preview-item-info">
                                    <h6>Uploaded Video</h6>
                                    <p>{{ $content->contentable->original_filename ?? 'Video file' }}</p>
                                </div>
                            </div>
                        @break

                        @case('document')
                            @if ($content->contentable)
                                <div class="file-download">
                                    <div class="file-download-icon">
                                        @switch($content->contentable->document_type ?? 'other')
                                            @case('pdf')
                                                <i class="fas fa-file-pdf text-danger"></i>
                                            @break

                                            @case('word')
                                                <i class="fas fa-file-word text-primary"></i>
                                            @break

                                            @case('powerpoint')
                                                <i class="fas fa-file-powerpoint text-warning"></i>
                                            @break

                                            @default
                                                <i class="fas fa-file-alt"></i>
                                        @endswitch
                                    </div>
                                    <div class="file-download-info">
                                        <h6>{{ $content->contentable->original_filename ?? 'Document' }}</h6>
                                        <p>
                                            {{ strtoupper($content->contentable->document_type ?? 'FILE') }}
                                            @if ($content->contentable->file_size_bytes)
                                                &bull; {{ number_format($content->contentable->file_size_bytes / 1024 / 1024, 2) }}
                                                MB
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">Document not uploaded</p>
                            @endif
                        @break

                        @case('quiz')
                            @if ($content->contentable)
                                <div class="preview-info">
                                    <div class="preview-item">
                                        <div class="preview-item-icon">
                                            <i class="fas fa-question"></i>
                                        </div>
                                        <div class="preview-item-info">
                                            <h6>{{ $content->contentable->questions()->count() }} Questions</h6>
                                            <p>Total points: {{ $content->contentable->total_points }}</p>
                                        </div>
                                    </div>
                                    @if ($content->contentable->hasTimeLimit())
                                        <div class="preview-item">
                                            <div class="preview-item-icon">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                            <div class="preview-item-info">
                                                <h6>{{ $content->contentable->time_limit_minutes }} Minutes</h6>
                                                <p>Time limit for this quiz</p>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="preview-item">
                                        <div class="preview-item-icon">
                                            <i class="fas fa-trophy"></i>
                                        </div>
                                        <div class="preview-item-info">
                                            <h6>{{ $content->contentable->passing_score }}% to Pass</h6>
                                            <p>Minimum score required</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">Quiz not configured</p>
                            @endif
                        @break

                        @case('text')
                            @if ($content->content)
                                <div class="text-preview">
                                    <div class="description-content">
                                        {!! nl2br(e(Str::limit($content->content, 500))) !!}
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">No content available</p>
                            @endif
                        @break

                        @case('audio')
                            <div class="preview-item">
                                <div class="preview-item-icon">
                                    <i class="fas fa-headphones"></i>
                                </div>
                                <div class="preview-item-info">
                                    <h6>Audio Content</h6>
                                    <p>Listen to this audio content</p>
                                </div>
                            </div>
                        @break

                        @case('image')
                            @if ($content->file_path)
                                <img src="{{ Storage::url($content->file_path) }}" alt="{{ $content->title }}"
                                    style="max-width: 100%; max-height: 400px; border-radius: 3px;">
                            @else
                                <p class="text-muted">Image not uploaded</p>
                            @endif
                        @break

                        @case('external_link')
                            <div class="preview-item">
                                <div class="preview-item-icon">
                                    <i class="fas fa-external-link-alt"></i>
                                </div>
                                <div class="preview-item-info">
                                    <h6>External Resource</h6>
                                    <p>{{ $content->external_url ?? 'Link to external website' }}</p>
                                </div>
                            </div>
                            <div class="alert alert-info mt-3" style="font-size: 13px;">
                                <i class="fas fa-info-circle me-1"></i>
                                This link will open in a new browser tab.
                            </div>
                        @break

                        @default
                            <p class="text-muted">Content preview not available</p>
                    @endswitch
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card sidebar-card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-play-circle me-2"></i>Start Content</h5>
                </div>
                <div class="card-body">
                    <div class="progress-section">
                        <div class="progress-status not-started">
                            <i class="fas fa-circle"></i> Not Started
                        </div>
                        <p class="text-muted mb-0" style="font-size: 13px;">
                            Begin this content to track your progress
                        </p>
                    </div>

                    @if ($content->type === 'quiz' && $content->contentable)
                        <a href="{{ route('lms.quizzes.show', $content->contentable) }}"
                            class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-play me-1"></i> Start Quiz
                        </a>
                    @elseif ($content->type === 'external_link' && $content->external_url)
                        <a href="{{ $content->external_url }}" target="_blank" rel="noopener"
                            class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-external-link-alt me-1"></i> Open Link
                        </a>
                    @else
                        <a href="{{ route('lms.content.player', $content) }}" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-play me-1"></i> Start Content
                        </a>
                    @endif

                    @if ($content->is_required)
                        <div class="text-center">
                            <span class="required-badge">
                                <i class="fas fa-asterisk me-1"></i> Required Content
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            @if ($content->estimated_duration)
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                        <h5 class="mb-1">{{ $content->estimated_duration }} minutes</h5>
                        <p class="text-muted mb-0" style="font-size: 13px;">Estimated time to complete</p>
                    </div>
                </div>
            @endif

            @if ($content->module)
                <a href="{{ route('lms.courses.learn', $content->module->course) }}"
                    class="btn btn-outline-secondary w-100">
                    <i class="fas fa-arrow-left me-1"></i> Back to Course
                </a>
            @endif
        </div>
    </div>
@endsection
