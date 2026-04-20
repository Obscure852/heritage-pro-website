@extends('layouts.master')

@section('title')
    Edit Content - {{ $content->title }}
@endsection

@section('css')
    <style>
        .page-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .page-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 20px 24px;
            border-radius: 3px 3px 0 0;
        }

        .page-header h4 {
            margin: 0 0 4px 0;
            font-weight: 600;
        }

        .page-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .page-body {
            padding: 24px;
        }

        /* Helper Text */
        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #4e73df;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }

        /* Section Title */
        .section-title {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Content Type Badge */
        .content-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 3px;
            font-weight: 500;
            margin-bottom: 24px;
        }

        .content-type-badge.video {
            background: #fee2e2;
            color: #dc2626;
        }

        .content-type-badge.document {
            background: #dbeafe;
            color: #2563eb;
        }

        .content-type-badge.quiz {
            background: #fef3c7;
            color: #d97706;
        }

        .content-type-badge.text {
            background: #d1fae5;
            color: #059669;
        }

        .content-type-badge.audio {
            background: #fce7f3;
            color: #db2777;
        }

        .content-type-badge.image {
            background: #ede9fe;
            color: #7c3aed;
        }

        .content-type-badge.external_link {
            background: #cffafe;
            color: #0891b2;
        }

        .content-type-badge.scorm {
            background: #f3e8ff;
            color: #8b5cf6;
        }

        .content-type-badge.assignment {
            background: #d1fae5;
            color: #059669;
        }

        /* Form Elements */
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-label .required {
            color: #dc2626;
        }

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
            padding: 10px 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
        }

        .form-check-input:checked {
            background-color: #4e73df;
            border-color: #4e73df;
        }

        /* Type Help Text */
        .type-help {
            background: #f0f9ff;
            padding: 12px 16px;
            border-left: 4px solid #0ea5e9;
            border-radius: 0 3px 3px 0;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .type-help i {
            font-size: 18px;
            color: #0284c7;
        }

        .type-help p {
            margin: 0;
            font-size: 13px;
            color: #0c4a6e;
        }

        /* Custom File Input */
        .custom-file-input {
            position: relative;
            display: block;
        }

        .custom-file-input input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            z-index: 2;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            background: #fff;
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-input-label:hover {
            border-color: #4e73df;
            background: #f0f4ff;
        }

        .file-input-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .file-input-text .file-label {
            font-weight: 600;
            color: #374151;
            display: block;
            margin-bottom: 4px;
        }

        .file-input-text .file-hint {
            font-size: 13px;
            color: #6b7280;
        }

        /* Current File Display */
        .current-file {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 12px;
        }

        .current-file i {
            font-size: 24px;
            color: #4e73df;
        }

        .current-file-info {
            flex: 1;
        }

        .current-file-name {
            font-weight: 500;
            color: #1f2937;
        }

        .current-file-size {
            font-size: 12px;
            color: #6b7280;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: #4e73df;
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: #3d5fc7;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
            color: white;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            margin-top: 24px;
        }

        /* Button Loading Animation */
        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Two Column Layout for Details */
        .details-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 32px;
        }

        @media (max-width: 992px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Settings Item */
        .settings-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 12px;
            background: #fff;
        }

        .settings-item:last-child {
            margin-bottom: 0;
        }

        .settings-item-info h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .settings-item-info p {
            margin: 0;
            font-size: 12px;
            color: #6b7280;
        }

        /* Video Preview */
        .video-preview {
            margin-bottom: 16px;
        }

        .video-preview iframe {
            width: 100%;
            height: 315px;
            border-radius: 3px;
        }

        /* Info Item */
        .info-item {
            margin-bottom: 12px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-item small {
            display: block;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .info-item .value {
            font-weight: 500;
            color: #1f2937;
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('title')
            Edit Content
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-md-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('lms.content.update', $content) }}" method="POST" enctype="multipart/form-data"
        class="needs-validation" novalidate>
        @csrf
        @method('PUT')

        <div class="page-container">
            <div class="page-header">
                <h4>Edit Content</h4>
                <p>{{ $content->module->title }} &bull; {{ $content->module->course->title }}</p>
            </div>

            <div class="page-body">
                <div class="help-text">
                    <div class="help-title">Edit Content Item</div>
                    <p class="help-content">Update the details for "{{ $content->title }}". Fields marked with <span
                            class="text-danger">*</span> are required.</p>
                </div>

                <!-- Content Type Badge -->
                @php
                    $typeClass = match ($content->type) {
                        'video_youtube', 'video_upload' => 'video',
                        'document' => 'document',
                        'quiz' => 'quiz',
                        'text' => 'text',
                        'audio' => 'audio',
                        'image' => 'image',
                        'external_link' => 'external_link',
                        'scorm' => 'scorm',
                        'assignment' => 'assignment',
                        default => 'text',
                    };
                    $typeIcon = match ($content->type) {
                        'video_youtube' => 'fab fa-youtube',
                        'video_upload' => 'fas fa-video',
                        'document' => 'fas fa-file-pdf',
                        'quiz' => 'fas fa-question-circle',
                        'text' => 'fas fa-align-left',
                        'audio' => 'fas fa-headphones',
                        'image' => 'fas fa-image',
                        'external_link' => 'fas fa-external-link-alt',
                        'scorm' => 'fas fa-cube',
                        'assignment' => 'fas fa-tasks',
                        default => 'fas fa-file',
                    };
                    $typeName = match ($content->type) {
                        'video_youtube' => 'YouTube Video',
                        'video_upload' => 'Uploaded Video',
                        'document' => 'Document',
                        'quiz' => 'Quiz',
                        'text' => 'Text Content',
                        'audio' => 'Audio',
                        'image' => 'Image',
                        'external_link' => 'External Link',
                        'scorm' => 'SCORM Package',
                        'assignment' => 'Assignment',
                        default => ucfirst($content->type),
                    };
                @endphp
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="content-type-badge {{ $typeClass }}">
                        <i class="{{ $typeIcon }}"></i>
                        <span>{{ $typeName }}</span>
                    </div>
                    @if ($content->type === 'assignment' && $content->contentable)
                        <div class="d-flex gap-2">
                            <a href="{{ route('lms.assignments.edit', $content->contentable) }}"
                                class="btn btn-sm btn-primary">
                                <i class="fas fa-edit me-1"></i> Edit Assignment
                            </a>
                            <a href="{{ route('lms.assignments.submissions', $content->contentable) }}"
                                class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-inbox me-1"></i> View Submissions
                            </a>
                        </div>
                    @elseif ($content->type === 'scorm' && $content->contentable)
                        <div class="d-flex gap-2">
                            <a href="{{ route('lms.scorm.edit', $content->contentable) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit me-1"></i> Edit Package
                            </a>
                            <a href="{{ route('lms.scorm.preview', $content->contentable) }}"
                                class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="fas fa-play me-1"></i> Preview
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Content Details -->
                <div class="details-grid">
                    <div>
                        <h6 class="section-title">Content Details</h6>

                        <div class="mb-3">
                            <label class="form-label">Title <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control"
                                value="{{ old('title', $content->title) }}" placeholder="Enter content title" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this content">{{ old('description', $content->description) }}</textarea>
                        </div>

                        <!-- YouTube Video Fields -->
                        @if ($content->type === 'video_youtube')
                            @if ($content->contentable && $content->contentable->source_id)
                                <div class="type-help">
                                    <i class="fab fa-youtube"></i>
                                    <p>Update the YouTube video URL if needed. The video will be embedded for students to
                                        watch.</p>
                                </div>
                                <div class="video-preview">
                                    <iframe src="https://www.youtube.com/embed/{{ $content->contentable->source_id }}"
                                        frameborder="0" allowfullscreen></iframe>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">YouTube URL</label>
                                    <input type="url" name="youtube_url" class="form-control"
                                        value="{{ old('youtube_url', 'https://www.youtube.com/watch?v=' . $content->contentable->source_id) }}"
                                        placeholder="https://www.youtube.com/watch?v=...">
                                </div>
                            @else
                                <div class="alert alert-warning alert-label-icon label-arrow mb-3">
                                    <i class="mdi mdi-alert label-icon"></i>
                                    <strong>No YouTube video linked yet.</strong> Please add a YouTube URL below.
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">YouTube URL <span class="required">*</span></label>
                                    <input type="url" name="youtube_url" class="form-control"
                                        value="{{ old('youtube_url') }}" placeholder="https://www.youtube.com/watch?v=..."
                                        required>
                                </div>
                            @endif
                        @endif

                        <!-- Upload Video Fields -->
                        @if ($content->type === 'video_upload')
                            @php
                                $hasVideo =
                                    $content->library_item_id ||
                                    ($content->contentable && $content->contentable->file_path) ||
                                    $content->file_path;
                            @endphp
                            @if ($hasVideo)
                                <div class="type-help">
                                    <i class="fas fa-video"></i>
                                    <p>
                                        @if ($content->library_item_id)
                                            This content uses a video from the Content Library. Select a different item or
                                            upload new to replace it.
                                        @else
                                            Upload a new video file to replace the current one, or select from library.
                                        @endif
                                    </p>
                                </div>

                                <!-- Current Video Display -->
                                <div class="current-file">
                                    <i class="fas fa-video"></i>
                                    <div class="current-file-info">
                                        @if ($content->library_item_id && $content->libraryItem)
                                            <div class="current-file-name">
                                                <i class="fas fa-link text-primary me-1" title="From Library"></i>
                                                {{ $content->libraryItem->title }}
                                            </div>
                                            <div class="current-file-size">
                                                {{ $content->libraryItem->human_file_size ?? 'From Library' }}</div>
                                        @elseif ($content->contentable && $content->contentable->file_path)
                                            <div class="current-file-name">
                                                {{ $content->contentable->original_filename ?? 'Current Video' }}</div>
                                            @if ($content->contentable->file_size_bytes)
                                                <div class="current-file-size">
                                                    {{ number_format($content->contentable->file_size_bytes / 1024 / 1024, 2) }}
                                                    MB</div>
                                            @endif
                                        @else
                                            <div class="current-file-name">Current Video</div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Source Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Change Source</label>
                                    <select class="form-select library-source-select" name="library_item_id"
                                        data-type="video_upload">
                                        <option value="">Keep Current Video</option>
                                        <option value="upload">Upload New Video</option>
                                        @foreach ($libraryItems['video_upload'] ?? [] as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $content->library_item_id == $item->id ? 'selected' : '' }}>
                                                {{ $item->title }} ({{ $item->human_file_size }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="upload-field" data-type="video_upload" style="display: none;">
                                    <label class="form-label">Upload New Video</label>
                                    <div class="custom-file-input">
                                        <input type="file" name="video_file" id="videoFile"
                                            accept="video/mp4,video/webm,video/quicktime">
                                        <label for="videoFile" class="file-input-label">
                                            <div class="file-input-icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <div class="file-input-text">
                                                <span class="file-label">Choose new video file or drag here</span>
                                                <span class="file-hint">MP4, WebM, MOV (max 500MB)</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning alert-label-icon label-arrow mb-3">
                                    <i class="mdi mdi-alert label-icon"></i>
                                    <strong>No video uploaded yet.</strong> Please upload a video file or select from
                                    library below.
                                </div>

                                <!-- Source Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Source</label>
                                    <select class="form-select library-source-select" name="library_item_id"
                                        data-type="video_upload">
                                        <option value="upload">Upload New Video</option>
                                        @foreach ($libraryItems['video_upload'] ?? [] as $item)
                                            <option value="{{ $item->id }}">{{ $item->title }}
                                                ({{ $item->human_file_size }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="upload-field" data-type="video_upload">
                                    <label class="form-label">Upload Video <span class="required">*</span></label>
                                    <div class="custom-file-input">
                                        <input type="file" name="video_file" id="videoFile"
                                            accept="video/mp4,video/webm,video/quicktime">
                                        <label for="videoFile" class="file-input-label">
                                            <div class="file-input-icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <div class="file-input-text">
                                                <span class="file-label">Choose video file or drag here</span>
                                                <span class="file-hint">MP4, WebM, MOV (max 500MB)</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <!-- Document Fields -->
                        @if ($content->type === 'document')
                            @php
                                $hasDocument =
                                    $content->library_item_id ||
                                    ($content->contentable && $content->contentable->file_path) ||
                                    $content->file_path;
                            @endphp
                            @if ($hasDocument)
                                <div class="type-help">
                                    <i class="fas fa-file-alt"></i>
                                    <p>
                                        @if ($content->library_item_id)
                                            This content uses a document from the Content Library. Select a different item
                                            or upload new to replace it.
                                        @else
                                            Upload a new document to replace the current one, or select from library.
                                        @endif
                                    </p>
                                </div>

                                <!-- Current Document Display -->
                                <div class="current-file">
                                    <i class="fas fa-file-pdf"></i>
                                    <div class="current-file-info">
                                        @if ($content->library_item_id && $content->libraryItem)
                                            <div class="current-file-name">
                                                <i class="fas fa-link text-primary me-1" title="From Library"></i>
                                                {{ $content->libraryItem->title }}
                                            </div>
                                            <div class="current-file-size">
                                                {{ $content->libraryItem->human_file_size ?? 'From Library' }}</div>
                                        @elseif ($content->contentable && $content->contentable->file_path)
                                            <div class="current-file-name">
                                                {{ $content->contentable->original_filename ?? 'Current Document' }}</div>
                                            @if ($content->contentable->file_size_bytes)
                                                <div class="current-file-size">
                                                    {{ number_format($content->contentable->file_size_bytes / 1024 / 1024, 2) }}
                                                    MB</div>
                                            @endif
                                        @else
                                            <div class="current-file-name">Current Document</div>
                                        @endif
                                    </div>
                                    <a href="{{ $content->file_url }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>

                                <!-- Source Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Change Source</label>
                                    <select class="form-select library-source-select" name="library_item_id"
                                        data-type="document">
                                        <option value="">Keep Current Document</option>
                                        <option value="upload">Upload New Document</option>
                                        @foreach ($libraryItems['document'] ?? [] as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $content->library_item_id == $item->id ? 'selected' : '' }}>
                                                {{ $item->title }} ({{ $item->human_file_size }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="upload-field" data-type="document" style="display: none;">
                                    <label class="form-label">Upload New Document</label>
                                    <div class="custom-file-input">
                                        <input type="file" name="file" id="documentFile"
                                            accept=".pdf,.doc,.docx,.ppt,.pptx">
                                        <label for="documentFile" class="file-input-label">
                                            <div class="file-input-icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <div class="file-input-text">
                                                <span class="file-label">Choose new document or drag here</span>
                                                <span class="file-hint">PDF, DOC, DOCX, PPT, PPTX (max 20MB)</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning alert-label-icon label-arrow mb-3">
                                    <i class="mdi mdi-alert label-icon"></i>
                                    <strong>No document uploaded yet.</strong> Please upload a document file or select from
                                    library below.
                                </div>

                                <!-- Source Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Source</label>
                                    <select class="form-select library-source-select" name="library_item_id"
                                        data-type="document">
                                        <option value="upload">Upload New Document</option>
                                        @foreach ($libraryItems['document'] ?? [] as $item)
                                            <option value="{{ $item->id }}">{{ $item->title }}
                                                ({{ $item->human_file_size }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="upload-field" data-type="document">
                                    <label class="form-label">Upload Document <span class="required">*</span></label>
                                    <div class="custom-file-input">
                                        <input type="file" name="file" id="documentFile"
                                            accept=".pdf,.doc,.docx,.ppt,.pptx">
                                        <label for="documentFile" class="file-input-label">
                                            <div class="file-input-icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <div class="file-input-text">
                                                <span class="file-label">Choose document file or drag here</span>
                                                <span class="file-hint">PDF, DOC, DOCX, PPT, PPTX (max 20MB)</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <!-- Text Content Fields -->
                        @if ($content->type === 'text')
                            @if (empty($content->content))
                                <div class="alert alert-warning alert-label-icon label-arrow mb-3">
                                    <i class="mdi mdi-alert label-icon"></i>
                                    <strong>No content added yet.</strong> Please add your text content below.
                                </div>
                            @endif
                            <div class="mb-0">
                                <label class="form-label">Content <span class="required">*</span></label>
                                <textarea name="content" class="form-control" rows="12"
                                    placeholder="Enter your text content here. You can use HTML for formatting." required>{{ old('content', $content->content) }}</textarea>
                            </div>
                        @endif

                        <!-- Quiz Fields -->
                        @if ($content->type === 'quiz')
                            @if ($content->contentable)
                                @php $questionCount = $content->contentable->questions()->count(); @endphp
                                @if ($questionCount > 0)
                                    <div class="alert alert-info alert-label-icon label-arrow mb-3">
                                        <i class="mdi mdi-information label-icon"></i>
                                        <strong>{{ $questionCount }} question{{ $questionCount != 1 ? 's' : '' }}
                                            added.</strong>
                                    </div>
                                @else
                                    <div class="alert alert-warning alert-label-icon label-arrow mb-3">
                                        <i class="mdi mdi-alert label-icon"></i>
                                        <strong>No questions added yet.</strong> Click below to add questions to this quiz.
                                    </div>
                                @endif
                                <div class="type-help">
                                    <i class="fas fa-question-circle"></i>
                                    <p>Quiz settings and questions are managed separately.</p>
                                </div>
                                <a href="{{ route('lms.quizzes.questions', $content->contentable) }}"
                                    class="btn btn-primary">
                                    <i class="fas fa-list-ol me-1"></i> Manage Quiz Questions
                                </a>
                            @else
                                <div class="alert alert-danger alert-label-icon label-arrow mb-3">
                                    <i class="mdi mdi-block-helper label-icon"></i>
                                    <strong>Quiz not properly configured.</strong> Please delete and recreate this content
                                    item.
                                </div>
                            @endif
                        @endif

                        <!-- Audio Fields -->
                        @if ($content->type === 'audio')
                            @php
                                $hasAudio = $content->library_item_id || $content->file_path;
                            @endphp
                            @if ($hasAudio)
                                <div class="type-help">
                                    <i class="fas fa-headphones"></i>
                                    <p>
                                        @if ($content->library_item_id)
                                            This content uses audio from the Content Library. Select a different item or
                                            upload new to replace it.
                                        @else
                                            Upload a new audio file to replace the current one, or select from library.
                                        @endif
                                    </p>
                                </div>

                                <!-- Current Audio Display -->
                                <div class="current-file">
                                    <i class="fas fa-headphones"></i>
                                    <div class="current-file-info">
                                        @if ($content->library_item_id && $content->libraryItem)
                                            <div class="current-file-name">
                                                <i class="fas fa-link text-primary me-1" title="From Library"></i>
                                                {{ $content->libraryItem->title }}
                                            </div>
                                            <div class="current-file-size">
                                                {{ $content->libraryItem->human_file_size ?? 'From Library' }}</div>
                                        @else
                                            <div class="current-file-name">Current Audio File</div>
                                        @endif
                                    </div>
                                    <audio controls style="max-width: 200px;">
                                        <source src="{{ $content->file_url }}">
                                    </audio>
                                </div>

                                <!-- Source Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Change Source</label>
                                    <select class="form-select library-source-select" name="library_item_id"
                                        data-type="audio">
                                        <option value="">Keep Current Audio</option>
                                        <option value="upload">Upload New Audio</option>
                                        @foreach ($libraryItems['audio'] ?? [] as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $content->library_item_id == $item->id ? 'selected' : '' }}>
                                                {{ $item->title }} ({{ $item->human_file_size }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="upload-field" data-type="audio" style="display: none;">
                                    <label class="form-label">Upload New Audio</label>
                                    <div class="custom-file-input">
                                        <input type="file" name="audio_file" id="audioFile"
                                            accept="audio/mp3,audio/wav,audio/ogg">
                                        <label for="audioFile" class="file-input-label">
                                            <div class="file-input-icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <div class="file-input-text">
                                                <span class="file-label">Choose new audio file or drag here</span>
                                                <span class="file-hint">MP3, WAV, OGG (max 50MB)</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning alert-label-icon label-arrow mb-3">
                                    <i class="mdi mdi-alert label-icon"></i>
                                    <strong>No audio file uploaded yet.</strong> Please upload an audio file or select from
                                    library below.
                                </div>

                                <!-- Source Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Source</label>
                                    <select class="form-select library-source-select" name="library_item_id"
                                        data-type="audio">
                                        <option value="upload">Upload New Audio</option>
                                        @foreach ($libraryItems['audio'] ?? [] as $item)
                                            <option value="{{ $item->id }}">{{ $item->title }}
                                                ({{ $item->human_file_size }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="upload-field" data-type="audio">
                                    <label class="form-label">Upload Audio <span class="required">*</span></label>
                                    <div class="custom-file-input">
                                        <input type="file" name="audio_file" id="audioFile"
                                            accept="audio/mp3,audio/wav,audio/ogg">
                                        <label for="audioFile" class="file-input-label">
                                            <div class="file-input-icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <div class="file-input-text">
                                                <span class="file-label">Choose audio file or drag here</span>
                                                <span class="file-hint">MP3, WAV, OGG (max 50MB)</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <!-- Image Fields -->
                        @if ($content->type === 'image')
                            @php
                                $hasImage = $content->library_item_id || $content->file_path;
                            @endphp
                            @if ($hasImage)
                                <div class="type-help">
                                    <i class="fas fa-image"></i>
                                    <p>
                                        @if ($content->library_item_id)
                                            This content uses an image from the Content Library. Select a different item or
                                            upload new to replace it.
                                        @else
                                            Upload a new image to replace the current one, or select from library.
                                        @endif
                                    </p>
                                </div>

                                <!-- Current Image Display -->
                                <div class="mb-3">
                                    @if ($content->library_item_id && $content->libraryItem)
                                        <div class="mb-2">
                                            <span class="badge bg-primary">
                                                <i class="fas fa-link me-1"></i>From Library:
                                                {{ $content->libraryItem->title }}
                                            </span>
                                        </div>
                                    @endif
                                    <img src="{{ $content->file_url }}" alt="{{ $content->title }}"
                                        style="max-width: 100%; max-height: 300px; border-radius: 3px;">
                                </div>

                                <!-- Source Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Change Source</label>
                                    <select class="form-select library-source-select" name="library_item_id"
                                        data-type="image">
                                        <option value="">Keep Current Image</option>
                                        <option value="upload">Upload New Image</option>
                                        @foreach ($libraryItems['image'] ?? [] as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $content->library_item_id == $item->id ? 'selected' : '' }}>
                                                {{ $item->title }} ({{ $item->human_file_size }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="upload-field" data-type="image" style="display: none;">
                                    <label class="form-label">Upload New Image</label>
                                    <div class="custom-file-input">
                                        <input type="file" name="image_file" id="imageFile" accept="image/*">
                                        <label for="imageFile" class="file-input-label">
                                            <div class="file-input-icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <div class="file-input-text">
                                                <span class="file-label">Choose new image file or drag here</span>
                                                <span class="file-hint">JPG, PNG, GIF, WebP (max 10MB)</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning alert-label-icon label-arrow mb-3">
                                    <i class="mdi mdi-alert label-icon"></i>
                                    <strong>No image uploaded yet.</strong> Please upload an image or select from library
                                    below.
                                </div>

                                <!-- Source Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Source</label>
                                    <select class="form-select library-source-select" name="library_item_id"
                                        data-type="image">
                                        <option value="upload">Upload New Image</option>
                                        @foreach ($libraryItems['image'] ?? [] as $item)
                                            <option value="{{ $item->id }}">{{ $item->title }}
                                                ({{ $item->human_file_size }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="upload-field" data-type="image">
                                    <label class="form-label">Upload Image <span class="required">*</span></label>
                                    <div class="custom-file-input">
                                        <input type="file" name="image_file" id="imageFile" accept="image/*">
                                        <label for="imageFile" class="file-input-label">
                                            <div class="file-input-icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <div class="file-input-text">
                                                <span class="file-label">Choose image file or drag here</span>
                                                <span class="file-hint">JPG, PNG, GIF, WebP (max 10MB)</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <!-- External Link Fields -->
                        @if ($content->type === 'external_link')
                            @if (empty($content->external_url))
                                <div class="alert alert-warning alert-label-icon label-arrow mb-3">
                                    <i class="mdi mdi-alert label-icon"></i>
                                    <strong>No URL added yet.</strong> Please add the external URL below.
                                </div>
                            @else
                                <div class="type-help">
                                    <i class="fas fa-external-link-alt"></i>
                                    <p>Update the external URL. Students will be able to open this link in a new tab.</p>
                                </div>
                            @endif
                            <div class="mb-0">
                                <label class="form-label">External URL <span class="required">*</span></label>
                                <input type="url" name="external_url" class="form-control"
                                    value="{{ old('external_url', $content->external_url) }}"
                                    placeholder="https://example.com/resource" required>
                            </div>
                        @endif

                        <!-- SCORM Package Fields -->
                        @if ($content->type === 'scorm')
                            @if ($content->contentable)
                                <div class="type-help">
                                    <i class="fas fa-cube"></i>
                                    <p>SCORM packages are managed separately. Use the buttons above to edit package settings
                                        or preview content.</p>
                                </div>
                            @else
                                <div class="alert alert-danger alert-label-icon label-arrow mb-3">
                                    <i class="mdi mdi-block-helper label-icon"></i>
                                    <strong>SCORM package not properly configured.</strong> Please delete and recreate this
                                    content item.
                                </div>
                            @endif
                        @endif

                        <!-- Assignment Fields -->
                        @if ($content->type === 'assignment')
                            @if ($content->contentable)
                                <div class="type-help">
                                    <i class="fas fa-tasks"></i>
                                    <p>Assignments are managed separately. Use the buttons above to edit assignment
                                        settings, rubrics, and view submissions.</p>
                                </div>
                            @else
                                <div class="alert alert-danger alert-label-icon label-arrow mb-3">
                                    <i class="mdi mdi-block-helper label-icon"></i>
                                    <strong>Assignment not properly configured.</strong> Please delete and recreate this
                                    content item.
                                </div>
                            @endif
                        @endif

                        {{-- Fallback for unrecognized content types --}}
                        @if (
                            !in_array($content->type, [
                                'video_youtube',
                                'video_upload',
                                'document',
                                'text',
                                'quiz',
                                'audio',
                                'image',
                                'external_link',
                                'scorm',
                                'assignment',
                            ]))
                            <div class="alert alert-danger alert-label-icon label-arrow mb-3">
                                <i class="mdi mdi-block-helper label-icon"></i>
                                <strong>Unknown content type: "{{ $content->type }}".</strong> This content item may have
                                been created incorrectly. Please delete and recreate it with a valid content type.
                            </div>
                        @endif
                    </div>

                    <div>
                        <h6 class="section-title">Settings</h6>

                        <div class="mb-4">
                            <label class="form-label">Estimated Duration</label>
                            <div class="input-group">
                                <input type="number" name="estimated_duration" class="form-control" min="1"
                                    placeholder="Enter duration"
                                    value="{{ old('estimated_duration', $content->estimated_duration) }}">
                                <span class="input-group-text">minutes</span>
                            </div>
                            <div class="form-text">How long will this content take to complete?</div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <h6>Required Content</h6>
                                <p>Must be completed for progress tracking</p>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" class="form-check-input" name="is_required" id="isRequired"
                                    value="1" {{ old('is_required', $content->is_required) ? 'checked' : '' }}
                                    style="width: 40px; height: 20px;">
                            </div>
                        </div>

                        <h6 class="section-title mt-4">Content Info</h6>

                        <div class="info-item">
                            <small>Module</small>
                            <div class="value">{{ $content->module->title }}</div>
                        </div>
                        <div class="info-item">
                            <small>Course</small>
                            <div class="value">{{ $content->module->course->title }}</div>
                        </div>
                        <div class="info-item">
                            <small>Created</small>
                            <div class="value">{{ $content->created_at->format('M d, Y') }}</div>
                        </div>
                        <div class="info-item">
                            <small>Last Updated</small>
                            <div class="value">{{ $content->updated_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('lms.modules.edit', $content->module) }}" class="btn btn-secondary">
                        <i class="bx bx-x"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Update Content</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Updating...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeFormValidation();
            initializeFileInputs();
        });

        function initializeFormValidation() {
            const forms = document.querySelectorAll('.needs-validation');

            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();

                        const firstInvalidElement = form.querySelector(':invalid');
                        if (firstInvalidElement) {
                            firstInvalidElement.focus();
                            firstInvalidElement.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    } else {
                        // Show loading state on submit button
                        const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                        if (submitBtn) {
                            submitBtn.classList.add('loading');
                            submitBtn.disabled = true;
                        }
                    }

                    form.classList.add('was-validated');
                }, false);
            });
        }

        function initializeFileInputs() {
            const fileInputs = document.querySelectorAll('.custom-file-input input[type="file"]');
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const label = this.nextElementSibling;
                    const fileLabel = label.querySelector('.file-label');
                    if (this.files && this.files[0]) {
                        fileLabel.textContent = this.files[0].name;
                        label.style.borderColor = '#4e73df';
                        label.style.background = '#f0f4ff';
                    }
                });
            });
        }

        // Library source selection handling
        function initializeLibrarySourceSelects() {
            const sourceSelects = document.querySelectorAll('.library-source-select');
            sourceSelects.forEach(select => {
                const type = select.dataset.type;
                const uploadField = document.querySelector(`.upload-field[data-type="${type}"]`);

                if (!uploadField) return;

                // Initial state
                toggleUploadField(select, uploadField);

                // On change
                select.addEventListener('change', function() {
                    toggleUploadField(this, uploadField);
                });
            });
        }

        function toggleUploadField(select, uploadField) {
            if (select.value === 'upload') {
                uploadField.style.display = 'block';
                // Make file input required if no content exists
                const fileInput = uploadField.querySelector('input[type="file"]');
                if (fileInput) {
                    fileInput.removeAttribute('required'); // Don't require on edit page
                }
            } else {
                uploadField.style.display = 'none';
                const fileInput = uploadField.querySelector('input[type="file"]');
                if (fileInput) {
                    fileInput.removeAttribute('required');
                }
            }
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            initializeLibrarySourceSelects();
        });
    </script>
@endsection
