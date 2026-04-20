@extends('layouts.master')
@section('title')
    Edit Item
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
            <a class="text-muted font-size-14" href="{{ route('library.catalog.index') }}">Library</a>
        @endslot
        @slot('li_2')
            <a class="text-muted font-size-14" href="{{ route('library.catalog.show', $book) }}">{{ Str::limit($book->title, 30) }}</a>
        @endslot
        @slot('title')
            Edit Item
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
            <h1 class="page-title">Edit Item</h1>
        </div>

        {{-- ISBN Lookup Section (for refreshing data) --}}
        <h3 class="section-title">ISBN Lookup</h3>
        <div class="help-text">
            <div class="help-title">Refresh Item Data</div>
            <div class="help-content">
                You can re-lookup the ISBN to refresh item metadata from online databases. Existing field values will be overwritten.
            </div>
        </div>

        <div class="isbn-lookup-row">
            <div class="isbn-input-group">
                <label class="form-label" for="isbn_lookup">ISBN</label>
                <input type="text"
                    class="form-control"
                    id="isbn_lookup"
                    value="{{ $book->isbn }}"
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
        <form id="bookForm" method="POST" action="{{ route('library.books.update', $book) }}">
            @csrf
            @method('PUT')

            <h3 class="section-title">Item Details</h3>
            <div class="form-grid">
                {{-- Title --}}
                <div class="form-group">
                    <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control @error('title') is-invalid @enderror"
                        name="title" id="title"
                        value="{{ old('title', $book->title) }}"
                        placeholder="Item title"
                        required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Authors --}}
                <div class="form-group">
                    <label class="form-label" for="author_select">Author(s)</label>
                    @php
                        $selectedAuthorNames = old('author_names')
                            ? array_map('trim', explode(',', old('author_names')))
                            : $book->authors->map(fn($a) => trim($a->full_name))->toArray();
                    @endphp
                    <select id="author_select" multiple>
                        @foreach ($authors as $author)
                            <option value="{{ trim($author->full_name) }}" {{ in_array(trim($author->full_name), $selectedAuthorNames) ? 'selected' : '' }}>{{ trim($author->full_name) }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="author_names" id="author_names" value="{{ old('author_names', $authorNames) }}">
                    @error('author_names')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Publisher --}}
                <div class="form-group">
                    <label class="form-label" for="publisher_name">Publisher</label>
                    @php $selectedPublisher = old('publisher_name', $book->publisher->name ?? ''); @endphp
                    <select name="publisher_name" id="publisher_name">
                        <option value="">Select Publisher</option>
                        @foreach ($publishers as $publisher)
                            <option value="{{ $publisher->name }}" {{ $selectedPublisher == $publisher->name ? 'selected' : '' }}>{{ $publisher->name }}</option>
                        @endforeach
                    </select>
                    @error('publisher_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ISBN --}}
                <div class="form-group">
                    <label class="form-label" for="isbn">ISBN <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control @error('isbn') is-invalid @enderror"
                        name="isbn" id="isbn"
                        value="{{ old('isbn', $book->isbn) }}"
                        placeholder="ISBN-10 or ISBN-13"
                        maxlength="17"
                        required>
                    @error('isbn')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Changing the ISBN may affect catalog lookups</div>
                </div>

                {{-- Publication Year --}}
                <div class="form-group">
                    <label class="form-label" for="publication_year">Publication Year</label>
                    <input type="number"
                        class="form-control @error('publication_year') is-invalid @enderror"
                        name="publication_year" id="publication_year"
                        value="{{ old('publication_year', $book->publication_year) }}"
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
                        value="{{ old('edition', $book->edition) }}"
                        placeholder="e.g., 3rd Edition">
                    @error('edition')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Category (stored in genre column) --}}
                <div class="form-group">
                    <label class="form-label" for="genre">Category</label>
                    @php $currentGenre = old('genre', $book->genre); @endphp
                    <select class="form-select @error('genre') is-invalid @enderror"
                        name="genre" id="genre">
                        <option value="">Select Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" {{ $currentGenre == $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                        @if ($currentGenre && !in_array($currentGenre, $categories))
                            <option value="{{ $currentGenre }}" selected>{{ $currentGenre }}</option>
                        @endif
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
                        value="{{ old('language', $book->language) }}"
                        placeholder="e.g., English">
                    @error('language')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Item Type (stored in format column) --}}
                <div class="form-group">
                    <label class="form-label" for="format">Item Type</label>
                    @php $currentFormat = old('format', $book->format); @endphp
                    <select class="form-select @error('format') is-invalid @enderror"
                        name="format" id="format">
                        <option value="">Select Item Type</option>
                        @foreach ($itemTypeNames as $typeName)
                            <option value="{{ $typeName }}" {{ $currentFormat == $typeName ? 'selected' : '' }}>{{ $typeName }}</option>
                        @endforeach
                        @if ($currentFormat && !$itemTypeNames->contains($currentFormat))
                            <option value="{{ $currentFormat }}" selected>{{ $currentFormat }}</option>
                        @endif
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
                        value="{{ old('pages', $book->pages) }}"
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
                        value="{{ old('dewey_decimal', $book->dewey_decimal) }}"
                        placeholder="e.g., 813.54">
                    @error('dewey_decimal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Reading Level --}}
                <div class="form-group">
                    <label class="form-label" for="reading_level">Reading Level</label>
                    @php $currentReadingLevel = old('reading_level', $book->reading_level); @endphp
                    <select class="form-select @error('reading_level') is-invalid @enderror"
                        name="reading_level" id="reading_level">
                        <option value="">Select Reading Level</option>
                        @foreach ($readingLevels as $level)
                            <option value="{{ $level }}" {{ $currentReadingLevel == $level ? 'selected' : '' }}>{{ $level }}</option>
                        @endforeach
                        @if ($currentReadingLevel && !in_array($currentReadingLevel, $readingLevels))
                            <option value="{{ $currentReadingLevel }}" selected>{{ $currentReadingLevel }}</option>
                        @endif
                    </select>
                    @error('reading_level')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Condition --}}
                <div class="form-group">
                    <label class="form-label" for="condition">Condition</label>
                    @php $currentCondition = old('condition', $book->condition); @endphp
                    <select class="form-select @error('condition') is-invalid @enderror"
                        name="condition" id="condition">
                        <option value="">Select Condition</option>
                        <option value="New" {{ $currentCondition == 'New' ? 'selected' : '' }}>New</option>
                        <option value="Good" {{ $currentCondition == 'Good' ? 'selected' : '' }}>Good</option>
                        <option value="Fair" {{ $currentCondition == 'Fair' ? 'selected' : '' }}>Fair</option>
                        <option value="Poor" {{ $currentCondition == 'Poor' ? 'selected' : '' }}>Poor</option>
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
                            <option value="{{ $grade->id }}" {{ old('grade_id', $book->grade_id) == $grade->id ? 'selected' : '' }}>
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
                        value="{{ old('keywords', $book->keywords) }}"
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
                            value="{{ old('price', $book->price) }}"
                            placeholder="0.00"
                            min="0" step="0.01"
                            style="flex: 1;">
                        <input type="text"
                            class="form-control @error('currency') is-invalid @enderror"
                            name="currency" id="currency"
                            value="{{ old('currency', $book->currency ?? $defaultCurrency) }}"
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
                    @php $currentLocation = old('location', $book->location); @endphp
                    @if (count($locations) > 0)
                        <select class="form-select @error('location') is-invalid @enderror"
                            name="location" id="location">
                            <option value="">Select Location</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc }}" {{ $currentLocation == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                            @endforeach
                            @if ($currentLocation && !in_array($currentLocation, $locations))
                                <option value="{{ $currentLocation }}" selected>{{ $currentLocation }}</option>
                            @endif
                        </select>
                    @else
                        <input type="text"
                            class="form-control @error('location') is-invalid @enderror"
                            name="location" id="location"
                            value="{{ $currentLocation }}"
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
                        value="{{ old('cover_image_url', $book->cover_image_url) }}"
                        placeholder="https://example.com/cover.jpg">
                    @error('cover_image_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="cover-preview" id="coverPreview" style="{{ $book->cover_image_url ? '' : 'display: none;' }}">
                        <img id="coverPreviewImg" src="{{ $book->cover_image_url }}" alt="Cover preview">
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
                        placeholder="Description or synopsis...">{{ old('description', $book->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('library.catalog.show', $book) }}">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Update Item</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Updating...
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

                if (data.exists === true && data.book_id !== {{ $book->id }}) {
                    resultDiv.innerHTML = '<div class="alert alert-warning">' +
                        '<i class="fas fa-exclamation-triangle me-2"></i>' +
                        'Another book with this ISBN already exists. ' +
                        '<a href="{{ url("library/catalog") }}/' + data.book_id + '" class="alert-link">View that book</a>' +
                        '</div>';
                    return;
                }

                if (data.success === true && data.data) {
                    populateForm(data.data);
                    resultDiv.innerHTML = '<div class="alert alert-success">' +
                        '<i class="fas fa-check-circle me-2"></i>' +
                        'Book data refreshed from ' + (data.data.source || 'API') + '. Review changes before saving.' +
                        '</div>';
                    return;
                }

                resultDiv.innerHTML = '<div class="alert alert-info">' +
                    '<i class="fas fa-info-circle me-2"></i>' +
                    (data.message || 'No data found for this ISBN.') +
                    '</div>';
            })
            .catch(error => {
                resetLookupButton();
                console.error('ISBN Lookup Error:', error);
                resultDiv.innerHTML = '<div class="alert alert-danger">' +
                    '<i class="fas fa-times-circle me-2"></i>' +
                    'An error occurred during lookup. Please try again.' +
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
            if (data.title) document.getElementById('title').value = data.title;
            // Authors (update Choices.js select)
            if (data.authors && Array.isArray(data.authors) && data.authors.length > 0) {
                rebuildAuthorChoices(data.authors);
            }
            // Publisher (update Choices.js select)
            if (data.publisher) {
                rebuildPublisherChoice(data.publisher);
            }
            if (data.isbn13) document.getElementById('isbn').value = data.isbn13;
            if (data.pages) document.getElementById('pages').value = data.pages;
            if (data.publication_year) document.getElementById('publication_year').value = data.publication_year;
            if (data.genre) {
                const genreSelect = document.getElementById('genre');
                const genreLower = data.genre.toLowerCase();
                for (let i = 0; i < genreSelect.options.length; i++) {
                    if (genreSelect.options[i].value.toLowerCase() === genreLower) {
                        genreSelect.value = genreSelect.options[i].value;
                        break;
                    }
                }
            }
            if (data.dewey_decimal) document.getElementById('dewey_decimal').value = data.dewey_decimal;
            if (data.cover_image_url) {
                document.getElementById('cover_image_url').value = data.cover_image_url;
                updateCoverPreview(data.cover_image_url);
            }
            if (data.language) document.getElementById('language').value = data.language;
            if (data.description) document.getElementById('description').value = data.description;
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
