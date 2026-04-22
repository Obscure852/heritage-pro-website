@php
    $discussionCampaign = $discussionCampaign ?? null;
    $snapshot = $discussionCampaign?->audience_snapshot ?? [];
    $requested = $snapshot['requested'] ?? [];
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

        <div class="crm-field full">
            <label for="body">Message body</label>
            <textarea id="body" name="body" placeholder="Write the bulk message body" required>{{ old('body', $discussionCampaign?->body ?? ($sourceContext['body'] ?? '')) }}</textarea>
        </div>

        <div class="crm-field full">
            <label for="notes">Internal notes</label>
            <textarea id="notes" name="notes" placeholder="Add internal notes for this campaign">{{ old('notes', $discussionCampaign?->notes) }}</textarea>
        </div>

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

        <div class="crm-field full">
            <label for="recipient_user_ids">CRM users</label>
            <select id="recipient_user_ids" name="recipient_user_ids[]" multiple size="5">
                @php($selectedUsers = old('recipient_user_ids', $requested['recipient_user_ids'] ?? []))
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(in_array($user->id, array_map('intval', $selectedUsers), true))>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="crm-field full">
            <label for="lead_ids">Leads</label>
            <select id="lead_ids" name="lead_ids[]" multiple size="5">
                @php($selectedLeads = old('lead_ids', $requested['lead_ids'] ?? []))
                @foreach ($leads as $lead)
                    <option value="{{ $lead->id }}" @selected(in_array($lead->id, array_map('intval', $selectedLeads), true))>
                        {{ $lead->company_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="crm-field full">
            <label for="customer_ids">Customers</label>
            <select id="customer_ids" name="customer_ids[]" multiple size="5">
                @php($selectedCustomers = old('customer_ids', $requested['customer_ids'] ?? []))
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(in_array($customer->id, array_map('intval', $selectedCustomers), true))>
                        {{ $customer->company_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="crm-field full">
            <label for="contact_ids">Contacts</label>
            <select id="contact_ids" name="contact_ids[]" multiple size="5">
                @php($selectedContacts = old('contact_ids', $requested['contact_ids'] ?? []))
                @foreach ($contacts as $contact)
                    <option value="{{ $contact->id }}" @selected(in_array($contact->id, array_map('intval', $selectedContacts), true))>
                        {{ $contact->name }}
                    </option>
                @endforeach
            </select>
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
</form>
