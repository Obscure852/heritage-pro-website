@extends('layouts.master')

@section('title')
    Add Content - {{ $module->title }}
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

        /* Content Type Cards */
        .content-type-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        @media (max-width: 992px) {
            .content-type-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .content-type-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .content-type-card {
            border: 2px solid #e5e7eb;
            border-radius: 3px;
            padding: 24px 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #fff;
        }

        .content-type-card:hover {
            border-color: #4e73df;
            background: #f0f4ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.15);
        }

        .content-type-card.selected {
            border-color: #4e73df;
            background: #e8f0fe;
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
        }

        .content-type-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 24px;
        }

        .content-type-card.video .content-type-icon {
            background: #fee2e2;
            color: #dc2626;
        }

        .content-type-card.document .content-type-icon {
            background: #dbeafe;
            color: #2563eb;
        }

        .content-type-card.quiz .content-type-icon {
            background: #fef3c7;
            color: #d97706;
        }

        .content-type-card.text .content-type-icon {
            background: #d1fae5;
            color: #059669;
        }

        .content-type-card.audio .content-type-icon {
            background: #fce7f3;
            color: #db2777;
        }

        .content-type-card.image .content-type-icon {
            background: #ede9fe;
            color: #7c3aed;
        }

        .content-type-card.link .content-type-icon {
            background: #cffafe;
            color: #0891b2;
        }

        .content-type-card .type-name {
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
            display: block;
            margin-bottom: 4px;
        }

        .content-type-card .type-desc {
            font-size: 12px;
            color: #6b7280;
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

        /* Type Fields */
        .type-fields {
            display: none;
        }

        .type-fields.active {
            display: block;
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

        /* Library Source Select */
        .library-source-select {
            margin-bottom: 0;
        }

        .upload-field {
            margin-top: 0;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('title')
            Add Content
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

    <form action="{{ route('lms.content.store', $module) }}" method="POST" enctype="multipart/form-data"
        class="needs-validation" novalidate>
        @csrf

        <div class="page-container">
            <div class="page-header">
                <h4>Add Content</h4>
                <p>{{ $module->title }} &bull; {{ $module->course->title }}</p>
            </div>

            <div class="page-body">
                <div class="help-text">
                    <div class="help-title">Add Learning Content</div>
                    <p class="help-content">Create new content for the module "{{ $module->title }}". Select a content type,
                        fill in the details, and configure settings. Fields marked with <span class="text-danger">*</span>
                        are required.</p>
                </div>

                <!-- Hidden field for library item ID -->
                <input type="hidden" name="library_item_id" id="libraryItemId" value="">

                <!-- Content Type Selection Section -->
                <div id="contentTypeSection">
                    <!-- Content Type Selection -->
                    <h6 class="section-title">Select Content Type</h6>
                    <div class="content-type-grid">
                        <label class="content-type-card video" data-type="video_youtube">
                            <input type="radio" name="type" value="video_youtube" style="display: none;">
                            <div class="content-type-icon">
                                <i class="fab fa-youtube"></i>
                            </div>
                            <span class="type-name">YouTube Video</span>
                            <span class="type-desc">Embed from YouTube</span>
                        </label>

                        <label class="content-type-card video" data-type="video_upload">
                            <input type="radio" name="type" value="video_upload" style="display: none;">
                            <div class="content-type-icon">
                                <i class="fas fa-video"></i>
                            </div>
                            <span class="type-name">Upload Video</span>
                            <span class="type-desc">MP4, WebM, MOV</span>
                        </label>

                        <label class="content-type-card document" data-type="document">
                            <input type="radio" name="type" value="document" style="display: none;">
                            <div class="content-type-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <span class="type-name">Document</span>
                            <span class="type-desc">PDF, DOC, PPT</span>
                        </label>

                        <label class="content-type-card text" data-type="text">
                            <input type="radio" name="type" value="text" style="display: none;">
                            <div class="content-type-icon">
                                <i class="fas fa-align-left"></i>
                            </div>
                            <span class="type-name">Text Content</span>
                            <span class="type-desc">Rich text article</span>
                        </label>

                        <label class="content-type-card quiz" data-type="quiz">
                            <input type="radio" name="type" value="quiz" style="display: none;">
                            <div class="content-type-icon">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <span class="type-name">Quiz</span>
                            <span class="type-desc">Assessment questions</span>
                        </label>

                        <a href="{{ route('lms.assignments.create', $module) }}" class="content-type-card"
                            style="text-decoration: none;">
                            <div class="content-type-icon" style="background: #fed7aa; color: #ea580c;">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <span class="type-name">Assignment</span>
                            <span class="type-desc">Student submissions</span>
                        </a>

                        <label class="content-type-card audio" data-type="audio">
                            <input type="radio" name="type" value="audio" style="display: none;">
                            <div class="content-type-icon">
                                <i class="fas fa-headphones"></i>
                            </div>
                            <span class="type-name">Audio</span>
                            <span class="type-desc">MP3, WAV, OGG</span>
                        </label>

                        <label class="content-type-card image" data-type="image">
                            <input type="radio" name="type" value="image" style="display: none;">
                            <div class="content-type-icon">
                                <i class="fas fa-image"></i>
                            </div>
                            <span class="type-name">Image</span>
                            <span class="type-desc">JPG, PNG, GIF</span>
                        </label>

                        <label class="content-type-card link" data-type="external_link">
                            <input type="radio" name="type" value="external_link" style="display: none;">
                            <div class="content-type-icon">
                                <i class="fas fa-external-link-alt"></i>
                            </div>
                            <span class="type-name">External Link</span>
                            <span class="type-desc">Link to website</span>
                        </label>

                        <a href="{{ route('lms.scorm.create', $module) }}" class="content-type-card"
                            style="text-decoration: none;">
                            <div class="content-type-icon" style="background: #f3e8ff; color: #8b5cf6;">
                                <i class="fas fa-cube"></i>
                            </div>
                            <span class="type-name">SCORM Package</span>
                            <span class="type-desc">Interactive e-learning</span>
                        </a>
                    </div>
                </div><!-- End contentTypeSection -->

                <!-- Content Details -->
                <div class="details-grid">
                    <div>
                        <h6 class="section-title">Content Details</h6>

                        <div class="mb-3">
                            <label class="form-label">Title <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}"
                                placeholder="Enter content title" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this content">{{ old('description') }}</textarea>
                        </div>

                        <!-- YouTube Video Fields -->
                        <div class="type-fields" id="fields-video_youtube">
                            <div class="type-help">
                                <i class="fab fa-youtube"></i>
                                <p>Paste a YouTube video URL. The video will be embedded for students to watch directly.</p>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">YouTube URL <span class="required">*</span></label>
                                <input type="url" name="youtube_url" class="form-control"
                                    placeholder="https://www.youtube.com/watch?v=...">
                            </div>
                        </div>

                        <!-- Upload Video Fields -->
                        <div class="type-fields" id="fields-video_upload">
                            <div class="type-help">
                                <i class="fas fa-video"></i>
                                <p>Upload a video file directly or select from your library. Supported formats: MP4, WebM,
                                    MOV. Maximum file size: 500MB.</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Source</label>
                                <select class="form-select library-source-select" data-type="video_upload">
                                    <option value="">Upload New Video</option>
                                    @foreach ($libraryItems['video_upload'] as $item)
                                        <option value="{{ $item->id }}" data-title="{{ $item->title }}">
                                            {{ $item->title }} ({{ $item->formatted_size }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="upload-field" data-type="video_upload">
                                <label class="form-label">Video File <span class="required">*</span></label>
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
                        </div>

                        <!-- Document Fields -->
                        <div class="type-fields" id="fields-document">
                            <div class="type-help">
                                <i class="fas fa-file-alt"></i>
                                <p>Upload a document or select from your library. Supported formats: PDF, DOC, DOCX, PPT,
                                    PPTX.</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Source</label>
                                <select class="form-select library-source-select" data-type="document">
                                    <option value="">Upload New Document</option>
                                    @foreach ($libraryItems['document'] as $item)
                                        <option value="{{ $item->id }}" data-title="{{ $item->title }}">
                                            {{ $item->title }} ({{ $item->formatted_size }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="upload-field" data-type="document">
                                <label class="form-label">Document File <span class="required">*</span></label>
                                <div class="custom-file-input">
                                    <input type="file" name="file" id="documentFile"
                                        accept=".pdf,.doc,.docx,.ppt,.pptx">
                                    <label for="documentFile" class="file-input-label">
                                        <div class="file-input-icon">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                        </div>
                                        <div class="file-input-text">
                                            <span class="file-label">Choose document or drag here</span>
                                            <span class="file-hint">PDF, DOC, DOCX, PPT, PPTX (max 20MB)</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Text Content Fields -->
                        <div class="type-fields" id="fields-text">
                            <div class="mb-0">
                                <label class="form-label">Content <span class="required">*</span></label>
                                <textarea name="content" class="form-control" rows="12"
                                    placeholder="Enter your text content here. You can use HTML for formatting.">{{ old('content') }}</textarea>
                            </div>
                        </div>

                        <!-- Quiz Fields -->
                        <div class="type-fields" id="fields-quiz">
                            <div class="type-help">
                                <i class="fas fa-question-circle"></i>
                                <p>Create a quiz/test to assess student learning. After saving, you'll be able to add
                                    questions to this quiz.</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Instructions</label>
                                <textarea name="quiz_instructions" class="form-control" rows="4"
                                    placeholder="Enter instructions for students taking this quiz/test (e.g., rules, time allocation, materials allowed)">{{ old('quiz_instructions') }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Time Limit (minutes)</label>
                                    <input type="number" name="quiz_time_limit" class="form-control"
                                        placeholder="No limit" min="1" value="{{ old('quiz_time_limit') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Passing Score (%)</label>
                                    <input type="number" name="quiz_passing_score" class="form-control"
                                        value="{{ old('quiz_passing_score', 70) }}" min="0" max="100">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Max Attempts</label>
                                    <input type="number" name="quiz_max_attempts" class="form-control"
                                        placeholder="Unlimited" min="1" value="{{ old('quiz_max_attempts') }}">
                                </div>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="quiz_shuffle_questions"
                                    id="shuffleQuestions" value="1"
                                    {{ old('quiz_shuffle_questions') ? 'checked' : '' }}>
                                <label class="form-check-label" for="shuffleQuestions">
                                    Shuffle questions for each attempt
                                </label>
                            </div>
                        </div>

                        <!-- Audio Fields -->
                        <div class="type-fields" id="fields-audio">
                            <div class="type-help">
                                <i class="fas fa-headphones"></i>
                                <p>Upload an audio file or select from your library. Supported formats: MP3, WAV, OGG.</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Source</label>
                                <select class="form-select library-source-select" data-type="audio">
                                    <option value="">Upload New Audio</option>
                                    @foreach ($libraryItems['audio'] as $item)
                                        <option value="{{ $item->id }}" data-title="{{ $item->title }}">
                                            {{ $item->title }} ({{ $item->formatted_size }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="upload-field" data-type="audio">
                                <label class="form-label">Audio File <span class="required">*</span></label>
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
                        </div>

                        <!-- Image Fields -->
                        <div class="type-fields" id="fields-image">
                            <div class="type-help">
                                <i class="fas fa-image"></i>
                                <p>Upload an image or select from your library. Supported formats: JPG, PNG, GIF, WebP.</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Source</label>
                                <select class="form-select library-source-select" data-type="image">
                                    <option value="">Upload New Image</option>
                                    @foreach ($libraryItems['image'] as $item)
                                        <option value="{{ $item->id }}" data-title="{{ $item->title }}">
                                            {{ $item->title }} ({{ $item->formatted_size }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="upload-field" data-type="image">
                                <label class="form-label">Image File <span class="required">*</span></label>
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
                        </div>

                        <!-- External Link Fields -->
                        <div class="type-fields" id="fields-external_link">
                            <div class="type-help">
                                <i class="fas fa-external-link-alt"></i>
                                <p>Add an external link to a resource on another website. Students will be able to open this
                                    link in a new tab.</p>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">External URL <span class="required">*</span></label>
                                <input type="url" name="external_url" class="form-control"
                                    placeholder="https://example.com/resource">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h6 class="section-title">Settings</h6>

                        <div class="mb-4">
                            <label class="form-label">Estimated Duration</label>
                            <div class="input-group">
                                <input type="number" name="estimated_duration" class="form-control" min="1"
                                    placeholder="Enter duration" value="{{ old('estimated_duration') }}">
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
                                    value="1" checked style="width: 40px; height: 20px;">
                            </div>
                        </div>

                        <h6 class="section-title mt-4">Module Info</h6>

                        <div class="info-item">
                            <small>Module</small>
                            <div class="value">{{ $module->title }}</div>
                        </div>
                        <div class="info-item">
                            <small>Course</small>
                            <div class="value">{{ $module->course->title }}</div>
                        </div>
                        <div class="info-item">
                            <small>Existing Content</small>
                            <div class="value">{{ $module->contentItems->count() }} items</div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('lms.modules.edit', $module) }}" class="btn btn-secondary">
                        <i class="bx bx-x"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Add Content</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Adding...
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
            initializeContentTypeSelection();
            initializeFormValidation();
            initializeFileInputs();
            initializeLibrarySourceDropdowns();
        });

        function initializeContentTypeSelection() {
            document.querySelectorAll('.content-type-card').forEach(card => {
                card.addEventListener('click', function() {
                    document.querySelectorAll('.content-type-card').forEach(c => c.classList.remove(
                        'selected'));
                    this.classList.add('selected');

                    document.querySelectorAll('.type-fields').forEach(f => f.classList.remove('active'));

                    const type = this.dataset.type;
                    const fields = document.getElementById('fields-' + type);
                    if (fields) {
                        fields.classList.add('active');
                    }

                    // Reset library item selection when changing content type
                    document.getElementById('libraryItemId').value = '';
                });
            });

            // Select first option by default
            document.querySelector('.content-type-card').click();
        }

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

        function initializeLibrarySourceDropdowns() {
            const dropdowns = document.querySelectorAll('.library-source-select');

            dropdowns.forEach(dropdown => {
                dropdown.addEventListener('change', function() {
                    const type = this.dataset.type;
                    const uploadField = document.querySelector(`.upload-field[data-type="${type}"]`);
                    const libraryItemId = this.value;

                    if (libraryItemId) {
                        // Library item selected - hide upload field
                        uploadField.style.display = 'none';
                        document.getElementById('libraryItemId').value = libraryItemId;

                        // Pre-fill title if empty
                        const titleInput = document.querySelector('input[name="title"]');
                        const selectedOption = this.options[this.selectedIndex];
                        if (!titleInput.value && selectedOption.dataset.title) {
                            titleInput.value = selectedOption.dataset.title;
                        }
                    } else {
                        // Upload new selected - show upload field
                        uploadField.style.display = 'block';
                        document.getElementById('libraryItemId').value = '';
                    }
                });
            });
        }
    </script>
@endsection
