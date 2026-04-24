@php
    $discussionThread = $discussionThread ?? null;
    $draftMessage = $discussionThread?->messages?->last();
    $recipientType = old('recipient_type', $discussionThread?->target_type ?? ($sourceContext ? 'manual' : 'user'));
    $targetId = $discussionThread?->target_id;
    $channelKey = $channelKey ?? strtolower((string) $channelLabel);
    $isEmailChannel = $channelKey === 'email';
    $isManualRecipient = $recipientType === 'manual';
    $compactAttachmentName = function (?string $name): string {
        $name = trim((string) $name);

        if ($name === '') {
            return 'attachment';
        }

        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $basename = pathinfo($name, PATHINFO_FILENAME);

        if ($basename === '') {
            return $name;
        }

        $shortBase = \Illuminate\Support\Str::substr($basename, 0, 10);

        return $extension !== '' ? $shortBase . '.' . $extension : $shortBase;
    };
@endphp

<form method="POST" action="{{ $action }}" class="crm-form" enctype="multipart/form-data" data-external-discussion-form data-channel-key="{{ $channelKey }}">
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

        <div class="crm-field full">
            <label for="recipient_type">Recipient type</label>
            <select id="recipient_type" name="recipient_type" data-recipient-type-select>
                <option value="user" @selected($recipientType === 'user')>CRM user</option>
                <option value="lead" @selected($recipientType === 'lead')>Lead</option>
                <option value="customer" @selected($recipientType === 'customer')>Customer</option>
                <option value="contact" @selected($recipientType === 'contact')>Contact</option>
                <option value="manual" @selected($recipientType === 'manual')>Manual address</option>
            </select>
        </div>

        @if (! $isEmailChannel)
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
        @endif

        <div class="crm-field full {{ $recipientType === 'user' ? '' : 'd-none' }}" data-recipient-panel="user">
            <label for="recipient_user_id">CRM user</label>
            <select id="recipient_user_id" name="recipient_user_id" @disabled($recipientType !== 'user')>
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
            <select id="lead_id" name="lead_id" @disabled($recipientType !== 'lead')>
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
            <select id="customer_id" name="customer_id" @disabled($recipientType !== 'customer')>
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
            <select id="contact_id" name="contact_id" @disabled($recipientType !== 'contact')>
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
                @disabled(! $isManualRecipient)
            >
        </div>

        @if ($isEmailChannel)
            <div class="crm-field {{ $isManualRecipient ? '' : 'd-none' }}" data-manual-address-field="email">
                <label for="recipient_email">Recipient email</label>
                <input
                    id="recipient_email"
                    name="recipient_email"
                    type="email"
                    value="{{ old('recipient_email', $discussionThread?->recipient_email ?? ($sourceContext['recipient_email'] ?? '')) }}"
                    placeholder="name@company.com"
                    @disabled(! $isManualRecipient)
                >
            </div>
        @else
            <div class="crm-field {{ $isManualRecipient ? '' : 'd-none' }}" data-manual-address-field="phone">
                <label for="recipient_phone">Recipient phone</label>
                <input
                    id="recipient_phone"
                    name="recipient_phone"
                    value="{{ old('recipient_phone', $discussionThread?->recipient_phone ?? ($sourceContext['recipient_phone'] ?? '')) }}"
                    placeholder="Recipient phone number"
                    @disabled(! $isManualRecipient)
                >
            </div>
        @endif

        <div class="crm-field full {{ $isEmailChannel ? 'crm-email-editor-field' : '' }}" @if ($isEmailChannel) data-email-editor-field @endif>
            <label for="body">Draft message</label>
            <textarea
                id="{{ $isEmailChannel ? 'email-editor' : 'body' }}"
                name="body"
                placeholder="Write the message body"
                required
                @if ($isEmailChannel) data-email-editor @endif
            >{{ old('body', $draftMessage?->body ?? ($sourceContext['body'] ?? '')) }}</textarea>
            @if ($errors->has('body'))
                <div class="invalid-feedback d-block" @if ($isEmailChannel) data-email-editor-error @endif>{{ $errors->first('body') }}</div>
            @elseif ($isEmailChannel)
                <div class="invalid-feedback d-none" data-email-editor-error>Message body is required.</div>
            @endif
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
            <div class="crm-attachments-grid crm-discussion-attachment-list">
                @foreach ($draftMessage->attachments as $attachment)
                    <article class="crm-attachment-card crm-discussion-attachment-row">
                        <div class="crm-discussion-attachment-file">
                            <span class="crm-discussion-attachment-badge"><i class="{{ $attachment->iconClass() }}"></i></span>
                            <div class="crm-attachment-copy crm-discussion-attachment-copy">
                                <strong>{{ $compactAttachmentName($attachment->original_name) }}</strong>
                                <span>{{ strtoupper($attachment->extension ?: 'file') }} · {{ $attachment->formattedSize() }}</span>
                            </div>
                        </div>
                        <div class="crm-action-row crm-attachment-actions crm-discussion-attachment-actions">
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
        @if ($isEmailChannel)
            <script src="{{ asset('assets/libs/@ckeditor/@ckeditor.min.js') }}"></script>
        @endif
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
                            var isActive = panel.getAttribute('data-recipient-panel') === activeType;
                            panel.classList.toggle('d-none', !isActive);

                            panel.querySelectorAll('input, select, textarea').forEach(function (input) {
                                input.disabled = !isActive;
                            });
                        });

                        var manualAddressActive = activeType === 'manual';
                        form.querySelectorAll('[data-manual-address-field]').forEach(function (field) {
                            field.classList.toggle('d-none', !manualAddressActive);

                            field.querySelectorAll('input, select, textarea').forEach(function (input) {
                                input.disabled = !manualAddressActive;
                            });
                        });
                    }

                    typeSelect.addEventListener('change', syncRecipientPanels);
                    syncRecipientPanels();
                });

                if (window.ClassicEditor) {
                    function editorTextContent(html) {
                        var container = document.createElement('div');
                        container.innerHTML = html;

                        return (container.textContent || container.innerText || '')
                            .replace(/\u00a0/g, ' ')
                            .trim();
                    }

                    document.querySelectorAll('[data-email-editor]').forEach(function (element) {
                        if (element.dataset.ckeditorReady === 'true') {
                            return;
                        }

                        ClassicEditor.create(element).then(function (editor) {
                            element.dataset.ckeditorReady = 'true';
                            element.required = false;
                            element._crmEditor = editor;
                            editor.ui.view.editable.element.style.minHeight = '220px';

                            var form = element.closest('form');
                            var field = element.closest('[data-email-editor-field]');
                            var errorNode = field ? field.querySelector('[data-email-editor-error]') : null;

                            function syncEditorValidity() {
                                editor.updateSourceElement();

                                var isValid = editorTextContent(editor.getData()) !== '';

                                if (field) {
                                    field.classList.toggle('is-invalid', !isValid);
                                }

                                if (errorNode) {
                                    errorNode.classList.toggle('d-none', isValid);
                                    errorNode.classList.toggle('d-block', !isValid);
                                }

                                return isValid;
                            }

                            editor.model.document.on('change:data', syncEditorValidity);

                            if (form) {
                                form.setAttribute('novalidate', 'novalidate');

                                if (form.dataset.emailEditorValidationReady !== 'true') {
                                    form.dataset.emailEditorValidationReady = 'true';

                                    form.addEventListener('submit', function (event) {
                                        var isValid = true;

                                        form.querySelectorAll('[data-email-editor]').forEach(function (input) {
                                            if (input._crmEditor && typeof input._crmEditor._crmValidate === 'function') {
                                                isValid = input._crmEditor._crmValidate() && isValid;
                                            }
                                        });

                                        if (isValid) {
                                            return;
                                        }

                                        event.preventDefault();
                                        event.stopPropagation();

                                        form.querySelectorAll('button[type="submit"].btn-loading').forEach(function (button) {
                                            button.classList.remove('loading');
                                            button.disabled = false;
                                        });

                                        var editable = form.querySelector('[data-email-editor-field].is-invalid .ck-editor__editable');
                                        if (editable) {
                                            editable.focus();
                                        }
                                    });
                                }
                            }

                            editor._crmValidate = syncEditorValidity;
                            syncEditorValidity();
                        }).catch(function (error) {
                            console.error(error);
                        });
                    });
                }
            });
        </script>
    @endpush
@endonce
