@extends('layouts.master')
@section('title')
    Search Documents
@endsection
@section('css')
    <style>
        .search-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .search-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .search-body {
            padding: 24px;
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

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        .controls .form-control:focus,
        .controls .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .table tbody tr td a {
            text-decoration: none;
            color: #1f2937;
            font-weight: 500;
        }

        .table tbody tr td a:hover {
            color: #3b82f6;
        }

        .doc-icon {
            width: 28px;
            text-align: center;
            font-size: 18px;
        }

        .doc-icon.pdf { color: #ef4444; }
        .doc-icon.word { color: #3b82f6; }
        .doc-icon.excel { color: #22c55e; }
        .doc-icon.ppt { color: #f97316; }
        .doc-icon.image { color: #a855f7; }

        .result-tag {
            background: #f3f4f6;
            color: #6b7280;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 11px;
            display: inline-block;
        }

        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 14px;
        }

        .status-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-draft { background: #f3f4f6; color: #4b5563; }
        .status-pending_review { background: #fef3c7; color: #92400e; }
        .status-under_review { background: #dbeafe; color: #1e40af; }
        .status-revision_required { background: #fee2e2; color: #991b1b; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-published { background: #ede9fe; color: #5b21b6; }
        .status-archived { background: #e5e7eb; color: #374151; }

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

        @media (max-width: 768px) {
            .search-body {
                padding: 16px;
            }

            .search-header {
                padding: 20px;
            }

            .stat-item h4 {
                font-size: 1.25rem;
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
            Search Documents
        @endslot
    @endcomponent

    <div class="search-container">
        <div class="search-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;"><i class="fas fa-search me-2"></i>Search Documents</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        @if($searchQuery)
                            Results for "{{ $searchQuery }}"
                        @else
                            Search across all documents by title and tags
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center justify-content-end">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $documents->total() }}</h4>
                                <small class="opacity-75">Results</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="search-body">
            <div class="help-text">
                <div class="help-title">Document Search</div>
                <div class="help-content">
                    Search across all documents by title, description, and tags. Use the filters below to narrow your results.
                    Click on a document to view full details, or use the action buttons to download or delete.
                </div>
            </div>

            {{-- Horizontal Filter Bar --}}
            <form method="GET" action="{{ route('documents.search') }}" id="filter-form">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-10 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" placeholder="Search documents..." name="q" value="{{ $searchQuery }}">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select class="form-select" name="file_type">
                                        <option value="">All Types</option>
                                        @foreach($fileTypes as $ext)
                                            <option value="{{ $ext }}" {{ request('file_type') === $ext ? 'selected' : '' }}>.{{ $ext }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select class="form-select" name="category_id">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ (int) request('category_id') === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select class="form-select" name="owner_id">
                                        <option value="">All Owners</option>
                                        @foreach($owners as $owner)
                                            <option value="{{ $owner->id }}" {{ (int) request('owner_id') === $owner->id ? 'selected' : '' }}>{{ $owner->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-1 col-md-2 col-sm-6">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-12 text-lg-end text-md-start mt-lg-0 mt-2">
                        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                            <a href="{{ route('documents.search') }}" class="btn btn-light btn-sm">Reset</a>
                            <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Results Table --}}
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Document</th>
                            <th>Owner</th>
                            <th>Category</th>
                            <th>Size</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $index => $document)
                            @php
                                $ext = strtolower($document->extension ?? '');
                                $iconClass = $document->isExternalUrl() ? 'fa-link' : 'fa-file';
                                $colorClass = '';
                                if(!$document->isExternalUrl() && in_array($ext, ['pdf'])) { $iconClass = 'fa-file-pdf'; $colorClass = 'pdf'; }
                                elseif(!$document->isExternalUrl() && in_array($ext, ['doc', 'docx'])) { $iconClass = 'fa-file-word'; $colorClass = 'word'; }
                                elseif(!$document->isExternalUrl() && in_array($ext, ['xls', 'xlsx'])) { $iconClass = 'fa-file-excel'; $colorClass = 'excel'; }
                                elseif(!$document->isExternalUrl() && in_array($ext, ['ppt', 'pptx'])) { $iconClass = 'fa-file-powerpoint'; $colorClass = 'ppt'; }
                                elseif(!$document->isExternalUrl() && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) { $iconClass = 'fa-file-image'; $colorClass = 'image'; }
                                elseif(!$document->isExternalUrl() && in_array($ext, ['txt'])) { $iconClass = 'fa-file-lines'; }

                                $statusClass = 'status-' . str_replace(' ', '_', strtolower($document->status ?? 'draft'));
                            @endphp
                            <tr>
                                <td>{{ $documents->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="doc-icon {{ $colorClass }}">
                                            <i class="fas {{ $iconClass }}"></i>
                                        </div>
                                        <div>
                                            <a href="{{ url('/documents/' . $document->id) }}">
                                                {{ \Illuminate\Support\Str::limit($document->title, 50) }}
                                            </a>
                                            @if($document->isExternalUrl())
                                                <span class="badge bg-warning-subtle text-warning-emphasis ms-1" style="font-size: 10px;">Remote</span>
                                            @endif
                                            @if($document->tags->count() > 0)
                                                <div class="mt-1">
                                                    @foreach($document->tags->take(3) as $tag)
                                                        <span class="result-tag">{{ $tag->name }}</span>
                                                    @endforeach
                                                    @if($document->tags->count() > 3)
                                                        <span class="result-tag">+{{ $document->tags->count() - 3 }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size: 13px; color: #6b7280; white-space: nowrap;">
                                    {{ $document->owner->full_name ?? 'Unknown' }}
                                </td>
                                <td style="font-size: 13px;">
                                    @if($document->category)
                                        <span class="badge bg-info bg-opacity-10 text-info">{{ $document->category->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="font-size: 13px; color: #6b7280; white-space: nowrap;">
                                    @if(is_null($document->size_bytes))
                                        <span class="text-muted">Remote</span>
                                    @else
                                        {{ number_format($document->size_bytes / 1024, 0) }} KB
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $document->status)) }}</span>
                                </td>
                                <td style="font-size: 13px; color: #6b7280; white-space: nowrap;">
                                    {{ $document->created_at->format('M d, Y') }}
                                </td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <a href="{{ url('/documents/' . $document->id) }}"
                                           class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ url('/documents/' . $document->id . '/download') }}"
                                           class="btn btn-sm btn-outline-primary" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @if($document->owner_id == auth()->id())
                                            <form method="POST" action="{{ url('/documents/' . $document->id) }}" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this document?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="fas fa-search d-block"></i>
                                        <h5>No documents found</h5>
                                        <p>No documents match your search criteria. Try adjusting your filters or search term.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($documents->hasPages())
                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <div class="text-muted" style="font-size: 13px;">
                        Showing {{ $documents->firstItem() ?? 0 }} to {{ $documents->lastItem() ?? 0 }} of {{ $documents->total() }} results
                    </div>
                    {{ $documents->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
