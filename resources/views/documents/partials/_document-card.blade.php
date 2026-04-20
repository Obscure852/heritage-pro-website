<div class="document-card" data-draggable-item data-type="document" data-id="{{ $document->id }}" style="position: relative;">
    @php $isCardFav = in_array($document->id, $favoriteDocIds ?? []); @endphp
    <i class="{{ $isCardFav ? 'fas' : 'far' }} fa-star favorite-star"
       data-document-id="{{ $document->id }}"
       style="position: absolute; top: 12px; right: 12px; font-size: 14px; cursor: pointer; color: {{ $isCardFav ? '#f59e0b' : '#d1d5db' }}; z-index: 2;"
       onclick="toggleFavorite({{ $document->id }}, this)"></i>
    <div class="d-flex justify-content-between align-items-start mb-2">
        <input type="checkbox" class="form-check-input doc-checkbox" data-id="{{ $document->id }}" onchange="toggleSelect({{ $document->id }}, this)">
        @php
            $statusColors = [
                'draft' => '#fef3c7',
                'published' => '#d1fae5',
                'archived' => '#f3f4f6',
                'under_review' => '#dbeafe',
            ];
            $statusTextColors = [
                'draft' => '#92400e',
                'published' => '#065f46',
                'archived' => '#4b5563',
                'under_review' => '#1e40af',
            ];
            $bgColor = $statusColors[$document->status] ?? '#f3f4f6';
            $txtColor = $statusTextColors[$document->status] ?? '#4b5563';
        @endphp
        <span class="status-pill" style="background: {{ $bgColor }}; color: {{ $txtColor }}; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 500; text-transform: capitalize;">
            {{ str_replace('_', ' ', $document->status) }}
        </span>
    </div>

    <a href="{{ route('documents.show', $document) }}" class="text-decoration-none d-block text-center mb-3">
        @php
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
            $iconClass = $iconMap[strtolower((string) $document->extension)] ?? 'fa-file text-muted';
        @endphp
        @if($document->isExternalUrl())
            @php $iconClass = 'fa-link text-warning'; @endphp
        @endif
        <i class="fas {{ $iconClass }}" style="font-size: 40px;"></i>
    </a>

    <a href="{{ route('documents.show', $document) }}" class="text-decoration-none">
        <h6 class="card-title mb-1" style="color: #1f2937; font-size: 14px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $document->title }}">
            {{ \Illuminate\Support\Str::limit($document->title, 40) }}
            @if($document->isExternalUrl())
                <span class="badge bg-warning-subtle text-warning-emphasis" style="font-size: 9px; padding: 2px 5px;">Remote</span>
            @endif
            @if($document->legal_hold)
                <span class="badge bg-danger" title="Legal Hold" style="font-size: 9px; padding: 2px 5px;"><i class="fas fa-lock"></i></span>
            @endif
        </h6>
    </a>

    <div style="font-size: 12px; color: #6b7280;">
        <div class="mb-1">
            <i class="fas fa-user" style="width: 14px;"></i>
            {{ $document->owner->full_name ?? 'Unknown' }}
        </div>
        <div class="d-flex justify-content-between">
            <span>
                <i class="fas fa-hdd" style="width: 14px;"></i>
                @if (is_null($document->size_bytes))
                    Remote
                @elseif ($document->size_bytes >= 1048576)
                    {{ number_format($document->size_bytes / 1048576, 1) }} MB
                @else
                    {{ number_format($document->size_bytes / 1024, 1) }} KB
                @endif
            </span>
            <span title="{{ $document->created_at->format('M d, Y H:i') }}">
                {{ $document->created_at->diffForHumans() }}
            </span>
        </div>
    </div>
</div>
