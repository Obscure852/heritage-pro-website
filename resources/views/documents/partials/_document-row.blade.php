<tr data-draggable-item data-type="document" data-id="{{ $document->id }}">
    <td style="width: 30px; text-align: center; padding-right: 0;">
        @php $isFav = in_array($document->id, $favoriteDocIds ?? []); @endphp
        <i class="{{ $isFav ? 'fas' : 'far' }} fa-star favorite-star"
           data-document-id="{{ $document->id }}"
           style="font-size: 14px; cursor: pointer; color: {{ $isFav ? '#f59e0b' : '#d1d5db' }};"
           onclick="toggleFavorite({{ $document->id }}, this)"></i>
    </td>
    <td>
        <input type="checkbox" class="form-check-input doc-checkbox" data-id="{{ $document->id }}" onchange="toggleSelect({{ $document->id }}, this)">
    </td>
    <td>
        <div class="d-flex align-items-center gap-2">
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
                $iconClass = $document->isExternalUrl()
                    ? 'fa-link text-warning'
                    : ($iconMap[strtolower((string) $document->extension)] ?? 'fa-file text-muted');
            @endphp
            <i class="fas {{ $iconClass }}" style="font-size: 20px; width: 24px; text-align: center;"></i>
            <a href="{{ route('documents.show', $document) }}" class="text-decoration-none" style="color: #1f2937; font-weight: 500;">
                {{ \Illuminate\Support\Str::limit($document->title, 50) }}
            </a>
            @if($document->isExternalUrl())
                <span class="badge bg-warning-subtle text-warning-emphasis" style="font-size: 10px;">Remote</span>
            @endif
            @if($document->legal_hold)
                <span class="badge bg-danger" title="Legal Hold" style="font-size: 9px; padding: 2px 5px;"><i class="fas fa-lock"></i></span>
            @endif
        </div>
    </td>
    <td style="color: #6b7280; font-size: 13px; white-space: nowrap;">
        {{ $document->owner->full_name ?? 'Unknown' }}
    </td>
    <td>
        <span class="badge bg-light text-dark" style="font-size: 11px; font-weight: 500; text-transform: uppercase;">
            {{ strtoupper($document->extension ?: ($document->isExternalUrl() ? 'LINK' : 'N/A')) }}
        </span>
    </td>
    <td style="color: #6b7280; font-size: 13px; white-space: nowrap;">
        @if (is_null($document->size_bytes))
            <span class="text-muted">Remote</span>
        @elseif ($document->size_bytes >= 1048576)
            {{ number_format($document->size_bytes / 1048576, 1) }} MB
        @else
            {{ number_format($document->size_bytes / 1024, 1) }} KB
        @endif
    </td>
    <td style="color: #6b7280; font-size: 13px; white-space: nowrap;">
        {{ $document->created_at->format('M d, Y') }}
    </td>
    <td style="white-space: nowrap;">
        @php
            $statusColors = [
                'draft' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                'published' => ['bg' => '#d1fae5', 'text' => '#065f46'],
                'archived' => ['bg' => '#f3f4f6', 'text' => '#4b5563'],
                'under_review' => ['bg' => '#dbeafe', 'text' => '#1e40af'],
            ];
            $colors = $statusColors[$document->status] ?? ['bg' => '#f3f4f6', 'text' => '#4b5563'];
        @endphp
        <span style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; text-transform: capitalize;">
            {{ str_replace('_', ' ', $document->status) }}
        </span>
    </td>
    <td class="text-end">
        <div class="dropdown">
            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown" aria-expanded="false" style="padding: 4px 8px;">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('documents.show', $document) }}">
                        <i class="fas fa-eye me-2 text-info"></i> View
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('documents.edit', $document) }}">
                        <i class="fas fa-edit me-2 text-primary"></i> Edit
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('documents.download', $document) }}">
                        <i class="fas fa-download me-2 text-success"></i> Download
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('documents.destroy', $document) }}" method="POST" class="d-inline delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger" onclick="return confirmSingleDelete(event)">
                            <i class="fas fa-trash me-2"></i> Delete
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </td>
</tr>
