@php
    $discussionThread = $discussionThread ?? null;
    $draftMessage = $discussionThread?->messages?->last();
    $recipientType = old('recipient_type', $discussionThread?->target_type ?? ($sourceContext ? 'manual' : 'user'));
    $targetId = $discussionThread?->target_id;
@endphp

<form method="POST" action="{{ $action }}" class="crm-form" enctype="multipart/form-data" data-external-discussion-form>
    @csrf
    @if (! empty($method))
        @method($method)
    @endif

    @if ($sourceContext)
        <input type="hidden" name="source_type" value="{{ $sourceContext['type'] }}">
        <input type="hidden" name="source_id" value="{{ $sourceContext['id'] }}">
        @include('crm.partials.helper-text', [
            'title' => 'Commercial Source',
            'content' => 'This draft is linked to ' . $sourceContext['title'] . '. The latest private PDF will be attached automatically when the message is sent.',
        ])
    @endif

    <div class="crm-field-grid">
        <div class="crm-field full">
            <label for="subject">Subject</label>
            <input
                id="subject"
                name="subject"
                value="{{ old('subject', $discussionThread?->subject ?? ($sourceContext['subject'] ?? '')) }}"
                placeholder="Enter message subject"
                required
            >
        </div>

        <div class="crm-field">
            <label for="recipient_type">Recipient type</label>
            <select id="recipient_type" name="recipient_type" data-recipient-type-select>
                <option value="user" @selected($recipientType === 'user')>CRM user</option>
                <option value="lead" @selected($recipientType === 'lead')>Lead</option>
                <option value="customer" @selected($recipientType === 'customer')>Customer</option>
                <option value="contact" @selected($recipientType === 'contact')>Contact</option>
                <option value="manual" @selected($recipientType === 'manual')>Manual address</option>
            </select>
        </div>

        <div class="crm-field">
            <label for="integration_id">Integration</label>
            <select id="integration_id" name="integration_id">
                <option value="">No integration</option>
                @foreach ($integrations as $integration)
                    <option value="{{ $integration->id }}" @selected((int) old('integration_id', $discussionThread?->integration_id) === (int) $integration->id)>
                        {{ $integration->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="crm-field full {{ $recipientType === 'user' ? '' : 'd-none' }}" data-recipient-panel="user">
            <label for="recipient_user_id">CRM user</label>
            <select id="recipient_user_id" name="recipient_user_id">
                <option value="">Select a CRM user</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((int) old('recipient_user_id', $discussionThread?->target_type === 'user' ? $discussionThread->recipient_user_id : null) === (int) $user->id)>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="crm-field full {{ $recipientType === 'lead' ? '' : 'd-none' }}" data-recipient-panel="lead">
            <label for="lead_id">Lead</label>
            <select id="lead_id" name="lead_id">
                <option value="">Select a lead</option>
                @foreach ($leads as $lead)
                    <option value="{{ $lead->id }}" @selected((int) old('lead_id', $discussionThread?->target_type === 'lead' ? $targetId : null) === (int) $lead->id)>
                        {{ $lead->company_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="crm-field full {{ $recipientType === 'customer' ? '' : 'd-none' }}" data-recipient-panel="customer">
            <label for="customer_id">Customer</label>
            <select id="customer_id" name="customer_id">
                <option value="">Select a customer</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected((int) old('customer_id', $discussionThread?->target_type === 'customer' ? $targetId : null) === (int) $customer->id)>
                        {{ $customer->company_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="crm-field full {{ $recipientType === 'contact' ? '' : 'd-none' }}" data-recipient-panel="contact">
            <label for="contact_id">Contact</label>
            <select id="contact_id" name="contact_id">
                <option value="">Select a contact</option>
                @foreach ($contacts as $contact)
                    <option value="{{ $contact->id }}" @selected((int) old('contact_id', $discussionThread?->target_type === 'contact' ? $targetId : null) === (int) $contact->id)>
                        {{ $contact->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="crm-field full {{ $recipientType === 'manual' ? '' : 'd-none' }}" data-recipient-panel="manual">
            <label for="recipient_label">Recipient label</label>
            <input
                id="recipient_label"
                name="recipient_label"
                value="{{ old('recipient_label', $sourceContext['recipient_label'] ?? '') }}"
                placeholder="Company or contact name"
            >
        </div>

        <div class="crm-field">
            <label for="recipient_email">Recipient email</label>
            <input
                id="recipient_email"
                name="recipient_email"
                type="email"
                value="{{ old('recipient_email', $discussionThread?->recipient_email ?? ($sourceContext['recipient_email'] ?? '')) }}"
                placeholder="name@company.com"
            >
        </div>

        <div class="crm-field">
            <label for="recipient_phone">Recipient phone</label>
            <input
                id="recipient_phone"
                name="recipient_phone"
                value="{{ old('recipient_phone', $discussionThread?->recipient_phone ?? ($sourceContext['recipient_phone'] ?? '')) }}"
                placeholder="Recipient phone number"
            >
        </div>

        <div class="crm-field full">
            <label for="body">Draft message</label>
            <textarea id="body" name="body" placeholder="Write the message body" required>{{ old('body', $draftMessage?->body ?? ($sourceContext['body'] ?? '')) }}</textarea>
        </div>

        <div class="crm-field full">
            <label for="notes">Internal notes</label>
            <textarea id="notes" name="notes" placeholder="Add internal notes for this thread">{{ old('notes', $discussionThread?->notes) }}</textarea>
        </div>

        @include('crm.discussions.partials.attachment-dropzone', [
            'inputId' => 'thread-attachments',
            'title' => 'Attachments',
            'hint' => 'Attach PDFs, Office documents, or images for this draft.',
        ])
    </div>

    @if ($draftMessage && $draftMessage->attachments->isNotEmpty())
        <div class="crm-stack" style="margin-top: 20px;">
            @include('crm.partials.helper-text', [
                'title' => 'Draft Attachments',
                'content' => 'Existing draft attachments will be sent with the message. Upload additional files above to append more.',
            ])
            <div class="crm-attachments-grid">
                @foreach ($draftMessage->attachments as $attachment)
                    <article class="crm-attachment-card">
                        <div class="crm-attachment-head">
                            <span class="crm-attachment-icon"><i class="{{ $attachment->iconClass() }}"></i></span>
                            <div class="crm-attachment-copy">
                                <strong>{{ $attachment->original_name }}</strong>
                                <span>{{ strtoupper($attachment->extension ?: 'file') }} · {{ $attachment->formattedSize() }}</span>
                            </div>
                        </div>
                        <div class="crm-action-row crm-attachment-actions">
                            <a href="{{ route('crm.discussions.app.attachments.open', $attachment) }}" class="btn btn-light crm-btn-light" target="_blank" rel="noopener">
                                <i class="bx bx-link-external"></i> Open
                            </a>
                            <a href="{{ route('crm.discussions.app.attachments.download', $attachment) }}" class="btn btn-light crm-btn-light">
                                <i class="bx bx-download"></i> Download
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif

    <div class="form-actions">
        @if (! empty($cancelUrl))
            <a href="{{ $cancelUrl }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Cancel</a>
        @endif
        <button type="submit" name="intent" value="draft" class="btn btn-light crm-btn-light">
            <i class="bx bx-save"></i> Save draft
        </button>
        <button type="submit" name="intent" value="send" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="bx bx-send"></i> Send {{ $channelLabel }}</span>
            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...</span>
        </button>
    </div>
</form>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-external-discussion-form]').forEach(function (form) {
                    var typeSelect = form.querySelector('[data-recipient-type-select]');

                    if (!typeSelect) {
                        return;
                    }

                    function syncRecipientPanels() {
                        var activeType = typeSelect.value;

                        form.querySelectorAll('[data-recipient-panel]').forEach(function (panel) {
                            panel.classList.toggle('d-none', panel.getAttribute('data-recipient-panel') !== activeType);
                        });
                    }

                    typeSelect.addEventListener('change', syncRecipientPanels);
                    syncRecipientPanels();
                });
            });
        </script>
    @endpush
@endonce
