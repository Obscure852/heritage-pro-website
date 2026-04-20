@extends('layouts.master')

@section('title', 'Announcements')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('title')
            Announcements
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="help-text mb-4" style="background: #f8f9fa; padding: 12px 16px; border-left: 4px solid #3b82f6; border-radius: 0 3px 3px 0;">
        <div class="help-title" style="font-weight: 600; color: #374151; margin-bottom: 4px;">Course Announcements</div>
        <div class="help-content" style="color: #6b7280; font-size: 13px; line-height: 1.5;">
            View important announcements from your instructors. Filter by course to see specific announcements or view all at once.
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Announcements</h4>
        </div>
        <div class="col-md-6 text-md-end">
            @if($courses->count())
                <select class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="window.location.href=this.value">
                    <option value="{{ route('lms.announcements') }}">All Announcements</option>
                    @foreach($courses as $c)
                        <option value="{{ route('lms.announcements', ['course_id' => $c->id]) }}" {{ $courseId == $c->id ? 'selected' : '' }}>
                            {{ $c->title }}
                        </option>
                    @endforeach
                </select>
            @endif
            @can('manage-lms-content')
                <a href="{{ route('lms.announcements.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>New Announcement
                </a>
            @endcan
        </div>
    </div>

    <div class="row">
        @forelse($announcements as $announcement)
            <div class="col-12 mb-3">
                <div class="card shadow-sm {{ $announcement->is_pinned ? 'border-warning' : '' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title mb-1">
                                    @if($announcement->is_pinned)
                                        <i class="fas fa-thumbtack text-warning me-1"></i>
                                    @endif
                                    <a href="{{ route('lms.announcements.show', $announcement) }}" class="text-decoration-none">
                                        {{ $announcement->title }}
                                    </a>
                                </h5>
                                <div class="text-muted small mb-2">
                                    <span class="badge {{ $announcement->priority_badge }}">{{ ucfirst($announcement->priority) }}</span>
                                    @if($announcement->course)
                                        <span class="badge bg-info">{{ $announcement->course->title }}</span>
                                    @else
                                        <span class="badge bg-secondary">Global</span>
                                    @endif
                                    &bull; {{ $announcement->published_at->format('M j, Y g:i A') }}
                                    &bull; by {{ $announcement->author->name }}
                                </div>
                                <p class="card-text text-muted">
                                    {{ Str::limit(strip_tags($announcement->content), 200) }}
                                </p>
                            </div>
                            @if($student && !$announcement->isReadBy($student))
                                <span class="badge bg-danger">New</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                        <h5>No announcements</h5>
                        <p class="text-muted mb-0">Check back later for updates.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{ $announcements->links() }}
</div>
@endsection
