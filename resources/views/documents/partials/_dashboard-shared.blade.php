{{-- DSH-04: Shared With Me Widget --}}
@php
$favIconMap = $favIconMap ?? [
    'pdf' => 'fas fa-file-pdf text-danger',
    'doc' => 'fas fa-file-word text-primary',
    'docx' => 'fas fa-file-word text-primary',
    'xls' => 'fas fa-file-excel text-success',
    'xlsx' => 'fas fa-file-excel text-success',
    'ppt' => 'fas fa-file-powerpoint text-warning',
    'pptx' => 'fas fa-file-powerpoint text-warning',
    'jpg' => 'fas fa-file-image text-info',
    'jpeg' => 'fas fa-file-image text-info',
    'png' => 'fas fa-file-image text-info',
    'txt' => 'fas fa-file-alt text-secondary',
];
$permBadgeColors = [
    'view' => 'secondary',
    'comment' => 'info',
    'edit' => 'warning',
    'manage' => 'success',
];
@endphp

<div class="widget-header">
    <h6><i class="fas fa-share-alt" style="color: #6b7280; margin-right: 8px;"></i>Shared With Me</h6>
    <a href="{{ route('documents.shared') }}" style="font-size: 12px; color: #3b82f6; text-decoration: none;">View All</a>
</div>
<div class="widget-body">
    @if($sharedDocuments->isNotEmpty())
        @foreach($sharedDocuments as $share)
            <div class="widget-item">
                <div>
                    <div class="widget-item-title">
                        @if($share->document)
                            <i class="{{ $favIconMap[$share->document->extension ?? ''] ?? 'fas fa-file text-secondary' }}" style="margin-right: 6px;"></i>
                            <a href="{{ route('documents.show', $share->document->id) }}">{{ Str::limit($share->document->title, 30) }}</a>
                        @else
                            <span style="color: #9ca3af; font-style: italic;">Document unavailable</span>
                        @endif
                    </div>
                    <div class="widget-item-meta">
                        Shared by {{ $share->sharedBy->full_name ?? 'Unknown' }}
                    </div>
                </div>
                <div>
                    <span class="badge bg-{{ $permBadgeColors[$share->permission_level] ?? 'secondary' }}" style="font-size: 10px; text-transform: capitalize;">
                        {{ $share->permission_level }}
                    </span>
                </div>
            </div>
        @endforeach
    @else
        <div class="widget-empty">
            <i class="fas fa-share-alt" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
            No documents shared with you.
        </div>
    @endif
</div>
