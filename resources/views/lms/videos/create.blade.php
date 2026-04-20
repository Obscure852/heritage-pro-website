@extends('layouts.master')

@section('title', 'Upload Video - ' . $module->title)

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.modules.edit', $module) }}">{{ Str::limit($module->title, 20) }}</a>
        @endslot
        @slot('title')
            Upload Video
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-video me-2"></i>Upload Video
                    </h5>
                    <a href="{{ route('lms.modules.edit', $module) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Module
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('lms.videos.store', $module) }}" method="POST" enctype="multipart/form-data" id="videoUploadForm">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold">Video Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Video Source <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="source_type" id="sourceUpload" value="upload"
                                       {{ old('source_type', 'upload') === 'upload' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="sourceUpload">
                                    <i class="fas fa-upload me-2"></i>Upload File
                                </label>

                                <input type="radio" class="btn-check" name="source_type" id="sourceYoutube" value="youtube"
                                       {{ old('source_type') === 'youtube' ? 'checked' : '' }}>
                                <label class="btn btn-outline-danger" for="sourceYoutube">
                                    <i class="fab fa-youtube me-2"></i>YouTube URL
                                </label>
                            </div>
                        </div>

                        <div id="uploadSection" class="mb-4">
                            <label class="form-label fw-bold">Video File <span class="text-danger">*</span></label>
                            <div class="upload-zone border border-2 border-dashed rounded p-5 text-center" id="dropZone">
                                <input type="file" name="video_file" id="videoFile" class="d-none"
                                       accept="video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm">
                                <div class="upload-placeholder" id="uploadPlaceholder">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h5>Drag and drop video here</h5>
                                    <p class="text-muted mb-3">or click to browse</p>
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('videoFile').click()">
                                        <i class="fas fa-folder-open me-2"></i>Choose File
                                    </button>
                                    <p class="text-muted small mt-3 mb-0">
                                        Supported formats: MP4, MOV, AVI, MKV, WebM (Max 2GB)
                                    </p>
                                </div>
                                <div class="upload-preview d-none" id="uploadPreview">
                                    <i class="fas fa-file-video fa-3x text-primary mb-3"></i>
                                    <h5 id="fileName">video.mp4</h5>
                                    <p class="text-muted mb-2" id="fileSize">0 MB</p>
                                    <button type="button" class="btn btn-outline-danger btn-sm" id="removeFile">
                                        <i class="fas fa-times me-1"></i>Remove
                                    </button>
                                </div>
                            </div>
                            @error('video_file')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror

                            <div class="progress mt-3 d-none" id="uploadProgress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                     style="width: 0%"></div>
                            </div>
                        </div>

                        <div id="youtubeSection" class="mb-4 d-none">
                            <label class="form-label fw-bold">YouTube URL <span class="text-danger">*</span></label>
                            <input type="url" name="youtube_url" id="youtubeUrl"
                                   class="form-control @error('youtube_url') is-invalid @enderror"
                                   value="{{ old('youtube_url') }}"
                                   placeholder="https://www.youtube.com/watch?v=...">
                            @error('youtube_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="youtube-preview mt-3 d-none" id="youtubePreview">
                                <div class="ratio ratio-16x9">
                                    <iframe id="youtubeEmbed" src="" allowfullscreen></iframe>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Thumbnail (Optional)</label>
                            <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror"
                                   accept="image/*">
                            <div class="form-text">If not provided, a thumbnail will be generated automatically for uploaded videos.</div>
                            @error('thumbnail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="transcodingSection" class="mb-4 p-3 bg-light rounded">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="autoTranscode" name="auto_transcode" value="1"
                                       {{ old('auto_transcode', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="autoTranscode">
                                    <i class="fas fa-cogs me-2"></i>Enable Video Transcoding
                                </label>
                            </div>
                            <p class="text-muted small mb-3">
                                Transcoding creates multiple quality versions for adaptive streaming.
                            </p>

                            <div id="transcodingOptions">
                                <label class="form-label">Quality Versions</label>
                                <div class="row">
                                    <div class="col-6 col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="transcode_formats[]"
                                                   value="1080p" id="quality1080">
                                            <label class="form-check-label" for="quality1080">
                                                1080p (Full HD)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="transcode_formats[]"
                                                   value="720p" id="quality720" checked>
                                            <label class="form-check-label" for="quality720">
                                                720p (HD)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="transcode_formats[]"
                                                   value="480p" id="quality480" checked>
                                            <label class="form-check-label" for="quality480">
                                                480p (SD)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="transcode_formats[]"
                                                   value="360p" id="quality360" checked>
                                            <label class="form-check-label" for="quality360">
                                                360p (Mobile)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('lms.modules.edit', $module) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-upload me-2"></i>Upload Video
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .upload-zone {
        border-color: #dee2e6 !important;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .upload-zone:hover, .upload-zone.dragover {
        border-color: #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.05);
    }
    .border-dashed {
        border-style: dashed !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sourceUpload = document.getElementById('sourceUpload');
    const sourceYoutube = document.getElementById('sourceYoutube');
    const uploadSection = document.getElementById('uploadSection');
    const youtubeSection = document.getElementById('youtubeSection');
    const transcodingSection = document.getElementById('transcodingSection');
    const dropZone = document.getElementById('dropZone');
    const videoFile = document.getElementById('videoFile');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const uploadPreview = document.getElementById('uploadPreview');
    const youtubeUrl = document.getElementById('youtubeUrl');
    const youtubePreview = document.getElementById('youtubePreview');
    const youtubeEmbed = document.getElementById('youtubeEmbed');
    const autoTranscode = document.getElementById('autoTranscode');
    const transcodingOptions = document.getElementById('transcodingOptions');

    function toggleSource() {
        if (sourceYoutube.checked) {
            uploadSection.classList.add('d-none');
            youtubeSection.classList.remove('d-none');
            transcodingSection.classList.add('d-none');
            videoFile.removeAttribute('required');
        } else {
            uploadSection.classList.remove('d-none');
            youtubeSection.classList.add('d-none');
            transcodingSection.classList.remove('d-none');
        }
    }

    sourceUpload.addEventListener('change', toggleSource);
    sourceYoutube.addEventListener('change', toggleSource);

    autoTranscode.addEventListener('change', function() {
        transcodingOptions.style.display = this.checked ? 'block' : 'none';
    });

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, e => {
            e.preventDefault();
            e.stopPropagation();
        });
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'));
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'));
    });

    dropZone.addEventListener('drop', e => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            videoFile.files = files;
            showFilePreview(files[0]);
        }
    });

    dropZone.addEventListener('click', e => {
        if (e.target === dropZone || e.target.closest('.upload-placeholder')) {
            videoFile.click();
        }
    });

    videoFile.addEventListener('change', function() {
        if (this.files.length > 0) {
            showFilePreview(this.files[0]);
        }
    });

    function showFilePreview(file) {
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatFileSize(file.size);
        uploadPlaceholder.classList.add('d-none');
        uploadPreview.classList.remove('d-none');
    }

    document.getElementById('removeFile').addEventListener('click', function(e) {
        e.stopPropagation();
        videoFile.value = '';
        uploadPlaceholder.classList.remove('d-none');
        uploadPreview.classList.add('d-none');
    });

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    let youtubeTimeout;
    youtubeUrl.addEventListener('input', function() {
        clearTimeout(youtubeTimeout);
        youtubeTimeout = setTimeout(() => {
            const videoId = extractYoutubeId(this.value);
            if (videoId) {
                youtubeEmbed.src = 'https://www.youtube.com/embed/' + videoId;
                youtubePreview.classList.remove('d-none');
            } else {
                youtubePreview.classList.add('d-none');
            }
        }, 500);
    });

    function extractYoutubeId(url) {
        const patterns = [
            /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&?/]+)/,
            /^([a-zA-Z0-9_-]{11})$/
        ];
        for (const pattern of patterns) {
            const match = url.match(pattern);
            if (match) return match[1];
        }
        return null;
    }

    toggleSource();
});
</script>
@endpush
