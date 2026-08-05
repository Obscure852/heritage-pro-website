<section class="crm-card">
    <div class="crm-card-title">
        <div>
            <p class="crm-kicker">My day</p>
            <h2>{{ now()->format('l d F') }}</h2>
            <p>Your meetings today, plus every follow-up that is due or already late.</p>
        </div>
    </div>

    @if (empty($myDay))
        <div class="crm-empty">Nothing is scheduled and no follow-ups are due. Clear day.</div>
    @else
        <div class="crm-day-list">
            @foreach ($myDay as $item)
                <div class="crm-day-item{{ $item['overdue'] ? ' is-overdue' : '' }}">
                    <div class="crm-day-time">{{ $item['time'] }}</div>

                    <div class="crm-day-body">
                        <div class="crm-day-label">
                            @if ($item['url'])
                                <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                            @else
                                {{ $item['label'] }}
                            @endif
                        </div>
                        <div class="crm-muted-copy">{{ $item['context'] }}</div>
                    </div>

                    <span class="crm-pill {{ $item['kind'] === 'event' ? 'primary' : ($item['overdue'] ? 'danger' : 'muted') }}">
                        {{ $item['kind'] === 'event' ? 'Meeting' : 'Follow-up' }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</section>
