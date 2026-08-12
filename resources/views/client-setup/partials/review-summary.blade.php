@php
    $summaryPayload = collect($payload ?? [])
        ->filter(static fn ($value): bool => $value !== null && $value !== '' && $value !== [])
        ->take(6);
@endphp

<section class="crm-wizard-review-summary" aria-labelledby="{{ $headingId ?? 'wizard-review-heading' }}">
    <div class="crm-wizard-review-summary-heading">
        <div>
            <p class="crm-kicker">Saved snapshot</p>
            <h2 id="{{ $headingId ?? 'wizard-review-heading' }}">Review what is on file</h2>
        </div>
        <i class="bx bx-show-alt" aria-hidden="true"></i>
    </div>

    @if ($summaryPayload->isEmpty())
        <p class="crm-muted">Nothing has been saved in this stage yet.</p>
    @else
        <dl class="crm-wizard-review-list">
            @foreach ($summaryPayload as $key => $value)
                <div>
                    <dt>{{ ucfirst(str_replace(['_', '-'], ' ', $key)) }}</dt>
                    <dd>{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_SLASHES) : (is_bool($value) ? ($value ? 'Yes' : 'No') : $value) }}</dd>
                </div>
            @endforeach
        </dl>
    @endif
</section>
