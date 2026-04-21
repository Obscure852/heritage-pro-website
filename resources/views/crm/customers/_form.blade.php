<form method="POST" action="{{ $action }}" class="crm-form">
    @csrf
    @if (! empty($method))
        @method($method)
    @endif

    <div class="crm-field-grid">
        <div class="crm-field">
            <label for="company_name">Institution name</label>
            <input id="company_name" name="company_name" value="{{ old('company_name', $customer->company_name ?? '') }}" placeholder="Enter institution name" required>
        </div>
        <div class="crm-field">
            <label for="industry">Sector</label>
            <input id="industry" name="industry" value="{{ old('industry', $customer->industry ?? 'Education') }}" placeholder="Enter sector, e.g. Education">
        </div>
        <div class="crm-field">
            <label for="website">Website</label>
            <input id="website" name="website" type="url" value="{{ old('website', $customer->website ?? '') }}" placeholder="https://example.com">
        </div>
        <div class="crm-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $customer->email ?? '') }}" placeholder="name@institution.org">
        </div>
        <div class="crm-field">
            <label for="phone">Phone</label>
            <input id="phone" name="phone" value="{{ old('phone', $customer->phone ?? '') }}" placeholder="Enter phone number">
        </div>
        <div class="crm-field">
            <label for="country">Country</label>
            <input id="country" name="country" value="{{ old('country', $customer->country ?? 'Botswana') }}" placeholder="Enter country">
        </div>
        <div class="crm-field">
            <label for="owner_id">Owner</label>
            <select id="owner_id" name="owner_id">
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((int) old('owner_id', $customer->owner_id ?? auth()->id()) === $owner->id)>{{ $owner->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="customer_status">Status</label>
            <select id="customer_status" name="status">
                @foreach ($customerStatuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $customer->status ?? 'active') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="purchased_at">Purchase date</label>
            <input id="purchased_at" name="purchased_at" type="date" value="{{ old('purchased_at', isset($customer) && $customer->purchased_at ? $customer->purchased_at->format('Y-m-d') : '') }}" placeholder="Select purchase date">
        </div>
        <div class="crm-field full">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" placeholder="Add onboarding notes, account context, or customer history">{{ old('notes', $customer->notes ?? '') }}</textarea>
        </div>
    </div>

    <div class="form-actions">
        @if (! empty($deleteUrl))
            @include('crm.partials.delete-button', [
                'action' => $deleteUrl,
                'message' => $deleteMessage ?? 'Are you sure you want to permanently delete this customer?',
                'label' => $deleteLabel ?? 'Delete customer',
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
