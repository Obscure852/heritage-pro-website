@extends('layouts.master')

@section('title')
    Content Items - {{ $module->title }}
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

        .page-body {
            padding: 24px;
        }

        /* Header Stats */
        .stat-item {
            padding: 6px 16px;
            text-align: center;
        }

        .stat-item h4 {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
            color: #fff;
        }

        .stat-item small {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
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

        /* Action Bar */
        .action-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 20px;
        }

        /* Buttons */
        .btn {
            padding: 10px 16px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
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

        /* Content Items */
        .content-item {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 8px;
            background: white;
            transition: all 0.2s;
        }

        .content-item:hover {
            border-color: #4e73df;
            box-shadow: 0 2px 8px rgba(78, 115, 223, 0.1);
        }

        .content-item:last-child {
            margin-bottom: 0;
        }

        .content-icon {
            width: 40px;
            height: 40px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 14px;
            font-size: 16px;
        }

        .content-icon.video {
            background: #fee2e2;
            color: #dc2626;
        }

        .content-icon.document {
            background: #dbeafe;
            color: #2563eb;
        }

        .content-icon.quiz {
            background: #fef3c7;
            color: #d97706;
        }

        .content-icon.text {
            background: #d1fae5;
            color: #059669;
        }

        .content-icon.audio {
            background: #f3e8ff;
            color: #9333ea;
        }

        .content-icon.image {
            background: #fce7f3;
            color: #db2777;
        }

        .content-icon.external_link {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .content-icon.scorm {
            background: #f3e8ff;
            color: #8b5cf6;
        }

        .drag-handle {
            cursor: grab;
            color: #9ca3af;
            margin-right: 12px;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .content-info {
            flex: 1;
        }

        .content-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .content-meta {
            font-size: 13px;
            color: #6b7280;
        }

        /* Action Buttons */
        .content-actions {
            display: flex;
            gap: 4px;
        }

        .content-actions .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .content-actions .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .content-actions .btn i {
            font-size: 14px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.4;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            color: #374151;
            margin-bottom: 8px;
        }

        .empty-state p {
            margin: 0;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('title')
            Content Items
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

    <div class="page-container">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h4 style="margin: 0 0 4px 0; font-weight: 600;">Content Items</h4>
                    <p style="margin: 0; opacity: 0.9; font-size: 14px;">{{ $module->title }} &bull;
                        {{ $module->course->title }}</p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex justify-content-lg-end align-items-center mt-3 mt-lg-0">
                        <div class="stat-item">
                            <h4>{{ $module->contentItems->count() }}</h4>
                            <small>Content Items</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="help-text">
                <div class="help-title">Manage Content</div>
                <p class="help-content">Organize and manage the learning content for this module. Drag items to reorder
                    them. Students will see content in this order.</p>
            </div>

            <div class="action-bar">
                <a href="{{ route('lms.content.create', $module) }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Content
                </a>
            </div>

            @if ($module->contentItems->count() > 0)
                <div id="contentList">
                    @foreach ($module->contentItems as $content)
                        <div class="content-item" data-id="{{ $content->id }}">
                            <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
                            <div
                                class="content-icon {{ str_contains($content->type, 'video') ? 'video' : $content->type }}">
                                @switch($content->type)
                                    @case('video_youtube')
                                    @case('video_upload')
                                        <i class="fas fa-play"></i>
                                    @break

                                    @case('document')
                                        <i class="fas fa-file-alt"></i>
                                    @break

                                    @case('quiz')
                                        <i class="fas fa-question-circle"></i>
                                    @break

                                    @case('audio')
                                        <i class="fas fa-headphones"></i>
                                    @break

                                    @case('image')
                                        <i class="fas fa-image"></i>
                                    @break

                                    @case('external_link')
                                        <i class="fas fa-external-link-alt"></i>
                                    @break

                                    @case('scorm')
                                        <i class="fas fa-cube"></i>
                                    @break

                                    @default
                                        <i class="fas fa-align-left"></i>
                                @endswitch
                            </div>
                            <div class="content-info">
                                <div class="content-title">{{ $content->title }}</div>
                                <div class="content-meta">
                                    {{ ucfirst(str_replace('_', ' ', $content->type)) }}
                                    @if ($content->estimated_duration)
                                        <span class="mx-1">|</span>
                                        <i class="far fa-clock me-1"></i>{{ $content->estimated_duration }} min
                                    @endif
                                    @if ($content->is_required)
                                        <span class="mx-1">|</span>
                                        <span class="badge bg-info">Required</span>
                                    @endif
                                </div>
                            </div>
                            <div class="content-actions">
                                <a href="{{ route('lms.content.edit', $content) }}" class="btn btn-sm btn-outline-info"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('lms.content.destroy', $content) }}" method="POST"
                                    onsubmit="return confirm('Delete this content?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-file-alt d-block"></i>
                    <h5>No Content Yet</h5>
                    <p>Add your first content item to this module using the button above.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Drag and drop reordering can be implemented here
        // using SortableJS or similar library
    </script>
@endsection
