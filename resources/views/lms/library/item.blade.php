@extends('layouts.master')

@section('title', $item->title . ' - Content Library')

@section('css')
    <style>
        .page-container {
            background: white;
            border-radius: 3px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-header-left {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .item-icon {
            width: 56px;
            height: 56px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .item-icon.video {
            background: #fef3c7;
            color: #d97706;
        }

        .item-icon.image {
            background: #dbeafe;
            color: #2563eb;
        }

        .item-icon.document {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .item-icon.pdf {
            background: #fee2e2;
            color: #dc2626;
        }

        .item-icon.audio {
            background: #d1fae5;
            color: #059669;
        }

        .item-icon.presentation {
            background: #fce7f3;
            color: #db2777;
        }

        .item-icon.spreadsheet {
            background: #d1fae5;
            color: #059669;
        }

        .item-icon i {
            font-size: 24px;
        }

        .page-header-info h1 {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 6px 0;
        }

        .page-header-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 14px;
            color: #6b7280;
        }

        .page-header-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .visibility-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }

        .visibility-badge.public {
            background: #d1fae5;
            color: #047857;
        }

        .visibility-badge.shared {
            background: #fef3c7;
            color: #b45309;
        }

        .visibility-badge.private {
            background: #e5e7eb;
            color: #4b5563;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-outline {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-outline:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            color: #1f2937;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        /* Main Content Grid - Preview + Sidebar */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 24px;
            margin-bottom: 24px;
        }

        @media (max-width: 1200px) {
            .main-grid {
                grid-template-columns: 1fr 260px;
            }
        }

        @media (max-width: 992px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        .main-sidebar {
            align-self: start;
        }

        /* Preview Section */
        .preview-container {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .preview-container video,
        .preview-container audio {
            width: 100%;
            display: block;
        }

        .preview-container img {
            width: 100%;
            height: auto;
            display: block;
        }

        .preview-container iframe {
            width: 100%;
            height: 550px;
            border: none;
            display: block;
        }

        .preview-placeholder {
            padding: 60px 20px;
            text-align: center;
        }

        .preview-placeholder i {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .preview-placeholder h3 {
            font-size: 18px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 8px 0;
        }

        .preview-placeholder p {
            color: #6b7280;
            margin: 0 0 20px 0;
        }

        .audio-preview {
            padding: 40px 20px;
            text-align: center;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }

        .audio-preview i {
            font-size: 48px;
            color: #059669;
            margin-bottom: 20px;
        }

        .audio-preview audio {
            max-width: 100%;
            margin: 0 auto;
        }

        /* Bottom Info Section - Match main-grid layout */
        .bottom-info {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 24px;
        }

        @media (max-width: 1200px) {
            .bottom-info {
                grid-template-columns: 1fr 260px;
            }
        }

        @media (max-width: 992px) {
            .bottom-info {
                grid-template-columns: 1fr;
            }
        }

        .bottom-info .info-card {
            margin-bottom: 0;
        }

        /* Section Title */
        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Info Card */
        .info-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .info-card h4 {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .info-card p {
            font-size: 14px;
            color: #4b5563;
            margin: 0;
            line-height: 1.5;
        }

        /* Description - Compact */
        .info-card.description-card {
            padding: 14px 16px;
        }

        .info-card.description-card p {
            font-size: 13px;
            line-height: 1.5;
        }

        /* Details List */
        .details-list {
            margin: 0;
            padding: 0;
        }

        .details-list dt {
            font-size: 11px;
            font-weight: 500;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .details-list dd {
            font-size: 13px;
            color: #1f2937;
            margin: 0 0 12px 0;
            padding-bottom: 12px;
            border-bottom: 1px solid #f3f4f6;
        }

        .details-list dd:last-of-type {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .details-list code {
            background: #e5e7eb;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
        }

        /* Tags */
        .tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tag-item {
            padding: 4px 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            font-size: 13px;
            color: #4b5563;
            text-decoration: none;
            transition: all 0.2s;
        }

        .tag-item:hover {
            border-color: #3b82f6;
            color: #2563eb;
            background: #eff6ff;
        }

        /* Collection Link */
        .collection-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .collection-link:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .collection-link i {
            font-size: 24px;
        }

        .collection-link span {
            font-size: 14px;
            font-weight: 500;
            color: #1f2937;
        }

        /* Usage Section */
        .usage-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .usage-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .usage-list li:last-child {
            margin-bottom: 0;
        }

        .usage-list .usage-title {
            color: #1f2937;
        }

        .usage-list .usage-date {
            color: #9ca3af;
            font-size: 12px;
        }

        /* Version History */
        .version-table {
            width: 100%;
            border-collapse: collapse;
        }

        .version-table th,
        .version-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .version-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .version-table td {
            color: #4b5563;
        }

        .version-badge {
            display: inline-block;
            padding: 2px 8px;
            background: #e5e7eb;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
            color: #4b5563;
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 3px;
            border: none;
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        /* File Upload */
        .file-upload-area {
            display: block;
            width: 100%;
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #f9fafb;
        }

        .file-upload-area:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .file-upload-area input[type="file"] {
            display: none;
        }

        .file-upload-area i {
            font-size: 32px;
            color: #3b82f6;
            margin-bottom: 12px;
        }

        .file-upload-area .upload-text {
            font-size: 14px;
            color: #374151;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .file-upload-area .upload-hint {
            font-size: 13px;
            color: #6b7280;
        }

        .file-upload-area .file-name {
            font-size: 14px;
            color: #3b82f6;
            font-weight: 500;
            margin-top: 8px;
        }

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

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 16px;
            }

            .page-header-left {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                width: 100%;
                flex-wrap: wrap;
            }

            .header-actions .btn {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('lms.library.index') }}">Content Library</a>
        @endslot
        @slot('title')
            Library Item
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="page-container">
            <!-- Header -->
            <div class="page-header">
                <div class="page-header-left">
                    <div class="item-icon {{ $item->type }}">
                        @include('lms.library.partials.type-icon', ['type' => $item->type])
                    </div>
                    <div class="page-header-info">
                        <h1>{{ $item->title }}</h1>
                        <div class="page-header-meta">
                            <span><i class="fas fa-file"></i> {{ ucfirst($item->type) }}</span>
                            <span><i class="fas fa-hdd"></i> {{ $item->human_file_size }}</span>
                            <span><i class="fas fa-calendar"></i> {{ $item->created_at->format('M j, Y') }}</span>
                            <span class="visibility-badge {{ $item->visibility }}">{{ ucfirst($item->visibility) }}</span>
                        </div>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="{{ Storage::url($item->file_path) }}" class="btn btn-primary" download>
                        <i class="fas fa-download"></i> Download
                    </a>
                    <form action="{{ route('lms.library.toggle-favorite', $item) }}" method="POST"
                        style="display: inline;">
                        @csrf
                        <button type="submit" class="btn {{ $isFavorite ? 'btn-warning' : 'btn-outline' }}">
                            <i class="fas fa-star"></i> {{ $isFavorite ? 'Favorited' : 'Favorite' }}
                        </button>
                    </form>
                    @if ($item->created_by === auth()->id() || auth()->user()->can('manage-lms-content'))
                        <a href="{{ route('lms.library.edit', $item) }}" class="btn btn-outline">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endif
                </div>
            </div>

            <!-- Main Grid: Preview + File Details Side by Side -->
            <div class="main-grid">
                <!-- Preview -->
                <div class="preview-container">
                    @if ($item->type === 'video')
                        <video controls>
                            <source src="{{ Storage::url($item->file_path) }}" type="{{ $item->mime_type }}">
                            Your browser does not support the video tag.
                        </video>
                    @elseif($item->type === 'audio')
                        <div class="audio-preview">
                            <i class="fas fa-music"></i>
                            <audio controls>
                                <source src="{{ Storage::url($item->file_path) }}" type="{{ $item->mime_type }}">
                                Your browser does not support the audio tag.
                            </audio>
                        </div>
                    @elseif($item->type === 'image')
                        <img src="{{ Storage::url($item->file_path) }}" alt="{{ $item->title }}">
                    @elseif($item->type === 'pdf')
                        <iframe src="{{ Storage::url($item->file_path) }}"></iframe>
                    @else
                        <div class="preview-placeholder">
                            @include('lms.library.partials.type-icon', ['type' => $item->type])
                            <h3>{{ $item->file_name }}</h3>
                            <p>Preview not available for this file type</p>
                            <a href="{{ Storage::url($item->file_path) }}" class="btn btn-primary" download>
                                <i class="fas fa-download"></i> Download to View
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Sidebar: File Details + Collection -->
                <div class="main-sidebar">
                    <div class="info-card">
                        <h4>File Details</h4>
                        <dl class="details-list">
                            <dt>File Name</dt>
                            <dd title="{{ $item->file_name }}"
                                style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $item->file_name }}</dd>

                            <dt>Type</dt>
                            <dd>{{ ucfirst($item->type) }}</dd>

                            <dt>Size</dt>
                            <dd>{{ $item->human_file_size }}</dd>

                            <dt>MIME Type</dt>
                            <dd><code>{{ $item->mime_type }}</code></dd>

                            <dt>Uploaded</dt>
                            <dd>{{ $item->created_at->format('M j, Y g:i A') }}</dd>

                            <dt>Uploaded By</dt>
                            <dd>{{ $item->creator?->full_name ?? 'Unknown' }}</dd>

                            @if ($item->usage_count > 0)
                                <dt>Times Used</dt>
                                <dd>{{ $item->usage_count }}</dd>
                            @endif
                        </dl>
                    </div>

                    @if ($item->collection)
                        <div class="info-card">
                            <h4>Collection</h4>
                            <a href="{{ route('lms.library.collection', $item->collection) }}" class="collection-link">
                                <i class="fas fa-folder" style="color: {{ $item->collection->color ?? '#6c757d' }}"></i>
                                <span>{{ $item->collection->name }}</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Bottom Section: Description + Tags (matching preview width) -->
            @if ($item->description || $item->tags->count())
                <div class="bottom-info">
                    <div>
                        @if ($item->description)
                            <div class="info-card description-card">
                                <h4>Description</h4>
                                <p>{{ $item->description }}</p>
                            </div>
                        @endif
                    </div>
                    <div>
                        @if ($item->tags->count())
                            <div class="info-card">
                                <h4>Tags</h4>
                                <div class="tags-list">
                                    @foreach ($item->tags as $tag)
                                        <a href="{{ route('lms.library.index', ['tag' => $tag->slug]) }}" class="tag-item">
                                            {{ $tag->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Version History -->
            @if ($item->versions->count())
                <div class="info-card" style="margin-top: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h4 style="margin: 0;">Version History</h4>
                        @if ($item->created_by === auth()->id())
                            <button class="btn btn-sm btn-outline" data-bs-toggle="modal"
                                data-bs-target="#uploadVersionModal">
                                <i class="fas fa-plus"></i> New Version
                            </button>
                        @endif
                    </div>
                    <table class="version-table">
                        <thead>
                            <tr>
                                <th>Version</th>
                                <th>Size</th>
                                <th>Notes</th>
                                <th>Date</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($item->versions->sortByDesc('version_number') as $version)
                                <tr>
                                    <td><span class="version-badge">v{{ $version->version_number }}</span></td>
                                    <td>{{ $version->human_file_size }}</td>
                                    <td>{{ $version->change_notes ?? '-' }}</td>
                                    <td>{{ $version->created_at->format('M j, Y') }}</td>
                                    <td>{{ $version->creator?->full_name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Usage -->
            @if ($item->usages->count())
                <div class="info-card" style="margin-top: 16px;">
                    <h4>Used In</h4>
                    <ul class="usage-list">
                        @foreach ($item->usages as $usage)
                            <li>
                                <span class="usage-title">
                                    @if ($usage->usable)
                                        {{ class_basename($usage->usable_type) }}:
                                        {{ $usage->usable->title ?? ($usage->usable->name ?? 'Unknown') }}
                                    @else
                                        {{ $usage->usable_type }} #{{ $usage->usable_id }}
                                    @endif
                                </span>
                                <span class="usage-date">{{ $usage->created_at->format('M j, Y') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <!-- Upload Version Modal -->
    @if ($item->created_by === auth()->id())
        <div class="modal fade" id="uploadVersionModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('lms.library.upload-version', $item) }}" method="POST"
                        enctype="multipart/form-data" class="needs-validation" novalidate>
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-file-upload me-2"></i>Upload New Version</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">File <span class="text-danger">*</span></label>
                                <label class="file-upload-area" for="versionFile">
                                    <input type="file" name="file" id="versionFile" required>
                                    <i class="fas fa-file-upload"></i>
                                    <div class="upload-text">Click to choose the updated file</div>
                                    <div class="upload-hint">Select a new version of this file</div>
                                    <div class="file-name d-none" id="versionFileName"></div>
                                </label>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Change Notes</label>
                                <textarea name="change_notes" class="form-control" rows="2" placeholder="What changed in this version?"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-upload"></i> Upload Version</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Uploading...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeFileInput();
            initializeFormValidation();
        });

        function initializeFileInput() {
            const fileInput = document.getElementById('versionFile');
            const fileName = document.getElementById('versionFileName');
            const uploadArea = fileInput?.closest('.file-upload-area');

            if (fileInput && uploadArea) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        fileName.textContent = this.files[0].name;
                        fileName.classList.remove('d-none');
                        uploadArea.style.borderColor = '#3b82f6';
                        uploadArea.style.background = '#eff6ff';
                    } else {
                        fileName.classList.add('d-none');
                        uploadArea.style.borderColor = '';
                        uploadArea.style.background = '';
                    }
                });

                uploadArea.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.style.borderColor = '#3b82f6';
                    this.style.background = '#eff6ff';
                });

                uploadArea.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    if (!fileInput.files.length) {
                        this.style.borderColor = '';
                        this.style.background = '';
                    }
                });

                uploadArea.addEventListener('drop', function(e) {
                    e.preventDefault();
                    if (e.dataTransfer.files.length) {
                        fileInput.files = e.dataTransfer.files;
                        fileInput.dispatchEvent(new Event('change'));
                    }
                });
            }
        }

        function initializeFormValidation() {
            const forms = document.querySelectorAll('.needs-validation');

            forms.forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    } else {
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
    </script>
@endsection
