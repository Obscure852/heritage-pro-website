@php
    // Four copies of the list keep the track wider than any viewport, so the
    // -25% loop (exactly one copy) never exposes a gap. Only the first copy is
    // read out; the rest are decorative duplicates.
    $marqueeCopies = 4;
@endphp
<section class="hp-logos hp-band" aria-label="Institutions running Heritage Pro">
    <p class="hp-logos__label">In service at</p>
    <div class="hp-logos__viewport">
        <div class="hp-logos__track">
            @for ($copy = 0; $copy < $marqueeCopies; $copy++)
                <ul class="hp-logos__set" @if ($copy > 0) aria-hidden="true" @endif>
                    @foreach ($site['clients'] as $client)
                        <li class="hp-logos__item">
                            <span class="hp-logos__name">{{ $client['label'] }}</span>
                            <span class="hp-logos__sep" aria-hidden="true"></span>
                        </li>
                    @endforeach
                </ul>
            @endfor
        </div>
    </div>
</section>
