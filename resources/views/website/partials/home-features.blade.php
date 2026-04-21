@php
    $moduleIcons = ['users', 'clipboard', 'book', 'calendar', 'credit', 'megaphone', 'bio', 'grad', 'lib', 'bus', 'shield', 'cpu'];
@endphp
<section id="features" class="section features">
    <div class="container">
        <div class="center" style="max-width: 720px; margin: 0 auto;">
            <span class="eyebrow">Operational coverage</span>
            <h2 style="margin-top: 14px;">End-to-end modules for academics, administration, and learning.</h2>
            <p class="lead" style="margin-top: 16px;">From school records and report cards to collegiate registration, LMS delivery, transcripts, and attendance automation, Heritage Pro connects the workflows institutions use every day.</p>
        </div>

        <div class="features-row">
            <div class="feature-copy">
                <span class="eyebrow">{{ $site['feature_rows'][0]['eyebrow'] }}</span>
                <h3>{{ $site['feature_rows'][0]['title'] }}</h3>
                <p>{{ $site['feature_rows'][0]['description'] }}</p>
                <ul>
                    @foreach ($site['feature_rows'][0]['items'] as $item)
                        <li>{!! $item !!}</li>
                    @endforeach
                </ul>
                <a href="{{ route($site['feature_rows'][0]['route']) }}" class="btn btn-secondary">{{ $site['feature_rows'][0]['cta'] }}</a>
            </div>
            <div class="feature-mock">
                @include('website.partials.window-chrome')
                @include('website.partials.student-record-mock')
            </div>
        </div>

        <div class="features-row reverse">
            <div class="feature-copy">
                <span class="eyebrow">{{ $site['feature_rows'][1]['eyebrow'] }}</span>
                <h3>{{ $site['feature_rows'][1]['title'] }}</h3>
                <p>{{ $site['feature_rows'][1]['description'] }}</p>
                <ul>
                    @foreach ($site['feature_rows'][1]['items'] as $item)
                        <li>{!! $item !!}</li>
                    @endforeach
                </ul>
                <a href="{{ route($site['feature_rows'][1]['route']) }}" class="btn btn-secondary">{{ $site['feature_rows'][1]['cta'] }}</a>
            </div>
            <div class="feature-mock">
                @include('website.partials.report-card-mock')
            </div>
        </div>

        <div class="features-row">
            <div class="feature-copy">
                <span class="eyebrow">{{ $site['feature_rows'][2]['eyebrow'] }}</span>
                <h3>{{ $site['feature_rows'][2]['title'] }}</h3>
                <p>{{ $site['feature_rows'][2]['description'] }}</p>
                <ul>
                    @foreach ($site['feature_rows'][2]['items'] as $item)
                        <li>{!! $item !!}</li>
                    @endforeach
                </ul>
                <a href="{{ route($site['feature_rows'][2]['route']) }}" class="btn btn-secondary">{{ $site['feature_rows'][2]['cta'] }}</a>
            </div>
            <div class="feature-mock">
                @include('website.partials.fees-mock')
            </div>
        </div>

        <h3 class="center" style="margin-top: 120px; margin-bottom: 14px; font-size: 32px;">Every module, working in concert.</h3>
        <p class="lead center" style="max-width: 620px; margin: 0 auto;">A complete administrative operating system — twelve modules, one database, one login.</p>

        <div class="modules-grid">
            @foreach ($site['modules'] as $index => $module)
                <div class="module-tile">
                    <div class="icon">@include('website.partials.icon', ['name' => $moduleIcons[$index] ?? 'users', 'size' => 22])</div>
                    <h4>{{ $module['title'] }}</h4>
                    <p>{{ $module['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
