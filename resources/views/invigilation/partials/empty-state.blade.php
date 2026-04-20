<div class="invigilation-empty-state {{ !empty($compact) ? 'invigilation-empty-state-compact' : '' }}">
    <div class="invigilation-empty-icon">
        <i class="{{ $icon ?? 'fas fa-clipboard-list' }}"></i>
    </div>
    <div class="invigilation-empty-title">{{ $title ?? '' }}</div>
    @if (!empty($copy))
        <div class="invigilation-empty-copy">{{ $copy }}</div>
    @endif
</div>
