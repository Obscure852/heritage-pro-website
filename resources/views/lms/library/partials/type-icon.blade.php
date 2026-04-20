@php
    $iconClass = match($type ?? 'other') {
        'video' => 'fa-video text-danger',
        'audio' => 'fa-music text-purple',
        'image' => 'fa-image text-success',
        'pdf' => 'fa-file-pdf text-danger',
        'document' => 'fa-file-word text-primary',
        'spreadsheet' => 'fa-file-excel text-success',
        'presentation' => 'fa-file-powerpoint text-warning',
        'archive' => 'fa-file-archive text-secondary',
        default => 'fa-file text-muted'
    };
@endphp
<i class="fas {{ $iconClass }} fa-2x"></i>
