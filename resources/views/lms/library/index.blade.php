@extends('layouts.master')

@section('title', 'Content Library')

@section('css')
    <style>
        .library-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .library-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .library-body {
            padding: 24px;
        }

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #8b5cf6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
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

        .page-container {
            background: white;
            border-radius: 3px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-header-left h1 {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 4px 0;
        }

        .page-header-left p {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
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

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
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

        /* Search and Filter Bar */
        .filter-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 280px;
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .search-box input {
            width: 100%;
            padding: 10px 12px 10px 38px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .filter-select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            min-width: 160px;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Quick Filters */
        .quick-filters {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-chip {
            padding: 6px 14px;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            font-size: 13px;
            color: #4b5563;
            text-decoration: none;
            transition: all 0.2s;
        }

        .filter-chip:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .filter-chip.active {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        .content-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            transition: all 0.2s;
        }

        .content-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .content-card-header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }

        .content-icon {
            width: 48px;
            height: 48px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .content-icon.video { background: #fef3c7; color: #d97706; }
        .content-icon.image { background: #dbeafe; color: #2563eb; }
        .content-icon.document { background: #e0e7ff; color: #4f46e5; }
        .content-icon.pdf { background: #fee2e2; color: #dc2626; }
        .content-icon.audio { background: #d1fae5; color: #059669; }
        .content-icon.presentation { background: #fce7f3; color: #db2777; }
        .content-icon.spreadsheet { background: #d1fae5; color: #059669; }

        .content-icon i {
            font-size: 20px;
        }

        .content-info {
            flex: 1;
            min-width: 0;
        }

        .content-info h3 {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 4px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .content-info h3 a {
            color: inherit;
            text-decoration: none;
        }

        .content-info h3 a:hover {
            color: #3b82f6;
        }

        .content-meta {
            font-size: 13px;
            color: #6b7280;
        }

        .content-tags {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .content-tag {
            padding: 2px 8px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            font-size: 11px;
            color: #6b7280;
        }

        .content-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
        }

        .content-creator {
            font-size: 12px;
            color: #9ca3af;
        }

        .content-actions {
            display: flex;
            gap: 6px;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
            background: white;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #eff6ff;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state h3 {
            font-size: 18px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 8px 0;
        }

        .empty-state p {
            color: #6b7280;
            margin: 0 0 20px 0;
        }

        /* Sidebar */
        .sidebar-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 16px;
        }

        .sidebar-header {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            font-size: 14px;
            color: #374151;
        }

        .sidebar-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .sidebar-list li a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            color: #4b5563;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.15s;
            border-bottom: 1px solid #f3f4f6;
        }

        .sidebar-list li:last-child a {
            border-bottom: none;
        }

        .sidebar-list li a:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .sidebar-list li a.active {
            background: #eff6ff;
            color: #2563eb;
            font-weight: 500;
        }

        .sidebar-list li a i {
            margin-right: 10px;
            width: 16px;
            text-align: center;
        }

        .sidebar-badge {
            background: #e5e7eb;
            color: #6b7280;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
        }

        .sidebar-list li a.active .sidebar-badge {
            background: #bfdbfe;
            color: #1d4ed8;
        }

        /* Collection color dot */
        .collection-dot {
            width: 10px;
            height: 10px;
            border-radius: 2px;
            margin-right: 10px;
            flex-shrink: 0;
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

        /* Pagination */
        .pagination-wrapper {
            margin-top: 24px;
            display: flex;
            justify-content: center;
        }

        @media (max-width: 992px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .filter-bar {
                flex-direction: column;
            }

            .search-box {
                min-width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('title')
            Content Library
        @endslot
    @endcomponent

    <div class="library-container">
        <div class="library-header">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <h3 style="margin:0;"><i class="fas fa-photo-video me-2"></i>Content Library</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage and organize your reusable content assets</p>
                </div>
                <div class="col-md-7">
                    <div class="row text-center">
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalItems ?? 0 }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $typeCounts['video'] ?? 0 }}</h4>
                                <small class="opacity-75">Videos</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $typeCounts['image'] ?? 0 }}</h4>
                                <small class="opacity-75">Images</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $typeCounts['document'] ?? 0 }}</h4>
                                <small class="opacity-75">Docs</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $typeCounts['pdf'] ?? 0 }}</h4>
                                <small class="opacity-75">PDFs</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="library-body">
            <div class="help-text">
                <div class="help-title">Content Library</div>
                <div class="help-content">
                    Upload and organize your content assets for use across courses. Videos, images, documents, PDFs, and audio files can be stored here and reused in multiple courses without duplicating files.
                </div>
            </div>

            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3 mb-4">
                    <!-- Quick Links -->
                    <div class="sidebar-section">
                        <ul class="sidebar-list">
                            <li>
                                <a href="{{ route('lms.library.index') }}" class="{{ !request('collection_id') && !request('type') ? 'active' : '' }}">
                                    <span><i class="fas fa-th-large"></i> All Items</span>
                                    <span class="sidebar-badge">{{ $items->total() }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('lms.library.favorites') }}">
                                    <span><i class="fas fa-star text-warning"></i> Favorites</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('lms.library.templates') }}">
                                    <span><i class="fas fa-clone"></i> Templates</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Collections -->
                    <div class="sidebar-section">
                        <div class="sidebar-header">Collections</div>
                        @if($collections->count())
                            <ul class="sidebar-list">
                                @foreach($collections as $collection)
                                    <li>
                                        <a href="{{ route('lms.library.collection', $collection) }}">
                                            <span>
                                                <span class="collection-dot" style="display: inline-block; background-color: {{ $collection->color ?? '#6c757d' }};"></span>
                                                {{ $collection->name }}
                                            </span>
                                            <span class="sidebar-badge">{{ $collection->items_count }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="p-3 text-center text-muted" style="font-size: 13px;">
                                No collections yet
                            </div>
                        @endif
                    </div>

                    <!-- Filter by Type -->
                    <div class="sidebar-section">
                        <div class="sidebar-header">Filter by Type</div>
                        <ul class="sidebar-list">
                            <li>
                                <a href="{{ route('lms.library.index', ['type' => 'video']) }}" class="{{ request('type') === 'video' ? 'active' : '' }}">
                                    <span><i class="fas fa-video"></i> Videos</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('lms.library.index', ['type' => 'image']) }}" class="{{ request('type') === 'image' ? 'active' : '' }}">
                                    <span><i class="fas fa-image"></i> Images</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('lms.library.index', ['type' => 'document']) }}" class="{{ request('type') === 'document' ? 'active' : '' }}">
                                    <span><i class="fas fa-file-alt"></i> Documents</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('lms.library.index', ['type' => 'pdf']) }}" class="{{ request('type') === 'pdf' ? 'active' : '' }}">
                                    <span><i class="fas fa-file-pdf"></i> PDFs</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('lms.library.index', ['type' => 'audio']) }}" class="{{ request('type') === 'audio' ? 'active' : '' }}">
                                    <span><i class="fas fa-music"></i> Audio</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Tags -->
                    @if($tags->count())
                        <div class="sidebar-section">
                            <div class="sidebar-header">Popular Tags</div>
                            <div class="p-3">
                                @foreach($tags as $tag)
                                    <a href="{{ route('lms.library.index', ['tag' => $tag->slug]) }}"
                                        class="filter-chip {{ request('tag') === $tag->slug ? 'active' : '' }}" style="display: inline-block; margin: 0 4px 8px 0;">
                                        {{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <div class="d-flex justify-content-end mb-3">
                        <div class="header-actions">
                            <button class="btn btn-outline" data-bs-toggle="modal" data-bs-target="#createCollectionModal">
                                <i class="fas fa-folder-plus"></i> New Collection
                            </button>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fas fa-cloud-upload-alt"></i> Upload
                            </button>
                        </div>
                    </div>

                    <!-- Search and Filter -->
                    <form action="{{ route('lms.library.index') }}" method="GET">
                        <div class="filter-bar">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" name="search" placeholder="Search content by title, description, or tags..." value="{{ request('search') }}">
                            </div>
                            <select name="sort" class="filter-select" onchange="this.form.submit()">
                                <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name A-Z</option>
                                <option value="size" {{ request('sort') === 'size' ? 'selected' : '' }}>Size</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>

                    <!-- Content Grid -->
                    @if($items->count())
                        <div class="content-grid">
                            @foreach($items as $item)
                                <div class="content-card">
                                    <div class="content-card-header">
                                        <div class="content-icon {{ $item->type }}">
                                            @include('lms.library.partials.type-icon', ['type' => $item->type])
                                        </div>
                                        <div class="content-info">
                                            <h3><a href="{{ route('lms.library.item', $item) }}">{{ $item->title }}</a></h3>
                                            <div class="content-meta">
                                                {{ ucfirst($item->type) }} &bull; {{ $item->human_file_size }}
                                                @if($item->collection)
                                                    &bull; <i class="fas fa-folder" style="font-size: 10px;"></i> {{ $item->collection->name }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @if($item->tags->count())
                                        <div class="content-tags">
                                            @foreach($item->tags->take(3) as $tag)
                                                <span class="content-tag">{{ $tag->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="content-card-footer">
                                        <span class="content-creator">{{ $item->creator?->full_name }}</span>
                                        <div class="content-actions">
                                            <a href="{{ route('lms.library.item', $item) }}" class="action-btn" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($item->created_by === auth()->id())
                                                <a href="{{ route('lms.library.edit', $item) }}" class="action-btn" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pagination-wrapper">
                            {{ $items->links() }}
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-photo-video"></i>
                            <h3>No Content Found</h3>
                            <p>
                                @if(request('search'))
                                    No items match your search criteria. Try a different search term.
                                @else
                                    Start building your content library by uploading files.
                                @endif
                            </p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fas fa-cloud-upload-alt"></i> Upload Content
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('lms.library.upload') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-cloud-upload-alt me-2"></i>Upload Content</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="Enter a descriptive title for this content" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this content (optional)"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File <span class="text-danger">*</span></label>
                            <label class="file-upload-area" for="libraryFile">
                                <input type="file" name="file" id="libraryFile" required>
                                <i class="fas fa-cloud-upload-alt"></i>
                                <div class="upload-text">Click to choose a file or drag it here</div>
                                <div class="upload-hint">Max 500MB. Videos, images, PDFs, documents supported.</div>
                                <div class="file-name d-none" id="fileName"></div>
                            </label>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Collection</label>
                                <select name="collection_id" class="form-select">
                                    <option value="">None</option>
                                    @foreach($collections as $collection)
                                        <option value="{{ $collection->id }}">{{ $collection->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Visibility</label>
                                <select name="visibility" class="form-select">
                                    <option value="private">Private</option>
                                    <option value="shared">Shared</option>
                                    <option value="public">Public</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Tags</label>
                            <input type="text" name="tags" class="form-control" placeholder="Enter tags separated by commas (e.g. math, grade10, homework)">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-cloud-upload-alt"></i> Upload</span>
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

    <!-- Create Collection Modal -->
    <div class="modal fade" id="createCollectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('lms.library.collections.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-folder-plus me-2"></i>New Collection</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Enter collection name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this collection (optional)"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Color</label>
                                <input type="color" name="color" class="form-control form-control-color" value="#3b82f6" style="height: 42px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Visibility</label>
                                <select name="visibility" class="form-select">
                                    <option value="private">Private</option>
                                    <option value="shared">Shared</option>
                                    <option value="public">Public</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-plus"></i> Create</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Creating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeFileInput();
            initializeFormValidation();
        });

        function initializeFileInput() {
            const fileInput = document.getElementById('libraryFile');
            const fileName = document.getElementById('fileName');
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

                // Drag and drop
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
