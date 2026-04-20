{{-- Catalog Options Tab Content --}}
@php $currency = $settings['library_currency']['code'] ?? 'BWP'; @endphp
<div class="help-text">
    <div class="help-title">Catalog Options</div>
    <div class="help-content">
        Configure the dropdown options available when adding or editing books. Item types can also have their own borrowing rules that override the global defaults.
    </div>
</div>

<form id="catalogOptionsForm">
    @csrf
    {{-- Sentinel inputs ensure these keys are always present in the form data --}}
    <input type="hidden" name="catalog_locations" value="">
    <input type="hidden" name="catalog_categories" value="">
    <input type="hidden" name="catalog_reading_levels" value="">
    <div class="row">
        <div class="col-lg-10">
            {{-- Locations --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-map-marker-alt me-2"></i>Locations</h6>
                <div class="form-hint mb-3">Shelf or area locations for organizing books (e.g., Shelf A1, Reference Section)</div>

                <div class="chip-container" id="locationsContainer">
                    @foreach ($settings['catalog_locations'] ?? [] as $location)
                        <span class="chip">
                            {{ $location }}
                            <input type="hidden" name="catalog_locations[]" value="{{ $location }}">
                            <button type="button" class="chip-remove" onclick="this.parentElement.remove()">&times;</button>
                        </span>
                    @endforeach
                </div>
                <div class="chip-add-row mt-2">
                    <input type="text" class="form-control form-control-sm" id="newLocationInput" placeholder="Add a location..." maxlength="100">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addChip('locationsContainer', 'newLocationInput', 'catalog_locations[]')">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>

            {{-- Categories --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-folder me-2"></i>Categories</h6>
                <div class="form-hint mb-3">Book categories used in the catalog (e.g., Fiction, Non-Fiction, Reference)</div>

                <div class="chip-container" id="categoriesContainer">
                    @foreach ($settings['catalog_categories'] ?? [] as $category)
                        <span class="chip">
                            {{ $category }}
                            <input type="hidden" name="catalog_categories[]" value="{{ $category }}">
                            <button type="button" class="chip-remove" onclick="this.parentElement.remove()">&times;</button>
                        </span>
                    @endforeach
                </div>
                <div class="chip-add-row mt-2">
                    <input type="text" class="form-control form-control-sm" id="newCategoryInput" placeholder="Add a category..." maxlength="100">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addChip('categoriesContainer', 'newCategoryInput', 'catalog_categories[]')">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>

            {{-- Reading Levels --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-signal me-2"></i>Reading Levels</h6>
                <div class="form-hint mb-3">Reading proficiency levels (e.g., Beginner, Intermediate, Advanced)</div>

                <div class="chip-container" id="readingLevelsContainer">
                    @foreach ($settings['catalog_reading_levels'] ?? [] as $level)
                        <span class="chip">
                            {{ $level }}
                            <input type="hidden" name="catalog_reading_levels[]" value="{{ $level }}">
                            <button type="button" class="chip-remove" onclick="this.parentElement.remove()">&times;</button>
                        </span>
                    @endforeach
                </div>
                <div class="chip-add-row mt-2">
                    <input type="text" class="form-control form-control-sm" id="newReadingLevelInput" placeholder="Add a reading level..." maxlength="100">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addChip('readingLevelsContainer', 'newReadingLevelInput', 'catalog_reading_levels[]')">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>

            {{-- Item Types with Rules --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-cubes me-2"></i>Item Types</h6>
                <div class="form-hint mb-3">
                    Define item types (e.g., Book, Map, Periodical) with optional per-type borrowing rules.
                    Leave loan/fine fields empty to use the global defaults from the Borrowing Rules and Fines tabs.
                </div>

                <div id="itemTypesContainer">
                    @foreach ($settings['catalog_item_types'] ?? [] as $index => $itemType)
                        <div class="item-type-card" data-index="{{ $index }}">
                            <div class="item-type-header">
                                <div class="item-type-name-group">
                                    <input type="text"
                                        class="form-control form-control-sm"
                                        name="item_types[{{ $index }}][name]"
                                        value="{{ $itemType['name'] ?? '' }}"
                                        placeholder="Item type name"
                                        required
                                        maxlength="100">
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItemType(this)">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="item-type-rules">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label form-label-sm">Loan Period (days)</label>
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <input type="number" class="form-control form-control-sm"
                                                    name="item_types[{{ $index }}][loan_period_student]"
                                                    value="{{ $itemType['loan_period_student'] ?? '' }}"
                                                    placeholder="Student" min="1" max="365">
                                            </div>
                                            <div class="col-6">
                                                <input type="number" class="form-control form-control-sm"
                                                    name="item_types[{{ $index }}][loan_period_staff]"
                                                    value="{{ $itemType['loan_period_staff'] ?? '' }}"
                                                    placeholder="Staff" min="1" max="365">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label form-label-sm">Fine Rate (<span class="currency-label">{{ $currency }}</span>/day)</label>
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <input type="number" class="form-control form-control-sm"
                                                    name="item_types[{{ $index }}][fine_rate_student]"
                                                    value="{{ $itemType['fine_rate_student'] ?? '' }}"
                                                    placeholder="Student" min="0" max="100" step="0.01">
                                            </div>
                                            <div class="col-6">
                                                <input type="number" class="form-control form-control-sm"
                                                    name="item_types[{{ $index }}][fine_rate_staff]"
                                                    value="{{ $itemType['fine_rate_staff'] ?? '' }}"
                                                    placeholder="Staff" min="0" max="100" step="0.01">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label form-label-sm">Max Renewals</label>
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <input type="number" class="form-control form-control-sm"
                                                    name="item_types[{{ $index }}][max_renewals_student]"
                                                    value="{{ $itemType['max_renewals_student'] ?? '' }}"
                                                    placeholder="Student" min="0" max="10">
                                            </div>
                                            <div class="col-6">
                                                <input type="number" class="form-control form-control-sm"
                                                    name="item_types[{{ $index }}][max_renewals_staff]"
                                                    value="{{ $itemType['max_renewals_staff'] ?? '' }}"
                                                    placeholder="Staff" min="0" max="10">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addItemType()">
                    <i class="fas fa-plus me-1"></i> Add Item Type
                </button>
            </div>

            {{-- Form Actions --}}
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Save Catalog Options</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </div>
    </div>
</form>