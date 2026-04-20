@extends('layouts.master')
@section('title')
    {{ $document->title }} - Document Details
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

        /* Action toolbar below help text */
        .doc-toolbar {
            display: flex;
            align-items: center;
            gap: 2px;
            padding: 6px 8px;
            background: #1e293b;
            border-radius: 3px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .doc-toolbar .toolbar-btn {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            border-radius: 3px;
            color: #94a3b8;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
            position: relative;
        }

        .doc-toolbar .toolbar-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
        }

        .doc-toolbar .toolbar-btn.active {
            color: #f59e0b;
        }

        .doc-toolbar .toolbar-btn.btn-danger-icon:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        .doc-toolbar .toolbar-btn.btn-amber-icon:hover {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }

        .doc-toolbar .toolbar-btn.btn-publish-icon:hover {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        .doc-toolbar .toolbar-divider {
            width: 1px;
            height: 20px;
            background: rgba(255, 255, 255, 0.12);
            margin: 0 4px;
        }

        .doc-toolbar .toolbar-btn[title]::after {
            content: attr(title);
            position: absolute;
            bottom: -28px;
            left: 50%;
            transform: translateX(-50%);
            background: #0f172a;
            color: #e2e8f0;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 3px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.15s;
            z-index: 10;
        }

        .doc-toolbar .toolbar-btn:hover[title]::after {
            opacity: 1;
        }

        .documents-body {
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


        /* Preview area */
        .preview-container {
            background: #f8f9fa;
            border-radius: 3px;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .pdf-preview {
            width: 100%;
            height: 600px;
            border: none;
            border-radius: 3px;
        }

        .preview-image {
            max-width: 100%;
            max-height: 600px;
            border-radius: 3px;
            object-fit: contain;
        }

        .no-preview {
            text-align: center;
            padding: 60px 20px;
        }

        .no-preview .file-icon {
            font-size: 64px;
            color: #9ca3af;
            margin-bottom: 16px;
        }

        .no-preview .file-type {
            font-size: 18px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .no-preview .file-hint {
            font-size: 14px;
            color: #9ca3af;
            margin-bottom: 20px;
        }

        /* Metadata panel */
        .metadata-panel .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 16px;
        }

        .metadata-panel .card-header {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 16px;
            font-weight: 600;
            font-size: 14px;
            color: #374151;
        }

        .metadata-panel .card-body {
            padding: 16px;
        }

        .meta-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }

        .meta-item:last-child {
            border-bottom: none;
        }

        .meta-label {
            color: #6b7280;
            font-weight: 500;
            flex-shrink: 0;
            margin-right: 12px;
        }

        .meta-value {
            color: #1f2937;
            text-align: right;
            word-break: break-all;
        }

        /* Status & visibility badges */
        .status-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-draft { background: #e5e7eb; color: #374151; }
        .status-pending_review { background: #fef3c7; color: #92400e; }
        .status-under_review { background: #dbeafe; color: #1e40af; }
        .status-revision_required { background: #fce7f3; color: #9d174d; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-published { background: #c7d2fe; color: #3730a3; }
        .status-archived { background: #f3f4f6; color: #6b7280; }

        .visibility-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .visibility-private { background: #fef3c7; color: #92400e; }
        .visibility-internal { background: #dbeafe; color: #1e40af; }
        .visibility-public { background: #d1fae5; color: #065f46; }

        /* Tag chips */
        .tag-chip {
            display: inline-block;
            background: #e5e7eb;
            color: #374151;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin: 2px 4px 2px 0;
        }

        a.tag-chip:hover {
            background: #d1d5db;
            color: #1f2937;
        }

        /* Version timeline */
        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .version-timeline {
            position: relative;
            padding-left: 24px;
        }

        .version-timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }

        .version-item {
            position: relative;
            padding: 12px 0 16px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .version-item:last-child {
            border-bottom: none;
        }

        .version-item::before {
            content: '';
            position: absolute;
            left: -20px;
            top: 16px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #3b82f6;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #e5e7eb;
        }

        .version-number {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 8px;
        }

        .version-type {
            display: inline-block;
            background: #f3f4f6;
            color: #6b7280;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .version-meta {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        .version-notes {
            font-size: 13px;
            color: #374151;
            font-style: italic;
            margin-top: 4px;
        }

        .version-size {
            font-size: 12px;
            color: #9ca3af;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 32px;
            margin-bottom: 12px;
        }

        .version-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .version-actions .btn {
            font-size: 12px;
            padding: 4px 10px;
        }

        /* Legal hold banner */
        .legal-hold-banner {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border: none;
            border-radius: 3px;
        }

        .legal-hold-banner strong {
            font-size: 14px;
        }

        .legal-hold-banner span {
            font-size: 13px;
            opacity: 0.95;
        }

        /* Audit timeline compact */
        .audit-timeline {
            max-height: 400px;
            overflow-y: auto;
        }

        .audit-entry {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }

        .audit-entry:last-child {
            border-bottom: none;
        }

        .audit-entry .audit-badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 3px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .audit-entry .audit-meta {
            color: #6b7280;
            font-size: 12px;
        }


        @media (max-width: 768px) {
            .documents-header {
                padding: 20px;
            }

            .documents-body {
                padding: 16px;
            }

            .doc-toolbar {
                gap: 1px;
                padding: 4px 6px;
            }

            .doc-toolbar .toolbar-btn {
                width: 32px;
                height: 32px;
                font-size: 13px;
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
            {{ $document->title }}
        @endslot
    @endcomponent

    <div class="documents-container">
        <div class="documents-header">
            <h3 style="margin:0;">
                {{ $document->title }}
                <span class="status-badge status-{{ $document->status }}" style="font-size: 12px; vertical-align: middle;">{{ str_replace('_', ' ', $document->status) }}</span>
            </h3>
            <p style="margin:6px 0 0 0; opacity:.9;">
                Uploaded by {{ $document->owner->full_name ?? 'Unknown' }} on {{ $document->created_at->format('M d, Y') }}
            </p>
        </div>
        @if($document->is_locked && in_array($document->status, ['pending_review', 'under_review']))
            <div class="alert alert-warning mx-3 mt-3 mb-0" style="border-radius: 3px; font-size: 13px;">
                <i class="fas fa-lock me-2"></i>
                <strong>Document under review</strong> &mdash; This document is locked for editing while under review.
                @if($document->status === 'pending_review')
                    Status: Awaiting reviewer pickup.
                @else
                    Status: Review in progress.
                @endif
            </div>
        @endif

        <div class="documents-body">

        {{-- Legal Hold Banner --}}
        @if($document->legal_hold)
            <div class="alert legal-hold-banner d-flex align-items-center mb-3">
                <i class="bx bx-lock-alt me-3 fs-3"></i>
                <div>
                    <strong>Legal Hold Active</strong><br>
                    <span>This document is under legal hold &mdash; deletion and archival are blocked.
                    @if($document->legalHoldBy) Placed by {{ $document->legalHoldBy->full_name }}@endif
                    @if($document->legal_hold_at) on {{ $document->legal_hold_at->format('d M Y') }}@endif.
                    @if($document->legal_hold_reason) Reason: {{ $document->legal_hold_reason }}@endif</span>
                </div>
            </div>
        @endif

        {{-- Expiry Warning Banners --}}
        @if($document->expiry_date && $document->expiry_date->isFuture() && $document->expiry_date->diffInDays(now()) <= 7)
            <div class="alert alert-warning d-flex align-items-center mb-3">
                <i class="bx bx-time-five me-2 fs-5"></i>
                <div>
                    <strong>Expiring Soon:</strong> This document expires {{ $document->expiry_date->diffForHumans() }}
                    ({{ $document->expiry_date->format('d M Y') }}).
                </div>
            </div>
        @elseif($document->expiry_date && $document->expiry_date->isPast() && $document->status !== 'archived')
            <div class="alert alert-danger d-flex align-items-center mb-3" style="border-radius: 3px;">
                <i class="bx bx-error me-2 fs-5"></i>
                <div>
                    <strong>Expired:</strong> This document expired on {{ $document->expiry_date->format('d M Y') }}.
                    It will be auto-archived after the grace period.
                </div>
            </div>
        @endif

        <div class="help-text">
            <div class="help-title">Document Details</div>
            <div class="help-content">
                View document information, preview the file, and access version history.
            </div>
        </div>

        {{-- Action Toolbar --}}
        <div class="doc-toolbar">
            {{-- Favorite --}}
            <button type="button" class="toolbar-btn {{ ($isFavorited ?? false) ? 'active' : '' }}"
                    id="show-favorite-star"
                    data-document-id="{{ $document->id }}"
                    title="{{ ($isFavorited ?? false) ? 'Unfavorite' : 'Favorite' }}"
                    onclick="toggleShowFavorite({{ $document->id }}, this)">
                <i class="{{ ($isFavorited ?? false) ? 'fas' : 'far' }} fa-star"></i>
            </button>

            {{-- Download --}}
            <a href="{{ route('documents.download', $document) }}" class="toolbar-btn" title="Download">
                <i class="fas fa-download"></i>
            </a>

            @can('share', $document)
                <button type="button" class="toolbar-btn" data-bs-toggle="modal" data-bs-target="#shareModal" title="Share">
                    <i class="fas fa-share-alt"></i>
                </button>
            @endcan

            <div class="toolbar-divider"></div>

            {{-- Workflow actions --}}
            @can('publish', $document)
                @if(config('documents.approval.require_approval'))
                    @if(in_array($document->status, ['draft', 'revision_required']))
                        <button type="button" class="toolbar-btn btn-publish-icon" data-bs-toggle="modal" data-bs-target="#submitReviewModal" title="Submit for Review">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    @elseif($document->status === 'pending_review' && $document->owner_id === auth()->id())
                        <button type="button" class="toolbar-btn btn-danger-icon" id="btn-withdraw" title="Withdraw">
                            <i class="fas fa-undo"></i>
                        </button>
                    @elseif($document->status === 'approved')
                        <button type="button" class="toolbar-btn btn-publish-icon" data-bs-toggle="modal" data-bs-target="#publishModal" title="Publish">
                            <i class="fas fa-globe"></i>
                        </button>
                    @elseif($document->status === 'published')
                        <button type="button" class="toolbar-btn btn-danger-icon" id="btn-unpublish" title="Unpublish">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                        <button type="button" class="toolbar-btn {{ $document->is_featured ? 'active' : '' }}" id="btn-featured" title="{{ $document->is_featured ? 'Unfeature' : 'Feature' }}">
                            <i class="fas fa-star"></i>
                        </button>
                        <button type="button" class="toolbar-btn" id="btn-archive" title="Archive">
                            <i class="fas fa-archive"></i>
                        </button>
                    @elseif($document->status === 'archived')
                        <button type="button" class="toolbar-btn btn-publish-icon" id="btn-unarchive" title="Unarchive">
                            <i class="fas fa-box-open"></i>
                        </button>
                    @endif
                @else
                    <button type="button" class="toolbar-btn {{ $document->status === 'published' ? 'btn-danger-icon' : 'btn-publish-icon' }}" id="btn-publish" title="{{ $document->status === 'published' ? 'Unpublish' : 'Publish' }}">
                        <i class="fas {{ $document->status === 'published' ? 'fa-eye-slash' : 'fa-globe' }}"></i>
                    </button>
                    @if($document->status === 'published')
                        <button type="button" class="toolbar-btn {{ $document->is_featured ? 'active' : '' }}" id="btn-featured" title="{{ $document->is_featured ? 'Unfeature' : 'Feature' }}">
                            <i class="fas fa-star"></i>
                        </button>
                    @endif
                @endif
            @endcan

            {{-- Legal Hold Toggle (admin only) --}}
            @if(auth()->user()->hasAnyRoles(['Administrator']))
                @if($document->legal_hold)
                    <button type="button" class="toolbar-btn btn-danger-icon" id="btn-legal-hold-toggle" data-action="remove" title="Remove Legal Hold">
                        <i class="fas fa-unlock"></i>
                    </button>
                @else
                    <button type="button" class="toolbar-btn btn-amber-icon" id="btn-legal-hold-toggle" data-action="place" title="Legal Hold">
                        <i class="fas fa-lock"></i>
                    </button>
                @endif
            @endif

            <div class="toolbar-divider"></div>

            {{-- Edit --}}
            @can('update', $document)
                @unless($document->is_locked)
                    <a href="{{ route('documents.edit', $document) }}" class="toolbar-btn" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                @endunless
            @endcan

            {{-- Upload New Version --}}
            @can('uploadVersion', $document)
                @if(!in_array($document->status, [\App\Models\Document::STATUS_PENDING_REVIEW, \App\Models\Document::STATUS_UNDER_REVIEW]))
                    <a href="{{ route('documents.versions.create', $document) }}" class="toolbar-btn" title="New Version">
                        <i class="fas fa-upload"></i>
                    </a>
                @endif
            @endcan

            {{-- Delete --}}
            @can('delete', $document)
                <button type="button" class="toolbar-btn btn-danger-icon" onclick="confirmDelete()" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            @endcan
        </div>

        @if($document->isExternalUrl())
            <div class="alert alert-info" style="font-size: 13px; border-radius: 3px;">
                <i class="fas fa-link me-1"></i> This document is hosted externally. Preview and download actions redirect to the remote source.
            </div>
        @endif

        @if($document->supportsVersioning() && in_array($document->status, [\App\Models\Document::STATUS_PENDING_REVIEW, \App\Models\Document::STATUS_UNDER_REVIEW]))
            <div class="alert alert-warning" style="font-size: 13px; border-radius: 3px;">
                <i class="fas fa-lock me-1"></i> New versions cannot be uploaded while this document is under review.
            </div>
        @endif

        <div class="row">
            {{-- Left column: Preview --}}
            <div class="col-lg-8 mb-4">
                {{-- Inline Preview --}}
                <div class="preview-container">
                    @if($document->isExternalUrl())
                        <div class="no-preview">
                            <div class="file-icon">
                                <i class="fas fa-link"></i>
                            </div>
                            <div class="file-type">Remote Document</div>
                            <div class="file-hint">This document is hosted externally. Open it in a new tab to view the source file.</div>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a href="{{ route('documents.preview', $document) }}" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                                    <i class="fas fa-up-right-from-square"></i> Open Remote Document
                                </a>
                                <a href="{{ route('documents.download', $document) }}" class="btn btn-outline-secondary" target="_blank" rel="noopener noreferrer">
                                    <i class="fas fa-download"></i> Go To Download
                                </a>
                            </div>
                        </div>
                    @elseif(Str::startsWith((string) $document->mime_type, 'image/'))
                        <img src="{{ route('documents.preview', $document) }}"
                             alt="{{ $document->title }}"
                             class="img-fluid preview-image">
                    @elseif($document->mime_type === 'application/pdf')
                        <iframe src="{{ route('documents.preview', $document) }}"
                                class="pdf-preview"
                                title="{{ $document->title }}"></iframe>
                    @else
                        <div class="no-preview">
                            <div class="file-icon">
                                <i class="fas fa-file{{ $document->extension ? '-' . (in_array($document->extension, ['doc', 'docx']) ? 'word' : (in_array($document->extension, ['xls', 'xlsx']) ? 'excel' : (in_array($document->extension, ['ppt', 'pptx']) ? 'powerpoint' : ''))) : '' }}"></i>
                            </div>
                            <div class="file-type">{{ strtoupper($document->extension ?: 'FILE') }} Document</div>
                            <div class="file-hint">Preview is not available for this file type</div>
                            <a href="{{ route('documents.download', $document) }}" class="btn btn-primary">
                                <i class="fas fa-download"></i> Download File
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Version History --}}
                <h5 class="section-title mt-4">
                    <i class="fas fa-history me-1"></i> Version History
                </h5>
                @if(!$document->supportsVersioning())
                    <div class="empty-state">
                        <i class="fas fa-link d-block"></i>
                        <span>Version history is not available for URL-backed documents</span>
                    </div>
                @elseif($document->versions->isNotEmpty())
                    <div class="version-timeline">
                        @foreach($document->versions as $version)
                            <div class="version-item">
                                <div>
                                    <span class="version-number">v{{ $version->version_number }}</span>
                                    @if($version->is_current)
                                        <span class="badge bg-success" style="font-size: 11px;">Current</span>
                                    @endif
                                    <span class="version-type">{{ $version->version_type }}</span>
                                    <span class="version-size">
                                        @if($version->size_bytes >= 1048576)
                                            {{ number_format($version->size_bytes / 1048576, 2) }} MB
                                        @else
                                            {{ number_format($version->size_bytes / 1024, 1) }} KB
                                        @endif
                                    </span>
                                </div>
                                <div class="version-meta">
                                    Uploaded by {{ $version->uploadedBy->full_name ?? 'Unknown' }}
                                    &middot; {{ $version->created_at->diffForHumans() }}
                                </div>
                                @if($version->version_notes)
                                    <div class="version-notes">{{ $version->version_notes }}</div>
                                @endif
                                <div class="version-actions">
                                    <a href="{{ route('documents.versions.download', [$document, $version]) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    @if(!$version->is_current)
                                        @can('uploadVersion', $document)
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="confirmRestore({{ $version->id }}, '{{ $version->version_number }}')">
                                                <i class="fas fa-undo"></i> Restore
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                                @if(!$version->is_current)
                                    <form id="restore-form-{{ $version->id }}" action="{{ route('documents.versions.restore', [$document, $version]) }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-clock-rotate-left d-block"></i>
                        <span>No version history</span>
                    </div>
                @endif

                {{-- Review Panel (for assigned reviewers) --}}
                @if(($isReviewer ?? false) && in_array($document->status, ['pending_review', 'under_review']))
                    @include('documents.partials._review-panel')
                @endif
            </div>

            {{-- Right column: Metadata --}}
            <div class="col-lg-4 metadata-panel">
                {{-- File Information --}}
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-file-alt me-1"></i> File Information
                    </div>
                    <div class="card-body">
                        <div class="meta-item">
                            <span class="meta-label">Source</span>
                            <span class="meta-value">{{ $document->sourceLabel() }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">{{ $document->isExternalUrl() ? 'Remote filename' : 'Original filename' }}</span>
                            <span class="meta-value">{{ $document->original_name ?: 'Not provided' }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">File type</span>
                            <span class="meta-value">{{ strtoupper($document->extension ?: ($document->isExternalUrl() ? 'LINK' : 'Unknown')) }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Size</span>
                            <span class="meta-value">
                                @if(is_null($document->size_bytes))
                                    {{ $document->isExternalUrl() ? 'Remote size not tracked' : 'Unknown' }}
                                @elseif($document->size_bytes >= 1048576)
                                    {{ number_format($document->size_bytes / 1048576, 2) }} MB
                                @else
                                    {{ number_format($document->size_bytes / 1024, 1) }} KB
                                @endif
                            </span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">MIME type</span>
                            <span class="meta-value">{{ $document->mime_type ?: 'Unknown' }}</span>
                        </div>
                        @if($document->isExternalUrl())
                            <div class="meta-item">
                                <span class="meta-label">Remote URL</span>
                                <span class="meta-value">
                                    <a href="{{ $document->external_url }}" target="_blank" rel="noopener noreferrer">
                                        {{ Str::limit($document->external_url, 48) }}
                                    </a>
                                </span>
                            </div>
                        @endif
                        @if($document->checksum_sha256)
                            <div class="meta-item">
                                <span class="meta-label">Checksum</span>
                                <span class="meta-value" title="{{ $document->checksum_sha256 }}">
                                    {{ Str::limit($document->checksum_sha256, 16, '...') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Document Details --}}
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-info-circle me-1"></i> Document Details
                    </div>
                    <div class="card-body">
                        <div class="meta-item">
                            <span class="meta-label">Status</span>
                            <span class="meta-value">
                                <span class="status-badge status-{{ $document->status }}">{{ str_replace('_', ' ', $document->status) }}</span>
                                @if($document->status === 'published')
                                    <span class="badge bg-success ms-1" style="font-size: 10px;"><i class="fas fa-globe"></i> Published</span>
                                    @if($document->published_at)
                                        <small class="text-muted ms-1">{{ $document->published_at->format('M d, Y') }}</small>
                                    @endif
                                @endif
                                @if($document->is_featured)
                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;"><i class="fas fa-star"></i> Featured</span>
                                @endif
                            </span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Visibility</span>
                            <span class="meta-value">
                                <span class="visibility-badge visibility-{{ $document->visibility }}">{{ $document->visibility }}</span>
                            </span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Category</span>
                            <span class="meta-value">{{ $document->category->name ?? 'Uncategorized' }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Tags</span>
                            <span class="meta-value">
                                @if($document->tags->isNotEmpty())
                                    @foreach($document->tags as $tag)
                                        <a href="{{ route('documents.search', ['tag_ids[]' => $tag->id]) }}" class="tag-chip" style="text-decoration: none; transition: all 0.2s;">{{ $tag->name }}</a>
                                    @endforeach
                                @else
                                    <span class="text-muted">No tags</span>
                                @endif
                            </span>
                        </div>
                        @if($document->supportsVersioning())
                            <div class="meta-item">
                                <span class="meta-label">Version</span>
                                <span class="meta-value">v{{ $document->current_version }}</span>
                            </div>
                        @endif
                        <div class="meta-item">
                            <span class="meta-label">Views</span>
                            <span class="meta-value">{{ number_format($document->view_count ?? 0) }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Downloads</span>
                            <span class="meta-value">{{ number_format($document->download_count ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Dates --}}
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calendar-alt me-1"></i> Dates
                    </div>
                    <div class="card-body">
                        <div class="meta-item">
                            <span class="meta-label">Created</span>
                            <span class="meta-value">{{ $document->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Last modified</span>
                            <span class="meta-value">{{ $document->updated_at->format('M d, Y H:i') }}</span>
                        </div>
                        @if($document->effective_date)
                            <div class="meta-item">
                                <span class="meta-label">Effective date</span>
                                <span class="meta-value">{{ $document->effective_date->format('M d, Y') }}</span>
                            </div>
                        @endif
                        @if($document->expiry_date)
                            <div class="meta-item">
                                <span class="meta-label">Expiry date</span>
                                <span class="meta-value">{{ $document->expiry_date->format('M d, Y') }}</span>
                            </div>
                        @endif
                        @if($document->published_at)
                            <div class="meta-item">
                                <span class="meta-label">Published</span>
                                <span class="meta-value">{{ $document->published_at->format('M d, Y H:i') }}</span>
                            </div>
                        @endif
                        @if($document->archived_at)
                            <div class="meta-item">
                                <span class="meta-label">Archived</span>
                                <span class="meta-value">{{ $document->archived_at->format('M d, Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Activity History --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-history me-1"></i> Activity History</span>
                        <a href="{{ route('documents.settings', ['tab' => 'audit', 'document_id' => $document->id]) }}" style="font-size: 12px; font-weight: 400;">View All</a>
                    </div>
                    <div class="card-body p-0">
                        @if($documentAudits->isNotEmpty())
                            <div class="audit-timeline px-3 py-2">
                                @foreach($documentAudits->take(20) as $audit)
                                    <div class="audit-entry">
                                        <span class="badge bg-{{ $auditService->getActionColor($audit->action) }} audit-badge">
                                            {{ $auditService->getActionLabel($audit->action) }}
                                        </span>
                                        <div>
                                            <span>{{ $audit->user->full_name ?? 'System' }}</span>
                                            <div class="audit-meta">{{ $audit->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 text-muted" style="font-size: 13px;">
                                <i class="fas fa-history d-block mb-2" style="font-size: 24px;"></i>
                                No activity recorded
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    {{-- Hidden delete form --}}
    <form id="delete-form" action="{{ route('documents.destroy', $document) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    {{-- Share modal --}}
    @can('share', $document)
        @include('documents.partials._share-modal', ['document' => $document])
    @endcan

    {{-- Workflow modals (approval mode only) --}}
    @if(config('documents.approval.require_approval'))
        @if(in_array($document->status, ['draft', 'revision_required']))
            @include('documents.partials._submit-review-modal')
        @endif
        @if($document->status === 'approved')
            @include('documents.partials._publish-modal')
        @endif
    @endif
@endsection

@section('script')
    <script>
        function toggleShowFavorite(documentId, btnElement) {
            var icon = btnElement.querySelector('i') || btnElement;
            $.ajax({
                url: '/documents/' + documentId + '/favorite',
                method: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.is_favorited) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        btnElement.classList.add('active');
                        btnElement.title = 'Unfavorite';
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        btnElement.classList.remove('active');
                        btnElement.title = 'Favorite';
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

        function confirmRestore(versionId, versionNumber) {
            Swal.fire({
                title: 'Restore Version?',
                text: 'This will create a new version from v' + versionNumber + '. Continue?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, restore it'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('restore-form-' + versionId).submit();
                }
            });
        }

        function confirmDelete() {
            Swal.fire({
                title: 'Delete this document?',
                text: 'It will be moved to trash for 30 days.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form').submit();
                }
            });
        }

        // ==================== Publish / Unpublish ====================

        var showDocumentId = {{ $document->id }};
        var showCsrfToken = $('meta[name="csrf-token"]').attr('content');

        $(document).on('click', '#btn-publish', function() {
            var btn = $(this);
            var isPublished = '{{ $document->status }}' === 'published';
            var action = isPublished ? 'unpublish' : 'publish';

            Swal.fire({
                title: isPublished ? 'Unpublish Document?' : 'Publish Document?',
                text: isPublished
                    ? 'This will remove the document from public access. Existing public links will stop working.'
                    : 'This will make the document available for public link sharing.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: isPublished ? '#ef4444' : '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: isPublished ? 'Unpublish' : 'Publish',
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/documents/' + showDocumentId + '/publish',
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': showCsrfToken },
                        success: function(response) {
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'success',
                                title: response.status === 'published' ? 'Document published!' : 'Document unpublished.',
                                showConfirmButton: false, timer: 2000
                            });
                            location.reload();
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to update status.', 'error');
                        }
                    });
                }
            });
        });

        // ==================== Featured Toggle ====================

        $(document).on('click', '#btn-featured', function() {
            $.ajax({
                url: '/documents/' + showDocumentId + '/featured',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': showCsrfToken },
                success: function(response) {
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'success',
                        title: response.is_featured ? 'Marked as featured!' : 'Removed from featured.',
                        showConfirmButton: false, timer: 2000
                    });
                    location.reload();
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed to update featured status.', 'error');
                }
            });
        });

        // ==================== Workflow: Withdraw ====================

        $(document).on('click', '#btn-withdraw', function() {
            Swal.fire({
                title: 'Withdraw Submission?',
                text: 'This will cancel the review request and return the document to draft status.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Withdraw'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/documents/' + showDocumentId + '/workflow/withdraw',
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': showCsrfToken },
                        success: function(response) {
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'success',
                                title: response.message || 'Submission withdrawn.',
                                showConfirmButton: false, timer: 2000
                            });
                            setTimeout(function() { location.reload(); }, 1000);
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to withdraw submission.', 'error');
                        }
                    });
                }
            });
        });

        // ==================== Workflow: Unpublish (approval mode) ====================

        $(document).on('click', '#btn-unpublish', function() {
            Swal.fire({
                title: 'Unpublish Document?',
                text: 'This will remove the document from public access. Existing public links will stop working.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Unpublish'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/documents/' + showDocumentId + '/workflow/unpublish',
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': showCsrfToken },
                        success: function(response) {
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'success',
                                title: response.message || 'Document unpublished.',
                                showConfirmButton: false, timer: 2000
                            });
                            setTimeout(function() { location.reload(); }, 1000);
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to unpublish document.', 'error');
                        }
                    });
                }
            });
        });

        // ==================== Workflow: Archive ====================

        $(document).on('click', '#btn-archive', function() {
            Swal.fire({
                title: 'Archive Document?',
                text: 'Archive this document? It will be removed from active listings.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6b7280',
                cancelButtonColor: '#3b82f6',
                confirmButtonText: 'Yes, Archive'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/documents/' + showDocumentId + '/workflow/archive',
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': showCsrfToken },
                        success: function(response) {
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'success',
                                title: response.message || 'Document archived.',
                                showConfirmButton: false, timer: 2000
                            });
                            setTimeout(function() { location.reload(); }, 1000);
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to archive document.', 'error');
                        }
                    });
                }
            });
        });

        // ==================== Workflow: Unarchive ====================

        $(document).on('click', '#btn-unarchive', function() {
            Swal.fire({
                title: 'Unarchive Document?',
                text: 'This will restore the document to approved status.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Unarchive'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/documents/' + showDocumentId + '/workflow/unarchive',
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': showCsrfToken },
                        success: function(response) {
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'success',
                                title: response.message || 'Document unarchived.',
                                showConfirmButton: false, timer: 2000
                            });
                            setTimeout(function() { location.reload(); }, 1000);
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to unarchive document.', 'error');
                        }
                    });
                }
            });
        });

        // ==================== Legal Hold Toggle ====================

        $(document).on('click', '#btn-legal-hold-toggle', function() {
            var btn = $(this);
            var action = btn.data('action');

            if (action === 'place') {
                Swal.fire({
                    title: 'Place Legal Hold',
                    html: '<p style="font-size: 14px; color: #6b7280;">This will block deletion and archival of this document.</p>',
                    input: 'text',
                    inputLabel: 'Reason for legal hold',
                    inputPlaceholder: 'Enter the reason...',
                    inputValidator: function(value) {
                        if (!value) return 'Please provide a reason for the legal hold.';
                    },
                    showCancelButton: true,
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Place Hold'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/documents/' + showDocumentId + '/legal-hold',
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': showCsrfToken },
                            data: { legal_hold: true, reason: result.value },
                            success: function(response) {
                                Swal.fire({
                                    toast: true, position: 'top-end', icon: 'success',
                                    title: 'Legal hold placed.',
                                    showConfirmButton: false, timer: 2000
                                });
                                setTimeout(function() { location.reload(); }, 1000);
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to place legal hold.', 'error');
                            }
                        });
                    }
                });
            } else {
                Swal.fire({
                    title: 'Remove Legal Hold?',
                    text: 'This will allow deletion and archival of this document again.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, Remove Hold'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/documents/' + showDocumentId + '/legal-hold',
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': showCsrfToken },
                            data: { legal_hold: false },
                            success: function(response) {
                                Swal.fire({
                                    toast: true, position: 'top-end', icon: 'success',
                                    title: 'Legal hold removed.',
                                    showConfirmButton: false, timer: 2000
                                });
                                setTimeout(function() { location.reload(); }, 1000);
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to remove legal hold.', 'error');
                            }
                        });
                    }
                });
            }
        });
    </script>

    @if(session('success'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ session("success") }}',
                showConfirmButton: false,
                timer: 3000
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Action Blocked',
                html: '{!! session("error") !!}',
                confirmButtonColor: '#3b82f6'
            });
        </script>
    @endif
@endsection
