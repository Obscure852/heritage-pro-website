@extends('layouts.master-student-portal')
@section('title', 'Student Learning Management Portal')
@section('css')
    <style>
        .assignment-upload-form {
            margin: 1.5rem 0;
        }

        .file-upload-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .file-upload-wrapper {
            position: relative;
            border: 2px dashed #3498db;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .file-upload-wrapper:hover {
            background-color: #e9f2fd;
            border-color: #2980b9;
        }

        .file-upload-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }

        .file-upload-label {
            display: block;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            cursor: pointer;
        }

        .file-upload-label i {
            font-size: 2rem;
            display: block;
            margin-bottom: 0.5rem;
            color: #3498db;
        }

        .file-name {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        .file-format-hint {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-top: 0.5rem;
        }

        .upload-btn {
            align-self: flex-end;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .error-message {
            margin-top: 0.75rem;
            color: #e74c3c;
            font-size: 0.9rem;
            padding: 0.5rem;
            background-color: #fdedeb;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .grading-card .card-header {
            background-color: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
        }

        .grading-card .list-group-item {
            border: none;
            padding: 0.75rem 0;
        }

        .grading-card .list-group-item strong {
            color: #2c3e50;
        }

        .grading-card .list-group-item small {
            color: #6b7280;
        }

        @media (min-width: 768px) {
            .file-upload-container {
                flex-direction: row;
                align-items: flex-end;
            }

            .file-upload-wrapper {
                flex: 1;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{ $subjectContent->title }}</h4>
                        <p class="text-muted">{{ $subjectContent->gradeSubject->subject->name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>{{ $selectedTopic->title }}</h5>
                        @php
                            $selectedResource = $selectedTopic->topicResources->first();
                            $totalResources = $selectedTopic->topicResources->count();
                            $completedResources = $progress
                                ->resourceProgress()
                                ->whereIn('topic_resource_id', $selectedTopic->topicResources->pluck('id'))
                                ->where('status', 'completed')
                                ->count();
                            $progressPercentage =
                                $totalResources > 0 ? ($completedResources / $totalResources) * 100 : 0;
                        @endphp
                        <div class="progress progress-sm">
                            <div class="progress-bar" role="progressbar" style="width: {{ $progressPercentage }}%"
                                aria-valuenow="{{ $progressPercentage }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ number_format($progressPercentage, 0) }}% Complete
                            ({{ $completedResources }}/{{ $totalResources }})
                        </small>
                    </div>
                    <div class="card-body">
                        @if ($selectedResource)
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6>{{ $selectedResource->title }}</h6>
                                        <small class="text-muted">{{ ucfirst($selectedResource->type) }}</small>
                                    </div>
                                    <div>
                                        <span
                                            class="badge bg-{{ $selectedResource->getCompletionForStudent($progress->id) === 'completed' ? 'success' : 'info' }}"
                                            id="status-badge-{{ $selectedResource->id }}">
                                            {{ ucfirst($selectedResource->getCompletionForStudent($progress->id)) }}
                                        </span>
                                        @if ($selectedResource->estimated_duration)
                                            <small class="text-muted ms-2">{{ $selectedResource->estimated_duration }}
                                                mins</small>
                                        @endif
                                    </div>
                                </div>

                                @php
                                    $resourceProgress = $progress
                                        ->resourceProgress()
                                        ->where('topic_resource_id', $selectedResource->id)
                                        ->first();
                                @endphp

                                <!-- Tabs -->
                                <ul class="nav nav-tabs" id="resourceTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="content-tab" data-bs-toggle="tab"
                                            data-bs-target="#content" type="button" role="tab" aria-controls="content"
                                            aria-selected="true">Content</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="attachments-tab" data-bs-toggle="tab"
                                            data-bs-target="#attachments" type="button" role="tab"
                                            aria-controls="attachments" aria-selected="false">Attachments</button>
                                    </li>
                                    @if ($resourceProgress && $resourceProgress->isGraded())
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="grading-tab" data-bs-toggle="tab"
                                                data-bs-target="#grading" type="button" role="tab"
                                                aria-controls="grading" aria-selected="false">Grading</button>
                                        </li>
                                    @endif
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="comments-tab" data-bs-toggle="tab"
                                            data-bs-target="#comments" type="button" role="tab"
                                            aria-controls="comments" aria-selected="false">Comments</button>
                                    </li>
                                </ul>
                                <div class="tab-content" id="resourceTabContent">
                                    <!-- Content Tab -->
                                    <div class="tab-pane fade show active" id="content" role="tabpanel"
                                        aria-labelledby="content-tab">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mt-3 d-flex justify-content-end">
                                                    <form
                                                        action="{{ route('student.resource-download-assignment', $selectedResource->id) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-info">
                                                            <i class="bx bxs-file-pdf me-1 text-danger"></i> Download
                                                            Assignment (PDF)
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @if ($selectedResource->description)
                                            <div class="mt-3 text-muted">
                                                <strong>Instructions</strong>
                                                <p style="padding: 5px; border-radius: 2px;"
                                                    class="bg-secondary text-white">
                                                    {{ $selectedResource->description }}
                                                </p>
                                            </div>
                                        @endif
                                        <div class="mt-3">
                                            {!! $selectedResource->content !!}
                                        </div>
                                        <div class="mt-3">
                                            <!-- Mark as Completed Form with File Upload (only if not graded) -->
                                            @if ($resourceProgress && !$resourceProgress->isGraded())
                                                <form method="POST"
                                                    action="{{ route('student.resource-completed', $selectedResource->id) }}"
                                                    enctype="multipart/form-data" class="assignment-upload-form">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="file-upload-container">
                                                                <div class="file-upload-wrapper">
                                                                    <input type="file" name="assignment"
                                                                        id="assignment-file"
                                                                        class="file-upload-input @error('assignment') is-invalid @enderror"
                                                                        accept=".docx,.pdf,image/*" required>
                                                                    <label for="assignment-file"
                                                                        class="file-upload-label">
                                                                        <i class="fas fa-cloud-upload-alt"></i>
                                                                        <span class="file-name">Choose a file...</span>
                                                                    </label>
                                                                    <div class="file-format-hint">
                                                                        Accepted formats: Word, PDF, Images
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12 d-flex justify-content-end">
                                                            <button type="submit"
                                                                class="btn btn-success btn-sm upload-btn mt-4">
                                                                <i class="fas fa-paper-plane"></i> Submit Assignment
                                                            </button>
                                                        </div>
                                                    </div>
                                                    @error('assignment')
                                                        <div class="error-message">
                                                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                                        </div>
                                                    @enderror
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- Attachments Tab -->
                                    <div class="tab-pane fade" id="attachments" role="tabpanel"
                                        aria-labelledby="attachments-tab">
                                        <div class="mt-3">
                                            @if ($selectedResource->hasAttachments())
                                                @foreach ($selectedResource->attachments as $attachment)
                                                    @php
                                                        $iconClass = match (true) {
                                                            str_contains($attachment->filetype, 'pdf')
                                                                => 'bx bxs-file-pdf text-danger',
                                                            str_contains($attachment->filetype, 'word')
                                                                => 'bx bxs-file-doc text-primary',
                                                            str_contains($attachment->filetype, 'excel')
                                                                => 'bx bxs-file text-success',
                                                            str_contains($attachment->filetype, 'image')
                                                                => 'bx bxs-file-image text-info',
                                                            default => 'bx bxs-file text-secondary',
                                                        };
                                                    @endphp
                                                    <div class="d-flex align-items-center border rounded p-3 mb-2">
                                                        <i class="{{ $iconClass }} font-size-24 me-2"></i>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">
                                                                {{ $attachment->display_name ?? $attachment->filename }}
                                                            </h6>
                                                            <small class="text-muted">
                                                                Size: {{ $attachment->getFormattedSize() }} |
                                                                Type: {{ $attachment->filetype }}
                                                                @if ($attachment->description)
                                                                    | {{ $attachment->description }}
                                                                @endif
                                                            </small>
                                                        </div>
                                                        <a href="{{ route('student.material-download', [$attachment->id, $progress->id]) }}"
                                                            class="btn btn-sm btn-primary ms-2" target="_blank"
                                                            download="{{ $attachment->filename }}">
                                                            <i class="bx bx-download"></i>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-muted">No attachments available for this resource.</p>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- Grading Tab -->
                                    @if ($resourceProgress && $resourceProgress->isGraded())
                                        <div class="tab-pane fade" id="grading" role="tabpanel"
                                            aria-labelledby="grading-tab">
                                            <div class="card grading-card mb-4">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">Assessment Results</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <small class="text-muted d-block mb-1">Score</small>
                                                            <h5 class="mb-0 fw-bold text-dark">
                                                                {{ $resourceProgress->score }}/100
                                                            </h5>
                                                        </div>
                                                        @if ($resourceProgress->graded_at)
                                                            <div class="col-md-6 mb-3">
                                                                <small class="text-muted d-block mb-1">Graded On</small>
                                                                <h5 class="mb-0 fw-bold text-dark">
                                                                    {{ optional($resourceProgress->graded_at)->diffForHumans() }}
                                                                </h5>
                                                            </div>
                                                        @endif
                                                        @if ($resourceProgress)
                                                            <div class="col-12 mb-3">
                                                                <small class="text-muted d-block mb-1">Progress
                                                                    Status</small>
                                                                <ul class="list-group">
                                                                    <li class="list-group-item">
                                                                        <strong>Status:</strong>
                                                                        <span class="badge bg-success ms-2">
                                                                            {{ ucfirst($resourceProgress->status) }}
                                                                        </span>
                                                                    </li>
                                                                    @if ($resourceProgress->started_at)
                                                                        <li class="list-group-item">
                                                                            <strong>Started:</strong>
                                                                            <small>{{ $resourceProgress->started_at->diffForHumans() }}</small>
                                                                        </li>
                                                                    @endif
                                                                    @if ($resourceProgress->submitted_at)
                                                                        <li class="list-group-item">
                                                                            <strong>Submitted:</strong>
                                                                            <small>{{ $resourceProgress->submitted_at->diffForHumans() }}</small>
                                                                        </li>
                                                                        <li class="list-group-item">
                                                                            <strong>File:</strong>
                                                                            <small>
                                                                                <a href="{{ asset('storage/' . $resourceProgress->file_path) }}"
                                                                                    target="_blank" class="text-primary">
                                                                                    {{ basename($resourceProgress->file_path) }}
                                                                                </a>
                                                                            </small>
                                                                        </li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @endif
                                                        @if ($resourceProgress->progress_data['feedback'] ?? false)
                                                            <div class="col-12">
                                                                <small class="text-muted d-block mb-1">Teacher
                                                                    Feedback</small>
                                                                <div class="p-3 bg-white border rounded shadow-sm">
                                                                    <p class="mb-0 text-dark fw-medium">
                                                                        {{ $resourceProgress->progress_data['feedback'] }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <!-- Comments Tab -->
                                    <div class="tab-pane fade" id="comments" role="tabpanel"
                                        aria-labelledby="comments-tab">
                                        <div class="d-flex flex-column" style="height: 500px; margin-top:15px;">
                                            <!-- Scrollable Comments Container -->
                                            <div id="comments-container" class="flex-grow-1 overflow-y-auto mb-3">
                                                @forelse ($selectedResource->messages->sortBy('created_at') as $message)
                                                    <div
                                                        class="chat-message mb-3 d-flex {{ $message->sender === 'student' ? 'justify-content-end' : 'justify-content-start' }}">
                                                        @if ($message->sender === 'teacher')
                                                            <div class="avatar me-2">
                                                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white"
                                                                    style="width: 35px; height: 35px;">
                                                                    <span>T</span>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <div class="message-bubble {{ $message->sender === 'student' ? 'ms-auto' : '' }}"
                                                            style="max-width: 70%;">
                                                            <div
                                                                class="p-3 rounded {{ $message->sender === 'student' ? 'bg-primary text-white' : 'bg-light border' }}">
                                                                <p class="mb-1">{{ $message->message }}</p>
                                                                <small
                                                                    class="{{ $message->sender === 'student' ? 'text-light' : 'text-muted' }}">
                                                                    {{ $message->created_at->format('M d, g:i a') }}
                                                                    @if ($message->read_at)
                                                                        <i class="bx bx-check-double"></i>
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        </div>

                                                        @if ($message->sender === 'student')
                                                            <div class="avatar ms-2">
                                                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-success text-white"
                                                                    style="width: 35px; height: 35px;">
                                                                    <span>Y</span>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <div class="text-center py-5">
                                                        <i class="bx bx-message-dots text-muted fs-1"></i>
                                                        <p class="text-muted mt-2">No comments yet. Start the conversation!
                                                        </p>
                                                    </div>
                                                @endforelse
                                            </div>

                                            <!-- Fixed Comment Form -->
                                            <form method="POST"
                                                action="{{ route('student.send-resource-message', $selectedResource->id) }}"
                                                class="comment-form mt-auto">
                                                @csrf
                                                <div class="input-group">
                                                    <textarea name="message" class="form-control" rows="1" placeholder="Type your message..." required
                                                        style="resize: none; border-radius: 20px 0 0 20px;"></textarea>
                                                    <button type="submit" class="btn btn-primary"
                                                        style="border-radius: 0 20px 20px 0;">
                                                        <i class="bx bx-send"></i> Send
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <p class="text-muted">No resources available for this topic.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const activeTab = localStorage.getItem('activeTab') || '#content';
            const tab = new bootstrap.Tab(document.querySelector(`[data-bs-target="${activeTab}"]`));
            tab.show();

            document.querySelectorAll('#resourceTabs .nav-link').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function() {
                    localStorage.setItem('activeTab', this.getAttribute('data-bs-target'));
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('assignment-file');
            const fileNameDisplay = document.querySelector('.file-name');

            if (fileInput && fileNameDisplay) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        fileNameDisplay.textContent = this.files[0].name;
                        this.closest('.file-upload-wrapper').classList.add('has-file');
                    } else {
                        fileNameDisplay.textContent = 'Choose a file...';
                        this.closest('.file-upload-wrapper').classList.remove('has-file');
                    }
                });
            }

            const commentsContainer = document.getElementById('comments-container');
            if (commentsContainer) {
                commentsContainer.scrollTop = commentsContainer.scrollHeight;
            }

        });
    </script>
@endsection
