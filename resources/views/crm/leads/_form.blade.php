<form method="POST" action="{{ $action }}" class="crm-form">
    @csrf
    @if (! empty($method))
        @method($method)
    @endif

    <div class="crm-field-grid">
        <div class="crm-field">
            <label for="company_name">Institution name</label>
            <input id="company_name" name="company_name" value="{{ old('company_name', $lead->company_name ?? '') }}" placeholder="Enter institution name" required>
        </div>
        <div class="crm-field">
            <label for="industry">Sector</label>
            <input id="industry" name="industry" value="{{ old('industry', $lead->industry ?? 'Education') }}" placeholder="Enter sector, e.g. Education">
        </div>
        <div class="crm-field">
            <label for="website">Website</label>
            <input id="website" name="website" type="url" value="{{ old('website', $lead->website ?? '') }}" placeholder="https://example.com">
        </div>
        <div class="crm-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $lead->email ?? '') }}" placeholder="name@institution.org">
        </div>
        <div class="crm-field">
            <label for="phone">Phone</label>
            <input id="phone" name="phone" value="{{ old('phone', $lead->phone ?? '') }}" placeholder="Enter phone number">
        </div>
        <div class="crm-field">
            <label for="country">Country</label>
            <input id="country" name="country" value="{{ old('country', $lead->country ?? 'Botswana') }}" placeholder="Enter country">
        </div>
        <div class="crm-field">
            <label for="owner_id">Owner</label>
            <select id="owner_id" name="owner_id">
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((int) old('owner_id', $lead->owner_id ?? auth()->id()) === $owner->id)>{{ $owner->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="lead_status">Status</label>
            <select id="lead_status" name="status">
                @foreach ($leadStatuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $lead->status ?? 'active') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field full">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" placeholder="Add lead notes, call context, or qualification details">{{ old('notes', $lead->notes ?? '') }}</textarea>
        </div>
    </div>

    <div class="form-actions">
        @if (! empty($deleteUrl))
            @include('crm.partials.delete-button', [
                'action' => $deleteUrl,
                'message' => $deleteMessage ?? 'Are you sure you want to permanently delete this lead?',
                'label' => $deleteLabel ?? 'Delete lead',
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
