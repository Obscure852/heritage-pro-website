{{-- Day Schedule Visual Preview --}}
<div class="section-title"><i class="fas fa-eye me-2"></i>Day Preview</div>
<div class="day-preview-container" id="dayPreview">
    @if (!empty($daySchedule))
        @foreach ($daySchedule as $item)
            @if ($item['type'] === 'period')
                <div class="day-preview-period" style="flex: {{ $item['duration'] }}">
                    <span>P{{ $item['period'] }}</span>
                    <span class="period-time">{{ $item['start_time'] }}-{{ $item['end_time'] }}</span>
                </div>
            @else
                <div class="day-preview-break" style="flex: {{ $item['duration'] }}">
                    <span>{{ $item['label'] }}</span>
                </div>
            @endif
        @endforeach
    @else
        <div style="display: flex; align-items: center; justify-content: center; width: 100%; color: #9ca3af; font-size: 13px;">
            No periods configured yet. Add periods and save to see the day preview.
        </div>
    @endif
</div>
