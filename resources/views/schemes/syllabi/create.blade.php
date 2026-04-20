@extends('layouts.master')
@section('title')
    Create Syllabus
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('syllabi.index') }}">Syllabi</a>
        @endslot
        @slot('title')
            Create Syllabus
        @endslot
    @endcomponent

    <div class="syllabi-container">
        <div class="header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin: 0;">Create Syllabus</h3>
                    <p style="margin: 6px 0 0 0; opacity: .9;">Add a new syllabus record to the system</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('syllabi.index') }}" class="btn-outline-white">
                        <i class="fas fa-arrow-left"></i> Back to Syllabi
                    </a>
                </div>
            </div>
        </div>

        <div class="form-container">
            <div class="help-text">
                <div class="help-title">Creating a Syllabus</div>
                <div class="help-content">
                    Create a new syllabus record. After creating, you can add topics and objectives on the edit page.
                </div>
            </div>

            <form action="{{ route('syllabi.store') }}" method="POST" id="syllabus-form">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id"
                                class="form-select @error('subject_id') is-invalid @enderror"
                                required>
                            <option value="">Select Subject...</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}"
                                    {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="grades" class="form-label">Grades <span class="text-danger">*</span></label>
                        <select name="grades[]" id="grades"
                                class="form-select @error('grades') is-invalid @enderror @error('grades.*') is-invalid @enderror"
                                multiple
                                required
                                style="min-height: 120px;">
                            @foreach ($gradeNames as $grade)
                                <option value="{{ $grade->name }}"
                                    {{ in_array($grade->name, old('grades', [])) ? 'selected' : '' }}>
                                    {{ $grade->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-muted" style="font-size: 12px; margin-top: 4px;">
                            Hold Ctrl/Cmd to select multiple grades.
                        </div>
                        @error('grades')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('grades.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                        <select name="level" id="level"
                                class="form-select @error('level') is-invalid @enderror"
                                required>
                            <option value="">Select Level...</option>
                            @foreach ($levels as $level)
                                <option value="{{ $level }}"
                                    {{ old('level') == $level ? 'selected' : '' }}>
                                    {{ $level }}
                                </option>
                            @endforeach
                        </select>
                        @error('level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="3"
                              maxlength="5000"
                              placeholder="Optional description for this syllabus...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="section-title">Remote Syllabus Source <span class="text-muted fw-normal" style="font-size: 13px;">(Optional)</span></div>

                <div class="help-text">
                    <div class="help-title">Shared syllabus JSON on S3</div>
                    <div class="help-content">
                        Enter the stable public URL for the shared syllabus JSON file on S3. The scheme show page will
                        fetch it once, cache the last successful copy locally, and keep using that cached copy until an
                        admin refreshes it.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="source_url" class="form-label">Remote Syllabus URL</label>
                    <input type="url"
                           name="source_url"
                           id="source_url"
                           class="form-control @error('source_url') is-invalid @enderror"
                           maxlength="2048"
                           placeholder="https://your-bucket.s3.amazonaws.com/syllabi/junior-secondary-english.json"
                           value="{{ old('source_url') }}">
                    <div class="text-muted" style="font-size: 12px; margin-top: 4px;">
                        Leave this blank if you only want to manage the linked PDF and local topic/objective records.
                    </div>
                    @error('source_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox"
                               class="form-check-input"
                               name="is_active"
                               id="is_active"
                               value="1"
                               {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <div class="text-muted" style="font-size: 12px; margin-top: 4px;">
                        Active syllabi are available for teachers to use when creating schemes of work.
                    </div>
                </div>

                <div class="section-title">DMS Document Link <span class="text-muted fw-normal" style="font-size: 13px;">(Optional)</span></div>

                <div class="mb-3">
                    <label for="doc-search-input" class="form-label">Link a DMS Document</label>
                    <div class="doc-picker-wrapper">
                        <input type="text"
                               id="doc-search-input"
                               class="form-control"
                               placeholder="Search for a DMS document by title (optional)..."
                               autocomplete="off">
                        <div id="doc-search-results" style="display: none;"></div>
                    </div>
                    <div id="doc-selected"></div>
                    <input type="hidden" name="document_id" id="document_id_hidden" value="{{ old('document_id') }}">
                    @error('document_id')
                        <div class="text-danger" style="font-size: 13px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-actions">
                    <a href="{{ route('syllabi.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Create Syllabus</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Creating...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
        (function () {
            const form = document.getElementById('syllabus-form');
            const docSearchInput = document.getElementById('doc-search-input');
            const docSearchResults = document.getElementById('doc-search-results');
            const docSelectedArea = document.getElementById('doc-selected');
            const documentIdHidden = document.getElementById('document_id_hidden');

            let debounceTimer = null;

            // Form submit — show loading state
            form.addEventListener('submit', function () {
                const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });

            // Document picker — debounced search
            docSearchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                const query = this.value.trim();

                if (query.length < 2) {
                    docSearchResults.style.display = 'none';
                    docSearchResults.innerHTML = '';
                    return;
                }

                debounceTimer = setTimeout(function () {
                    fetch('{{ route('syllabi.documents.search') }}?q=' + encodeURIComponent(query), {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        docSearchResults.innerHTML = '';

                        if (!data.length) {
                            docSearchResults.innerHTML = '<div class="doc-result-item text-muted">No documents found.</div>';
                            docSearchResults.style.display = 'block';
                            return;
                        }

                        data.forEach(function (doc) {
                            const item = document.createElement('div');
                            item.className = 'doc-result-item';
                            item.textContent = doc.title || doc.original_name;
                            item.addEventListener('click', function () {
                                selectDocument(doc.id, doc.title || doc.original_name);
                            });
                            docSearchResults.appendChild(item);
                        });

                        docSearchResults.style.display = 'block';
                    })
                    .catch(function () {
                        docSearchResults.innerHTML = '<div class="doc-result-item text-danger">Error searching documents.</div>';
                        docSearchResults.style.display = 'block';
                    });
                }, 300);
            });

            function selectDocument(id, title) {
                documentIdHidden.value = id;
                docSearchInput.value = '';
                docSearchResults.style.display = 'none';
                docSearchResults.innerHTML = '';

                docSelectedArea.innerHTML = '<div class="doc-chip">'
                    + '<i class="fas fa-file-alt"></i>'
                    + '<span>' + title + '</span>'
                    + '<button type="button" onclick="clearDocument()" title="Remove document">&times;</button>'
                    + '</div>';
            }

            window.clearDocument = function () {
                documentIdHidden.value = '';
                docSelectedArea.innerHTML = '';
                docSearchInput.value = '';
            };

            // Hide results when clicking outside
            document.addEventListener('click', function (e) {
                if (!docSearchInput.contains(e.target) && !docSearchResults.contains(e.target)) {
                    docSearchResults.style.display = 'none';
                }
            });

            // Pre-populate if old() has document_id (validation failed and returned)
            @if (old('document_id'))
                documentIdHidden.value = '{{ old('document_id') }}';
                // Show a placeholder chip — we don't have the title from old(), show the ID
                docSelectedArea.innerHTML = '<div class="doc-chip">'
                    + '<i class="fas fa-file-alt"></i>'
                    + '<span>Document #{{ old('document_id') }}</span>'
                    + '<button type="button" onclick="clearDocument()" title="Remove document">&times;</button>'
                    + '</div>';
            @endif
        })();
    </script>
@endsection
