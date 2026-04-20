@extends('layouts.master')
@section('title')
    Add Item
@endsection
@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .required {
            color: #dc2626;
        }

        .text-danger {
            color: #dc2626;
        }

        /* ISBN Lookup Section */
        .isbn-lookup-row {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .isbn-lookup-row .isbn-input-group {
            flex: 1;
        }

        .isbn-lookup-row .btn-info {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            height: 42px;
        }

        .isbn-lookup-row .btn-info:hover {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }

        .isbn-lookup-row .btn-info:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .isbn-result-message {
            margin-top: 12px;
        }

        .isbn-result-message .alert {
            margin-bottom: 0;
            font-size: 13px;
            padding: 10px 14px;
            border-radius: 3px;
        }

        /* Cover Image Preview */
        .cover-preview {
            margin-top: 8px;
        }

        .cover-preview img {
            max-height: 120px;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
            color: white;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .form-hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        /* Spinner for ISBN lookup */
        .lookup-spinner {
            display: none;
        }

        .lookup-spinner.active {
            display: inline-flex;
            align-items: center;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .isbn-lookup-row {
                flex-direction: column;
                align-items: stretch;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('library.dashboard') }}">Library</a>
        @endslot
        @slot('li_2')
            <a class="text-muted font-size-14" href="{{ route('library.catalog.index') }}">Catalog</a>
        @endslot
        @slot('title')
            Add Item
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-md-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="form-container">
        <div class="page-header">
            <h1 class="page-title">Add Item to Catalog</h1>
        </div>

        {{-- ISBN Lookup Section --}}
        <h3 class="section-title">ISBN Lookup</h3>
        <div class="help-text">
            <div class="help-title">Quick Add via ISBN</div>
            <div class="help-content">
                Scan a barcode or enter an ISBN to auto-populate item fields from online databases. You can also enter all fields manually below.
            </div>
        </div>

        <div class="isbn-lookup-row">
            <div class="isbn-input-group">
                <label class="form-label" for="isbn_lookup">ISBN</label>
                <input type="text"
                    class="form-control"
                    id="isbn_lookup"
                    placeholder="Scan barcode or enter ISBN..."
                    maxlength="17"
                    autocomplete="off">
            </div>
            <button type="button" class="btn btn-info" id="isbnLookupBtn">
                <span class="lookup-text"><i class="fas fa-search"></i> Lookup</span>
                <span class="lookup-spinner">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Looking up...
                </span>
            </button>
        </div>
        <div class="isbn-result-message" id="isbnResultMessage"></div>

        {{-- Book Details Form --}}
        <form id="bookForm" method="POST" action="{{ route('library.books.store') }}">
            @csrf

            <h3 class="section-title">Item Details</h3>
            <div class="form-grid">
                {{-- Title --}}
                <div class="form-group">
                    <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control @error('title') is-invalid @enderror"
                        name="title" id="title"
                        value="{{ old('title') }}"
                        placeholder="Item title"
                        required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Authors --}}
                <div class="form-group">
                    <label class="form-label" for="author_select">Author(s)</label>
                    @php $oldAuthors = old('author_names') ? array_map('trim', explode(',', old('author_names'))) : []; @endphp
                    <select id="author_select" multiple>
                        @foreach ($authors as $author)
                            <option value="{{ trim($author->full_name) }}" {{ in_array(trim($author->full_name), $oldAuthors) ? 'selected' : '' }}>{{ trim($author->full_name) }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="author_names" id="author_names" value="{{ old('author_names') }}">
                    @error('author_names')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Publisher --}}
                <div class="form-group">
                    <label class="form-label" for="publisher_name">Publisher</label>
                    <select name="publisher_name" id="publisher_name">
                        <option value="">Select Publisher</option>
                        @foreach ($publishers as $publisher)
                            <option value="{{ $publisher->name }}" {{ old('publisher_name') == $publisher->name ? 'selected' : '' }}>{{ $publisher->name }}</option>
                        @endforeach
                    </select>
                    @error('publisher_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ISBN (hidden carries value, display is disabled) --}}
                <div class="form-group">
                    <label class="form-label" for="isbn_display">ISBN <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control"
                        id="isbn_display"
                        disabled
                        placeholder="Enter ISBN above in lookup section">
                    <input type="hidden" name="isbn" id="isbn" value="{{ old('isbn') }}">
                    @error('isbn')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Publication Year --}}
                <div class="form-group">
                    <label class="form-label" for="publication_year">Publication Year</label>
                    <input type="number"
                        class="form-control @error('publication_year') is-invalid @enderror"
                        name="publication_year" id="publication_year"
                        value="{{ old('publication_year') }}"
                        placeholder="e.g., 2024"
                        min="1000" max="{{ date('Y') + 1 }}">
                    @error('publication_year')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Edition --}}
                <div class="form-group">
                    <label class="form-label" for="edition">Edition</label>
                    <input type="text"
                        class="form-control @error('edition') is-invalid @enderror"
                        name="edition" id="edition"
                        value="{{ old('edition') }}"
                        placeholder="e.g., 3rd Edition">
                    @error('edition')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Category (stored in genre column) --}}
                <div class="form-group">
                    <label class="form-label" for="genre">Category</label>
                    <select class="form-select @error('genre') is-invalid @enderror"
                        name="genre" id="genre">
                        <option value="">Select Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" {{ old('genre') == $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                    @error('genre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Language --}}
                <div class="form-group">
                    <label class="form-label" for="language">Language</label>
                    <input type="text"
                        class="form-control @error('language') is-invalid @enderror"
                        name="language" id="language"
                        value="{{ old('language') }}"
                        placeholder="e.g., English">
                    @error('language')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Item Type (stored in format column) --}}
                <div class="form-group">
                    <label class="form-label" for="format">Item Type</label>
                    <select class="form-select @error('format') is-invalid @enderror"
                        name="format" id="format">
                        <option value="">Select Item Type</option>
                        @foreach ($itemTypeNames as $typeName)
                            <option value="{{ $typeName }}" {{ old('format') == $typeName ? 'selected' : '' }}>{{ $typeName }}</option>
                        @endforeach
                    </select>
                    @error('format')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Pages --}}
                <div class="form-group">
                    <label class="form-label" for="pages">Pages</label>
                    <input type="number"
                        class="form-control @error('pages') is-invalid @enderror"
                        name="pages" id="pages"
                        value="{{ old('pages') }}"
                        placeholder="Number of pages"
                        min="1">
                    @error('pages')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Dewey Decimal --}}
                <div class="form-group">
                    <label class="form-label" for="dewey_decimal">Dewey Decimal</label>
                    <input type="text"
                        class="form-control @error('dewey_decimal') is-invalid @enderror"
                        name="dewey_decimal" id="dewey_decimal"
                        value="{{ old('dewey_decimal') }}"
                        placeholder="e.g., 813.54">
                    @error('dewey_decimal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Reading Level --}}
                <div class="form-group">
                    <label class="form-label" for="reading_level">Reading Level</label>
                    <select class="form-select @error('reading_level') is-invalid @enderror"
                        name="reading_level" id="reading_level">
                        <option value="">Select Reading Level</option>
                        @foreach ($readingLevels as $level)
                            <option value="{{ $level }}" {{ old('reading_level') == $level ? 'selected' : '' }}>{{ $level }}</option>
                        @endforeach
                    </select>
                    @error('reading_level')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Condition --}}
                <div class="form-group">
                    <label class="form-label" for="condition">Condition</label>
                    <select class="form-select @error('condition') is-invalid @enderror"
                        name="condition" id="condition">
                        <option value="">Select Condition</option>
                        <option value="New" {{ old('condition') == 'New' ? 'selected' : '' }}>New</option>
                        <option value="Good" {{ old('condition') == 'Good' ? 'selected' : '' }}>Good</option>
                        <option value="Fair" {{ old('condition') == 'Fair' ? 'selected' : '' }}>Fair</option>
                        <option value="Poor" {{ old('condition') == 'Poor' ? 'selected' : '' }}>Poor</option>
                    </select>
                    @error('condition')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Grade Level --}}
                <div class="form-group">
                    <label class="form-label" for="grade_id">Grade Level</label>
                    <select class="form-select @error('grade_id') is-invalid @enderror"
                        name="grade_id" id="grade_id">
                        <option value="">Not grade-specific</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>
                                {{ $grade->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('grade_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Keywords --}}
                <div class="form-group">
                    <label class="form-label" for="keywords">Keywords</label>
                    <input type="text"
                        class="form-control @error('keywords') is-invalid @enderror"
                        name="keywords" id="keywords"
                        value="{{ old('keywords') }}"
                        placeholder="e.g., chemistry, organic, textbook">
                    @error('keywords')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Comma-separated keywords for search</div>
                </div>

                {{-- Price --}}
                <div class="form-group">
                    <label class="form-label" for="price">Price</label>
                    <div class="d-flex gap-2">
                        <input type="number"
                            class="form-control @error('price') is-invalid @enderror"
                            name="price" id="price"
                            value="{{ old('price') }}"
                            placeholder="0.00"
                            min="0" step="0.01"
                            style="flex: 1;">
                        <input type="text"
                            class="form-control @error('currency') is-invalid @enderror"
                            name="currency" id="currency"
                            value="{{ old('currency', $defaultCurrency) }}"
                            placeholder="{{ $defaultCurrency }}"
                            maxlength="10"
                            style="width: 80px;">
                    </div>
                    @error('price')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Location --}}
                <div class="form-group">
                    <label class="form-label" for="location">Location</label>
                    @if (count($locations) > 0)
                        <select class="form-select @error('location') is-invalid @enderror"
                            name="location" id="location">
                            <option value="">Select Location</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc }}" {{ old('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text"
                            class="form-control @error('location') is-invalid @enderror"
                            name="location" id="location"
                            value="{{ old('location') }}"
                            placeholder="e.g., Shelf A3, Section 2">
                    @endif
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Cover Image URL (full width) --}}
            <div class="form-grid" style="margin-top: 16px;">
                <div class="form-group full-width">
                    <label class="form-label" for="cover_image_url">Cover Image URL</label>
                    <input type="url"
                        class="form-control @error('cover_image_url') is-invalid @enderror"
                        name="cover_image_url" id="cover_image_url"
                        value="{{ old('cover_image_url') }}"
                        placeholder="https://example.com/cover.jpg">
                    @error('cover_image_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="cover-preview" id="coverPreview" style="display: none;">
                        <img id="coverPreviewImg" src="" alt="Cover preview">
                    </div>
                </div>
            </div>

            {{-- Description (full width) --}}
            <div class="form-grid" style="margin-top: 16px;">
                <div class="form-group full-width">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                        name="description" id="description"
                        rows="4"
                        placeholder="Description or synopsis...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('library.catalog.index') }}">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Save Item</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/onscan.js@1.5.1/onscan.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeChoicesSelects();
            initializeIsbnLookup();
            initializeBarcodeScanner();
            initializeCoverPreview();
            initializeFormSubmit();
            initializeAlertDismissal();
        });

        // ========================================
        // Choices.js for Author & Publisher
        // ========================================
        var authorChoices, publisherChoices;

        var authorChoicesConfig = {
            removeItemButton: true,
            searchEnabled: true,
            placeholder: true,
            placeholderValue: 'Search and select authors...',
            searchPlaceholderValue: 'Type to search...',
            noResultsText: 'No authors found',
            noChoicesText: 'No authors available',
            shouldSort: false,
            itemSelectText: '',
            searchResultLimit: 20,
        };

        var publisherChoicesConfig = {
            searchEnabled: true,
            placeholder: true,
            placeholderValue: 'Search and select publisher...',
            searchPlaceholderValue: 'Type to search...',
            noResultsText: 'No publishers found',
            noChoicesText: 'No publishers available',
            shouldSort: false,
            itemSelectText: '',
            searchResultLimit: 20,
        };

        function initializeChoicesSelects() {
            authorChoices = new Choices(document.getElementById('author_select'), authorChoicesConfig);
            document.getElementById('author_select').addEventListener('change', syncAuthorHidden);

            publisherChoices = new Choices(document.getElementById('publisher_name'), publisherChoicesConfig);
        }

        function syncAuthorHidden() {
            var selected = authorChoices.getValue(true);
            document.getElementById('author_names').value = selected.join(', ');
        }

        function rebuildAuthorChoices(selectedNames) {
            authorChoices.destroy();
            var select = document.getElementById('author_select');
            Array.from(select.options).forEach(function(opt) { opt.selected = false; });

            selectedNames.forEach(function(name) {
                var found = Array.from(select.options).find(function(opt) { return opt.value === name; });
                if (found) {
                    found.selected = true;
                } else {
                    select.add(new Option(name, name, true, true));
                }
            });

            authorChoices = new Choices(select, authorChoicesConfig);
            select.addEventListener('change', syncAuthorHidden);
            syncAuthorHidden();
        }

        function rebuildPublisherChoice(name) {
            publisherChoices.destroy();
            var select = document.getElementById('publisher_name');
            Array.from(select.options).forEach(function(opt) { opt.selected = false; });

            var found = Array.from(select.options).find(function(opt) { return opt.value === name; });
            if (found) {
                found.selected = true;
            } else {
                select.add(new Option(name, name, true, true));
            }

            publisherChoices = new Choices(select, publisherChoicesConfig);
        }

        // ========================================
        // ISBN Lookup
        // ========================================
        function initializeIsbnLookup() {
            const lookupBtn = document.getElementById('isbnLookupBtn');
            const isbnInput = document.getElementById('isbn_lookup');

            lookupBtn.addEventListener('click', function() {
                performLookup();
            });

            isbnInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performLookup();
                }
            });

            // Sync ISBN lookup field to hidden ISBN input
            isbnInput.addEventListener('input', function() {
                const cleanIsbn = this.value.replace(/[\s\-]/g, '');
                document.getElementById('isbn').value = cleanIsbn;
                document.getElementById('isbn_display').value = this.value;
            });

            // If old ISBN exists (from validation error), populate lookup field
            const existingIsbn = document.getElementById('isbn').value;
            if (existingIsbn) {
                isbnInput.value = existingIsbn;
                document.getElementById('isbn_display').value = existingIsbn;
            }
        }

        function performLookup() {
            const isbnInput = document.getElementById('isbn_lookup');
            const isbn = isbnInput.value.trim();
            const lookupBtn = document.getElementById('isbnLookupBtn');
            const resultDiv = document.getElementById('isbnResultMessage');

            if (!isbn || isbn.length < 10) {
                resultDiv.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Please enter a valid ISBN (at least 10 characters).</div>';
                return;
            }

            // Show spinner, disable button
            lookupBtn.querySelector('.lookup-text').style.display = 'none';
            lookupBtn.querySelector('.lookup-spinner').classList.add('active');
            lookupBtn.disabled = true;
            resultDiv.innerHTML = '';

            fetch('{{ route("library.books.lookup") }}?isbn=' + encodeURIComponent(isbn), {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            })
            .then(response => response.json())
            .then(data => {
                resetLookupButton();

                if (data.exists === true) {
                    resultDiv.innerHTML = '<div class="alert alert-warning">' +
                        '<i class="fas fa-exclamation-triangle me-2"></i>' +
                        data.message +
                        ' <a href="{{ url("library/catalog") }}/' + data.book_id + '" class="alert-link">View existing book</a>' +
                        '</div>';
                    return;
                }

                if (data.success === true && data.data) {
                    populateForm(data.data);
                    resultDiv.innerHTML = '<div class="alert alert-success">' +
                        '<i class="fas fa-check-circle me-2"></i>' +
                        'Item data found via ' + (data.data.source || 'API') + '. Review and edit the fields below before saving.' +
                        '</div>';
                    return;
                }

                resultDiv.innerHTML = '<div class="alert alert-info">' +
                    '<i class="fas fa-info-circle me-2"></i>' +
                    (data.message || 'No data found. Enter details manually.') +
                    '</div>';
            })
            .catch(error => {
                resetLookupButton();
                console.error('ISBN Lookup Error:', error);
                resultDiv.innerHTML = '<div class="alert alert-danger">' +
                    '<i class="fas fa-times-circle me-2"></i>' +
                    'An error occurred during lookup. Please try again or enter details manually.' +
                    '</div>';
            });
        }

        function resetLookupButton() {
            const lookupBtn = document.getElementById('isbnLookupBtn');
            lookupBtn.querySelector('.lookup-text').style.display = '';
            lookupBtn.querySelector('.lookup-spinner').classList.remove('active');
            lookupBtn.disabled = false;
        }

        function populateForm(data) {
            // Title
            if (data.title) {
                document.getElementById('title').value = data.title;
            }

            // Authors (update Choices.js select)
            if (data.authors && Array.isArray(data.authors) && data.authors.length > 0) {
                rebuildAuthorChoices(data.authors);
            }

            // Publisher (update Choices.js select)
            if (data.publisher) {
                rebuildPublisherChoice(data.publisher);
            }

            // ISBN - set hidden and display fields
            if (data.isbn13) {
                document.getElementById('isbn').value = data.isbn13;
                document.getElementById('isbn_display').value = data.isbn13;
                document.getElementById('isbn_lookup').value = data.isbn13;
            }

            // Pages
            if (data.pages) {
                document.getElementById('pages').value = data.pages;
            }

            // Publication Year
            if (data.publication_year) {
                document.getElementById('publication_year').value = data.publication_year;
            }

            // Genre -> Category (try to match select option)
            if (data.genre) {
                const genreSelect = document.getElementById('genre');
                const genreLower = data.genre.toLowerCase();
                let matched = false;
                for (let i = 0; i < genreSelect.options.length; i++) {
                    if (genreSelect.options[i].value.toLowerCase() === genreLower) {
                        genreSelect.value = genreSelect.options[i].value;
                        matched = true;
                        break;
                    }
                }
            }

            // Dewey Decimal
            if (data.dewey_decimal) {
                document.getElementById('dewey_decimal').value = data.dewey_decimal;
            }

            // Cover Image URL
            if (data.cover_image_url) {
                document.getElementById('cover_image_url').value = data.cover_image_url;
                updateCoverPreview(data.cover_image_url);
            }

            // Language
            if (data.language) {
                document.getElementById('language').value = data.language;
            }

            // Description
            if (data.description) {
                document.getElementById('description').value = data.description;
            }

            // Binding -> Item Type (try to match select option)
            if (data.binding) {
                const formatSelect = document.getElementById('format');
                const bindingLower = data.binding.toLowerCase();
                for (let i = 0; i < formatSelect.options.length; i++) {
                    if (formatSelect.options[i].value.toLowerCase() === bindingLower) {
                        formatSelect.value = formatSelect.options[i].value;
                        break;
                    }
                }
            }
        }

        // ========================================
        // Barcode Scanner (onScan.js)
        // ========================================
        function initializeBarcodeScanner() {
            if (typeof onScan !== 'undefined') {
                onScan.attachTo(document, {
                    avgTimeByChar: 30,
                    minLength: 10,
                    suffixKeyCodes: [13],
                    onScan: function(code, qty) {
                        document.getElementById('isbn_lookup').value = code;
                        document.getElementById('isbn').value = code.replace(/[\s\-]/g, '');
                        document.getElementById('isbn_display').value = code;
                        document.getElementById('isbnLookupBtn').click();
                    },
                    onScanError: function(debug) {
                        console.log('Scan rejected (likely keyboard input):', debug);
                    }
                });
            }
        }

        // ========================================
        // Cover Image Preview
        // ========================================
        function initializeCoverPreview() {
            const coverInput = document.getElementById('cover_image_url');
            coverInput.addEventListener('blur', function() {
                updateCoverPreview(this.value);
            });
        }

        function updateCoverPreview(url) {
            const previewDiv = document.getElementById('coverPreview');
            const previewImg = document.getElementById('coverPreviewImg');

            if (url && url.trim() !== '') {
                previewImg.src = url;
                previewDiv.style.display = 'block';

                previewImg.onerror = function() {
                    previewDiv.style.display = 'none';
                };
            } else {
                previewDiv.style.display = 'none';
            }
        }

        // ========================================
        // Form Submit Loading Animation
        // ========================================
        function initializeFormSubmit() {
            const form = document.getElementById('bookForm');
            form.addEventListener('submit', function() {
                syncAuthorHidden();
                const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        }

        // ========================================
        // Auto-dismiss alerts
        // ========================================
        function initializeAlertDismissal() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) dismissButton.click();
                }, 5000);
            });
        }
    </script>
@endsection
