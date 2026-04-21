<form method="POST" action="{{ $action }}" class="crm-form">
    @csrf
    @if (! empty($method))
        @method($method)
    @endif

    <div class="crm-field-grid">
        <div class="crm-field">
            <label for="name">Name</label>
            <input id="name" name="name" value="{{ old('name', $integration->name ?? '') }}" placeholder="Enter integration name" required>
        </div>
        <div class="crm-field">
            <label for="owner_id">Owner</label>
            <select id="owner_id" name="owner_id">
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((int) old('owner_id', $integration->owner_id ?? auth()->id()) === $owner->id)>{{ $owner->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="kind">Kind</label>
            <select id="kind" name="kind">
                @foreach ($integrationKinds as $value => $label)
                    <option value="{{ $value }}" @selected(old('kind', $integration->kind ?? 'school_api') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="integration_status">Status</label>
            <select id="integration_status" name="status">
                @foreach ($integrationStatuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $integration->status ?? 'inactive') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="school_code">School code</label>
            <input id="school_code" name="school_code" value="{{ old('school_code', $integration->school_code ?? '') }}" placeholder="Enter school code">
        </div>
        <div class="crm-field">
            <label for="auth_type">Auth type</label>
            <input id="auth_type" name="auth_type" value="{{ old('auth_type', $integration->auth_type ?? '') }}" placeholder="Enter auth type">
        </div>
        <div class="crm-field full">
            <label for="base_url">Base URL</label>
            <input id="base_url" name="base_url" type="url" value="{{ old('base_url', $integration->base_url ?? '') }}" placeholder="https://api.school-domain.com">
        </div>
        <div class="crm-field full">
            <label for="webhook_url">Webhook URL</label>
            <input id="webhook_url" name="webhook_url" type="url" value="{{ old('webhook_url', $integration->webhook_url ?? '') }}" placeholder="https://api.school-domain.com/webhooks/heritage">
        </div>
        <div class="crm-field full">
            <label for="api_key">API key / token</label>
            <textarea id="api_key" name="api_key" placeholder="Paste the API key or token">{{ old('api_key', $integration->api_key ?? '') }}</textarea>
        </div>
        <div class="crm-field">
            <label for="last_synced_at">Last synced at</label>
            <input id="last_synced_at" name="last_synced_at" type="datetime-local" value="{{ old('last_synced_at', isset($integration) && $integration->last_synced_at ? $integration->last_synced_at->format('Y-m-d\TH:i') : '') }}" placeholder="Select last sync date and time">
        </div>
        <div class="crm-field full">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" placeholder="Add integration notes, setup steps, or support context">{{ old('notes', $integration->notes ?? '') }}</textarea>
        </div>
    </div>

    <div class="form-actions">
        @if (! empty($deleteUrl))
            @include('crm.partials.delete-button', [
                'action' => $deleteUrl,
                'message' => $deleteMessage ?? 'Are you sure you want to permanently delete this integration?',
                'label' => $deleteLabel ?? 'Delete integration',
            ])
        @endif
        @if (! empty($cancelUrl))
            <a href="{{ $cancelUrl }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Cancel</a>
        @endif
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="{{ $submitIcon ?? 'fas fa-save' }}"></i> {{ $submitLabel }}</span>
            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
        </button>
    </div>
</form>
