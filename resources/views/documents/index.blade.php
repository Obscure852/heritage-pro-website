@extends('layouts.master')
@section('title')
    Documents
@endsection
@section('css')
    <style>
        .documents-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .documents-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
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

        .btn {
            padding: 10px 20px;
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

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
            color: white;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
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
            line-height: 1.4;
        }

        /* View toggle */
        .view-toggle {
            display: inline-flex;
            border-radius: 3px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .view-toggle .btn {
            background: #f9fafb;
            color: #6b7280;
            border: none;
            border-radius: 0;
            padding: 6px 12px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .view-toggle .btn.active {
            background: #3b82f6;
            color: white;
        }

        .view-toggle .btn:hover:not(.active) {
            background: #f3f4f6;
        }

        /* Grid view */
        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
        }

        .document-card {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 16px;
            transition: all 0.2s;
            background: white;
        }

        .document-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        /* Table styles */
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            white-space: nowrap;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        /* Sortable column headers */
        .sort-link {
            color: #374151;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .sort-link:hover {
            color: #3b82f6;
        }

        .sort-link .sort-icon {
            font-size: 10px;
            opacity: 0.4;
        }

        .sort-link.active .sort-icon {
            opacity: 1;
            color: #3b82f6;
        }

        /* Toolbar */
        .toolbar-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* Sort dropdown */
        .sort-dropdown .dropdown-toggle {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            color: #374151;
            font-size: 13px;
            padding: 6px 12px;
            border-radius: 3px;
        }

        .sort-dropdown .dropdown-menu {
            font-size: 13px;
            min-width: 160px;
        }

        .sort-dropdown .dropdown-item.active {
            background: #3b82f6;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 60px;
            opacity: 0.3;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            color: #374151;
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 14px;
            margin-bottom: 20px;
        }

        /* ==================== Dual-Pane Layout ==================== */
        .documents-dual-pane {
            display: flex;
            min-height: 500px;
        }

        .folder-sidebar {
            width: 280px;
            min-width: 280px;
            background: #f8f9fa;
            border-right: 1px solid #e5e7eb;
            transition: width 0.2s ease, min-width 0.2s ease, opacity 0.2s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .folder-sidebar.collapsed {
            width: 0;
            min-width: 0;
            opacity: 0;
            pointer-events: none;
        }

        .sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            background: #f0f1f3;
        }

        .sidebar-title {
            font-weight: 600;
            font-size: 14px;
            color: #374151;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 3px;
            transition: all 0.2s;
        }

        .sidebar-toggle:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .sidebar-content {
            padding: 8px 0;
        }

        .sidebar-actions {
            padding: 8px 12px;
        }

        .folder-sidebar-rail {
            width: 48px;
            min-width: 48px;
            background: #f8f9fa;
            border-right: 1px solid #e5e7eb;
            display: none;
            flex-direction: column;
            align-items: center;
            padding-top: 12px;
        }

        .folder-sidebar-rail.visible {
            display: flex;
        }

        .rail-btn {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 8px;
            border-radius: 3px;
            font-size: 18px;
        }

        .rail-btn:hover {
            background: #e5e7eb;
            color: #3b82f6;
        }

        .documents-main {
            flex: 1;
            min-width: 0;
            padding: 24px;
        }

        /* Tree section styles */
        .tree-section {
            margin-bottom: 8px;
        }

        .tree-section-header {
            padding: 6px 16px;
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tree-section-header i {
            width: 16px;
            text-align: center;
            margin-right: 4px;
        }

        /* Folder tree styles */
        .folder-tree-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .folder-tree-list .folder-tree-list {
            padding-left: 16px;
        }

        .folder-tree-item {
            position: relative;
        }

        .folder-tree-label {
            display: flex;
            align-items: center;
            padding: 4px 12px;
            cursor: pointer;
            border-radius: 3px;
            margin: 1px 8px;
            font-size: 13px;
            transition: background 0.15s;
        }

        .folder-tree-label:hover {
            background: #e5e7eb;
        }

        .folder-tree-item.active > .folder-tree-label {
            background: #dbeafe;
        }

        .folder-toggle {
            width: 16px;
            text-align: center;
            cursor: pointer;
            color: #9ca3af;
            font-size: 10px;
            transition: transform 0.2s;
            flex-shrink: 0;
        }

        .folder-toggle.expanded {
            transform: rotate(90deg);
        }

        .folder-toggle-spacer {
            width: 16px;
            flex-shrink: 0;
        }

        .folder-name {
            color: #374151;
            text-decoration: none;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .folder-name:hover {
            color: #3b82f6;
        }

        .folder-count {
            color: #9ca3af;
            font-size: 11px;
            margin-left: 4px;
            flex-shrink: 0;
        }

        .folder-children {
            display: none;
        }

        .folder-children.expanded {
            display: block;
        }

        /* Breadcrumb styles */
        .folder-breadcrumbs {
            margin-bottom: 16px;
        }

        .breadcrumb-trail {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 13px;
        }

        .breadcrumb-item a {
            color: #6b7280;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: #3b82f6;
        }

        .breadcrumb-item .active {
            color: #374151;
            font-weight: 600;
        }

        .breadcrumb-separator {
            color: #d1d5db;
            font-size: 10px;
            margin-right: 4px;
        }

        /* Subfolder row in list view */
        .subfolder-row td {
            background: #fafbfc;
        }

        .subfolder-row:hover td {
            background: #f0f4f8;
        }

        /* Subfolder card in grid view */
        .subfolder-card {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 16px;
            text-align: center;
            transition: all 0.2s;
            background: #fafbfc;
            position: relative;
        }

        .subfolder-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
            background: white;
        }

        /* ==================== Drag and Drop Visual Feedback ==================== */
        .folder-tree-label.drop-hover {
            background: #dbeafe !important;
            outline: 2px solid #3b82f6;
            outline-offset: -2px;
        }

        .folder-tree-item.drop-disabled .folder-tree-label {
            opacity: 0.4;
            cursor: not-allowed;
        }

        body.is-dragging [data-draggable-item] {
            cursor: grabbing;
        }

        [data-draggable-item] {
            cursor: grab;
        }

        [data-draggable-item]:active {
            cursor: grabbing;
        }

        .breadcrumb-item.drop-hover a {
            background: #dbeafe;
            border-radius: 3px;
            padding: 2px 6px;
        }

        /* Mobile folder toggle button */
        .mobile-folder-toggle {
            display: none !important;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .documents-header {
                padding: 20px;
            }

            .toolbar-row {
                flex-direction: column;
                align-items: stretch;
            }

            .documents-grid {
                grid-template-columns: 1fr;
            }

            .documents-dual-pane {
                flex-direction: column;
            }

            .folder-sidebar {
                width: 100%;
                min-width: 100%;
                max-height: 0;
                overflow: hidden;
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
                transition: max-height 0.3s ease;
            }

            .folder-sidebar.mobile-open {
                max-height: 400px;
                overflow-y: auto;
            }

            .folder-sidebar.collapsed {
                width: 100%;
                min-width: 100%;
            }

            .folder-sidebar-rail {
                display: none !important;
            }

            .documents-main {
                padding: 16px;
            }

            .mobile-folder-toggle {
                display: inline-flex !important;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('documents.index') }}">Documents</a>
        @endslot
        @slot('title')
            Document Management
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

    <div class="documents-container">
        <div class="documents-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin:0;">Documents</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        @if($currentFolder)
                            <i class="fas fa-folder-open me-1"></i> {{ $currentFolder->name }}
                        @else
                            Browse and manage school documents
                        @endif
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="row text-center justify-content-end">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $documents->total() }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        @if($currentFolder)
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $subfolders->count() }}</h4>
                                <small class="opacity-75">Folders</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="documents-dual-pane">
            @include('documents.partials._folder-sidebar')

            <div class="documents-main">
                @include('documents.partials._breadcrumbs')

                {{-- Quota Warning Banner --}}
                @if(isset($userQuota) && !$userQuota->is_unlimited && $userQuota->is_warning)
                <div class="alert alert-warning d-flex align-items-center mb-3" style="border-left: 4px solid #f59e0b;">
                    <i class="bx bx-error-circle me-2 fs-5"></i>
                    <div>
                        <strong>Storage Warning:</strong> You are using {{ number_format($userQuota->usage_percent, 0) }}% of your storage quota
                        ({{ $usedFormatted }} of {{ $totalFormatted }}).
                        Please review and remove unused documents to free up space.
                    </div>
                </div>
                @endif

                <div class="help-text">
                    <div class="help-title">Document Management</div>
                    <div class="help-content">
                        Browse, upload, and manage school documents. Use the view toggle to switch between list and grid views.
                        Select multiple documents for bulk actions.
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-3">
                    {{-- Mobile folder toggle --}}
                    <button type="button" class="btn btn-secondary mobile-folder-toggle" id="mobile-folder-btn" onclick="toggleMobileSidebar()">
                        <i class="fas fa-folder"></i> Folders
                    </button>
                    <a href="{{ route('documents.trash') }}" class="btn btn-secondary">
                        <i class="fas fa-trash-alt"></i> Trash
                    </a>
                    <a href="{{ route('documents.create', ($canUploadToCurrentFolder && $currentFolder) ? ['folder' => $currentFolder->id] : []) }}" class="btn btn-primary">
                        <i class="fas fa-cloud-upload-alt"></i> Upload
                    </a>
                </div>

                {{-- Bulk Actions Toolbar --}}
                @include('documents.partials._bulk-actions')

                {{-- Toolbar: View Toggle + Sort --}}
                <div class="toolbar-row">
                    <div class="d-flex align-items-center gap-3">
                        {{-- View Toggle --}}
                        <div class="view-toggle">
                            <button type="button" class="btn" id="list-view-btn" title="List view"
                                onclick="setView('list')">
                                <i class="fas fa-list"></i>
                            </button>
                            <button type="button" class="btn" id="grid-view-btn" title="Grid view"
                                onclick="setView('grid')">
                                <i class="fas fa-th"></i>
                            </button>
                        </div>

                        {{-- Favorites Toggle (SRC-07) --}}
                        <a href="{{ request()->fullUrlWithQuery(['only_favorites' => request('only_favorites') ? null : 1]) }}"
                           class="btn btn-sm {{ request('only_favorites') ? 'btn-warning' : 'btn-outline-secondary' }}"
                           title="Show Favorites Only">
                            <i class="fas fa-star"></i>
                            {{ request('only_favorites') ? 'All Documents' : 'Favorites' }}
                        </a>

                        {{-- Archived Documents Toggle (WFL-11) --}}
                        <a href="{{ request()->fullUrlWithQuery(['include_archived' => request('include_archived') ? null : 1]) }}"
                           class="btn btn-sm {{ request('include_archived') ? 'btn-secondary' : 'btn-outline-secondary' }}"
                           title="Show Archived Documents">
                            <i class="fas fa-archive"></i>
                            {{ request('include_archived') ? 'Hide Archived' : 'Show Archived' }}
                        </a>

                        {{-- Search Page --}}
                        <a href="{{ route('documents.search') }}"
                           class="btn btn-sm btn-outline-secondary"
                           title="Advanced Search">
                            <i class="fas fa-search"></i> Search
                        </a>

                        {{-- Sort Dropdown (for grid view / mobile) --}}
                        <div class="dropdown sort-dropdown">
                            <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-sort-amount-down me-1"></i>
                                Sort by:
                                @switch($sortBy)
                                    @case('title')
                                        Name
                                    @break

                                    @case('size_bytes')
                                        Size
                                    @break

                                    @case('extension')
                                        Type
                                    @break

                                    @default
                                        Date
                                    @break
                                @endswitch
                                ({{ $sortDir === 'asc' ? 'A-Z' : 'Z-A' }})
                            </button>
                            <ul class="dropdown-menu">
                                @php
                                    $sortOptions = [
                                        'title' => 'Name',
                                        'created_at' => 'Date',
                                        'size_bytes' => 'Size',
                                        'extension' => 'Type',
                                    ];
                                @endphp
                                @foreach ($sortOptions as $field => $label)
                                    @php
                                        $isActive = $sortBy === $field;
                                        $nextDir = $isActive && $sortDir === 'asc' ? 'desc' : 'asc';
                                    @endphp
                                    <li>
                                        <a class="dropdown-item {{ $isActive ? 'active' : '' }}"
                                            href="{{ route('documents.index', ['sort' => $field, 'direction' => $nextDir, 'folder' => $currentFolder->id ?? null]) }}">
                                            {{ $label }}
                                            @if ($isActive)
                                                <i class="fas fa-{{ $sortDir === 'asc' ? 'arrow-up' : 'arrow-down' }} ms-2"
                                                    style="font-size: 11px;"></i>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="text-muted" style="font-size: 13px;">
                        Showing {{ $documents->firstItem() ?? 0 }}-{{ $documents->lastItem() ?? 0 }} of
                        {{ $documents->total() }}
                    </div>
                </div>

                @if ($documents->count() > 0 || $subfolders->count() > 0)
                    {{-- List View (default) --}}
                    <div id="list-view">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 30px;"></th>
                                        <th style="width: 40px;">
                                            <input type="checkbox" class="form-check-input" id="select-all"
                                                onchange="toggleSelectAll(this)">
                                        </th>
                                        <th>
                                            @php
                                                $nameDir = $sortBy === 'title' && $sortDir === 'asc' ? 'desc' : 'asc';
                                            @endphp
                                            <a href="{{ route('documents.index', ['sort' => 'title', 'direction' => $nameDir, 'folder' => $currentFolder->id ?? null]) }}"
                                                class="sort-link {{ $sortBy === 'title' ? 'active' : '' }}">
                                                Name
                                                <i
                                                    class="fas fa-{{ $sortBy === 'title' ? ($sortDir === 'asc' ? 'arrow-up' : 'arrow-down') : 'sort' }} sort-icon"></i>
                                            </a>
                                        </th>
                                        <th style="width: 130px;">Owner</th>
                                        <th style="width: 70px;">Type</th>
                                        <th style="width: 90px;">
                                            @php
                                                $sizeDir = $sortBy === 'size_bytes' && $sortDir === 'asc' ? 'desc' : 'asc';
                                            @endphp
                                            <a href="{{ route('documents.index', ['sort' => 'size_bytes', 'direction' => $sizeDir, 'folder' => $currentFolder->id ?? null]) }}"
                                                class="sort-link {{ $sortBy === 'size_bytes' ? 'active' : '' }}" style="white-space: nowrap;">
                                                Size
                                                <i
                                                    class="fas fa-{{ $sortBy === 'size_bytes' ? ($sortDir === 'asc' ? 'arrow-up' : 'arrow-down') : 'sort' }} sort-icon"></i>
                                            </a>
                                        </th>
                                        <th style="width: 110px;">
                                            @php
                                                $dateDir = $sortBy === 'created_at' && $sortDir === 'asc' ? 'desc' : 'asc';
                                            @endphp
                                            <a href="{{ route('documents.index', ['sort' => 'created_at', 'direction' => $dateDir, 'folder' => $currentFolder->id ?? null]) }}"
                                                class="sort-link {{ $sortBy === 'created_at' ? 'active' : '' }}">
                                                Date
                                                <i
                                                    class="fas fa-{{ $sortBy === 'created_at' ? ($sortDir === 'asc' ? 'arrow-up' : 'arrow-down') : 'sort' }} sort-icon"></i>
                                            </a>
                                        </th>
                                        <th style="width: 100px;">Status</th>
                                        <th style="width: 70px;" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Subfolders first --}}
                                    @if($subfolders->count() > 0)
                                        @foreach ($subfolders as $folder)
                                            @include('documents.partials._folder-row', ['folder' => $folder])
                                        @endforeach
                                    @endif
                                    {{-- Then documents --}}
                                    @foreach ($documents as $document)
                                        @include('documents.partials._document-row', ['document' => $document])
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Grid View --}}
                    <div id="grid-view" style="display: none;">
                        <div class="documents-grid">
                            {{-- Subfolder cards first --}}
                            @if($subfolders->count() > 0)
                                @foreach ($subfolders as $folder)
                                    @include('documents.partials._folder-card', ['folder' => $folder])
                                @endforeach
                            @endif
                            {{-- Then document cards --}}
                            @foreach ($documents as $document)
                                @include('documents.partials._document-card', ['document' => $document])
                            @endforeach
                        </div>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $documents->links() }}
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="empty-state">
                        <i class="fas fa-folder-open d-block"></i>
                        <h5>No documents found</h5>
                        <p>
                            @if($currentFolder)
                                This folder is empty. Upload a document or create a subfolder.
                            @else
                                Upload your first document to get started.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- New Folder Modal --}}
    <div class="modal fade" id="newFolderModal" tabindex="-1" aria-labelledby="newFolderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newFolderModalLabel">Create New Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="new-folder-form">
                        <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? '' }}">
                        @php
                            $isSubfolderContext = isset($currentFolder) && $currentFolder !== null;
                            $parentScopeLabel = $isSubfolderContext && in_array($currentFolder->visibility, [\App\Models\DocumentFolder::VISIBILITY_INTERNAL, \App\Models\DocumentFolder::VISIBILITY_PUBLIC], true)
                                ? 'Public'
                                : 'Private';
                        @endphp
                        <div class="mb-3">
                            <label for="folder-name" class="form-label fw-semibold">Folder Name</label>
                            <input type="text" class="form-control" id="folder-name" name="name" required maxlength="100" placeholder="Enter folder name">
                            <div class="invalid-feedback" id="folder-name-error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="folder-description" class="form-label fw-semibold">Description <span class="text-muted fw-normal">(optional)</span></label>
                            <textarea class="form-control" id="folder-description" name="description" rows="2" maxlength="500" placeholder="Brief description"></textarea>
                        </div>
                        <div class="mb-3{{ $isSubfolderContext ? ' d-none' : '' }}" id="folder-access-scope-wrapper">
                            <label for="folder-access-scope" class="form-label fw-semibold">Access Scope</label>
                            <select class="form-select" id="folder-access-scope" name="access_scope" {{ $isSubfolderContext ? 'disabled' : '' }}>
                                <option value="private" selected>Private (only me)</option>
                                <option value="public">Public (visible to all staff)</option>
                            </select>
                            <div class="form-text">
                                Public folders are visible to all staff. Document visibility rules stay unchanged.
                            </div>
                        </div>
                        <div class="alert alert-info py-2 px-3 mb-0{{ $isSubfolderContext ? '' : ' d-none' }}" id="folder-access-inherited">
                            <i class="fas fa-info-circle me-1"></i>
                            Subfolders inherit access scope from parent folder ({{ $parentScopeLabel }}).
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm btn-loading" id="create-folder-btn" onclick="createFolder()">
                        <span class="btn-text"><i class="fas fa-folder-plus"></i> Create Folder</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Creating...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Move Modal --}}
    @include('documents.partials._move-modal')

    {{-- Rename Folder Modal --}}
    <div class="modal fade" id="renameFolderModal" tabindex="-1" aria-labelledby="renameFolderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="renameFolderModalLabel">Rename Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rename-folder-form">
                        <input type="hidden" id="rename-folder-id" name="folder_id" value="">
                        <div class="mb-3">
                            <label for="rename-folder-name" class="form-label fw-semibold">New Name</label>
                            <input type="text" class="form-control" id="rename-folder-name" name="name" required maxlength="100">
                            <div class="invalid-feedback" id="rename-folder-error"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm btn-loading" id="rename-folder-btn" onclick="renameFolder()">
                        <span class="btn-text"><i class="fas fa-save"></i> Save</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        // ==================== Favorite Toggle ====================
        function toggleFavorite(documentId, starElement) {
            $.ajax({
                url: '/documents/' + documentId + '/favorite',
                method: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.is_favorited) {
                        starElement.classList.remove('far');
                        starElement.classList.add('fas');
                        starElement.style.color = '#f59e0b';
                    } else {
                        starElement.classList.remove('fas');
                        starElement.classList.add('far');
                        starElement.style.color = '#d1d5db';
                    }
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'success',
                        title: response.is_favorited ? 'Added to favorites' : 'Removed from favorites',
                        showConfirmButton: false, timer: 1500
                    });
                },
                error: function() {
                    Swal.fire('Error', 'Failed to update favorite.', 'error');
                }
            });
        }

        // ==================== View Toggle ====================
        function setView(view) {
            var listView = document.getElementById('list-view');
            var gridView = document.getElementById('grid-view');
            var listBtn = document.getElementById('list-view-btn');
            var gridBtn = document.getElementById('grid-view-btn');

            if (!listView || !gridView) return;

            if (view === 'grid') {
                listView.style.display = 'none';
                gridView.style.display = 'block';
                listBtn.classList.remove('active');
                gridBtn.classList.add('active');
            } else {
                listView.style.display = 'block';
                gridView.style.display = 'none';
                listBtn.classList.add('active');
                gridBtn.classList.remove('active');
            }

            localStorage.setItem('doc-view', view);
        }

        // Restore view preference on load
        (function() {
            var savedView = localStorage.getItem('doc-view') || 'list';
            setView(savedView);
        })();

        // ==================== Sidebar Toggle ====================
        (function() {
            var sidebar = document.getElementById('folder-sidebar');
            var rail = document.getElementById('folder-sidebar-rail');
            var toggleBtn = document.getElementById('sidebar-toggle');
            var expandBtn = document.getElementById('rail-expand');

            if (!sidebar || !rail) return;

            function collapseSidebar() {
                sidebar.classList.add('collapsed');
                rail.classList.add('visible');
                localStorage.setItem('doc-sidebar', 'collapsed');
            }

            function expandSidebar() {
                sidebar.classList.remove('collapsed');
                rail.classList.remove('visible');
                localStorage.setItem('doc-sidebar', 'expanded');
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', collapseSidebar);
            }

            if (expandBtn) {
                expandBtn.addEventListener('click', expandSidebar);
            }

            // Restore sidebar state
            var savedState = localStorage.getItem('doc-sidebar');
            if (savedState === 'collapsed') {
                collapseSidebar();
            }
        })();

        // ==================== Folder Tree Expand/Collapse ====================
        (function() {
            var expandedFolders = [];
            try {
                expandedFolders = JSON.parse(localStorage.getItem('doc-expanded-folders')) || [];
            } catch(e) {
                expandedFolders = [];
            }

            // Restore expanded state on page load
            expandedFolders.forEach(function(folderId) {
                var toggle = document.querySelector('.folder-toggle[data-folder="' + folderId + '"]');
                var children = document.querySelector('.folder-children[data-parent="' + folderId + '"]');
                if (toggle && children) {
                    toggle.classList.add('expanded');
                    children.classList.add('expanded');
                }
            });

            // Also auto-expand ancestors of the current active folder
            var activeItem = document.querySelector('.folder-tree-item.active');
            if (activeItem) {
                var parent = activeItem.parentElement;
                while (parent) {
                    if (parent.classList && parent.classList.contains('folder-children')) {
                        parent.classList.add('expanded');
                        var parentId = parent.getAttribute('data-parent');
                        var parentToggle = document.querySelector('.folder-toggle[data-folder="' + parentId + '"]');
                        if (parentToggle) {
                            parentToggle.classList.add('expanded');
                        }
                        // Add to expanded list if not already there
                        if (expandedFolders.indexOf(parseInt(parentId)) === -1) {
                            expandedFolders.push(parseInt(parentId));
                        }
                    }
                    parent = parent.parentElement;
                }
                localStorage.setItem('doc-expanded-folders', JSON.stringify(expandedFolders));
            }

            // Click handlers for folder toggles
            document.querySelectorAll('.folder-toggle').forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var folderId = parseInt(this.getAttribute('data-folder'));
                    var children = document.querySelector('.folder-children[data-parent="' + folderId + '"]');

                    if (!children) return;

                    var isExpanded = this.classList.contains('expanded');

                    if (isExpanded) {
                        this.classList.remove('expanded');
                        children.classList.remove('expanded');
                        expandedFolders = expandedFolders.filter(function(id) { return id !== folderId; });
                    } else {
                        this.classList.add('expanded');
                        children.classList.add('expanded');
                        if (expandedFolders.indexOf(folderId) === -1) {
                            expandedFolders.push(folderId);
                        }
                    }

                    localStorage.setItem('doc-expanded-folders', JSON.stringify(expandedFolders));
                });
            });
        })();

        // ==================== Mobile Sidebar Toggle ====================
        function toggleMobileSidebar() {
            var sidebar = document.getElementById('folder-sidebar');
            if (sidebar) {
                sidebar.classList.toggle('mobile-open');
            }
        }

        // ==================== Bulk Selection ====================
        var selectedIds = new Set();
        var selectedDocIds = new Set();
        var selectedFolderIds = new Set();

        function toggleSelect(id, checkbox) {
            var itemType = checkbox.dataset.type || 'document';
            if (checkbox.checked) {
                selectedIds.add(id);
                if (itemType === 'folder') {
                    selectedFolderIds.add(id);
                } else {
                    selectedDocIds.add(id);
                }
            } else {
                selectedIds.delete(id);
                if (itemType === 'folder') {
                    selectedFolderIds.delete(id);
                } else {
                    selectedDocIds.delete(id);
                }
            }
            updateBulkToolbar();
            updateSelectAllCheckbox();
        }

        function toggleSelectAll(masterCheckbox) {
            document.querySelectorAll('.doc-checkbox').forEach(function(cb) {
                cb.checked = masterCheckbox.checked;
                var id = parseInt(cb.dataset.id);
                if (masterCheckbox.checked) {
                    selectedIds.add(id);
                    selectedDocIds.add(id);
                } else {
                    selectedIds.delete(id);
                    selectedDocIds.delete(id);
                }
            });
            document.querySelectorAll('.folder-checkbox').forEach(function(cb) {
                cb.checked = masterCheckbox.checked;
                var id = parseInt(cb.dataset.id);
                if (masterCheckbox.checked) {
                    selectedIds.add(id);
                    selectedFolderIds.add(id);
                } else {
                    selectedIds.delete(id);
                    selectedFolderIds.delete(id);
                }
            });
            updateBulkToolbar();
        }

        function updateSelectAllCheckbox() {
            var allCheckboxes = document.querySelectorAll('.doc-checkbox, .folder-checkbox');
            var selectAll = document.getElementById('select-all');
            if (selectAll && allCheckboxes.length > 0) {
                var allChecked = true;
                allCheckboxes.forEach(function(cb) {
                    if (!cb.checked) allChecked = false;
                });
                selectAll.checked = allChecked;
            }
        }

        function updateBulkToolbar() {
            var toolbar = document.getElementById('bulk-toolbar');
            var count = document.getElementById('selected-count');
            var totalSelected = selectedDocIds.size + selectedFolderIds.size;
            if (totalSelected > 0) {
                toolbar.style.display = 'flex';
                count.textContent = totalSelected;
            } else {
                toolbar.style.display = 'none';
            }
        }

        function deselectAll() {
            selectedIds.clear();
            selectedDocIds.clear();
            selectedFolderIds.clear();
            document.querySelectorAll('.doc-checkbox, .folder-checkbox').forEach(function(cb) {
                cb.checked = false;
            });
            var selectAll = document.getElementById('select-all');
            if (selectAll) selectAll.checked = false;
            updateBulkToolbar();
        }

        // ==================== Bulk Actions ====================
        function bulkDelete() {
            var docIds = Array.from(selectedDocIds);
            var folderIds = Array.from(selectedFolderIds);
            var totalSelected = docIds.length + folderIds.length;

            if (totalSelected === 0) return;

            var copy = getBulkDeleteCopy(docIds.length, folderIds.length);

            Swal.fire({
                title: copy.title,
                text: copy.text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete them'
            }).then(function(result) {
                if (result.isConfirmed) {
                    executeBulkDelete(folderIds, docIds, []);
                }
            });
        }

        function getBulkDeleteCopy(docCount, folderCount) {
            if (docCount > 0 && folderCount > 0) {
                return {
                    title: 'Delete Selected Items?',
                    text: docCount + ' document(s) and ' + folderCount + ' folder(s) will be moved to trash.'
                };
            }

            if (folderCount > 0) {
                return {
                    title: 'Delete Selected Folders?',
                    text: folderCount + ' folder(s) and all their contents will be moved to trash.'
                };
            }

            return {
                title: 'Delete Selected Documents?',
                text: docCount + ' document(s) will be moved to trash.'
            };
        }

        function executeBulkDelete(folderIds, docIds, messages) {
            if (folderIds.length > 0) {
                $.ajax({
                    url: '{{ route('documents.folders.bulk.destroy') }}',
                    method: 'POST',
                    data: {
                        ids: folderIds,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        messages.push(response.message);
                        executeBulkDelete([], docIds, messages);
                    },
                    error: function(xhr) {
                        handleBulkDeleteError(xhr, messages, 'An error occurred while deleting folders.');
                    }
                });
                return;
            }

            if (docIds.length > 0) {
                $.ajax({
                    url: '{{ route('documents.bulk.delete') }}',
                    method: 'POST',
                    data: {
                        ids: docIds,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        messages.push(response.message);
                        showBulkDeleteSuccess(messages);
                    },
                    error: function(xhr) {
                        handleBulkDeleteError(xhr, messages, 'An error occurred while deleting documents.');
                    }
                });
                return;
            }

            showBulkDeleteSuccess(messages);
        }

        function showBulkDeleteSuccess(messages) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: messages.join(' '),
                showConfirmButton: false,
                timer: 3000
            });

            setTimeout(function() {
                location.reload();
            }, 1000);
        }

        function handleBulkDeleteError(xhr, previousMessages, fallbackMessage) {
            var msg = fallbackMessage;

            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }

            if (previousMessages.length > 0) {
                msg = previousMessages.join(' ') + ' ' + msg;
            }

            Swal.fire('Error', msg, 'error').then(function() {
                if (previousMessages.length > 0) {
                    location.reload();
                }
            });
        }

        function bulkDownload() {
            if (selectedDocIds.size === 0) return;

            // Create a hidden form and submit it for download
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('documents.bulk.download') }}';
            form.style.display = 'none';

            // CSRF token
            var tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = $('meta[name="csrf-token"]').attr('content');
            form.appendChild(tokenInput);

            // Add each selected ID
            selectedDocIds.forEach(function(id) {
                var idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'ids[]';
                idInput.value = id;
                form.appendChild(idInput);
            });

            document.body.appendChild(form);
            form.submit();

            // Clean up after a short delay
            setTimeout(function() {
                document.body.removeChild(form);
            }, 1000);
        }

        // ==================== Single Delete Confirmation ====================
        function confirmSingleDelete(event) {
            event.preventDefault();
            var form = event.target.closest('form');

            Swal.fire({
                title: 'Delete Document?',
                text: 'This document will be moved to trash.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it'
            }).then(function(result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });

            return false;
        }

        // ==================== New Folder Modal ====================
        function showNewFolderModal() {
            document.getElementById('folder-name').value = '';
            document.getElementById('folder-description').value = '';
            document.getElementById('folder-name').classList.remove('is-invalid');
            var accessScopeInput = document.getElementById('folder-access-scope');
            if (accessScopeInput && !accessScopeInput.disabled) {
                accessScopeInput.value = 'private';
            }
            var modal = new bootstrap.Modal(document.getElementById('newFolderModal'));
            modal.show();
        }

        function createFolder() {
            var nameInput = document.getElementById('folder-name');
            var descInput = document.getElementById('folder-description');
            var accessScopeInput = document.getElementById('folder-access-scope');
            var btn = document.getElementById('create-folder-btn');
            var name = nameInput.value.trim();

            if (!name) {
                nameInput.classList.add('is-invalid');
                document.getElementById('folder-name-error').textContent = 'Folder name is required.';
                return;
            }

            nameInput.classList.remove('is-invalid');
            btn.classList.add('loading');
            btn.disabled = true;

            $.ajax({
                url: '{{ route('documents.folders.store') }}',
                method: 'POST',
                data: {
                    name: name,
                    description: descInput.value.trim() || null,
                    parent_id: '{{ $currentFolder->id ?? '' }}' || null,
                    access_scope: accessScopeInput && !accessScopeInput.disabled ? accessScopeInput.value : null,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    bootstrap.Modal.getInstance(document.getElementById('newFolderModal')).hide();
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message || 'Folder created successfully.',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    btn.classList.remove('loading');
                    btn.disabled = false;

                    var msg = 'Failed to create folder.';
                    if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.name) {
                        nameInput.classList.add('is-invalid');
                        document.getElementById('folder-name-error').textContent = xhr.responseJSON.errors.name[0];
                        return;
                    }
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', msg, 'error');
                }
            });
        }

        // ==================== Rename Folder ====================
        $(document).on('click', '.rename-folder-btn', function(e) {
            e.preventDefault();
            var folderId = $(this).data('folder-id');
            var folderName = $(this).data('folder-name');
            document.getElementById('rename-folder-id').value = folderId;
            document.getElementById('rename-folder-name').value = folderName;
            document.getElementById('rename-folder-name').classList.remove('is-invalid');
            var modal = new bootstrap.Modal(document.getElementById('renameFolderModal'));
            modal.show();
        });

        function renameFolder() {
            var folderId = document.getElementById('rename-folder-id').value;
            var nameInput = document.getElementById('rename-folder-name');
            var btn = document.getElementById('rename-folder-btn');
            var name = nameInput.value.trim();

            if (!name) {
                nameInput.classList.add('is-invalid');
                document.getElementById('rename-folder-error').textContent = 'Folder name is required.';
                return;
            }

            nameInput.classList.remove('is-invalid');
            btn.classList.add('loading');
            btn.disabled = true;

            $.ajax({
                url: '/documents/folders/' + folderId,
                method: 'PUT',
                data: {
                    name: name,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    bootstrap.Modal.getInstance(document.getElementById('renameFolderModal')).hide();
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message || 'Folder renamed successfully.',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    btn.classList.remove('loading');
                    btn.disabled = false;

                    var msg = 'Failed to rename folder.';
                    if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.name) {
                        nameInput.classList.add('is-invalid');
                        document.getElementById('rename-folder-error').textContent = xhr.responseJSON.errors.name[0];
                        return;
                    }
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', msg, 'error');
                }
            });
        }

        // ==================== Toggle Folder Access ====================
        $(document).on('click', '.toggle-folder-access-btn', function(e) {
            e.preventDefault();
            var folderId = $(this).data('folder-id');
            var folderName = $(this).data('folder-name');
            var targetScope = $(this).data('target-access');
            var makePublic = targetScope === 'public';

            Swal.fire({
                title: makePublic ? 'Make Folder Public?' : 'Make Folder Private?',
                html: '"' + folderName + '" and all subfolders will become <strong>' + (makePublic ? 'public' : 'private') + '</strong>.<br><br>Document-level visibility is not changed.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: makePublic ? 'Yes, make public' : 'Yes, make private'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: '/documents/folders/' + folderId + '/access',
                    method: 'PATCH',
                    data: {
                        access_scope: targetScope,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.message || 'Folder access updated.',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        var msg = 'Failed to update folder access.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });
        });

        // ==================== Delete Folder ====================
        $(document).on('click', '.delete-folder-btn', function(e) {
            e.preventDefault();
            var folderId = $(this).data('folder-id');
            var folderName = $(this).data('folder-name');

            Swal.fire({
                title: 'Delete Folder?',
                text: '"' + folderName + '" and all its contents will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/documents/folders/' + folderId,
                        method: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message || 'Folder deleted successfully.',
                                showConfirmButton: false,
                                timer: 2000
                            });
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        },
                        error: function(xhr) {
                            var msg = 'Failed to delete folder.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        });

        // ==================== Move Modal ====================
        var moveTargetFolderId = null;
        var moveTargetRepo = null;

        function showMoveModal() {
            if (selectedDocIds.size === 0 && selectedFolderIds.size === 0) return;

            var totalItems = selectedDocIds.size + selectedFolderIds.size;
            document.getElementById('move-modal-count').textContent = totalItems;

            // Reset state
            moveTargetFolderId = null;
            moveTargetRepo = null;
            document.getElementById('move-confirm-btn').disabled = true;
            document.getElementById('move-tree-loading').style.display = 'block';
            document.getElementById('move-tree-container').style.display = 'none';
            document.getElementById('move-tree-error').style.display = 'none';
            document.getElementById('move-tree-content').innerHTML = '';

            // Remove any previous selection
            document.querySelectorAll('.move-tree-item.selected').forEach(function(el) {
                el.classList.remove('selected');
            });

            var modal = new bootstrap.Modal(document.getElementById('move-modal'));
            modal.show();

            // Fetch folder tree via AJAX
            $.ajax({
                url: '{{ route("documents.folders.tree") }}',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    document.getElementById('move-tree-loading').style.display = 'none';
                    document.getElementById('move-tree-container').style.display = 'block';

                    var treeContainer = document.getElementById('move-tree-content');
                    treeContainer.innerHTML = '';
                    var treeData = response && response.tree ? response.tree : response;
                    var isDocumentsAdmin = @json(\App\Policies\DocumentFolderPolicy::isAdmin(auth()->user()));

                    // Render each repository section
                    var repoLabels = {
                        'personal': { label: isDocumentsAdmin ? 'Personal Repositories' : 'My Documents', icon: 'fa-user' },
                        'public': { label: 'Public', icon: 'fa-globe' },
                        'institutional': { label: 'Institutional', icon: 'fa-building' },
                        'shared': { label: 'Shared', icon: 'fa-share-alt' },
                        'department': { label: 'Department', icon: 'fa-users' }
                    };

                    var repoTypes = ['personal', 'public', 'institutional', 'shared', 'department'];
                    repoTypes.forEach(function(repoType) {
                        var folders = treeData[repoType] || [];
                        if (folders.length === 0) return;

                        var sectionDiv = document.createElement('div');
                        sectionDiv.style.cssText = 'margin-bottom: 8px;';

                        var headerDiv = document.createElement('div');
                        headerDiv.style.cssText = 'padding: 6px 12px; font-size: 11px; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px;';
                        headerDiv.innerHTML = '<i class="fas ' + repoLabels[repoType].icon + '" style="width: 16px; text-align: center; margin-right: 4px;"></i> ' + repoLabels[repoType].label;
                        sectionDiv.appendChild(headerDiv);

                        var effectiveRepoType = repoType === 'public' ? 'personal' : repoType;
                        renderMoveTree(folders, sectionDiv, 0, effectiveRepoType);
                        treeContainer.appendChild(sectionDiv);
                    });

                    // Bind click handler for root option
                    var rootItem = document.querySelector('#move-tree-container > .move-tree-item[data-target-folder=""]');
                    if (rootItem) {
                        rootItem.onclick = function() {
                            selectMoveTarget(rootItem, null, '');
                        };
                    }
                },
                error: function() {
                    document.getElementById('move-tree-loading').style.display = 'none';
                    document.getElementById('move-tree-error').style.display = 'block';
                }
            });
        }

        function renderMoveTree(folders, container, depth, repoType) {
            var currentFolderId = {{ $currentFolder->id ?? 'null' }};
            var draggedFolderIds = Array.from(selectedFolderIds);

            folders.forEach(function(folder) {
                var wrapper = document.createElement('div');

                var isCurrentFolder = (folder.id === currentFolderId);
                var isSelf = (draggedFolderIds.indexOf(folder.id) !== -1);
                var isDisabled = isCurrentFolder || isSelf;

                var hasChildren = folder.children && folder.children.length > 0;

                var itemDiv = document.createElement('div');
                itemDiv.className = 'move-tree-item' + (isDisabled ? ' disabled' : '');
                itemDiv.setAttribute('data-target-folder', folder.id);
                itemDiv.setAttribute('data-repository', repoType);
                itemDiv.style.paddingLeft = (12 + depth * 20) + 'px';

                // Toggle arrow for folders with children
                if (hasChildren) {
                    var toggleSpan = document.createElement('span');
                    toggleSpan.className = 'move-tree-toggle';
                    toggleSpan.innerHTML = '<i class="fas fa-chevron-right"></i>';
                    toggleSpan.onclick = function(e) {
                        e.stopPropagation();
                        var childrenDiv = wrapper.querySelector('.move-tree-children');
                        if (childrenDiv) {
                            var isExpanded = childrenDiv.style.display !== 'none';
                            childrenDiv.style.display = isExpanded ? 'none' : 'block';
                            toggleSpan.classList.toggle('expanded', !isExpanded);
                        }
                    };
                    itemDiv.appendChild(toggleSpan);
                } else {
                    var spacer = document.createElement('span');
                    spacer.className = 'move-tree-toggle-spacer';
                    itemDiv.appendChild(spacer);
                }

                var folderIcon = document.createElement('i');
                folderIcon.className = 'fas fa-folder';
                folderIcon.style.cssText = 'color: #f59e0b; width: 16px; text-align: center;';
                itemDiv.appendChild(folderIcon);

                var nameSpan = document.createElement('span');
                nameSpan.textContent = folder.name;
                nameSpan.style.cssText = 'flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;';
                itemDiv.appendChild(nameSpan);

                var shouldShowOwnerLabel = folder.repository_type === 'personal'
                    && folder.owner_name
                    && Number(folder.owner_id) !== Number(@json(auth()->id()))
                    && @json(\App\Policies\DocumentFolderPolicy::isAdmin(auth()->user()));

                if (shouldShowOwnerLabel) {
                    var ownerSpan = document.createElement('span');
                    ownerSpan.style.cssText = 'color: #6b7280; font-size: 11px; margin-left: 6px; flex-shrink: 0;';
                    ownerSpan.textContent = folder.owner_name;
                    itemDiv.appendChild(ownerSpan);
                }

                if (folder.document_count !== undefined) {
                    var countSpan = document.createElement('span');
                    countSpan.style.cssText = 'color: #9ca3af; font-size: 11px; flex-shrink: 0;';
                    countSpan.textContent = '(' + folder.document_count + ')';
                    itemDiv.appendChild(countSpan);
                }

                if (!isDisabled) {
                    itemDiv.onclick = function(e) {
                        if (e.target.closest('.move-tree-toggle')) return;
                        selectMoveTarget(itemDiv, folder.id, repoType);
                    };
                }

                wrapper.appendChild(itemDiv);

                // Render children
                if (hasChildren) {
                    var childrenDiv = document.createElement('div');
                    childrenDiv.className = 'move-tree-children';
                    childrenDiv.style.display = 'none';
                    renderMoveTree(folder.children, childrenDiv, depth + 1, repoType);
                    wrapper.appendChild(childrenDiv);
                }

                container.appendChild(wrapper);
            });
        }

        function selectMoveTarget(element, folderId, repoType) {
            // Remove previous selection
            document.querySelectorAll('.move-tree-item.selected').forEach(function(el) {
                el.classList.remove('selected');
            });

            element.classList.add('selected');
            moveTargetFolderId = folderId;
            moveTargetRepo = repoType;
            document.getElementById('move-confirm-btn').disabled = false;
        }

        function confirmMove() {
            var btn = document.getElementById('move-confirm-btn');
            btn.classList.add('loading');
            btn.disabled = true;

            var currentRepo = '{{ $currentFolder->repository_type ?? "" }}';

            // Check for cross-repository move
            if (moveTargetRepo && currentRepo && moveTargetRepo !== currentRepo) {
                var repoLabels = {
                    'personal': 'Personal',
                    'institutional': 'Institutional (visible to all staff)',
                    'shared': 'Shared',
                    'department': 'Department'
                };

                btn.classList.remove('loading');
                btn.disabled = false;

                Swal.fire({
                    title: 'Move Across Repositories?',
                    html: 'Moving from <strong>' + (repoLabels[currentRepo] || currentRepo) + '</strong> to <strong>' + (repoLabels[moveTargetRepo] || moveTargetRepo) + '</strong>.<br><br>This may change who can see these items.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, move them'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                        executeModalMove();
                    }
                });
            } else {
                executeModalMove();
            }
        }

        function executeModalMove() {
            var requests = [];
            var docIds = Array.from(selectedDocIds);
            var folderIds = Array.from(selectedFolderIds);

            if (docIds.length > 0) {
                requests.push({ type: 'document', ids: docIds, target_folder_id: moveTargetFolderId });
            }
            if (folderIds.length > 0) {
                requests.push({ type: 'folder', ids: folderIds, target_folder_id: moveTargetFolderId });
            }

            if (requests.length === 0) return;

            var completed = 0;
            var totalMoved = 0;
            var errors = [];

            requests.forEach(function(req) {
                $.ajax({
                    url: '{{ route("documents.folders.move") }}',
                    method: 'POST',
                    data: $.extend(req, { _token: $('meta[name="csrf-token"]').attr('content') }),
                    success: function(response) {
                        totalMoved += Number(response && response.moved ? response.moved : 0);
                        completed++;
                        if (completed === requests.length) {
                            bootstrap.Modal.getInstance(document.getElementById('move-modal')).hide();
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: totalMoved + ' item(s) moved successfully.',
                                showConfirmButton: false,
                                timer: 2000
                            });
                            setTimeout(function() { location.reload(); }, 1000);
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Move failed.';
                        errors.push(msg);
                        completed++;
                        if (completed === requests.length) {
                            var btn = document.getElementById('move-confirm-btn');
                            btn.classList.remove('loading');
                            btn.disabled = false;
                            if (totalMoved > 0) {
                                bootstrap.Modal.getInstance(document.getElementById('move-modal')).hide();
                                location.reload();
                            } else {
                                Swal.fire('Error', errors.join('\n'), 'error');
                            }
                        }
                    }
                });
            });
        }

        // ==================== HTML5 Drag and Drop ====================
        function initDragAndDrop() {
            document.querySelectorAll('[data-draggable-item]').forEach(function(el) {
                el.setAttribute('draggable', 'true');

                el.addEventListener('dragstart', function(e) {
                    var itemType = el.dataset.type;  // 'document' or 'folder'
                    var itemId = parseInt(el.dataset.id);

                    // Collect IDs: if this item is in selection, drag all selected; otherwise drag just this one
                    var dragDocIds = [];
                    var dragFolderIds = [];
                    var dragType = itemType;

                    if (selectedIds.has(itemId) && (selectedDocIds.size + selectedFolderIds.size) > 1) {
                        dragDocIds = Array.from(selectedDocIds);
                        dragFolderIds = Array.from(selectedFolderIds);
                        if (dragDocIds.length > 0 && dragFolderIds.length > 0) {
                            dragType = 'mixed';
                        } else if (dragFolderIds.length > 0) {
                            dragType = 'folder';
                        } else {
                            dragType = 'document';
                        }
                    } else {
                        if (itemType === 'folder') {
                            dragFolderIds = [itemId];
                        } else {
                            dragDocIds = [itemId];
                        }
                    }

                    var totalCount = dragDocIds.length + dragFolderIds.length;

                    e.dataTransfer.setData('application/json', JSON.stringify({
                        type: dragType,
                        itemType: itemType,
                        docIds: dragDocIds,
                        folderIds: dragFolderIds,
                        count: totalCount
                    }));
                    e.dataTransfer.effectAllowed = 'move';

                    // Create custom drag ghost with count badge
                    var ghost = document.createElement('div');
                    ghost.style.cssText = 'position: absolute; top: -9999px; left: -9999px; background: #3b82f6; color: white; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);';
                    ghost.innerHTML = '<i class="fas fa-' + (itemType === 'folder' ? 'folder' : 'file') + '"></i> ' + totalCount + ' item' + (totalCount > 1 ? 's' : '');
                    document.body.appendChild(ghost);
                    e.dataTransfer.setDragImage(ghost, 10, 10);

                    // Remove ghost after a brief delay (browser captures image immediately)
                    setTimeout(function() {
                        if (ghost.parentNode) {
                            document.body.removeChild(ghost);
                        }
                    }, 0);

                    // Mark invalid drop targets
                    markInvalidDropTargets(dragType, dragFolderIds);

                    // Add dragging class to body for visual feedback
                    document.body.classList.add('is-dragging');
                });

                el.addEventListener('dragend', function(e) {
                    clearDropTargetStates();
                    document.body.classList.remove('is-dragging');
                });
            });
        }

        function initDropTargets() {
            document.querySelectorAll('.folder-tree-item').forEach(function(el) {
                el.addEventListener('dragover', function(e) {
                    if (!el.classList.contains('drop-disabled')) {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'move';
                        var label = el.querySelector('.folder-tree-label');
                        if (label) label.classList.add('drop-hover');
                    }
                });

                el.addEventListener('dragleave', function(e) {
                    var label = el.querySelector('.folder-tree-label');
                    if (label) label.classList.remove('drop-hover');
                });

                el.addEventListener('drop', function(e) {
                    e.preventDefault();
                    var label = el.querySelector('.folder-tree-label');
                    if (label) label.classList.remove('drop-hover');

                    var data = JSON.parse(e.dataTransfer.getData('application/json'));
                    var targetFolderId = parseInt(el.dataset.folderId);
                    var targetRepo = el.dataset.repository;
                    var currentRepo = '{{ $currentFolder->repository_type ?? "" }}';

                    // Cross-repository move check
                    if (targetRepo && currentRepo && targetRepo !== currentRepo) {
                        showCrossRepoConfirmation(data, targetFolderId, targetRepo, currentRepo);
                    } else {
                        submitDragMove(data, targetFolderId);
                    }
                });
            });

            // Also make the "All Documents" breadcrumb root a drop target (move to root/unfiled)
            var rootBreadcrumb = document.querySelector('.breadcrumb-item:first-child a');
            if (rootBreadcrumb) {
                rootBreadcrumb.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    rootBreadcrumb.style.background = '#dbeafe';
                    rootBreadcrumb.style.borderRadius = '3px';
                    rootBreadcrumb.style.padding = '2px 6px';
                });
                rootBreadcrumb.addEventListener('dragleave', function(e) {
                    rootBreadcrumb.style.background = '';
                    rootBreadcrumb.style.padding = '';
                });
                rootBreadcrumb.addEventListener('drop', function(e) {
                    e.preventDefault();
                    rootBreadcrumb.style.background = '';
                    rootBreadcrumb.style.padding = '';
                    var data = JSON.parse(e.dataTransfer.getData('application/json'));
                    submitDragMove(data, null); // null = root/unfiled
                });
            }
        }

        function markInvalidDropTargets(dragType, dragFolderIds) {
            var currentFolderId = {{ $currentFolder->id ?? 'null' }};

            document.querySelectorAll('.folder-tree-item').forEach(function(el) {
                var folderId = parseInt(el.dataset.folderId);

                // Invalid targets: the item itself (if folder), current folder (same location)
                var isCurrentFolder = (folderId === currentFolderId);
                var isSelf = (dragFolderIds.indexOf(folderId) !== -1);

                if (isCurrentFolder || isSelf) {
                    el.classList.add('drop-disabled');
                }
            });
        }

        function clearDropTargetStates() {
            document.querySelectorAll('.folder-tree-item').forEach(function(el) {
                el.classList.remove('drop-disabled');
                var label = el.querySelector('.folder-tree-label');
                if (label) label.classList.remove('drop-hover');
            });
        }

        function showCrossRepoConfirmation(data, targetFolderId, targetRepo, currentRepo) {
            var repoLabels = {
                'personal': 'Personal',
                'institutional': 'Institutional (visible to all staff)',
                'shared': 'Shared',
                'department': 'Department'
            };

            Swal.fire({
                title: 'Move Across Repositories?',
                html: 'Moving from <strong>' + (repoLabels[currentRepo] || currentRepo) + '</strong> to <strong>' + (repoLabels[targetRepo] || targetRepo) + '</strong>.<br><br>This may change who can see these items.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, move them'
            }).then(function(result) {
                if (result.isConfirmed) {
                    submitDragMove(data, targetFolderId);
                }
            });
        }

        function submitDragMove(data, targetFolderId) {
            var requests = [];

            if (data.docIds && data.docIds.length > 0) {
                requests.push({ type: 'document', ids: data.docIds, target_folder_id: targetFolderId });
            }
            if (data.folderIds && data.folderIds.length > 0) {
                requests.push({ type: 'folder', ids: data.folderIds, target_folder_id: targetFolderId });
            }

            if (requests.length === 0) return;

            var completed = 0;
            var totalMoved = 0;
            var errors = [];

            requests.forEach(function(req) {
                $.ajax({
                    url: '{{ route("documents.folders.move") }}',
                    method: 'POST',
                    data: $.extend(req, { _token: $('meta[name="csrf-token"]').attr('content') }),
                    success: function(response) {
                        totalMoved += Number(response && response.moved ? response.moved : 0);
                        completed++;
                        if (completed === requests.length) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: totalMoved + ' item(s) moved successfully.',
                                showConfirmButton: false,
                                timer: 2000
                            });
                            setTimeout(function() { location.reload(); }, 1000);
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Move failed.';
                        errors.push(msg);
                        completed++;
                        if (completed === requests.length) {
                            if (totalMoved > 0) {
                                location.reload();
                            } else {
                                Swal.fire('Error', errors.join('\n'), 'error');
                            }
                        }
                    }
                });
            });
        }

        // ==================== Initialize Drag-and-Drop on DOM Ready ====================
        $(document).ready(function() {
            initDragAndDrop();
            initDropTargets();
        });

        // ==================== Loading state for btn-loading ====================
        document.querySelectorAll('.btn-loading').forEach(function(btn) {
            // Loading state is managed by individual action functions
        });
    </script>
@endsection
