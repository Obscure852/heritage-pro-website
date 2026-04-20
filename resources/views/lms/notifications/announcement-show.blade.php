@extends('layouts.master')

@section('title', $announcement->title)

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.announcements') }}">Announcements</a>
        @endslot
        @slot('title')
            {{ Str::limit($announcement->title, 30) }}
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm {{ $announcement->is_pinned ? 'border-warning' : '' }}">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="mb-2">
                                @if($announcement->is_pinned)
                                    <i class="fas fa-thumbtack text-warning me-2"></i>
                                @endif
                                {{ $announcement->title }}
                            </h4>
                            <div class="text-muted small">
                                <span class="badge {{ $announcement->priority_badge }} me-2">{{ ucfirst($announcement->priority) }}</span>
                                @if($announcement->course)
                                    <span class="badge bg-info me-2">{{ $announcement->course->title }}</span>
                                @else
                                    <span class="badge bg-secondary me-2">Global</span>
                                @endif
                                <i class="fas fa-calendar me-1"></i>{{ $announcement->published_at->format('M j, Y g:i A') }}
                                &bull;
                                <i class="fas fa-user me-1"></i>{{ $announcement->author->name }}
                            </div>
                        </div>
                        @can('manage-lms-content')
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a href="{{ route('lms.announcements.create', ['edit' => $announcement->id]) }}" class="dropdown-item">
                                            <i class="fas fa-edit me-2"></i>Edit
                                        </a>
                                    </li>
                                    @if(!$announcement->is_published)
                                        <li>
                                            <form action="{{ route('lms.announcements.publish', $announcement) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-paper-plane me-2"></i>Publish Now
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('lms.announcements.delete', $announcement) }}" method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this announcement?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="announcement-content">
                        {!! nl2br(e($announcement->content)) !!}
                    </div>

                    @if($announcement->expires_at)
                        <div class="mt-4 pt-3 border-top">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                @if($announcement->expires_at->isPast())
                                    This announcement expired on {{ $announcement->expires_at->format('M j, Y g:i A') }}
                                @else
                                    This announcement expires on {{ $announcement->expires_at->format('M j, Y g:i A') }}
                                @endif
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            @can('manage-lms-content')
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Read Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="h3 mb-0 text-primary">{{ $announcement->total_readers ?? 0 }}</div>
                                <small class="text-muted">Total Readers</small>
                            </div>
                            <div class="col-md-4">
                                <div class="h3 mb-0 text-success">{{ $announcement->reads_count ?? 0 }}</div>
                                <small class="text-muted">Marked as Read</small>
                            </div>
                            <div class="col-md-4">
                                @php
                                    $readRate = ($announcement->total_readers ?? 0) > 0
                                        ? round(($announcement->reads_count ?? 0) / $announcement->total_readers * 100)
                                        : 0;
                                @endphp
                                <div class="h3 mb-0 text-info">{{ $readRate }}%</div>
                                <small class="text-muted">Read Rate</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            @if($relatedAnnouncements->count())
                <div class="mt-4">
                    <h6 class="mb-3"><i class="fas fa-list me-2"></i>Related Announcements</h6>
                    @foreach($relatedAnnouncements as $related)
                        <a href="{{ route('lms.announcements.show', $related) }}" class="text-decoration-none">
                            <div class="card shadow-sm mb-2 card-hover">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            @if($related->is_pinned)
                                                <i class="fas fa-thumbtack text-warning me-1"></i>
                                            @endif
                                            <span class="text-dark">{{ $related->title }}</span>
                                        </div>
                                        <small class="text-muted">{{ $related->published_at->format('M j, Y') }}</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.card-hover:hover {
    background-color: #f8f9fa;
}
.announcement-content {
    font-size: 1.05rem;
    line-height: 1.7;
}
</style>
@endsection
