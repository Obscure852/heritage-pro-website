@extends('layouts.master')

@section('title', 'Edit Video - ' . ($video->contentItemMorph?->title ?? $video->original_filename))

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.videos.show', $video) }}">Video Details</a>
        @endslot
        @slot('title')
            Edit Video
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Video
                    </h5>
                    <a href="{{ route('lms.videos.show', $video) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Video
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('lms.videos.update', $video) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label fw-bold">Video Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $video->contentItemMorph?->title ?? $video->original_filename) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="3">{{ old('description', $video->contentItemMorph?->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Completion Threshold</label>
                            <div class="input-group" style="max-width: 200px;">
                                <input type="number" name="completion_threshold" 
                                       class="form-control @error('completion_threshold') is-invalid @enderror"
                                       value="{{ old('completion_threshold', $video->completion_threshold ?? 90) }}"
                                       min="50" max="100" required>
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Percentage of video that must be watched to mark as complete (50-100%).</div>
                            @error('completion_threshold')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Update Thumbnail</label>
                            @if($video->thumbnail_path)
                                <div class="mb-2">
                                    <img src="{{ Storage::disk('public')->url($video->thumbnail_path) }}" 
                                         alt="Current thumbnail" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                            @endif
                            <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror"
                                   accept="image/*">
                            <div class="form-text">Leave empty to keep current thumbnail.</div>
                            @error('thumbnail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('lms.videos.show', $video) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Video Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Source Type:</td>
                            <td>
                                @if($video->isYouTube())
                                    <span class="badge bg-danger"><i class="fab fa-youtube me-1"></i>YouTube</span>
                                @else
                                    <span class="badge bg-primary"><i class="fas fa-upload me-1"></i>Uploaded</span>
                                @endif
                            </td>
                        </tr>
                        @if($video->isYouTube())
                            <tr>
                                <td class="text-muted">YouTube ID:</td>
                                <td><code>{{ $video->source_id }}</code></td>
                            </tr>
                        @else
                            <tr>
                                <td class="text-muted">File:</td>
                                <td>{{ $video->original_filename }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Size:</td>
                                <td>{{ number_format($video->file_size_bytes / 1048576, 2) }} MB</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Duration:</td>
                                <td>{{ $video->formatted_duration ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Resolution:</td>
                                <td>{{ $video->width }}x{{ $video->height }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Uploaded:</td>
                            <td>{{ $video->created_at->format('M j, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($video->isUpload())
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Transcoding</h6>
                    </div>
                    <div class="card-body">
                        @if($video->transcoding_status === 'completed')
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle me-2"></i>Transcoding Complete
                            </div>
                            @if($video->qualities->count())
                                <h6 class="text-muted mb-2">Available Qualities:</h6>
                                <ul class="list-unstyled mb-0">
                                    @foreach($video->qualities->sortByDesc('height') as $quality)
                                        <li class="mb-1">
                                            <i class="fas fa-video text-primary me-2"></i>
                                            {{ $quality->label }} ({{ number_format($quality->file_size / 1048576, 1) }} MB)
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        @elseif($video->transcoding_status === 'processing')
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-spinner fa-spin me-2"></i>Transcoding in progress...
                            </div>
                        @elseif($video->transcoding_status === 'failed')
                            <div class="alert alert-danger mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>Transcoding failed
                            </div>
                            <form action="{{ route('lms.videos.transcode', $video) }}" method="POST">
                                @csrf
                                <input type="hidden" name="formats[]" value="720p">
                                <input type="hidden" name="formats[]" value="480p">
                                <input type="hidden" name="formats[]" value="360p">
                                <button type="submit" class="btn btn-warning btn-sm w-100">
                                    <i class="fas fa-redo me-1"></i>Retry Transcoding
                                </button>
                            </form>
                        @else
                            <p class="text-muted mb-3">Video has not been transcoded.</p>
                            <form action="{{ route('lms.videos.transcode', $video) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="formats[]" value="1080p" id="f1080">
                                        <label class="form-check-label" for="f1080">1080p</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="formats[]" value="720p" id="f720" checked>
                                        <label class="form-check-label" for="f720">720p</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="formats[]" value="480p" id="f480" checked>
                                        <label class="form-check-label" for="f480">480p</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="formats[]" value="360p" id="f360" checked>
                                        <label class="form-check-label" for="f360">360p</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-info btn-sm w-100">
                                    <i class="fas fa-cog me-1"></i>Start Transcoding
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
