<section class="logo-strip">
    <div class="container">
        <div class="label">Selected live institutions running Heritage Pro</div>
        <div class="logo-marquee-shell">
            <div class="logo-row heritage-logo-marquee-track">
                @for ($iteration = 0; $iteration < 2; $iteration++)
                    @foreach ($site['clients'] as $client)
                        <div class="fake-logo heritage-logo-pill">
                            <div class="mark">{{ $client['mark'] }}</div>
                            <span>{{ $client['label'] }}</span>
                        </div>
                    @endforeach
                @endfor
            </div>
        </div>
    </div>
</section>
