<section class="crm-card">
    <div class="crm-card-title">
        <div>
            <p class="crm-kicker">Needs attention</p>
            <h2>Ageing past its useful life</h2>
            <p>Records that have sat too long without moving, most neglected first.</p>
        </div>
    </div>

    @if (empty($needsAttention))
        <div class="crm-empty">Nothing is overdue for attention.</div>
    @else
        <div class="crm-attention-list">
            @foreach ($needsAttention as $row)
                <div class="crm-attention-item is-{{ $row['severity'] }}">
                    <span class="crm-pill {{ $row['severity'] === 'danger' ? 'danger' : ($row['severity'] === 'warning' ? 'primary' : 'muted') }}">
                        {{ $row['type'] }}
                    </span>

                    <div class="crm-attention-body">
                        <div class="crm-attention-label">
                            @if ($row['url'])
                                <a href="{{ $row['url'] }}">{{ $row['label'] }}</a>
                            @else
                                {{ $row['label'] }}
                            @endif
                        </div>
                        <div class="crm-muted-copy">{{ $row['context'] }}</div>
                    </div>

                    <div class="crm-attention-age">{{ $row['age'] }}</div>
                </div>
            @endforeach
        </div>
    @endif
</section>
