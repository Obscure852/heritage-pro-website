@extends('layouts.master')

@section('title', ($video->contentItemMorph?->title ?? $video->original_filename) . ' - Video Details')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('title')
            Video Details
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-video me-2"></i>{{ $video->contentItemMorph?->title ?? $video->original_filename }}
                    </h5>
                    <div>
                        <a href="{{ route('lms.videos.edit', $video) }}" class="btn btn-light btn-sm me-2">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        @if($video->contentItemMorph?->module)
                            <a href="{{ route('lms.modules.edit', $video->contentItemMorph->module) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Back to Module
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="ratio ratio-16x9 bg-dark">
                        @if($video->isYouTube())
                            <iframe 
                                src="{{ $video->getYouTubeEmbedUrl() }}"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        @elseif($video->isTranscoding())
                            <div class="d-flex flex-column justify-content-center align-items-center text-white">
                                <i class="fas fa-cog fa-spin fa-3x mb-3"></i>
                                <h5>Video Processing</h5>
                                <p class="text-muted">This video is being processed...</p>
                            </div>
                        @else
                            <video controls poster="{{ $video->thumbnail_path ? Storage::disk('public')->url($video->thumbnail_path) : '' }}">
                                @if($video->qualities->count())
                                    @php $defaultQuality = $video->qualities->where('is_default', true)->first() ?? $video->qualities->sortByDesc('height')->first(); @endphp
                                    <source src="{{ $defaultQuality->url }}" type="video/mp4">
                                @else
                                    <source src="{{ Storage::disk('public')->url($video->file_path) }}" type="{{ $video->mime_type }}">
                                @endif
                                Your browser does not support the video tag.
                            </video>
                        @endif
                    </div>
                </div>
            </div>

            @if($video->contentItemMorph?->description)
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-align-left me-2"></i>Description</h6>
                    </div>
                    <div class="card-body">
                        {{ $video->contentItemMorph->description }}
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Video Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width: 40%;">Source:</td>
                            <td>
                                @if($video->isYouTube())
                                    <span class="badge bg-danger"><i class="fab fa-youtube me-1"></i>YouTube</span>
                                @else
                                    <span class="badge bg-primary"><i class="fas fa-upload me-1"></i>Uploaded</span>
                                @endif
                            </td>
                        </tr>
                        @if($video->isUpload())
                            <tr>
                                <td class="text-muted">File Name:</td>
                                <td>{{ $video->original_filename }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">File Size:</td>
                                <td>{{ number_format($video->file_size_bytes / 1048576, 2) }} MB</td>
                            </tr>
                            @if($video->duration_seconds)
                                <tr>
                                    <td class="text-muted">Duration:</td>
                                    <td>{{ gmdate('H:i:s', $video->duration_seconds) }}</td>
                                </tr>
                            @endif
                            @if($video->width && $video->height)
                                <tr>
                                    <td class="text-muted">Resolution:</td>
                                    <td>{{ $video->width }} x {{ $video->height }}</td>
                                </tr>
                            @endif
                        @else
                            <tr>
                                <td class="text-muted">YouTube ID:</td>
                                <td><code>{{ $video->source_id }}</code></td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Uploaded By:</td>
                            <td>{{ $video->uploader?->name ?? 'Unknown' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $video->created_at->format('M j, Y g:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($video->isUpload())
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Transcoding Status</h6>
                    </div>
                    <div class="card-body">
                        @if($video->transcoding_status === 'completed')
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle me-2"></i>All transcoding jobs complete
                            </div>
                            @if($video->qualities->count())
                                <h6 class="text-muted mb-2">Available Qualities:</h6>
                                @foreach($video->qualities->sortByDesc('height') as $quality)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>
                                            <i class="fas fa-video text-primary me-2"></i>
                                            {{ $quality->label }}
                                        </span>
                                        <span class="badge bg-secondary">
                                            {{ number_format($quality->file_size / 1048576, 1) }} MB
                                        </span>
                                    </div>
                                @endforeach
                            @endif
                        @elseif($video->transcoding_status === 'processing')
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-spinner fa-spin me-2"></i>Transcoding in progress...
                            </div>
                            @foreach($video->transcodingJobs as $job)
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>{{ $job->format }}</small>
                                        <small>{{ $job->progress }}%</small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ $job->progress }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        @elseif($video->transcoding_status === 'failed')
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>Transcoding failed
                                <a href="{{ route('lms.videos.edit', $video) }}" class="alert-link">Retry</a>
                            </div>
                        @else
                            <p class="text-muted mb-0">Video has not been transcoded yet.</p>
                            <a href="{{ route('lms.videos.edit', $video) }}" class="btn btn-info btn-sm mt-2">
                                <i class="fas fa-cog me-1"></i>Configure Transcoding
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-trash me-2"></i>Danger Zone</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Deleting this video will remove all associated files and progress data.
                    </p>
                    <form action="{{ route('lms.videos.destroy', $video) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this video? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="fas fa-trash me-1"></i>Delete Video
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
