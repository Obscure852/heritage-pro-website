<div class="mini-dash">
    <div class="mini-side">
        <div class="mini-side-brand">
            <div class="mini-tile"></div>
            <b>Heritage <span>Pro</span></b>
        </div>
        @foreach ($site['sidebar_modules'] as $module)
            <div @class(['mini-nav-item', 'active' => $module['active']])>
                <span class="dot"></span>{{ $module['label'] }}
            </div>
        @endforeach
    </div>
    <div class="mini-main">
        <div class="mini-h">Dashboard overview</div>
        <div class="mini-stats">
            @foreach ($site['hero_dashboard']['stats'] as $stat)
                <div class="mini-stat">
                    <b>{{ $stat['value'] }}</b>
                    <span>{{ $stat['label'] }}</span>
                    <span class="badge">{{ $stat['badge'] }}</span>
                </div>
            @endforeach
        </div>
        <div class="mini-chart">
            @foreach ($site['hero_dashboard']['bars'] as $bar)
                <div class="bar" style="height: {{ ($bar / 110) * 100 }}%;"></div>
            @endforeach
        </div>
        <div class="mini-table">
            @foreach ($site['hero_dashboard']['rows'] as $row)
                <div class="mini-table-row">
                    <span style="font-weight: 600; color: var(--fg-1);">{{ $row['name'] }}</span>
                    <code>{{ $row['code'] }}</code>
                    <span @class(['pill', 'warn' => $row['theme'] === 'warn', 'danger' => $row['theme'] === 'danger'])>● {{ $row['status'] }}</span>
                    <span style="color: var(--fg-3); text-align: right;">⋯</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
