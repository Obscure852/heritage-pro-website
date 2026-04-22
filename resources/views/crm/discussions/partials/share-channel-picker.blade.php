@php
    $sourceType = $sourceType ?? null;
    $sourceId = $sourceId ?? null;
    $sourceLabel = $sourceLabel ?? null;
    $backUrl = $backUrl ?? route('crm.discussions.index');
    $preferredChannel = (string) request('channel', 'app');

    $baseQuery = collect([
        'source_type' => $sourceType,
        'source_id' => $sourceId,
        'subject' => request('subject'),
        'body' => request('body'),
        'notes' => request('notes'),
        'recipient_user_id' => request('recipient_user_id'),
        'recipient_email' => request('recipient_email'),
        'recipient_phone' => request('recipient_phone'),
        'recipient_label' => request('recipient_label'),
        'integration_id' => request('integration_id'),
    ])->filter(function ($value) {
        return filled($value);
    })->all();

    $appDirectQuery = $baseQuery;
    $emailDirectQuery = $baseQuery + ['recipient_type' => request('recipient_type', 'manual')];
    $whatsAppDirectQuery = $baseQuery + ['recipient_type' => request('recipient_type', 'manual')];
@endphp

@include('crm.discussions.partials.channel-styles')

@include('crm.partials.helper-text', [
    'title' => 'Share Document',
    'content' => 'Choose a channel-specific workflow. Each card opens a dedicated direct or bulk page with the ' . strtolower($sourceLabel ?: 'document') . ' context preloaded.',
])

<div class="crm-choice-grid">
    <section class="crm-discussion-channel-card {{ $preferredChannel === 'app' ? 'preferred' : '' }}">
        <div>
            <p class="crm-kicker">App Messaging</p>
            <h3>Internal staff communication</h3>
            <p>Use a one-to-one DM for handoff or post a company chat announcement addressed to selected internal users.</p>
        </div>
        <div class="crm-discussion-channel-pills">
            <span class="crm-pill primary">Direct DM</span>
            <span class="crm-pill primary">Company chat announcement</span>
            @if ($sourceLabel)
                <span class="crm-pill muted">{{ $sourceLabel }}</span>
            @endif
        </div>
        <div class="crm-action-row">
            <a href="{{ route('crm.discussions.app.direct.create', $appDirectQuery) }}" class="btn btn-primary">
                <i class="bx bx-message-square-dots"></i> Direct message
            </a>
            <a href="{{ route('crm.discussions.app.bulk.create', $baseQuery) }}" class="btn btn-light crm-btn-light">
                <i class="bx bx-broadcast"></i> Bulk announcement
            </a>
        </div>
    </section>

    <section class="crm-discussion-channel-card {{ $preferredChannel === 'email' ? 'preferred' : '' }}">
        <div>
            <p class="crm-kicker">Email</p>
            <h3>External email delivery</h3>
            <p>Open the dedicated direct or bulk email composer with the latest document PDF linked automatically.</p>
        </div>
        <div class="crm-discussion-channel-pills">
            <span class="crm-pill primary">Direct email</span>
            <span class="crm-pill primary">Bulk email</span>
            @if (filled(request('recipient_email')))
                <span class="crm-pill muted">{{ request('recipient_email') }}</span>
            @endif
        </div>
        <div class="crm-action-row">
            <a href="{{ route('crm.discussions.email.direct.create', $emailDirectQuery) }}" class="btn btn-primary">
                <i class="bx bx-envelope"></i> Direct email
            </a>
            <a href="{{ route('crm.discussions.email.bulk.create', $baseQuery) }}" class="btn btn-light crm-btn-light">
                <i class="bx bx-layer-plus"></i> Bulk email
            </a>
        </div>
    </section>

    <section class="crm-discussion-channel-card {{ $preferredChannel === 'whatsapp' ? 'preferred' : '' }}">
        <div>
            <p class="crm-kicker">WhatsApp</p>
            <h3>Provider-ready messaging</h3>
            <p>Open the dedicated direct or bulk WhatsApp flow and queue the message where a live integration is not yet available.</p>
        </div>
        <div class="crm-discussion-channel-pills">
            <span class="crm-pill primary">Direct WhatsApp</span>
            <span class="crm-pill primary">Bulk WhatsApp</span>
            @if (filled(request('recipient_phone')))
                <span class="crm-pill muted">{{ request('recipient_phone') }}</span>
            @endif
        </div>
        <div class="crm-action-row">
            <a href="{{ route('crm.discussions.whatsapp.direct.create', $whatsAppDirectQuery) }}" class="btn btn-primary">
                <i class="bx bxl-whatsapp"></i> Direct WhatsApp
            </a>
            <a href="{{ route('crm.discussions.whatsapp.bulk.create', $baseQuery) }}" class="btn btn-light crm-btn-light">
                <i class="bx bx-layer-plus"></i> Bulk WhatsApp
            </a>
        </div>
    </section>
</div>

<div class="form-actions">
    <a href="{{ $backUrl }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Back</a>
</div>
