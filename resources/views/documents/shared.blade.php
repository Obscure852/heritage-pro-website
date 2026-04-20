@extends('layouts.master')
@section('title')
    Shared with me - Documents
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

        .sharer-group {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .sharer-group-header {
            background: #f9fafb;
            padding: 12px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.15s;
            user-select: none;
        }

        .sharer-group-header:hover {
            background: #f3f4f6;
        }

        .sharer-group-header .sharer-name {
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
            flex: 1;
        }

        .sharer-group-header .sharer-count {
            font-size: 12px;
            color: #6b7280;
        }

        .sharer-group-header .collapse-icon {
            color: #9ca3af;
            transition: transform 0.2s;
            font-size: 12px;
        }

        .sharer-group-header.collapsed .collapse-icon {
            transform: rotate(-90deg);
        }

        .sharer-group-body {
            border-top: 1px solid #e5e7eb;
        }

        .shared-doc-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
            transition: background 0.15s;
        }

        .shared-doc-row:last-child {
            border-bottom: none;
        }

        .shared-doc-row:hover {
            background: #f9fafb;
        }

        .shared-doc-row .doc-icon {
            width: 32px;
            height: 32px;
            border-radius: 3px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
        }

        .shared-doc-row .doc-info {
            flex: 1;
            min-width: 0;
        }

        .shared-doc-row .doc-title {
            font-weight: 500;
            color: #1f2937;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        .shared-doc-row .doc-title:hover {
            color: #3b82f6;
        }

        .shared-doc-row .doc-meta {
            font-size: 12px;
            color: #9ca3af;
        }

        .permission-badge {
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            white-space: nowrap;
        }

        .permission-badge.perm-view { background: #e5e7eb; color: #374151; }
        .permission-badge.perm-comment { background: #dbeafe; color: #1e40af; }
        .permission-badge.perm-edit { background: #fef3c7; color: #92400e; }
        .permission-badge.perm-manage { background: #d1fae5; color: #065f46; }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
        }

        .empty-state h5 {
            color: #6b7280;
            margin-bottom: 8px;
        }

        @media (max-width: 768px) {
            .documents-header { padding: 20px; }
            .documents-body { padding: 16px; }
            .shared-doc-row { flex-wrap: wrap; }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('documents.index') }}">Documents</a>
        @endslot
        @slot('title')
            Shared with me
        @endslot
    @endcomponent

    <div class="documents-container">
        <div class="documents-header">
            <div class="row align-items-center">
                @php $totalShared = $shareGroups->flatten()->count(); @endphp
                <div class="col-md-8">
                    <h3 style="margin:0;"><i class="fas fa-share-alt me-2"></i>Shared with me</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        Documents shared with you by other users
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="row text-center justify-content-end">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalShared }}</h4>
                                <small class="opacity-75">Shared</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="documents-body">

            <div class="help-text">
                <div class="help-title">Documents Shared with You</div>
                <div class="help-content">
                    These documents have been shared with you by other users. Documents are grouped by who shared them. Click a document title to view it.
                </div>
            </div>

            @if($shareGroups->isNotEmpty())
                @php $groupIndex = 0; @endphp
                @foreach($shareGroups as $sharerId => $shares)
                    @php
                        $sharerName = optional($shares->first()->sharedBy)->full_name ?? 'Unknown User';
                        $isCollapsed = $groupIndex >= 5;
                        $groupIndex++;
                    @endphp
                    <div class="sharer-group">
                        <div class="sharer-group-header {{ $isCollapsed ? 'collapsed' : '' }}"
                             data-bs-toggle="collapse"
                             data-bs-target="#sharer-group-{{ $sharerId ?? 'unknown' }}"
                             aria-expanded="{{ $isCollapsed ? 'false' : 'true' }}">
                            <i class="fas fa-user-circle" style="font-size: 18px; color: #6b7280;"></i>
                            <span class="sharer-name">From {{ $sharerName }}</span>
                            <span class="sharer-count">({{ $shares->count() }} document{{ $shares->count() !== 1 ? 's' : '' }})</span>
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </div>
                        <div class="collapse {{ $isCollapsed ? '' : 'show' }}" id="sharer-group-{{ $sharerId ?? 'unknown' }}">
                            <div class="sharer-group-body">
                                @foreach($shares as $share)
                                    @if($share->document)
                                        @php
                                            $doc = $share->document;
                                            $iconMap = [
                                                'pdf' => 'fa-file-pdf text-danger',
                                                'doc' => 'fa-file-word text-primary',
                                                'docx' => 'fa-file-word text-primary',
                                                'xls' => 'fa-file-excel text-success',
                                                'xlsx' => 'fa-file-excel text-success',
                                                'ppt' => 'fa-file-powerpoint text-warning',
                                                'pptx' => 'fa-file-powerpoint text-warning',
                                                'jpg' => 'fa-file-image text-info',
                                                'jpeg' => 'fa-file-image text-info',
                                                'png' => 'fa-file-image text-info',
                                                'txt' => 'fa-file-alt text-secondary',
                                            ];
                                            $docIcon = $doc->isExternalUrl()
                                                ? 'fa-link text-warning'
                                                : ($iconMap[strtolower($doc->extension ?? '')] ?? 'fa-file text-muted');
                                            $permClass = 'perm-' . $share->permission_level;
                                            $permLabels = [
                                                'view' => 'Can View',
                                                'comment' => 'Can Comment',
                                                'edit' => 'Can Edit',
                                                'manage' => 'Can Manage',
                                            ];
                                            $permLabel = $permLabels[$share->permission_level] ?? 'Can View';
                                        @endphp
                                        <div class="shared-doc-row">
                                            <div class="doc-icon">
                                                <i class="fas {{ $docIcon }}"></i>
                                            </div>
                                            <div class="doc-info">
                                                <a href="{{ route('documents.show', $doc->id) }}" class="doc-title">{{ $doc->title }}</a>
                                                <div class="doc-meta">
                                                    Shared {{ $share->created_at->diffForHumans() }}
                                                    @if($doc->isExternalUrl())
                                                        &middot; Remote
                                                    @elseif($doc->size_bytes)
                                                        &middot;
                                                        @if($doc->size_bytes >= 1048576)
                                                            {{ number_format($doc->size_bytes / 1048576, 2) }} MB
                                                        @else
                                                            {{ number_format($doc->size_bytes / 1024, 1) }} KB
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="permission-badge {{ $permClass }}">{{ $permLabel }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <i class="fas fa-share-alt"></i>
                    <h5>No documents have been shared with you yet</h5>
                    <p style="font-size: 14px;">When someone shares a document with you, it will appear here.</p>
                </div>
            @endif

        </div>
    </div>
@endsection
