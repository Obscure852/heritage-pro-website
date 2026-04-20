@extends('layouts.master')
@section('title')
    Edit Document - {{ $document->title }}
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

        .section-title:first-child {
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 20px;
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

        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #ef4444;
        }

        .invalid-feedback {
            color: #ef4444;
            font-size: 13px;
            margin-top: 4px;
        }

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

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Select2 sizing to match form-control */
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            min-height: 42px;
            padding: 4px 8px;
            font-size: 14px;
        }

        .select2-container--default .select2-selection--multiple:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            margin-top: 4px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('documents.index') }}">Documents</a>
        @endslot
        @slot('title')
            Edit Document
        @endslot
    @endcomponent

    <div class="form-container">
        <div class="page-header">
            <h1 class="page-title">Edit Document</h1>
        </div>

        <div class="help-text">
            <div class="help-title">Editing metadata</div>
            <div class="help-content">
                @if($document->isExternalUrl())
                    Update the document metadata below. This document is linked from an online URL, so version uploads are not available here.
                @else
                    Update the document metadata below. File content cannot be changed here — upload a new version instead.
                @endif
            </div>
        </div>

        <form action="{{ route('documents.update', $document) }}" method="POST" id="edit-form">
            @csrf
            @method('PUT')

            <h3 class="section-title" style="margin-top: 0;">Document Details</h3>

            <div class="form-group">
                <label class="form-label">Document Source</label>
                <input type="text" class="form-control" value="{{ $document->sourceLabel() }}" disabled>
                <small class="text-muted">Document source cannot be changed after creation.</small>
            </div>

            {{-- Title --}}
            <div class="form-group">
                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text"
                       name="title"
                       id="title"
                       class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title', $document->title) }}"
                       placeholder="Enter document title"
                       required>
                <small class="text-muted">The display name for this document</small>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if($document->isExternalUrl())
                <div class="form-group">
                    <label for="external_url" class="form-label">Online Document URL <span class="text-danger">*</span></label>
                    <input type="url"
                           name="external_url"
                           id="external_url"
                           class="form-control @error('external_url') is-invalid @enderror"
                           value="{{ old('external_url', $document->external_url) }}"
                           placeholder="https://example.com/document.pdf"
                           required>
                    <small class="text-muted">Opening or downloading this document redirects users to this remote URL.</small>
                    @error('external_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            {{-- Description --}}
            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea name="description"
                          id="description"
                          class="form-control @error('description') is-invalid @enderror"
                          rows="4"
                          placeholder="Add a description for this document...">{{ old('description', $document->description) }}</textarea>
                <small class="text-muted">Optional description to help others find and understand this document</small>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Category --}}
            <div class="form-group">
                <label for="category_id" class="form-label">Category</label>
                <select name="category_id"
                        id="category_id"
                        class="form-select @error('category_id') is-invalid @enderror">
                    <option value="">No category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category_id', $document->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Assign a category to organise this document</small>
                @error('category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Expiry Date & Tags --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="date"
                                   name="expiry_date"
                                   id="expiry_date"
                                   class="form-control @error('expiry_date') is-invalid @enderror"
                                   value="{{ old('expiry_date', $document->expiry_date?->format('Y-m-d')) }}">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('expiry_date').value=''" title="Clear expiry date" style="white-space: nowrap;">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                        <small class="text-muted">Leave blank for no expiration.</small>
                        @error('expiry_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tag-select" class="form-label">Tags</label>
                        @php
                            $selectedTags = old('tag_ids', $document->tags->pluck('id')->toArray());
                        @endphp
                        <select id="tag-select" name="tag_ids[]" class="form-select" multiple>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ in_array($tag->id, $selectedTags) ? 'selected' : '' }}>{{ $tag->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Search existing tags or type to create new ones</small>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="form-actions">
                <a href="{{ route('documents.show', $document) }}" class="btn btn-secondary">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
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
    <script>
        $(document).ready(function() {
            // Initialize Select2 for tag selection with inline creation
            $('#tag-select').select2({
                placeholder: 'Search or create tags...',
                allowClear: true,
                width: '100%',
                multiple: true,
                tags: true,
                createTag: function(params) {
                    var term = $.trim(params.term);
                    if (term === '') return null;
                    return {
                        id: 'new:' + term,
                        text: term,
                        newTag: true
                    };
                },
                templateResult: function(data) {
                    if (data.newTag) {
                        return $('<span><i class="fas fa-plus-circle text-success me-1"></i> Create: "' + data.text + '"</span>');
                    }
                    return data.text;
                }
            });

            var form = document.getElementById('edit-form');
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
