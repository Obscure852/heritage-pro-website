@php
    $discussionCampaign = $discussionCampaign ?? null;
    $snapshot = $discussionCampaign?->audience_snapshot ?? [];
    $requested = $snapshot['requested'] ?? [];
    $channelKey = $channelKey ?? strtolower((string) $channelLabel);
    $isEmailChannel = $channelKey === 'email';
    $requestedUsers = collect(old('recipient_user_ids', $requested['recipient_user_ids'] ?? []))
        ->map(fn ($value) => (int) $value)
        ->filter()
        ->unique()
        ->values()
        ->all();
    $requestedLeads = collect(old('lead_ids', $requested['lead_ids'] ?? []))
        ->map(fn ($value) => (int) $value)
        ->filter()
        ->unique()
        ->values()
        ->all();
    $requestedCustomers = collect(old('customer_ids', $requested['customer_ids'] ?? []))
        ->map(fn ($value) => (int) $value)
        ->filter()
        ->unique()
        ->values()
        ->all();
    $requestedContacts = collect(old('contact_ids', $requested['contact_ids'] ?? []))
        ->map(fn ($value) => (int) $value)
        ->filter()
        ->unique()
        ->values()
        ->all();
    $recipientError = $errors->first('recipient_user_ids')
        ?: $errors->first('recipient_user_ids.*')
        ?: $errors->first('lead_ids.*')
        ?: $errors->first('customer_ids.*')
        ?: $errors->first('contact_ids.*');
    $audienceModalId = 'crm-external-audience-modal-' . $channelKey . '-' . ($discussionCampaign?->id ?: 'new');
    $initialSelectedCount = count($requestedUsers) + count($requestedLeads) + count($requestedCustomers) + count($requestedContacts);
@endphp

<form method="POST" action="{{ $action }}" class="crm-form" enctype="multipart/form-data">
    @csrf
    @if (! empty($method))
        @method($method)
    @endif

    @if ($sourceContext)
        <input type="hidden" name="source_type" value="{{ $sourceContext['type'] }}">
        <input type="hidden" name="source_id" value="{{ $sourceContext['id'] }}">
        @include('crm.partials.helper-text', [
            'title' => 'Commercial Source',
            'content' => 'This bulk draft is linked to ' . $sourceContext['title'] . '. The latest private PDF will be attached automatically when the campaign is sent.',
        ])
    @endif

    <div class="crm-field-grid">
        <div class="crm-field full">
            <label for="subject">Campaign subject</label>
            <input
                id="subject"
                name="subject"
                value="{{ old('subject', $discussionCampaign?->subject ?? ($sourceContext['subject'] ?? '')) }}"
                placeholder="Enter campaign subject"
                required
            >
        </div>

        <div class="crm-field full {{ $isEmailChannel ? 'crm-email-editor-field' : '' }}" @if ($isEmailChannel) data-email-editor-field @endif>
            <label for="body">Message body</label>
            <textarea
                id="{{ $isEmailChannel ? 'email-editor' : 'body' }}"
                name="body"
                placeholder="Write the bulk message body"
                required
                @if ($isEmailChannel) data-email-editor @endif
            >{{ old('body', $discussionCampaign?->body ?? ($sourceContext['body'] ?? '')) }}</textarea>
            @if ($errors->has('body'))
                <div class="invalid-feedback d-block" @if ($isEmailChannel) data-email-editor-error @endif>{{ $errors->first('body') }}</div>
            @elseif ($isEmailChannel)
                <div class="invalid-feedback d-none" data-email-editor-error>Message body is required.</div>
            @endif
        </div>

        <div class="crm-field full">
            <label for="notes">Internal notes</label>
            <textarea id="notes" name="notes" placeholder="Add internal notes for this campaign">{{ old('notes', $discussionCampaign?->notes) }}</textarea>
        </div>

        @if (! $isEmailChannel)
            <div class="crm-field">
                <label for="integration_id">Integration</label>
                <select id="integration_id" name="integration_id">
                    <option value="">No integration</option>
                    @foreach ($integrations as $integration)
                        <option value="{{ $integration->id }}" @selected((int) old('integration_id', $discussionCampaign?->integration_id) === (int) $integration->id)>
                            {{ $integration->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="crm-field full">
            <label for="crm-external-audience-launcher">Recipients</label>
            <div class="crm-audience-builder {{ $recipientError ? 'is-invalid' : '' }}" data-external-audience-builder data-modal-id="{{ $audienceModalId }}">
                <div class="crm-audience-builder-summary">
                    <div>
                        <div class="crm-audience-builder-summary-title">Audience builder</div>
                        <div class="crm-audience-builder-summary-copy" data-audience-summary-copy>
                            {{ $initialSelectedCount > 0 ? $initialSelectedCount . ' recipient(s) selected.' : 'No recipients selected yet. Choose recipients by type, then search and tick who should receive this campaign.' }}
                        </div>
                        <button
                            type="button"
                            id="crm-external-audience-launcher"
                            class="btn btn-light crm-btn-light crm-audience-builder-trigger"
                            data-audience-open
                        >
                            <i class="bx bx-user-plus"></i> Choose recipients
                        </button>
                    </div>
                </div>

                <div class="crm-discussion-recipient-pills" data-audience-summary-pills>
                    @if (count($requestedUsers))
                        <span class="crm-discussion-recipient-pill">Users · {{ count($requestedUsers) }}</span>
                    @endif
                    @if (count($requestedLeads))
                        <span class="crm-discussion-recipient-pill">Leads · {{ count($requestedLeads) }}</span>
                    @endif
                    @if (count($requestedCustomers))
                        <span class="crm-discussion-recipient-pill">Customers · {{ count($requestedCustomers) }}</span>
                    @endif
                    @if (count($requestedContacts))
                        <span class="crm-discussion-recipient-pill">Contacts · {{ count($requestedContacts) }}</span>
                    @endif
                </div>

                <div class="crm-audience-builder-selected" data-audience-selected-tags></div>
            </div>
            @if ($recipientError)
                <div class="invalid-feedback d-block">{{ $recipientError }}</div>
            @endif
            <small class="crm-muted">Search, tick, and review selected recipients without leaving the email draft.</small>
        </div>

        @include('crm.discussions.partials.attachment-dropzone', [
            'inputId' => 'campaign-attachments',
            'title' => 'Attachments',
            'hint' => 'Attach files that should be included when the campaign is sent.',
        ])
    </div>

    <div class="form-actions">
        @if (! empty($cancelUrl))
            <a href="{{ $cancelUrl }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Cancel</a>
        @endif
        <button type="submit" name="intent" value="draft" class="btn btn-light crm-btn-light">
            <i class="bx bx-save"></i> Save draft
        </button>
        <button type="submit" name="intent" value="send" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="bx bx-send"></i> Send {{ $channelLabel }} Bulk</span>
            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...</span>
        </button>
    </div>

    <div class="modal fade crm-audience-modal" id="{{ $audienceModalId }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Choose campaign recipients</h5>
                        <div class="crm-muted">Search across CRM users, leads, customers, and contacts, then tick who should receive this {{ strtolower($channelLabel) }} campaign.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="crm-audience-builder-toolbar">
                        <div class="crm-audience-builder-search">
                            <i class="bx bx-search"></i>
                            <input type="search" class="form-control" placeholder="Search recipients by name, company, email, or type" data-audience-search>
                        </div>
                        <div class="crm-pill-selector crm-audience-builder-filters">
                            <button type="button" class="crm-audience-filter is-active" data-audience-filter="all">All</button>
                            <button type="button" class="crm-audience-filter" data-audience-filter="user">CRM Users</button>
                            <button type="button" class="crm-audience-filter" data-audience-filter="lead">Leads</button>
                            <button type="button" class="crm-audience-filter" data-audience-filter="customer">Customers</button>
                            <button type="button" class="crm-audience-filter" data-audience-filter="contact">Contacts</button>
                        </div>
                        <div class="crm-audience-builder-actions">
                            <button type="button" class="btn btn-light crm-btn-light btn-sm" data-audience-select-visible>
                                <i class="bx bx-check-square"></i> Select visible
                            </button>
                            <button type="button" class="btn btn-light crm-btn-light btn-sm" data-audience-clear-visible>
                                <i class="bx bx-checkbox-minus"></i> Clear visible
                            </button>
                        </div>
                    </div>

                    <div class="crm-audience-builder-list">
                        <section class="crm-audience-section" data-audience-section="user">
                            <div class="crm-audience-section-head">
                                <div>
                                    <strong>CRM Users</strong>
                                    <span data-audience-section-copy="user">Internal CRM recipients.</span>
                                </div>
                                <span class="crm-pill muted" data-audience-section-count="user">{{ count($requestedUsers) }} selected</span>
                            </div>
                            <div class="crm-audience-options">
                                @foreach ($users as $user)
                                    @php($meta = collect([$user->email, ucfirst($user->role ?? '')])->filter()->join(' · '))
                                    <label
                                        class="crm-audience-option"
                                        data-audience-row
                                        data-audience-category="user"
                                        data-audience-label="{{ $user->name }}"
                                        data-audience-search="{{ strtolower(trim($user->name . ' ' . $meta)) }}"
                                    >
                                        <input
                                            type="checkbox"
                                            class="crm-audience-option-input"
                                            name="recipient_user_ids[]"
                                            value="{{ $user->id }}"
                                            @checked(in_array((int) $user->id, $requestedUsers, true))
                                            data-audience-checkbox
                                        >
                                        <span class="crm-audience-option-box">
                                            <span class="crm-audience-option-copy">
                                                <strong>{{ $user->name }}</strong>
                                                <span>{{ $meta ?: 'Internal CRM user' }}</span>
                                            </span>
                                            <span class="crm-pill muted">User</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        <section class="crm-audience-section" data-audience-section="lead">
                            <div class="crm-audience-section-head">
                                <div>
                                    <strong>Leads</strong>
                                    <span data-audience-section-copy="lead">Prospects and pipeline accounts.</span>
                                </div>
                                <span class="crm-pill muted" data-audience-section-count="lead">{{ count($requestedLeads) }} selected</span>
                            </div>
                            <div class="crm-audience-options">
                                @foreach ($leads as $lead)
                                    <label
                                        class="crm-audience-option"
                                        data-audience-row
                                        data-audience-category="lead"
                                        data-audience-label="{{ $lead->company_name }}"
                                        data-audience-search="{{ strtolower(trim($lead->company_name . ' lead ' . ($lead->status ?? ''))) }}"
                                    >
                                        <input
                                            type="checkbox"
                                            class="crm-audience-option-input"
                                            name="lead_ids[]"
                                            value="{{ $lead->id }}"
                                            @checked(in_array((int) $lead->id, $requestedLeads, true))
                                            data-audience-checkbox
                                        >
                                        <span class="crm-audience-option-box">
                                            <span class="crm-audience-option-copy">
                                                <strong>{{ $lead->company_name }}</strong>
                                                <span>{{ ucfirst($lead->status ?? 'Lead') }} lead</span>
                                            </span>
                                            <span class="crm-pill muted">Lead</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        <section class="crm-audience-section" data-audience-section="customer">
                            <div class="crm-audience-section-head">
                                <div>
                                    <strong>Customers</strong>
                                    <span data-audience-section-copy="customer">Existing customer accounts.</span>
                                </div>
                                <span class="crm-pill muted" data-audience-section-count="customer">{{ count($requestedCustomers) }} selected</span>
                            </div>
                            <div class="crm-audience-options">
                                @foreach ($customers as $customer)
                                    <label
                                        class="crm-audience-option"
                                        data-audience-row
                                        data-audience-category="customer"
                                        data-audience-label="{{ $customer->company_name }}"
                                        data-audience-search="{{ strtolower(trim($customer->company_name . ' customer ' . ($customer->status ?? ''))) }}"
                                    >
                                        <input
                                            type="checkbox"
                                            class="crm-audience-option-input"
                                            name="customer_ids[]"
                                            value="{{ $customer->id }}"
                                            @checked(in_array((int) $customer->id, $requestedCustomers, true))
                                            data-audience-checkbox
                                        >
                                        <span class="crm-audience-option-box">
                                            <span class="crm-audience-option-copy">
                                                <strong>{{ $customer->company_name }}</strong>
                                                <span>{{ ucfirst($customer->status ?? 'Customer') }} customer</span>
                                            </span>
                                            <span class="crm-pill muted">Customer</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        <section class="crm-audience-section" data-audience-section="contact">
                            <div class="crm-audience-section-head">
                                <div>
                                    <strong>Contacts</strong>
                                    <span data-audience-section-copy="contact">Named contact records.</span>
                                </div>
                                <span class="crm-pill muted" data-audience-section-count="contact">{{ count($requestedContacts) }} selected</span>
                            </div>
                            <div class="crm-audience-options">
                                @foreach ($contacts as $contact)
                                    @php($contactMeta = $contact->customer_id ? 'Customer contact' : ($contact->lead_id ? 'Lead contact' : 'Contact record'))
                                    <label
                                        class="crm-audience-option"
                                        data-audience-row
                                        data-audience-category="contact"
                                        data-audience-label="{{ $contact->name }}"
                                        data-audience-search="{{ strtolower(trim($contact->name . ' ' . $contactMeta)) }}"
                                    >
                                        <input
                                            type="checkbox"
                                            class="crm-audience-option-input"
                                            name="contact_ids[]"
                                            value="{{ $contact->id }}"
                                            @checked(in_array((int) $contact->id, $requestedContacts, true))
                                            data-audience-checkbox
                                        >
                                        <span class="crm-audience-option-box">
                                            <span class="crm-audience-option-copy">
                                                <strong>{{ $contact->name }}</strong>
                                                <span>{{ $contactMeta }}</span>
                                            </span>
                                            <span class="crm-pill muted">Contact</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    <div class="crm-empty-inline d-none" data-audience-empty>No recipients match the current search or filter.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light crm-btn-light" data-audience-clear-all>
                        <i class="bx bx-reset"></i> Clear all
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="bx bx-check"></i> Done
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@if ($isEmailChannel)
    @once
        @push('scripts')
            <script src="{{ asset('assets/libs/@ckeditor/@ckeditor.min.js') }}"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (!window.ClassicEditor) {
                        return;
                    }

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
                });
            </script>
        @endpush
    @endonce
@endif

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-external-audience-builder]').forEach(function (builder) {
                    if (builder.dataset.ready === 'true') {
                        return;
                    }

                    builder.dataset.ready = 'true';

                    var modalId = builder.getAttribute('data-modal-id');
                    var modalElement = modalId ? document.getElementById(modalId) : null;
                    var openButton = builder.querySelector('[data-audience-open]');
                    var summaryCopy = builder.querySelector('[data-audience-summary-copy]');
                    var summaryPills = builder.querySelector('[data-audience-summary-pills]');
                    var selectedTags = builder.querySelector('[data-audience-selected-tags]');
                    var searchInput = modalElement ? modalElement.querySelector('[data-audience-search]') : null;
                    var filterButtons = modalElement ? Array.prototype.slice.call(modalElement.querySelectorAll('[data-audience-filter]')) : [];
                    var rows = modalElement ? Array.prototype.slice.call(modalElement.querySelectorAll('[data-audience-row]')) : [];
                    var sectionCounts = modalElement ? Array.prototype.slice.call(modalElement.querySelectorAll('[data-audience-section-count]')) : [];
                    var emptyState = modalElement ? modalElement.querySelector('[data-audience-empty]') : null;
                    var selectVisibleButton = modalElement ? modalElement.querySelector('[data-audience-select-visible]') : null;
                    var clearVisibleButton = modalElement ? modalElement.querySelector('[data-audience-clear-visible]') : null;
                    var clearAllButton = modalElement ? modalElement.querySelector('[data-audience-clear-all]') : null;
                    var modal = modalElement && window.bootstrap && window.bootstrap.Modal
                        ? new window.bootstrap.Modal(modalElement)
                        : null;
                    var activeFilter = 'all';

                    if (!modalElement || rows.length === 0) {
                        return;
                    }

                    function rowCheckbox(row) {
                        return row.querySelector('[data-audience-checkbox]');
                    }

                    function visibleRows() {
                        return rows.filter(function (row) {
                            return !row.classList.contains('d-none');
                        });
                    }

                    function selectedRows() {
                        return rows.filter(function (row) {
                            var checkbox = rowCheckbox(row);
                            return checkbox && checkbox.checked;
                        });
                    }

                    function updateSectionCounts() {
                        sectionCounts.forEach(function (countNode) {
                            var category = countNode.getAttribute('data-audience-section-count');
                            var count = rows.filter(function (row) {
                                var checkbox = rowCheckbox(row);
                                return checkbox && checkbox.checked && row.getAttribute('data-audience-category') === category;
                            }).length;

                            countNode.textContent = count === 0 ? '0 selected' : count + ' selected';
                        });
                    }

                    function renderSummary() {
                        var selections = selectedRows();

                        if (summaryCopy) {
                            summaryCopy.textContent = selections.length === 0
                                ? 'No recipients selected yet. Choose recipients by type, then search and tick who should receive this campaign.'
                                : selections.length + ' recipient(s) selected and ready for the audience snapshot.';
                        }

                        if (summaryPills) {
                            var counts = {
                                user: 0,
                                lead: 0,
                                customer: 0,
                                contact: 0
                            };

                            selections.forEach(function (row) {
                                counts[row.getAttribute('data-audience-category')] += 1;
                            });

                            summaryPills.innerHTML = '';

                            [
                                ['user', 'Users'],
                                ['lead', 'Leads'],
                                ['customer', 'Customers'],
                                ['contact', 'Contacts']
                            ].forEach(function (pair) {
                                if (!counts[pair[0]]) {
                                    return;
                                }

                                var pill = document.createElement('span');
                                pill.className = 'crm-discussion-recipient-pill';
                                pill.textContent = pair[1] + ' · ' + counts[pair[0]];
                                summaryPills.appendChild(pill);
                            });
                        }

                        if (selectedTags) {
                            selectedTags.innerHTML = '';

                            if (selections.length === 0) {
                                return;
                            }

                            selections.slice(0, 12).forEach(function (row) {
                                var checkbox = rowCheckbox(row);
                                if (!checkbox) {
                                    return;
                                }

                                var tag = document.createElement('button');
                                tag.type = 'button';
                                tag.className = 'crm-audience-selected-tag';
                                tag.setAttribute('data-audience-remove', checkbox.name + ':' + checkbox.value);

                                var label = document.createElement('span');
                                label.textContent = row.getAttribute('data-audience-label') || 'Recipient';
                                tag.appendChild(label);

                                var icon = document.createElement('i');
                                icon.className = 'bx bx-x';
                                icon.setAttribute('aria-hidden', 'true');
                                tag.appendChild(icon);

                                selectedTags.appendChild(tag);
                            });

                            if (selections.length > 12) {
                                var more = document.createElement('span');
                                more.className = 'crm-pill muted';
                                more.textContent = '+' + (selections.length - 12) + ' more';
                                selectedTags.appendChild(more);
                            }
                        }

                        updateSectionCounts();
                    }

                    function syncRows() {
                        var query = searchInput ? searchInput.value.trim().toLowerCase() : '';
                        var visibleCount = 0;

                        rows.forEach(function (row) {
                            var category = row.getAttribute('data-audience-category') || '';
                            var searchText = row.getAttribute('data-audience-search') || '';
                            var matchesFilter = activeFilter === 'all' || activeFilter === category;
                            var matchesSearch = query === '' || searchText.indexOf(query) !== -1;
                            var shouldShow = matchesFilter && matchesSearch;

                            row.classList.toggle('d-none', !shouldShow);
                            visibleCount += shouldShow ? 1 : 0;
                        });

                        Array.prototype.slice.call(modalElement.querySelectorAll('[data-audience-section]')).forEach(function (section) {
                            var category = section.getAttribute('data-audience-section');
                            var hasVisibleRows = rows.some(function (row) {
                                return !row.classList.contains('d-none') && row.getAttribute('data-audience-category') === category;
                            });

                            section.classList.toggle('d-none', !hasVisibleRows);
                        });

                        if (emptyState) {
                            emptyState.classList.toggle('d-none', visibleCount !== 0);
                        }
                    }

                    function setFilter(nextFilter) {
                        activeFilter = nextFilter;

                        filterButtons.forEach(function (button) {
                            button.classList.toggle('is-active', button.getAttribute('data-audience-filter') === activeFilter);
                        });

                        syncRows();
                    }

                    if (openButton && modal) {
                        openButton.addEventListener('click', function () {
                            modal.show();
                        });
                    }

                    if (searchInput) {
                        searchInput.addEventListener('keydown', function (event) {
                            if (event.key === 'Enter') {
                                event.preventDefault();
                            }
                        });

                        searchInput.addEventListener('input', syncRows);
                    }

                    filterButtons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            setFilter(button.getAttribute('data-audience-filter') || 'all');
                        });
                    });

                    rows.forEach(function (row) {
                        var checkbox = rowCheckbox(row);

                        if (!checkbox) {
                            return;
                        }

                        checkbox.addEventListener('change', renderSummary);
                    });

                    if (selectedTags) {
                        selectedTags.addEventListener('click', function (event) {
                            var removeButton = event.target.closest('[data-audience-remove]');

                            if (!removeButton) {
                                return;
                            }

                            event.preventDefault();

                            var key = removeButton.getAttribute('data-audience-remove') || '';

                            rows.forEach(function (row) {
                                var checkbox = rowCheckbox(row);

                                if (!checkbox) {
                                    return;
                                }

                                if ((checkbox.name + ':' + checkbox.value) === key) {
                                    checkbox.checked = false;
                                }
                            });

                            renderSummary();
                            syncRows();
                        });
                    }

                    if (selectVisibleButton) {
                        selectVisibleButton.addEventListener('click', function () {
                            visibleRows().forEach(function (row) {
                                var checkbox = rowCheckbox(row);
                                if (checkbox) {
                                    checkbox.checked = true;
                                }
                            });

                            renderSummary();
                        });
                    }

                    if (clearVisibleButton) {
                        clearVisibleButton.addEventListener('click', function () {
                            visibleRows().forEach(function (row) {
                                var checkbox = rowCheckbox(row);
                                if (checkbox) {
                                    checkbox.checked = false;
                                }
                            });

                            renderSummary();
                        });
                    }

                    if (clearAllButton) {
                        clearAllButton.addEventListener('click', function () {
                            rows.forEach(function (row) {
                                var checkbox = rowCheckbox(row);
                                if (checkbox) {
                                    checkbox.checked = false;
                                }
                            });

                            renderSummary();
                            syncRows();
                        });
                    }

                    renderSummary();
                    syncRows();
                });
            });
        </script>
    @endpush
@endonce
