@extends('layouts.master')

@section('title', $course->title . ' - Discussions')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.edit', $course) }}">Learning Space</a>
        @endslot
        @slot('title')
            Discussions
        @endslot
    @endcomponent

    <div class="row mb-4">
        <div class="col-md-8">
            <h4 class="mb-1"><i class="fas fa-comments me-2"></i>{{ $forum->title }}</h4>
            <p class="text-muted mb-0">{{ $forum->description ?? 'Discuss course topics with your peers' }}</p>
        </div>
        <div class="col-md-4 text-md-end">
            @if ($student || $isInstructor)
                <a href="{{ route('lms.discussions.create-thread', $forum) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>New Thread
                </a>
            @endif
        </div>
    </div>

        @if ($forum->categories->count())
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('lms.discussions.forum', $course) }}"
                            class="btn btn-sm btn-outline-secondary">All</a>
                        @foreach ($forum->categories as $category)
                            <a href="{{ route('lms.discussions.forum', ['course' => $course, 'category' => $category->id]) }}"
                                class="btn btn-sm"
                                style="background-color: {{ $category->color }}20; color: {{ $category->color }}; border-color: {{ $category->color }};">
                                <i class="{{ $category->icon }} me-1"></i>{{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body p-0">
                @forelse($threads as $thread)
                    <div class="thread-item border-bottom p-3 {{ $thread->is_pinned ? 'bg-light' : '' }}">
                        <div class="d-flex">
                            <div class="thread-votes text-center me-3" style="min-width: 60px;">
                                <div class="text-muted small">
                                    <i class="fas fa-heart"></i> {{ $thread->likes_count }}
                                </div>
                                <div class="text-muted small">
                                    <i class="fas fa-reply"></i> {{ $thread->replies_count }}
                                </div>
                            </div>
                            <div class="thread-content flex-grow-1">
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            @if ($thread->is_pinned)
                                                <i class="fas fa-thumbtack text-warning me-1" title="Pinned"></i>
                                            @endif
                                            @if ($thread->is_locked)
                                                <i class="fas fa-lock text-danger me-1" title="Locked"></i>
                                            @endif
                                            @if ($thread->type === 'announcement')
                                                <span class="badge bg-warning text-dark me-1"><i class="fas fa-bullhorn"></i> Announcement</span>
                                            @elseif ($thread->type === 'question')
                                                @if ($thread->status === 'resolved')
                                                    <span class="badge bg-success me-1"><i class="fas fa-check"></i>
                                                        Answered</span>
                                                @else
                                                    <span class="badge bg-info me-1">Question</span>
                                                @endif
                                            @endif
                                            <a href="{{ route('lms.discussions.thread', [$forum, $thread]) }}"
                                                class="text-decoration-none text-dark">
                                                {{ $thread->title }}
                                            </a>
                                        </h6>
                                        <div class="text-muted small">
                                            @if ($thread->category)
                                                <span class="badge me-2"
                                                    style="background-color: {{ $thread->category->color }}20; color: {{ $thread->category->color }};">
                                                    {{ $thread->category->name }}
                                                </span>
                                            @endif
                                            Started by <strong>{{ $thread->display_author }}</strong>
                                            @if ($thread->isAuthorInstructor())
                                                <span class="badge bg-primary ms-1" style="font-size: 9px;">Instructor</span>
                                            @endif
                                            &bull; {{ $thread->created_at->diffForHumans() }}
                                            &bull; <i class="fas fa-eye"></i> {{ $thread->views_count }} views
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if ($thread->lastReply)
                                <div class="thread-last-reply text-end ms-3" style="min-width: 150px;">
                                    <small class="text-muted">
                                        Last reply by<br>
                                        <strong>{{ $thread->lastReply->display_author }}</strong><br>
                                        {{ $thread->last_activity_at->diffForHumans() }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h5>No discussions yet</h5>
                        <p class="text-muted">{{ $isInstructor ? 'Create a discussion thread to engage with your students!' : 'Be the first to start a discussion!' }}</p>
                        @if ($student || $isInstructor)
                            <a href="{{ route('lms.discussions.create-thread', $forum) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Create Thread
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>
            @if ($threads->hasPages())
            <div class="card-footer">
                {{ $threads->links() }}
            </div>
        @endif
    </div>
@endsection
