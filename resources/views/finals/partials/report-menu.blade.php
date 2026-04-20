@php
    $items = $items ?? [];
@endphp

@forelse ($items as $item)
    <li>
        @if ($item['enabled'] ?? true)
            <a class="dropdown-item" href="{{ $item['url'] }}">
                <i class="{{ $item['icon'] ?? 'fas fa-file-alt text-primary' }}"></i> {{ $item['label'] }}
            </a>
        @else
            <span class="dropdown-item disabled text-muted">
                <i class="{{ $item['icon'] ?? 'fas fa-ban text-muted' }}"></i> {{ $item['label'] }}
            </span>
        @endif
    </li>
@empty
    <li>
        <span class="dropdown-item disabled text-muted">
            <i class="fas fa-ban"></i> No reports available
        </span>
    </li>
@endforelse
