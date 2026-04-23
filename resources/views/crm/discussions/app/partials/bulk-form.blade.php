@php
    $discussionCampaign = $discussionCampaign ?? null;
    $requestedRecipients = collect(old('recipient_user_ids', $discussionCampaign?->audience_snapshot['requested']['recipient_user_ids'] ?? []))
        ->map(fn ($value) => (int) $value)
        ->filter()
        ->unique()
        ->values()
        ->all();
    $requestedDepartments = collect(old('department_ids', $discussionCampaign?->audience_snapshot['requested']['department_ids'] ?? []))
        ->map(fn ($value) => (int) $value)
        ->filter()
        ->unique()
        ->values()
        ->all();
    $recipientError = $errors->first('recipient_user_ids') ?: $errors->first('recipient_user_ids.*');
@endphp

<form method="POST" action="{{ $action }}" class="crm-form" enctype="multipart/form-data">
    @csrf
    @if (! empty($method))
        @method($method)
    @endif

    @if (! empty($sourceContext))
        <input type="hidden" name="source_type" value="{{ $sourceContext['type'] }}">
        <input type="hidden" name="source_id" value="{{ $sourceContext['id'] }}">
        @include('crm.partials.helper-text', [
            'title' => 'Commercial Source',
            'content' => 'This group chat is linked to ' . $sourceContext['title'] . '. The latest private PDF is attached automatically when the opening message is sent.',
        ])
    @endif

    <div class="crm-discussion-form-note">
        Group chat attachments upload when you send the opening message. Saving a draft stores the member selection and message copy, but not temporary files.
    </div>

    <div class="crm-field-grid">
        <div class="crm-field full">
            <label for="subject">Group chat name</label>
            <input id="subject" name="subject" value="{{ old('subject', $discussionCampaign?->subject) }}" placeholder="Operations rollout, Finance approvals, Admissions sprint..." required>
        </div>

        <div class="crm-field full">
            <label for="body">Opening message</label>
            <textarea id="body" name="body" placeholder="Write the first message for the new group chat" required>{{ old('body', $discussionCampaign?->body) }}</textarea>
        </div>

        <div class="crm-field full">
            <label for="notes">Internal notes</label>
            <textarea id="notes" name="notes" placeholder="Add private setup notes for this group chat draft">{{ old('notes', $discussionCampaign?->notes) }}</textarea>
        </div>

        <div class="crm-field full">
            <label for="recipient_user_lookup">Custom users</label>
            <div class="crm-app-user-picker {{ $recipientError ? 'is-invalid' : '' }}" data-app-user-picker>
                <div class="crm-app-user-picker-control" data-app-user-picker-control>
                    <div class="crm-app-user-picker-tags" data-app-user-picker-tags></div>
                    <input
                        id="recipient_user_lookup"
                        type="text"
                        class="crm-app-user-picker-input"
                        placeholder="Search internal users by name or email"
                        autocomplete="off"
                        data-app-user-picker-input
                    >
                </div>

                <div class="crm-app-user-picker-dropdown" data-app-user-picker-dropdown hidden>
                    @foreach ($crmUsers as $crmUser)
                        @continue((int) $crmUser->id === (int) auth()->id())
                        @php($userMeta = collect([$crmUser->email, ucfirst($crmUser->role ?? '')])->filter()->join(' · '))
                        <button
                            type="button"
                            class="crm-app-user-picker-option"
                            data-app-user-picker-option
                            data-user-id="{{ $crmUser->id }}"
                            data-user-label="{{ $crmUser->name }}"
                            data-user-meta="{{ $userMeta }}"
                        >
                            <span class="crm-app-user-picker-option-name">{{ $crmUser->name }}</span>
                            <span class="crm-app-user-picker-option-meta">{{ $userMeta }}</span>
                        </button>
                    @endforeach
                    <div class="crm-app-user-picker-empty" data-app-user-picker-empty hidden>No matching internal users found.</div>
                </div>

                <select id="recipient_user_ids" name="recipient_user_ids[]" multiple hidden data-app-user-picker-select>
                    @foreach ($crmUsers as $crmUser)
                        @continue((int) $crmUser->id === (int) auth()->id())
                        @php($userMeta = collect([$crmUser->email, ucfirst($crmUser->role ?? '')])->filter()->join(' · '))
                        <option
                            value="{{ $crmUser->id }}"
                            data-user-label="{{ $crmUser->name }}"
                            data-user-meta="{{ $userMeta }}"
                            @selected(in_array((int) $crmUser->id, $requestedRecipients, true))
                        >
                            {{ $crmUser->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if ($recipientError)
                <div class="invalid-feedback d-block">{{ $recipientError }}</div>
            @endif
            <small class="crm-muted">Pick individual team members to add alongside any departments. Your own user is included automatically.</small>
        </div>

        <div class="crm-field full">
            <label for="department_ids">Departments</label>
            <select id="department_ids" name="department_ids[]" multiple size="6">
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected(in_array((int) $department->id, array_map('intval', $requestedDepartments), true))>
                        {{ $department->name }}{{ $department->users_count ? ' · ' . $department->users_count . ' users' : '' }}
                    </option>
                @endforeach
            </select>
            <small class="crm-muted">Department members are merged with custom users and duplicates are removed automatically.</small>
        </div>

        @include('crm.discussions.partials.attachment-dropzone', [
            'inputId' => 'bulk-attachments',
            'title' => 'Attachments',
            'hint' => 'Attach files that should appear in the opening message when the group chat is created.',
        ])
    </div>

    <div class="form-actions">
        <a href="{{ $cancelUrl }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Cancel</a>
        <button type="submit" name="intent" value="draft" class="btn btn-light crm-btn-light">
            <i class="bx bx-save"></i> Save draft
        </button>
        <button type="submit" name="intent" value="send" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="bx bx-group"></i> Create group chat</span>
            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creating...</span>
        </button>
    </div>
</form>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-app-user-picker]').forEach(function (picker) {
                    if (picker.dataset.ready === 'true') {
                        return;
                    }

                    picker.dataset.ready = 'true';

                    var control = picker.querySelector('[data-app-user-picker-control]');
                    var tags = picker.querySelector('[data-app-user-picker-tags]');
                    var input = picker.querySelector('[data-app-user-picker-input]');
                    var dropdown = picker.querySelector('[data-app-user-picker-dropdown]');
                    var emptyState = picker.querySelector('[data-app-user-picker-empty]');
                    var select = picker.querySelector('[data-app-user-picker-select]');
                    var options = Array.prototype.slice.call(picker.querySelectorAll('[data-app-user-picker-option]'));

                    if (!control || !tags || !input || !dropdown || !select) {
                        return;
                    }

                    function selectedOptionIds() {
                        return Array.prototype.slice.call(select.options)
                            .filter(function (option) {
                                return option.selected;
                            })
                            .map(function (option) {
                                return String(option.value);
                            });
                    }

                    function findSelectOption(userId) {
                        return Array.prototype.find.call(select.options, function (option) {
                            return String(option.value) === String(userId);
                        });
                    }

                    function setSelected(userId, shouldSelect) {
                        var option = findSelectOption(userId);

                        if (!option) {
                            return;
                        }

                        option.selected = shouldSelect;
                    }

                    function removeLastTag() {
                        var ids = selectedOptionIds();
                        var lastId = ids[ids.length - 1];

                        if (!lastId) {
                            return;
                        }

                        setSelected(lastId, false);
                        renderTags();
                        filterOptions();
                    }

                    function renderTags() {
                        tags.innerHTML = '';

                        selectedOptionIds().forEach(function (userId) {
                            var option = findSelectOption(userId);

                            if (!option) {
                                return;
                            }

                            var tag = document.createElement('button');
                            tag.type = 'button';
                            tag.className = 'crm-app-user-tag';
                            tag.setAttribute('data-user-tag-id', userId);
                            tag.setAttribute('aria-label', 'Remove ' + (option.dataset.userLabel || option.textContent.trim()));

                            var label = document.createElement('span');
                            label.className = 'crm-app-user-tag-label';
                            label.textContent = option.dataset.userLabel || option.textContent.trim();
                            tag.appendChild(label);

                            if (option.dataset.userMeta) {
                                var meta = document.createElement('span');
                                meta.className = 'crm-app-user-tag-meta';
                                meta.textContent = option.dataset.userMeta;
                                tag.appendChild(meta);
                            }

                            var icon = document.createElement('i');
                            icon.className = 'bx bx-x';
                            icon.setAttribute('aria-hidden', 'true');
                            tag.appendChild(icon);

                            tags.appendChild(tag);
                        });
                    }

                    function filterOptions() {
                        var query = input.value.trim().toLowerCase();
                        var selectedIds = selectedOptionIds();
                        var visibleCount = 0;

                        options.forEach(function (optionButton) {
                            var userId = String(optionButton.dataset.userId || '');
                            var searchText = ((optionButton.dataset.userLabel || '') + ' ' + (optionButton.dataset.userMeta || '')).toLowerCase();
                            var isVisible = selectedIds.indexOf(userId) === -1 && (query === '' || searchText.indexOf(query) !== -1);

                            optionButton.hidden = !isVisible;

                            if (isVisible) {
                                visibleCount += 1;
                            }
                        });

                        if (emptyState) {
                            emptyState.hidden = visibleCount !== 0;
                        }

                        dropdown.hidden = visibleCount === 0 && query === '';
                    }

                    function openDropdown() {
                        filterOptions();

                        if (!dropdown.hidden || (emptyState && !emptyState.hidden)) {
                            dropdown.hidden = false;
                        }
                    }

                    function closeDropdown() {
                        dropdown.hidden = true;
                    }

                    function firstVisibleOption() {
                        return options.find(function (optionButton) {
                            return !optionButton.hidden;
                        }) || null;
                    }

                    control.addEventListener('click', function () {
                        input.focus();
                        openDropdown();
                    });

                    input.addEventListener('focus', function () {
                        openDropdown();
                    });

                    input.addEventListener('input', function () {
                        openDropdown();
                    });

                    input.addEventListener('keydown', function (event) {
                        if (event.key === 'Backspace' && input.value === '') {
                            removeLastTag();
                            return;
                        }

                        if (event.key === 'Escape') {
                            closeDropdown();
                            return;
                        }

                        if (event.key !== 'Enter') {
                            return;
                        }

                        var optionButton = firstVisibleOption();

                        if (!optionButton) {
                            return;
                        }

                        event.preventDefault();
                        setSelected(optionButton.dataset.userId, true);
                        input.value = '';
                        renderTags();
                        openDropdown();
                    });

                    dropdown.addEventListener('click', function (event) {
                        var optionButton = event.target.closest('[data-app-user-picker-option]');

                        if (!optionButton) {
                            return;
                        }

                        event.preventDefault();
                        setSelected(optionButton.dataset.userId, true);
                        input.value = '';
                        renderTags();
                        openDropdown();
                        input.focus();
                    });

                    tags.addEventListener('click', function (event) {
                        var tag = event.target.closest('[data-user-tag-id]');

                        if (!tag) {
                            return;
                        }

                        event.preventDefault();
                        setSelected(tag.getAttribute('data-user-tag-id'), false);
                        renderTags();
                        openDropdown();
                        input.focus();
                    });

                    document.addEventListener('click', function (event) {
                        if (!picker.contains(event.target)) {
                            closeDropdown();
                        }
                    });

                    renderTags();
                    filterOptions();
                });
            });
        </script>
    @endpush
@endonce
