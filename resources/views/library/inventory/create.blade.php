@extends('layouts.master')
@section('title')
    Start New Inventory
@endsection
@section('css')
    <style>
        .library-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .library-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .library-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select,
        textarea.form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus,
        textarea.form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Scope Type Radios */
        .scope-options {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 16px;
        }

        .scope-option {
            flex: 1;
            min-width: 140px;
        }

        .scope-option input[type="radio"] {
            display: none;
        }

        .scope-option label {
            display: block;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }

        .scope-option input[type="radio"]:checked + label {
            border-color: #3b82f6;
            background: #eff6ff;
            color: #1e40af;
        }

        .scope-option label:hover {
            border-color: #93c5fd;
            background: #f9fafb;
        }

        .scope-option .scope-icon {
            font-size: 1.5rem;
            display: block;
            margin-bottom: 6px;
        }

        .scope-option .scope-label {
            font-weight: 600;
            font-size: 14px;
        }

        .scope-option .scope-desc {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        /* Scope Value Select */
        .scope-value-group {
            display: none;
            margin-bottom: 16px;
        }

        .scope-value-group.visible {
            display: block;
        }

        /* Buttons */
        .btn {
            padding: 10px 16px;
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

        .btn-outline-secondary {
            background: transparent;
            border: 1px solid #d1d5db;
            color: #6b7280;
        }

        .btn-outline-secondary:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            color: #374151;
        }

        .btn-loading .btn-spinner {
            display: none;
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

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        @media (max-width: 768px) {
            .library-header {
                padding: 20px;
            }

            .library-body {
                padding: 16px;
            }

            .scope-options {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('library.inventory.index') }}">Inventory</a>
        @endslot
        @slot('title')
            Start New Inventory
        @endslot
    @endcomponent

    <div class="library-container">
        <div class="library-header">
            <h4 class="mb-1 text-white"><i class="bx bx-clipboard me-2"></i>Start New Inventory</h4>
            <p class="mb-0 opacity-75">Configure and begin a new stocktake session</p>
        </div>
        <div class="library-body">
            <div class="help-text">
                <div class="help-title">How Inventory Works</div>
                <div class="help-content">
                    An inventory session lets you verify physical stock by scanning each book's barcode.
                    Choose a scope (all items, by location, or by genre) to limit the stocktake.
                    After scanning, you can compare expected vs. actual counts and mark missing items.
                    Only one session can be active at a time.
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('library.inventory.store') }}" method="POST" id="inventory-form">
                @csrf

                <div class="section-title">Inventory Scope</div>

                <div class="scope-options">
                    <div class="scope-option">
                        <input type="radio" name="scope_type" value="all" id="scope-all"
                               {{ old('scope_type', 'all') === 'all' ? 'checked' : '' }}>
                        <label for="scope-all">
                            <span class="scope-icon"><i class="bx bx-library"></i></span>
                            <span class="scope-label">All Items</span>
                            <span class="scope-desc">Entire collection</span>
                        </label>
                    </div>
                    <div class="scope-option">
                        <input type="radio" name="scope_type" value="location" id="scope-location"
                               {{ old('scope_type') === 'location' ? 'checked' : '' }}>
                        <label for="scope-location">
                            <span class="scope-icon"><i class="bx bx-map"></i></span>
                            <span class="scope-label">By Location</span>
                            <span class="scope-desc">Specific shelf or room</span>
                        </label>
                    </div>
                    <div class="scope-option">
                        <input type="radio" name="scope_type" value="genre" id="scope-genre"
                               {{ old('scope_type') === 'genre' ? 'checked' : '' }}>
                        <label for="scope-genre">
                            <span class="scope-icon"><i class="bx bx-category"></i></span>
                            <span class="scope-label">By Genre</span>
                            <span class="scope-desc">Specific category</span>
                        </label>
                    </div>
                </div>

                <div class="scope-value-group" id="location-group">
                    <label class="form-label" for="location-select">Select Location</label>
                    <select class="form-select" id="location-select" name="scope_value_location">
                        <option value="">-- Choose location --</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location }}"
                                    {{ old('scope_value_location', old('scope_value')) === $location ? 'selected' : '' }}>
                                {{ $location }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="scope-value-group" id="genre-group">
                    <label class="form-label" for="genre-select">Select Genre</label>
                    <select class="form-select" id="genre-select" name="scope_value_genre">
                        <option value="">-- Choose genre --</option>
                        @foreach ($genres as $genre)
                            <option value="{{ $genre }}"
                                    {{ old('scope_value_genre', old('scope_value')) === $genre ? 'selected' : '' }}>
                                {{ $genre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="scope_value" id="scope-value-hidden" value="{{ old('scope_value') }}">

                <div class="mb-3">
                    <label class="form-label" for="notes">Notes (optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"
                              placeholder="Add any notes about this inventory session...">{{ old('notes') }}</textarea>
                </div>

                <div class="form-actions">
                    <a href="{{ route('library.inventory.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary btn-loading" id="start-btn">
                        <span class="btn-text"><i class="bx bx-play"></i> Start Inventory</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Starting...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var scopeRadios = document.querySelectorAll('input[name="scope_type"]');
            var locationGroup = document.getElementById('location-group');
            var genreGroup = document.getElementById('genre-group');
            var scopeValueHidden = document.getElementById('scope-value-hidden');
            var locationSelect = document.getElementById('location-select');
            var genreSelect = document.getElementById('genre-select');

            function updateScopeVisibility() {
                var selected = document.querySelector('input[name="scope_type"]:checked');
                if (!selected) return;

                locationGroup.classList.remove('visible');
                genreGroup.classList.remove('visible');

                if (selected.value === 'location') {
                    locationGroup.classList.add('visible');
                    scopeValueHidden.value = locationSelect.value;
                } else if (selected.value === 'genre') {
                    genreGroup.classList.add('visible');
                    scopeValueHidden.value = genreSelect.value;
                } else {
                    scopeValueHidden.value = '';
                }
            }

            scopeRadios.forEach(function(radio) {
                radio.addEventListener('change', updateScopeVisibility);
            });

            locationSelect.addEventListener('change', function() {
                scopeValueHidden.value = this.value;
            });

            genreSelect.addEventListener('change', function() {
                scopeValueHidden.value = this.value;
            });

            // Initial state
            updateScopeVisibility();

            // Form submit loading
            var form = document.getElementById('inventory-form');
            form.addEventListener('submit', function() {
                var submitBtn = form.querySelector('button[type="submit"].btn-loading');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        });
    </script>
@endsection
