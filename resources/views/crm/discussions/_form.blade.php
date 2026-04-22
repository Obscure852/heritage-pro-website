<form method="POST" action="{{ $action }}" class="crm-form">
    @csrf

    <div class="crm-help">In-app messages are sent directly between CRM users. Email threads send immediately when mail is configured. WhatsApp threads are stored with provider-ready status for integration wiring.</div>

    <div class="crm-field-grid">
        <div class="crm-field full">
            <label for="subject">Subject</label>
            <input id="subject" name="subject" value="{{ old('subject', request('subject')) }}" placeholder="Enter discussion subject" required>
        </div>
        <div class="crm-field">
            <label for="channel">Channel</label>
            <select id="channel" name="channel">
                @foreach ($discussionChannels as $value => $label)
                    <option value="{{ $value }}" @selected(old('channel', request('channel', 'app')) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="recipient_user_id">Recipient user</label>
            <select id="recipient_user_id" name="recipient_user_id">
                <option value="">Select a user</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((int) old('recipient_user_id', request('recipient_user_id')) === $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="recipient_email">Recipient email</label>
            <input id="recipient_email" name="recipient_email" type="email" value="{{ old('recipient_email', request('recipient_email')) }}" placeholder="name@company.com">
        </div>
        <div class="crm-field">
            <label for="recipient_phone">Recipient phone</label>
            <input id="recipient_phone" name="recipient_phone" value="{{ old('recipient_phone', request('recipient_phone')) }}" placeholder="Enter recipient phone number">
        </div>
        <div class="crm-field full">
            <label for="integration_id">Integration</label>
            <select id="integration_id" name="integration_id">
                <option value="">Select an integration</option>
                @foreach ($integrations as $integration)
                    <option value="{{ $integration->id }}" @selected((int) old('integration_id', request('integration_id')) === $integration->id)>{{ $integration->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field full">
            <label for="body">Opening message</label>
            <textarea id="body" name="body" placeholder="Write the opening message" required>{{ old('body', request('body')) }}</textarea>
        </div>
        <div class="crm-field full">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" placeholder="Add internal notes for this discussion">{{ old('notes', request('notes')) }}</textarea>
        </div>
    </div>

    <div class="form-actions">
        @if (! empty($cancelUrl))
            <a href="{{ $cancelUrl }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Cancel</a>
        @endif
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="{{ $submitIcon ?? 'bx bx-send' }}"></i> {{ $submitLabel }}</span>
            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
        </button>
    </div>
</form>
