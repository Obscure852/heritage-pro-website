<form method="POST" action="{{ route('crm.settings.company-information.update') }}" class="crm-form crm-settings-form-shell">
    @csrf
    @method('PATCH')

    <p class="crm-settings-form-copy">Maintain the core company details used across CRM branding, login screens, and shared reference points.</p>

    <div class="crm-field-grid">
        <div class="crm-field">
            <label for="company_name">Company name <span class="text-danger">*</span></label>
            <input id="company_name" name="company_name" value="{{ old('company_name', $settings?->company_name ?? '') }}" required>
        </div>
        <div class="crm-field">
            <label for="company_email">Company email</label>
            <input id="company_email" name="company_email" type="email" value="{{ old('company_email', $settings?->company_email ?? '') }}">
        </div>
        <div class="crm-field">
            <label for="company_phone">Company phone</label>
            <input id="company_phone" name="company_phone" value="{{ old('company_phone', $settings?->company_phone ?? '') }}">
        </div>
        <div class="crm-field">
            <label for="company_website">Website</label>
            <input id="company_website" name="company_website" type="url" value="{{ old('company_website', $settings?->company_website ?? '') }}">
        </div>
        <div class="crm-field full">
            <label for="company_address_line_1">Address line 1</label>
            <input id="company_address_line_1" name="company_address_line_1" value="{{ old('company_address_line_1', $settings?->company_address_line_1 ?? '') }}">
        </div>
        <div class="crm-field full">
            <label for="company_address_line_2">Address line 2</label>
            <input id="company_address_line_2" name="company_address_line_2" value="{{ old('company_address_line_2', $settings?->company_address_line_2 ?? '') }}">
        </div>
        <div class="crm-field">
            <label for="company_city">City / town</label>
            <input id="company_city" name="company_city" value="{{ old('company_city', $settings?->company_city ?? '') }}">
        </div>
        <div class="crm-field">
            <label for="company_state">State / province</label>
            <input id="company_state" name="company_state" value="{{ old('company_state', $settings?->company_state ?? '') }}">
        </div>
        <div class="crm-field">
            <label for="company_country">Country</label>
            <input id="company_country" name="company_country" value="{{ old('company_country', $settings?->company_country ?? '') }}">
        </div>
        <div class="crm-field">
            <label for="company_postal_code">Postal code</label>
            <input id="company_postal_code" name="company_postal_code" value="{{ old('company_postal_code', $settings?->company_postal_code ?? '') }}">
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="fas fa-save"></i> Save company information</span>
            <span class="btn-spinner d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...
            </span>
        </button>
    </div>
</form>
