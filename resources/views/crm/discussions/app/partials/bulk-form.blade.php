@php
    $discussionCampaign = $discussionCampaign ?? null;
    $requestedRecipients = old('recipient_user_ids', $discussionCampaign?->audience_snapshot['requested']['recipient_user_ids'] ?? []);
    $requestedDepartments = old('department_ids', $discussionCampaign?->audience_snapshot['requested']['department_ids'] ?? []);
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
            <label for="recipient_user_ids">Custom contacts</label>
            <select id="recipient_user_ids" name="recipient_user_ids[]" multiple size="8">
                @foreach ($crmUsers as $crmUser)
                    @continue((int) $crmUser->id === (int) auth()->id())
                    <option value="{{ $crmUser->id }}" @selected(in_array((int) $crmUser->id, array_map('intval', $requestedRecipients), true))>
                        {{ $crmUser->name }}{{ $crmUser->email ? ' · ' . $crmUser->email : '' }}
                    </option>
                @endforeach
            </select>
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
            <small class="crm-muted">Department members are merged with custom contacts and duplicates are removed automatically.</small>
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
