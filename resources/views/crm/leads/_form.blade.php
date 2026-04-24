@php
    $selectedCountry = old('country', $lead->country ?? 'Botswana');
    $countryNames = collect($countries ?? [])->pluck('name')->all();
    $sectorNames = collect($sectors ?? [])->pluck('name')->all();
    $selectedSector = old('industry', $lead->industry ?? ($sectorNames[0] ?? null));
@endphp

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
            <select id="industry" name="industry">
                <option value="">Select a sector</option>
                @if ($selectedSector && ! in_array($selectedSector, $sectorNames, true))
                    <option value="{{ $selectedSector }}" selected>{{ $selectedSector }}</option>
                @endif
                @foreach ($sectors ?? [] as $sector)
                    <option value="{{ $sector->name }}" @selected($selectedSector === $sector->name)>{{ $sector->name }}</option>
                @endforeach
            </select>
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
            <label for="fax">Fax</label>
            <input id="fax" name="fax" value="{{ old('fax', $lead->fax ?? '') }}" placeholder="Enter fax number">
        </div>
        <div class="crm-field">
            <label for="country">Country</label>
            <select id="country" name="country">
                <option value="">Select a country</option>
                @if ($selectedCountry && ! in_array($selectedCountry, $countryNames, true))
                    <option value="{{ $selectedCountry }}" selected>{{ $selectedCountry }}</option>
                @endif
                @foreach ($countries ?? [] as $country)
                    <option value="{{ $country['name'] }}" @selected($selectedCountry === $country['name'])>{{ $country['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="region">Region</label>
            <input id="region" name="region" value="{{ old('region', $lead->region ?? '') }}" placeholder="Enter region">
        </div>
        <div class="crm-field">
            <label for="location">Location</label>
            <input id="location" name="location" value="{{ old('location', $lead->location ?? '') }}" placeholder="Enter location">
        </div>
        <div class="crm-field">
            <label for="postal_address">P.O. Box address</label>
            <input id="postal_address" name="postal_address" value="{{ old('postal_address', $lead->postal_address ?? '') }}" placeholder="Enter postal address">
        </div>
        <div class="crm-field">
            <label for="owner_id">Owner</label>
            <select id="owner_id" name="owner_id">
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((int) old('owner_id', $lead->owner_id ?? auth()->id()) === $owner->id)>{{ $owner->name }}</option>
                @endforeach
            </select>
        </div>
        @if (($lead->converted_at ?? null) !== null || ($lead->status ?? null) === 'converted')
            <div class="crm-field">
                <label for="lead_status_locked">Status</label>
                <input id="lead_status_locked" value="Converted" placeholder="Converted" disabled>
                <input type="hidden" name="status" value="converted">
            </div>
        @else
            <div class="crm-field">
                <label for="lead_status">Status</label>
                <select id="lead_status" name="status">
                    @foreach ($leadStatuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $lead->status ?? 'active') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif
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
