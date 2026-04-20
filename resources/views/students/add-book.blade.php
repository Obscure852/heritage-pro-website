@extends('layouts.master')

@section('title')
    {{ isset($book) ? 'Edit Book' : 'Add New Book' }}
@endsection

@section('css')
    <style>
        .container-box {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
        }

        .header {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .body {
            padding: 32px;
        }

        .form-section {
            margin-bottom: 28px;
        }

        .form-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #8b5cf6;
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
            line-height: 1.4;
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

        .text-danger {
            font-size: 12px;
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

        .btn-light {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background: #e9ecef;
            color: #495057;
            transform: translateY(-1px);
        }

        .required::after {
            content: '*';
            color: #dc2626;
            margin-left: 4px;
        }

        .input-group .form-control,
        .input-group .form-select {
            border-radius: 0 3px 3px 0;
        }

        .input-group .form-control:first-child {
            border-radius: 3px 0 0 3px;
        }

        .cover-preview {
            margin-top: 12px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
        }

        .cover-preview img {
            max-width: 150px;
            height: auto;
            border-radius: 3px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Custom File Input */
        .custom-file-input {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .custom-file-input input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #f9fafb;
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-input-label:hover {
            border-color: #8b5cf6;
            background: #faf5ff;
        }

        .file-input-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .file-input-text {
            flex: 1;
        }

        .file-input-text .file-label {
            font-weight: 500;
            color: #374151;
            display: block;
            margin-bottom: 2px;
        }

        .file-input-text .file-hint {
            font-size: 13px;
            color: #6b7280;
        }

        .file-input-text .file-selected {
            font-size: 13px;
            color: #8b5cf6;
            font-weight: 500;
        }

        .current-cover-section {
            margin-top: 16px;
            padding: 16px;
            background: #f9fafb;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
        }

        .current-cover-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
            font-weight: 500;
        }

        .current-cover-img {
            max-width: 200px;
            height: auto;
            border-radius: 3px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('students.curriculum-materials') }}">Curriculum Materials</a>
        @endslot
        @slot('title')
            {{ isset($book) ? 'Edit Book' : 'Add New Book' }}
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i>
                    <strong>{{ $errors->first() }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="container-box">
        <div class="header">
            <h4 class="mb-1 text-white">
                <i class="fas fa-book me-2"></i>{{ isset($book) ? 'Edit Textbook' : 'Add New Textbook' }}
            </h4>
            <p class="mb-0 opacity-75">{{ isset($book) ? 'Update textbook information and metadata' : 'Add a new textbook to the curriculum materials library' }}</p>
        </div>
        <div class="body">
            <form action="{{ isset($book) ? route('students.update-book', $book->id) : route('students.store-book') }}"
                method="POST" enctype="multipart/form-data" id="bookForm">
                @csrf
                @if (isset($book))
                    @method('PUT')
                @endif

                <div class="help-text">
                    <div class="help-title">Textbook Information</div>
                    <div class="help-content">
                        Fill in the required fields marked with an asterisk (*). Optional fields can be added to provide more detailed cataloging information.
                    </div>
                </div>

                <!-- Basic Information -->
                <div class="form-section">
                    <div class="section-title">Basic Information</div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="isbn" class="form-label required">ISBN</label>
                            <input type="text" placeholder="978-0-123456-78-9" class="form-control"
                                id="isbn" name="isbn" value="{{ old('isbn', $book->isbn ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label required">Title</label>
                            <input type="text" placeholder="Enter book title" class="form-control"
                                id="title" name="title" value="{{ old('title', $book->title ?? '') }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="author_id" class="form-label required">Author</label>
                            <select class="form-select" data-trigger id="author_id" name="author_id" required>
                                <option value="">Select Author</option>
                                @foreach ($authors as $author)
                                    <option value="{{ $author->id }}"
                                        {{ old('author_id', $book->author_id ?? '') == $author->id ? 'selected' : '' }}>
                                        {{ $author->first_name }} {{ $author->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="grade_id" class="form-label required">Grade</label>
                            <select class="form-select" data-trigger id="grade_id" name="grade_id" required>
                                <option value="">Select Grade</option>
                                @foreach ($grades as $grade)
                                    <option value="{{ $grade->id }}"
                                        {{ old('grade_id', $book->grade_id ?? '') == $grade->id ? 'selected' : '' }}>
                                        {{ $grade->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Publication Details -->
                <div class="form-section">
                    <div class="section-title">Publication Details</div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="publication_year" class="form-label">Publication Year</label>
                            <input type="number" class="form-control" id="publication_year"
                                name="publication_year" placeholder="2024"
                                value="{{ old('publication_year', $book->publication_year ?? '') }}"
                                min="1900" max="{{ date('Y') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="publisher" class="form-label">Publisher</label>
                            <select name="publisher_id" id="publisher" class="form-select" data-trigger>
                                <option value="">Select Publisher</option>
                                @if (!empty($publishers))
                                    @foreach ($publishers as $publisher)
                                        <option value="{{ $publisher->id }}"
                                            {{ old('publisher_id', $book->publisher_id ?? '') == $publisher->id ? 'selected' : '' }}>
                                            {{ $publisher->name ?? '' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edition" class="form-label">Edition</label>
                            <input type="text" class="form-control" id="edition" name="edition"
                                placeholder="1st Edition" value="{{ old('edition', $book->edition ?? '') }}">
                        </div>
                    </div>
                </div>

                <!-- Classification -->
                <div class="form-section">
                    <div class="section-title">Classification</div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="genre" class="form-label required">Genre</label>
                            <select class="form-select" data-trigger id="genre" name="genre" required>
                                <option value="">Select Genre</option>
                                @php
                                    $genres = [
                                        'Fiction' => ['Action and Adventure', 'Classic', 'Comic Book or Graphic Novel', 'Crime and Detective', 'Fable', 'Fairy Tale', 'Fantasy', 'Historical Fiction', 'Horror', 'Humor', 'Legend', 'Literary Fiction', 'Mystery', 'Mythology', 'Romance', 'Science Fiction', 'Short Story', 'Suspense/Thriller', 'Western'],
                                        'Non-Fiction' => ['Autobiography', 'Biography', 'Essay', 'History', 'Memoir', 'Philosophy', 'Politics', 'Religion', 'Science', 'Self-help', 'Travel', 'True Crime'],
                                        'Educational' => ['Textbook', 'Reference Book', 'Workbook', 'Encyclopedia'],
                                        'Other' => ['Poetry', 'Drama', 'Anthology', 'Other'],
                                    ];
                                @endphp
                                @foreach ($genres as $category => $genreList)
                                    <optgroup label="{{ $category }}">
                                        @foreach ($genreList as $genre)
                                            <option value="{{ $genre }}"
                                                {{ old('genre', $book->genre ?? '') == $genre ? 'selected' : '' }}>
                                                {{ $genre }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="language" class="form-label">Language</label>
                            <select name="language" class="form-select" data-trigger id="language">
                                <option value="">Select language</option>
                                <option value="English" {{ old('language', $book->language ?? '') == 'English' ? 'selected' : '' }}>English</option>
                                <option value="Setswana" {{ old('language', $book->language ?? '') == 'Setswana' ? 'selected' : '' }}>Setswana</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="format" class="form-label required">Format</label>
                            <select class="form-select" data-trigger id="format" name="format" required>
                                <option value="">Select Format</option>
                                @php
                                    $formats = ['Hardcover', 'Paperback', 'E-book', 'Audiobook', 'Large Print', 'Board Book', 'Pop-up Book', 'Spiral-bound', 'Library Binding', 'Box Set', 'Other'];
                                @endphp
                                @foreach ($formats as $format)
                                    <option value="{{ $format }}"
                                        {{ old('format', $book->format ?? '') == $format ? 'selected' : '' }}>
                                        {{ $format }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Physical Details -->
                <div class="form-section">
                    <div class="section-title">Physical Details</div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="pages" class="form-label">Pages</label>
                            <input type="number" class="form-control" id="pages" name="pages"
                                placeholder="287" value="{{ old('pages', $book->pages ?? '') }}" min="1">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="quantity" class="form-label required">Quantity</label>
                            <input type="number" class="form-control" id="quantity"
                                name="quantity" value="{{ old('quantity', $book->quantity ?? 1) }}" min="1" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="condition" class="form-label required">Condition</label>
                            <select class="form-select" id="condition" name="condition" required>
                                @foreach (['new', 'good', 'fair', 'poor'] as $condition)
                                    <option value="{{ $condition }}"
                                        {{ old('condition', $book->condition ?? 'good') == $condition ? 'selected' : '' }}>
                                        {{ ucfirst($condition) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Status</label>
                            <select class="form-select" data-trigger name="status" required>
                                <option value="">Select status</option>
                                @foreach (['available', 'checked_out', 'on_hold', 'in_repair'] as $status)
                                    <option value="{{ $status }}"
                                        {{ old('status', $book->status ?? '') == $status ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="price"
                                    name="price" step="0.01" placeholder="0.00" value="{{ old('price', $book->price ?? '') }}">
                                <select class="form-select" id="currency" name="currency" style="max-width: 100px;">
                                    <option value="BWP" {{ old('currency', $book->currency ?? '') == 'BWP' ? 'selected' : '' }}>BWP</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Library System -->
                <div class="form-section">
                    <div class="section-title">Library System</div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="call_number" class="form-label">Call Number</label>
                            <input type="text" class="form-control" id="call_number"
                                name="call_number" value="{{ old('call_number', $book->call_number ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="dewey_decimal" class="form-label">Dewey Decimal</label>
                            <input type="text" class="form-control" id="dewey_decimal"
                                name="dewey_decimal" value="{{ old('dewey_decimal', $book->dewey_decimal ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="barcode" class="form-label">Barcode</label>
                            <input type="text" class="form-control" id="barcode" name="barcode"
                                value="{{ old('barcode', $book->barcode ?? '') }}">
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="form-section">
                    <div class="section-title">Additional Information</div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="series_name" class="form-label">Series Name</label>
                            <input type="text" class="form-control" id="series_name"
                                name="series_name" value="{{ old('series_name', $book->series_name ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="volume_number" class="form-label">Volume Number</label>
                            <input type="number" class="form-control" id="volume_number"
                                name="volume_number" value="{{ old('volume_number', $book->volume_number ?? '') }}" min="1">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="reading_level" class="form-label">Reading Level</label>
                            <select class="form-select" data-trigger id="reading_level" name="reading_level">
                                <option value="">Select Reading Level</option>
                                @php
                                    $readingLevels = ['Level' => ['F1', 'F2', 'F3', 'F4', 'F5']];
                                @endphp
                                @foreach ($readingLevels as $category => $levels)
                                    <optgroup label="{{ $category }}">
                                        @foreach ($levels as $level)
                                            <option value="{{ $level }}"
                                                {{ old('reading_level', $book->reading_level ?? '') == $level ? 'selected' : '' }}>
                                                {{ $level }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="keywords" class="form-label">Keywords</label>
                        <input type="text" class="form-control" id="keywords" name="keywords"
                            placeholder="Separate keywords with commas" value="{{ old('keywords', $book->keywords ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Enter a brief description of the book">{{ old('description', $book->description ?? '') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Cover Image</label>
                            <div class="custom-file-input">
                                <input type="file" id="cover_image" name="cover_image" accept="image/*">
                                <label for="cover_image" class="file-input-label">
                                    <div class="file-input-icon">
                                        <i class="fas fa-image"></i>
                                    </div>
                                    <div class="file-input-text">
                                        <span class="file-label">Choose Cover Image</span>
                                        <span class="file-hint" id="fileHint">JPG, PNG or GIF (Max 2MB)</span>
                                        <span class="file-selected d-none" id="fileName"></span>
                                    </div>
                                </label>
                            </div>
                            @if (isset($book) && $book->cover_image_url)
                                <div class="current-cover-section">
                                    <div class="current-cover-label">Current Cover Image:</div>
                                    <img src="{{ $book->cover_image_url }}" alt="Current Cover" class="current-cover-img">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('students.curriculum-materials') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ isset($book) ? 'Update Book' : 'Add Book' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Choices.js on select elements
            const selectElements = document.querySelectorAll('[data-trigger]');
            selectElements.forEach(function(element) {
                new Choices(element, {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false
                });
            });

            // Handle file input display
            const fileInput = document.getElementById('cover_image');
            const fileHint = document.getElementById('fileHint');
            const fileName = document.getElementById('fileName');

            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        fileHint.classList.add('d-none');
                        fileName.classList.remove('d-none');
                        fileName.textContent = file.name;
                    } else {
                        fileHint.classList.remove('d-none');
                        fileName.classList.add('d-none');
                        fileName.textContent = '';
                    }
                });
            }
        });
    </script>
@endsection
