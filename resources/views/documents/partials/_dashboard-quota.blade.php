{{-- DSH-01: Storage Quota Widget --}}
<div class="widget-header">
    <h6><i class="fas fa-hdd" style="color: #6b7280; margin-right: 8px;"></i>Storage Usage</h6>
</div>
<div class="widget-body">
    @if($userQuota->is_unlimited)
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span style="font-size: 13px; color: #374151; font-weight: 500;">{{ $usedFormatted }} used</span>
            <span style="font-size: 13px; color: #6b7280;">Unlimited</span>
        </div>
        <div style="background: #e5e7eb; border-radius: 4px; height: 10px; overflow: hidden;">
            <div class="bg-success" style="height: 100%; width: 0%; border-radius: 4px;"></div>
        </div>
        <div style="font-size: 12px; color: #9ca3af; margin-top: 8px; text-align: center;">
            Unlimited storage plan
        </div>
    @else
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span style="font-size: 13px; color: #374151; font-weight: 500;">{{ $usedFormatted }} / {{ $totalFormatted }}</span>
            <span style="font-size: 13px; color: #6b7280;">{{ number_format($usagePercent, 1) }}%</span>
        </div>
        <div style="background: #e5e7eb; border-radius: 4px; height: 10px; overflow: hidden;">
            <div class="{{ $barClass }}" style="height: 100%; width: {{ $barWidth }}%; border-radius: 4px; transition: width 0.3s ease;"></div>
        </div>
        @if($usagePercent > 100)
            <div style="font-size: 12px; color: #ef4444; margin-top: 8px; font-weight: 500;">
                <i class="fas fa-exclamation-triangle"></i> Storage quota exceeded! Contact an administrator.
            </div>
        @elseif($usagePercent >= 80)
            <div style="font-size: 12px; color: #d97706; margin-top: 8px;">
                <i class="fas fa-exclamation-circle"></i> Storage is {{ number_format($usagePercent, 0) }}% full
            </div>
        @else
            <div style="font-size: 12px; color: #9ca3af; margin-top: 8px; text-align: center;">
                {{ number_format(100 - $usagePercent, 1) }}% remaining
            </div>
        @endif
    @endif
</div>
