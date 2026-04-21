<div class="client-marquee" aria-label="Selected live institutions running Heritage Pro">
    <div class="client-marquee-track">
        @for ($iteration = 0; $iteration < 2; $iteration++)
            @foreach ($site['clients'] as $client)
                <div class="client-pill">
                    <div class="client-pill-mark">{{ $client['mark'] }}</div>
                    <div>
                        <strong>{{ $client['label'] }}</strong>
                        <span>{{ $client['type'] }}</span>
                    </div>
                </div>
            @endforeach
        @endfor
    </div>
</div>
