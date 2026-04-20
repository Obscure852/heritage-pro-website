{{-- API Keys Tab Content --}}
<div class="help-text">
    <div class="help-title">ISBN Lookup API</div>
    <div class="help-content">
        Configure API keys for automatic book metadata lookup when adding new books to the catalog.
        The system will try ISBNdb first (requires paid API key), then fall back to Open Library (free, no key required).
    </div>
</div>

<form id="apiKeysForm">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            {{-- ISBNdb API Key Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-database me-2"></i>ISBNdb API Key</h6>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label" for="isbndb_api_key">API Key</label>
                        <input type="text"
                            class="form-control"
                            id="isbndb_api_key"
                            name="isbndb_api_key"
                            value="{{ $settings['isbndb_api_key']['key'] ?? '' }}"
                            placeholder="Enter your ISBNdb API key">
                        <div class="form-hint">
                            Get a key at <a href="https://isbndb.com/isbn-database" target="_blank" rel="noopener">isbndb.com</a>.
                            If no key is configured, the system will use the free Open Library API as fallback.
                        </div>
                    </div>
                </div>
            </div>

            {{-- New Arrivals Period Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-star me-2"></i>New Arrivals</h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="new_arrivals_period">Display Period (days)</label>
                        <input type="number"
                            class="form-control"
                            id="new_arrivals_period"
                            name="new_arrivals_period"
                            value="{{ $settings['new_arrivals_period']['days'] ?? 30 }}"
                            min="1"
                            max="365">
                        <div class="form-hint">Books added within this many days are shown as "New Arrivals" on the catalog page</div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Save API Settings</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </div>
    </div>
</form>
