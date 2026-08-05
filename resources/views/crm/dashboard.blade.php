@extends('layouts.crm')

@section('title', 'CRM Dashboard')
@section('crm_heading', 'CRM Dashboard')
@section('crm_subheading', 'Monitor sales pipeline health, open support and sales requests, recent activity, and follow-up pressure from a single Heritage-branded workspace.')

@section('crm_header_stats')
    @foreach ($metrics as $metric)
        @include('crm.dashboard.partials.hero-stat', [
            'value' => number_format($metric['value']),
            'label' => $metric['label'],
            'url' => $metric['url'],
        ])
    @endforeach
@endsection

@section('crm_actions')
    <div class="crm-period-switch" role="group" aria-label="Reporting period">
        @foreach ($periodOptions as $optionKey => $optionLabel)
            <a href="{{ route('crm.dashboard', ['period' => $optionKey]) }}"
               class="crm-period-option{{ $period->key === $optionKey ? ' is-active' : '' }}"
               @if ($period->key === $optionKey) aria-current="true" @endif>
                {{ $optionLabel }}
            </a>
        @endforeach
    </div>
@endsection

@section('content')
    <div class="crm-stack">
        {{-- Widgets and their order come from config('heritage_crm.dashboard.widgets'). --}}
        @foreach ($widgetRows as $row)
            @if (count($row) === 1 && ($row[0]['size'] ?? 'full') === 'full')
                @include($row[0]['partial'])
            @else
                <div class="crm-grid cols-2">
                    @foreach ($row as $widget)
                        @include($widget['partial'])
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
@endsection

@push('scripts')
@if (in_array('revenue_trend', $visibleWidgets, true) || in_array('quote_conversion', $visibleWidgets, true))
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof ApexCharts === 'undefined') {
        return;
    }

    var currency = @json($currencyCode);

    function money(value) {
        return (currency ? currency + ' ' : '') + Number(value).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

@if (in_array('revenue_trend', $visibleWidgets, true))
    var trendEl = document.getElementById('crm-revenue-trend');
    if (trendEl && trendEl.getAttribute('data-empty') === 'false') {
        new ApexCharts(trendEl, {
            chart: { type: 'bar', height: 300, stacked: true, toolbar: { show: false }, fontFamily: 'inherit' },
            series: @json($revenueTrend['series']),
            xaxis: { categories: @json($revenueTrend['labels']), labels: { style: { fontSize: '11px' } } },
            yaxis: { labels: { formatter: function (value) { return Number(value).toLocaleString(); } } },
            colors: ['#4e73df', '#cbd5e1'],
            plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
            dataLabels: { enabled: false },
            legend: { position: 'top', horizontalAlign: 'left', fontSize: '12px' },
            grid: { borderColor: '#eef2f7' },
            tooltip: { y: { formatter: money } }
        }).render();
    }

@endif

@if (in_array('quote_conversion', $visibleWidgets, true))
    var conversionEl = document.getElementById('crm-quote-conversion');
    if (conversionEl) {
        new ApexCharts(conversionEl, {
            chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
            series: @json($quoteConversion['values']),
            labels: @json($quoteConversion['labels']),
            colors: ['#94a3b8', '#4e73df', '#0ab39c', '#f06548', '#f7b84b', '#64748b'],
            legend: { position: 'bottom', fontSize: '12px' },
            dataLabels: { enabled: true, formatter: function (percent) { return Math.round(percent) + '%'; } },
            plotOptions: { pie: { donut: { size: '62%' } } },
            tooltip: { y: { formatter: function (value) { return value + ' quotes'; } } }
        }).render();
    }
@endif
});
</script>
@endif

@if (in_array('clock', $visibleWidgets, true))
<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Live clock
    var clockEl = document.getElementById('crm-dashboard-clock');
    if (clockEl) {
        setInterval(function () {
            var now = new Date();
            clockEl.textContent = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        }, 10000);
    }

    // Elapsed counter — ticks every second
    var elapsedEl = document.getElementById('crm-dashboard-elapsed');
    var isClockedIn = {{ ($clockStatus['state'] ?? '') === 'clocked_in' ? 'true' : 'false' }};
    @if (($clockStatus['state'] ?? '') === 'clocked_in' && $clockStatus['clocked_in_at'])
        var elapsedTotalSeconds = {{ (int) $clockStatus['clocked_in_at']->diffInSeconds(now()) }};
    @else
        var elapsedTotalSeconds = 0;
    @endif

    if (elapsedEl && isClockedIn) {
        setInterval(function () {
            elapsedTotalSeconds++;
            var h = Math.floor(elapsedTotalSeconds / 3600);
            var m = Math.floor((elapsedTotalSeconds % 3600) / 60);
            var s = elapsedTotalSeconds % 60;
            elapsedEl.textContent = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        }, 1000);
    }

    // Clock button
    var clockBtn = document.getElementById('crm-dashboard-clock-btn');
    if (clockBtn) {
        clockBtn.addEventListener('click', function () {
            clockBtn.disabled = true;
            clockBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';

            fetch('{{ route("crm.attendance.clock") }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin',
                body: JSON.stringify({})
            })
            .then(function (r) { return r.json(); })
            .then(function () { window.location.reload(); })
            .catch(function () {
                clockBtn.disabled = false;
                clockBtn.innerHTML = 'Error — try again';
            });
        });
    }
});
</script>
@endif
@endpush
