{{-- DSH-02: Recent Documents Widget --}}
@php
$favIconMap = [
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
@endphp

<div class="widget-header">
    <h6><i class="fas fa-clock" style="color: #6b7280; margin-right: 8px;"></i>Recent Documents</h6>
    <a href="{{ route('documents.index') }}" style="font-size: 12px; color: #3b82f6; text-decoration: none;">View All</a>
</div>
<div class="widget-body">
    @if($recentDocuments->isNotEmpty())
        @foreach($recentDocuments as $doc)
            <div class="widget-item">
                <div class="widget-item-title">
                    <i class="{{ $favIconMap[$doc->extension] ?? 'fas fa-file text-secondary' }}" style="margin-right: 6px;"></i>
                    <a href="{{ route('documents.show', $doc->id) }}">{{ Str::limit($doc->title, 40) }}</a>
                </div>
                <div class="widget-item-meta">
                    {{ $doc->updated_at->diffForHumans() }}
                </div>
            </div>
        @endforeach
    @else
        <div class="widget-empty">
            <i class="fas fa-folder-open" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
            No documents viewed yet.
        </div>
    @endif
</div>
