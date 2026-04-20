{{-- Sidebar Quota Progress Bar Widget --}}
@if(isset($userQuota))
<div class="quota-widget" style="padding: 10px 12px;">
    @if($userQuota->is_unlimited)
        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; margin-bottom: 4px;">
            <span style="font-weight: 600; color: #374151;">Storage</span>
            <span style="color: #6b7280;">Unlimited</span>
        </div>
    @else
        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; margin-bottom: 4px;">
            <span style="font-weight: 600; color: #374151;">Storage</span>
            <span style="color: #6b7280;">{{ $usedFormatted }} / {{ $totalFormatted }}</span>
        </div>
        @php
            $usagePercent = $userQuota->usage_percent;
            $barWidth = min($usagePercent, 100);
            if ($usagePercent > 100) {
                $barClass = 'bg-danger';
            } elseif ($usagePercent >= 80) {
                $barClass = 'bg-warning';
            } else {
                $barClass = 'bg-success';
            }
        @endphp
        <div style="background: #e5e7eb; border-radius: 4px; height: 8px; overflow: hidden;">
            <div class="{{ $barClass }}" style="height: 100%; width: {{ $barWidth }}%; border-radius: 4px; transition: width 0.3s ease;"></div>
        </div>
        @if($usagePercent > 100)
            <div style="font-size: 11px; color: #ef4444; margin-top: 4px; font-weight: 500;">
                <i class="fas fa-exclamation-triangle"></i> Storage quota exceeded!
            </div>
        @elseif($usagePercent >= 80)
            <div style="font-size: 11px; color: #d97706; margin-top: 4px;">
                <i class="fas fa-exclamation-circle"></i> Storage is {{ number_format($usagePercent, 0) }}% full
            </div>
        @endif
    @endif
</div>
@endif
