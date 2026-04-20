@extends('layouts.master')

@section('title', $thread->title)

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.edit', $forum->course) }}">Learning Space</a>
        @endslot
        @slot('title')
            Discussions
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-9">
            <!-- Thread -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1">
                            @if ($thread->is_pinned)
                                <i class="fas fa-thumbtack text-warning me-1"></i>
                            @endif
                            @if ($thread->is_locked)
                                <i class="fas fa-lock text-danger me-1"></i>
                            @endif
                            {{ $thread->title }}
                        </h5>
                        <div class="text-muted small">
                            @if ($thread->type === 'announcement')
                                <span class="badge bg-warning text-dark me-2"><i class="fas fa-bullhorn"></i>
                                    Announcement</span>
                            @elseif($thread->category)
                                <span class="badge me-2"
                                    style="background-color: {{ $thread->category->color }}20; color: {{ $thread->category->color }};">
                                    {{ $thread->category->name }}
                                </span>
                            @endif
                            Posted by <strong>{{ $thread->display_author }}</strong>
                            @if ($thread->isAuthorInstructor())
                                <span class="badge bg-primary ms-1" style="font-size: 9px;">Instructor</span>
                            @endif
                            &bull; {{ $thread->created_at->format('M j, Y g:i A') }}
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @can('manage-lms-content')
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <form action="{{ route('lms.discussions.toggle-pin', $thread) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-thumbtack me-2"></i>{{ $thread->is_pinned ? 'Unpin' : 'Pin' }}
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route('lms.discussions.toggle-lock', $thread) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-lock me-2"></i>{{ $thread->is_locked ? 'Unlock' : 'Lock' }}
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="thread-body mb-3">
                        {!! nl2br(e($thread->body)) !!}
                    </div>
                    <div class="text-end">
                        <small class="text-muted"><i class="fas fa-eye"></i> {{ $thread->views_count }} views</small>
                    </div>
                </div>
            </div>

            <!-- Accepted Answer -->
            @if ($thread->acceptedAnswer)
                <div class="card shadow-sm mb-4 border-success">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-check-circle me-2"></i>Accepted Answer
                    </div>
                    <div class="card-body">
                        <div class="d-flex mb-2">
                            <strong>{{ $thread->acceptedAnswer->display_author }}</strong>
                            <span class="text-muted ms-2">{{ $thread->acceptedAnswer->created_at->diffForHumans() }}</span>
                        </div>
                        {!! nl2br(e($thread->acceptedAnswer->body)) !!}
                    </div>
                </div>
            @endif

            <!-- Replies -->
            <h6 class="mb-3"><i class="fas fa-comments me-2"></i>{{ $thread->replies_count }} Replies</h6>

            @foreach ($posts as $post)
                <div class="card shadow-sm mb-3" id="post-{{ $post->id }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <strong>{{ $post->display_author }}</strong>
                                @if ($post->isAuthorInstructor())
                                    <span class="badge bg-primary ms-1" style="font-size: 9px;">Instructor</span>
                                @endif
                                <span class="text-muted ms-2">{{ $post->created_at->diffForHumans() }}</span>
                                @if ($post->is_answer)
                                    <span class="badge bg-success ms-2"><i class="fas fa-check"></i> Answer</span>
                                @endif
                            </div>
                            @php
                                $canMarkAnswer =
                                    $thread->type === 'question' &&
                                    !$thread->accepted_answer_id &&
                                    (($isInstructor ?? false) ||
                                        ($student &&
                                            $thread->isAuthorStudent() &&
                                            $thread->author_id === $student->id));
                            @endphp
                            @if ($canMarkAnswer)
                                <button class="btn btn-sm btn-outline-success" onclick="markAsAnswer({{ $post->id }})">
                                    <i class="fas fa-check"></i> Accept
                                </button>
                            @endif
                        </div>
                        <div class="post-body">
                            {!! nl2br(e($post->body)) !!}
                        </div>
                        @if ($post->attachments->count())
                            <div class="mt-3">
                                @foreach ($post->attachments as $attachment)
                                    <a href="{{ $attachment->url }}" class="btn btn-sm btn-outline-primary me-1"
                                        target="_blank">
                                        <i class="fas fa-paperclip"></i> {{ $attachment->original_filename }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            {{ $posts->links() }}

            <!-- Reply Form -->
            @php
                $canReply = ($student || ($isInstructor ?? false)) && (!$thread->is_locked || ($isInstructor ?? false));
            @endphp
            @if ($canReply)
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-reply me-2"></i>Post a Reply</h6>
                        @if ($thread->is_locked && ($isInstructor ?? false))
                            <small class="text-muted">(Thread is locked, but you can reply as an instructor)</small>
                        @endif
                    </div>
                    <div class="card-body">
                        <form action="{{ route('lms.discussions.store-post', $thread) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <textarea name="body" class="form-control" rows="4" placeholder="Write your reply..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <input type="file" name="attachments[]" id="attachments" class="d-none" multiple>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        onclick="document.getElementById('attachments').click()">
                                        <i class="fas fa-paperclip"></i> Attach Files
                                    </button>
                                    <div id="attachment-list" class="mt-2"></div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i>Post Reply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @elseif($thread->is_locked && !($isInstructor ?? false))
                <div class="alert alert-warning mt-4">
                    <i class="fas fa-lock me-2"></i>This thread is locked. No new replies can be posted.
                </div>
            @endif
        </div>

        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">Thread Info</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Type:</td>
                            <td>
                                @if ($thread->type === 'announcement')
                                    <span class="badge bg-warning text-dark">Announcement</span>
                                @elseif($thread->type === 'question')
                                    <span class="badge bg-info">Question</span>
                                @else
                                    <span class="badge bg-secondary">Discussion</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                @if ($thread->status === 'resolved')
                                    <span class="badge bg-success">Resolved</span>
                                @elseif($thread->status === 'closed')
                                    <span class="badge bg-secondary">Closed</span>
                                @else
                                    <span class="badge bg-primary">Open</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Views:</td>
                            <td>{{ number_format($thread->views_count) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Replies:</td>
                            <td>{{ $thread->replies_count }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function markAsAnswer(postId) {
                fetch('/lms/discussions/posts/' + postId + '/mark-answer', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
            }

            // Attachment file selection feedback
            document.getElementById('attachments')?.addEventListener('change', function() {
                const list = document.getElementById('attachment-list');
                list.innerHTML = '';

                if (this.files.length > 0) {
                    const container = document.createElement('div');
                    container.className = 'd-flex flex-wrap gap-2';

                    Array.from(this.files).forEach((file, index) => {
                        const badge = document.createElement('span');
                        badge.className =
                            'badge bg-light text-dark border d-inline-flex align-items-center gap-1';
                        badge.innerHTML =
                            `<i class="fas fa-file"></i> ${file.name} <button type="button" class="btn-close btn-close-sm ms-1" style="font-size: 0.6rem;" onclick="removeAttachment(${index})"></button>`;
                        container.appendChild(badge);
                    });

                    list.appendChild(container);
                }
            });

            function removeAttachment(index) {
                const input = document.getElementById('attachments');
                const dt = new DataTransfer();
                const files = Array.from(input.files);

                files.forEach((file, i) => {
                    if (i !== index) dt.items.add(file);
                });

                input.files = dt.files;
                input.dispatchEvent(new Event('change'));
            }
        </script>
    @endpush
@endsection
