@extends('layouts.master')
@section('title')
    Edit Syllabus
@endsection
@section('css')
    @include('schemes.partials.schemes-styles')
    <style>
        /* --- Edit page specific styles --- */

        /* Topic Cards */
        .topic-card {
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .topic-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .topic-seq-badge {
            background: #4e73df;
            color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .topic-title {
            font-weight: 600;
            color: #1f2937;
            font-size: 15px;
            flex: 1;
        }

        .topic-weeks {
            font-size: 12px;
            color: #6b7280;
            background: #e5e7eb;
            padding: 2px 8px;
            border-radius: 20px;
        }

        .topic-description {
            font-size: 13px;
            color: #4b5563;
            margin-bottom: 10px;
            padding-left: 38px;
        }

        /* Objectives within topic */
        .objectives-list {
            padding-left: 38px;
        }

        .objective-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .objective-row:last-child {
            border-bottom: none;
        }

        .obj-seq {
            font-size: 12px;
            color: #9ca3af;
            width: 20px;
            flex-shrink: 0;
        }

        .obj-code {
            font-weight: 600;
            font-size: 12px;
            color: #374151;
            width: 70px;
            flex-shrink: 0;
        }

        .obj-text {
            flex: 1;
            font-size: 13px;
            color: #374151;
        }

        .obj-cog-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 20px;
            background: #e0e7ff;
            color: #3730a3;
            flex-shrink: 0;
        }

        /* Inline edit forms */
        .inline-edit-form,
        .inline-add-form {
            background: white;
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 12px;
            margin-top: 8px;
        }

        .inline-edit-form .form-control,
        .inline-add-form .form-control,
        .inline-edit-form .form-select,
        .inline-add-form .form-select {
            padding: 6px 10px;
            font-size: 13px;
        }

        /* Add topic form */
        #add-topic-form {
            border: 1px dashed #bfdbfe;
            border-radius: 3px;
            padding: 16px;
            margin-top: 12px;
        }

        /* Field errors for AJAX */
        .field-error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 3px;
        }

        .preview-panel {
            margin-top: 20px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #f8fafc;
            padding: 18px;
        }

        .preview-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin-top: 14px;
        }

        .preview-stat {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
        }

        .preview-stat-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .preview-stat-value {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 4px;
        }

        .preview-sections {
            display: grid;
            gap: 12px;
            margin-top: 18px;
        }

        .preview-section {
            border: 1px solid #dbeafe;
            border-radius: 6px;
            background: white;
        }

        .preview-section summary {
            cursor: pointer;
            list-style: none;
            padding: 12px 14px;
            font-weight: 600;
            color: #1e3a8a;
        }

        .preview-section summary::-webkit-details-marker {
            display: none;
        }

        .preview-section-content {
            border-top: 1px solid #e2e8f0;
            padding: 12px 14px;
        }

        .sync-change-list {
            display: grid;
            gap: 10px;
        }

        .sync-change-item {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px 12px;
            background: #f8fafc;
        }

        .sync-change-title {
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        .sync-change-meta {
            font-size: 12px;
            color: #64748b;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .sync-change-text {
            font-size: 13px;
            color: #374151;
            margin-top: 6px;
        }

        .sync-empty {
            color: #64748b;
            font-size: 13px;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('syllabi.index') }}">Syllabi</a>
        @endslot
        @slot('title')
            {{ ($canEditSyllabus ?? false) ? 'Edit Syllabus' : 'View Syllabus' }}
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-circle-outline label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="syllabi-container">
        <div class="header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin: 0;">{{ ($canEditSyllabus ?? false) ? 'Edit Syllabus' : 'View Syllabus' }}</h3>
                    <p style="margin: 6px 0 0 0; opacity: .9;">
                        {{ $syllabus->subject->name ?? 'Syllabus' }} &mdash; {{ $syllabus->grades_label }} ({{ $syllabus->level }})
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('syllabi.index') }}" class="btn-outline-white">
                        <i class="fas fa-arrow-left"></i> Back to Syllabi
                    </a>
                </div>
            </div>
        </div>

        <div class="form-container">
            @php
                $cachedStructure = is_array($syllabus->cached_structure) ? $syllabus->cached_structure : null;
                $cachedSectionCount = is_array($cachedStructure['sections'] ?? null) ? count($cachedStructure['sections']) : 0;
                $isReadOnlySyllabus = !($canEditSyllabus ?? false);
            @endphp

            {{-- ================================================================ --}}
            {{-- Section 1: Syllabus Details Form                                 --}}
            {{-- ================================================================ --}}
            <div class="section-title">{{ $isReadOnlySyllabus ? 'Syllabus Details (Read Only)' : 'Syllabus Details' }}</div>

            @if ($isReadOnlySyllabus)
                <div class="help-text" style="background: #f8fafc; border-left-color: #64748b;">
                    <div class="help-title" style="color: #334155;">Read-only access</div>
                    <div class="help-content" style="color: #475569;">
                        Teachers can review syllabus content here, but only HODs and administrators can update,
                        sync, or delete syllabus records.
                    </div>
                </div>
            @endif

            <form action="{{ route('syllabi.update', $syllabus) }}" method="POST" id="syllabus-form">
                @csrf
                @method('PUT')
                <fieldset @disabled($isReadOnlySyllabus) style="{{ $isReadOnlySyllabus ? 'opacity: .78;' : '' }}">

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id"
                                class="form-select @error('subject_id') is-invalid @enderror"
                                required>
                            <option value="">Select Subject...</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}"
                                    {{ old('subject_id', $syllabus->subject_id) == $subject->id ? 'selected' : '' }}>
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
                                    {{ in_array($grade->name, old('grades', $syllabus->grades ?? [])) ? 'selected' : '' }}>
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
                            @foreach ($levels as $lvl)
                                <option value="{{ $lvl }}"
                                    {{ old('level', $syllabus->level) == $lvl ? 'selected' : '' }}>
                                    {{ $lvl }}
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
                              placeholder="Optional description...">{{ old('description', $syllabus->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="section-title">Remote Syllabus Source <span class="text-muted fw-normal" style="font-size: 13px;">(Optional)</span></div>

                <div class="help-text">
                    <div class="help-title">Shared syllabus JSON on S3</div>
                    <div class="help-content">
                        The scheme show page reads syllabus content from this remote JSON source and stores the last
                        successful fetch locally. Local topics and objectives below are still used for the current
                        objective-linking and coverage flow.
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
                           value="{{ old('source_url', $syllabus->source_url) }}">
                    <div class="text-muted" style="font-size: 12px; margin-top: 4px;">
                        If you change this URL, the cached remote syllabus copy is cleared and must be refreshed again.
                    </div>
                    @error('source_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="help-text" style="border-left-color: {{ $cachedStructure ? '#22c55e' : '#f59e0b' }}; background: {{ $cachedStructure ? '#f0fdf4' : '#fffbeb' }};">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <div class="help-title" style="color: {{ $cachedStructure ? '#065f46' : '#92400e' }};">
                                Remote Cache Status
                            </div>
                            <div class="help-content" style="color: {{ $cachedStructure ? '#064e3b' : '#78350f' }};">
                                @if ($cachedStructure)
                                    Cached locally with {{ $cachedSectionCount }} section{{ $cachedSectionCount === 1 ? '' : 's' }}.
                                    Last fetched {{ $syllabus->cached_at?->format('d M Y H:i') ?? 'recently' }}.
                                @else
                                    No remote syllabus has been cached yet.
                                @endif
                            </div>
                            @if ($syllabus->source_url)
                                <div class="mt-2">
                                    <a href="{{ $syllabus->source_url }}" target="_blank" rel="noopener noreferrer"
                                        style="font-size: 13px;">
                                        <i class="fas fa-external-link-alt me-1"></i>Open remote JSON
                                    </a>
                                </div>
                            @endif
                            @if (($cachedImportSummary['topics'] ?? 0) > 0)
                                <div class="mt-2" style="font-size: 13px; color: {{ $cachedStructure ? '#064e3b' : '#78350f' }};">
                                    Importable structure: {{ $cachedImportSummary['topics'] }} topic{{ $cachedImportSummary['topics'] === 1 ? '' : 's' }}
                                    and {{ $cachedImportSummary['objectives'] }} objective{{ $cachedImportSummary['objectives'] === 1 ? '' : 's' }}.
                                </div>
                            @endif
                        </div>
                        @if ($canEditSyllabus ?? false)
                            <div class="d-flex gap-2 flex-wrap">
                                @if ($canPopulateFromCache ?? false)
                                    <button type="button" class="btn btn-sm btn-outline-success"
                                        onclick="document.getElementById('populate-cache-form').submit();">
                                        <i class="fas fa-file-import me-1"></i> Populate Topics
                                    </button>
                                @elseif ($canSyncFromCache ?? false)
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick="document.getElementById('preview-sync-cache-form').submit();">
                                        <i class="fas fa-eye me-1"></i> Preview Sync
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success"
                                        onclick="document.getElementById('sync-cache-form').submit();">
                                        <i class="fas fa-arrows-rotate me-1"></i> Sync Topics
                                    </button>
                                @endif
                                @if ($syllabus->source_url)
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="document.getElementById('refresh-cache-form').submit();">
                                        <i class="fas fa-sync-alt me-1"></i> Refresh Cache
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox"
                               class="form-check-input"
                               name="is_active"
                               id="is_active"
                               value="1"
                               {{ old('is_active', $syllabus->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
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
                    <div id="doc-selected">
                        @if ($syllabus->document)
                            <div class="doc-chip">
                                <i class="fas fa-file-alt"></i>
                                <span>{{ $syllabus->document->title ?? $syllabus->document->original_name }}</span>
                                <button type="button" onclick="clearDocument()" title="Remove document">&times;</button>
                            </div>
                        @endif
                    </div>
                    <input type="hidden"
                           name="document_id"
                           id="document_id_hidden"
                           value="{{ old('document_id', $syllabus->document_id) }}">
                    @error('document_id')
                        <div class="text-danger" style="font-size: 13px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>
                </fieldset>

                <div class="form-actions">
                    <a href="{{ route('syllabi.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    @if ($canEditSyllabus ?? false)
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Saving...
                            </span>
                        </button>
                    @endif
                </div>
            </form>

            @if (($canEditSyllabus ?? false) && $syllabus->source_url)
                <form action="{{ route('syllabi.refresh-cache', $syllabus) }}" method="POST" id="refresh-cache-form">
                    @csrf
                </form>
            @endif

            @if (($canEditSyllabus ?? false) && ($canPopulateFromCache ?? false))
                <form action="{{ route('syllabi.populate-from-cache', $syllabus) }}" method="POST" id="populate-cache-form">
                    @csrf
                </form>
            @endif

            @if (($canEditSyllabus ?? false) && ($canSyncFromCache ?? false))
                <form action="{{ route('syllabi.preview-sync-from-cache', $syllabus) }}" method="POST" id="preview-sync-cache-form">
                    @csrf
                </form>
            @endif

            @if (($canEditSyllabus ?? false) && ($canSyncFromCache ?? false))
                <form action="{{ route('syllabi.sync-from-cache', $syllabus) }}" method="POST" id="sync-cache-form">
                    @csrf
                </form>
            @endif

            @if (!empty($syncPreview))
                <div class="preview-panel" id="sync-preview-panel">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <div class="section-title" style="margin-top: 0; margin-bottom: 10px; border-bottom: none; padding-bottom: 0;">
                                Sync Preview
                            </div>
                            <div class="help-content" style="color: #475569;">
                                Dry run only. No topics or objectives have been changed yet. This preview is based on the currently cached JSON.
                            </div>
                        </div>
                        @if ($canEditSyllabus ?? false)
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="document.getElementById('preview-sync-cache-form').submit();">
                                    <i class="fas fa-sync-alt me-1"></i> Refresh Preview
                                </button>
                                <button type="button" class="btn btn-sm btn-success"
                                    onclick="document.getElementById('sync-cache-form').submit();">
                                    <i class="fas fa-check me-1"></i> Apply Sync
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="preview-summary-grid">
                        <div class="preview-stat">
                            <div class="preview-stat-label">Topics Created</div>
                            <div class="preview-stat-value">{{ $syncPreview['summary']['topics_created'] }}</div>
                        </div>
                        <div class="preview-stat">
                            <div class="preview-stat-label">Topics Updated</div>
                            <div class="preview-stat-value">{{ $syncPreview['summary']['topics_updated'] }}</div>
                        </div>
                        <div class="preview-stat">
                            <div class="preview-stat-label">Topics Deleted</div>
                            <div class="preview-stat-value">{{ $syncPreview['summary']['topics_deleted'] }}</div>
                        </div>
                        <div class="preview-stat">
                            <div class="preview-stat-label">Topics Preserved</div>
                            <div class="preview-stat-value">{{ $syncPreview['summary']['topics_preserved'] }}</div>
                        </div>
                        <div class="preview-stat">
                            <div class="preview-stat-label">Objectives Created</div>
                            <div class="preview-stat-value">{{ $syncPreview['summary']['objectives_created'] }}</div>
                        </div>
                        <div class="preview-stat">
                            <div class="preview-stat-label">Objectives Updated</div>
                            <div class="preview-stat-value">{{ $syncPreview['summary']['objectives_updated'] }}</div>
                        </div>
                        <div class="preview-stat">
                            <div class="preview-stat-label">Objectives Deleted</div>
                            <div class="preview-stat-value">{{ $syncPreview['summary']['objectives_deleted'] }}</div>
                        </div>
                        <div class="preview-stat">
                            <div class="preview-stat-label">Objectives Preserved</div>
                            <div class="preview-stat-value">{{ $syncPreview['summary']['objectives_preserved'] }}</div>
                        </div>
                    </div>

                    <div class="preview-sections">
                        <details class="preview-section" @if (count($syncPreview['changes']['topics']['updated']) > 0) open @endif>
                            <summary>Topic Updates ({{ count($syncPreview['changes']['topics']['updated']) }})</summary>
                            <div class="preview-section-content">
                                @forelse ($syncPreview['changes']['topics']['updated'] as $change)
                                    <div class="sync-change-item">
                                        <div class="sync-change-title">{{ $change['from']['name'] }} -> {{ $change['to']['name'] }}</div>
                                        <div class="sync-change-meta">
                                            <span>Seq {{ $change['from']['sequence'] }} -> {{ $change['to']['sequence'] }}</span>
                                        </div>
                                        @if (($change['from']['description'] ?? null) !== ($change['to']['description'] ?? null))
                                            <div class="sync-change-text">
                                                Description: {{ $change['from']['description'] ?: 'None' }} -> {{ $change['to']['description'] ?: 'None' }}
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="sync-empty">No existing topics will be updated.</div>
                                @endforelse
                            </div>
                        </details>

                        <details class="preview-section">
                            <summary>Topic Creates ({{ count($syncPreview['changes']['topics']['created']) }})</summary>
                            <div class="preview-section-content">
                                @forelse ($syncPreview['changes']['topics']['created'] as $change)
                                    <div class="sync-change-item">
                                        <div class="sync-change-title">{{ $change['name'] }}</div>
                                        <div class="sync-change-meta">
                                            <span>Seq {{ $change['sequence'] }}</span>
                                        </div>
                                        @if (!empty($change['description']))
                                            <div class="sync-change-text">{{ $change['description'] }}</div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="sync-empty">No new topics will be created.</div>
                                @endforelse
                            </div>
                        </details>

                        <details class="preview-section">
                            <summary>Topic Deletes ({{ count($syncPreview['changes']['topics']['deleted']) }})</summary>
                            <div class="preview-section-content">
                                @forelse ($syncPreview['changes']['topics']['deleted'] as $change)
                                    <div class="sync-change-item">
                                        <div class="sync-change-title">{{ $change['name'] }}</div>
                                        <div class="sync-change-meta">
                                            <span>Seq {{ $change['sequence'] }}</span>
                                            <span>{{ $change['reason'] }}</span>
                                        </div>
                                        @if (!empty($change['description']))
                                            <div class="sync-change-text">{{ $change['description'] }}</div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="sync-empty">No local topics will be deleted.</div>
                                @endforelse
                            </div>
                        </details>

                        <details class="preview-section">
                            <summary>Topic Preserved ({{ count($syncPreview['changes']['topics']['preserved']) }})</summary>
                            <div class="preview-section-content">
                                @forelse ($syncPreview['changes']['topics']['preserved'] as $change)
                                    <div class="sync-change-item">
                                        <div class="sync-change-title">{{ $change['name'] }}</div>
                                        <div class="sync-change-meta">
                                            <span>Seq {{ $change['sequence'] }} -> {{ $change['new_sequence'] }}</span>
                                            <span>{{ $change['reason'] }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="sync-empty">No linked legacy topics need to be preserved.</div>
                                @endforelse
                            </div>
                        </details>

                        <details class="preview-section" @if (count($syncPreview['changes']['objectives']['updated']) > 0) open @endif>
                            <summary>Objective Updates ({{ count($syncPreview['changes']['objectives']['updated']) }})</summary>
                            <div class="preview-section-content">
                                @forelse ($syncPreview['changes']['objectives']['updated'] as $change)
                                    <div class="sync-change-item">
                                        <div class="sync-change-title">
                                            {{ $change['from']['code'] ?: 'No code' }} -> {{ $change['to']['code'] ?: 'No code' }}
                                        </div>
                                        <div class="sync-change-meta">
                                            <span>{{ $change['from']['topic_name'] ?: 'Unassigned topic' }} -> {{ $change['to']['topic_name'] }}</span>
                                            <span>Seq {{ $change['from']['sequence'] }} -> {{ $change['to']['sequence'] }}</span>
                                        </div>
                                        @if (($change['from']['objective_text'] ?? null) !== ($change['to']['objective_text'] ?? null))
                                            <div class="sync-change-text">
                                                {{ $change['from']['objective_text'] }} -> {{ $change['to']['objective_text'] }}
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="sync-empty">No existing objectives will be updated.</div>
                                @endforelse
                            </div>
                        </details>

                        <details class="preview-section">
                            <summary>Objective Creates ({{ count($syncPreview['changes']['objectives']['created']) }})</summary>
                            <div class="preview-section-content">
                                @forelse ($syncPreview['changes']['objectives']['created'] as $change)
                                    <div class="sync-change-item">
                                        <div class="sync-change-title">{{ $change['code'] ?: 'No code' }} · {{ $change['topic_name'] }}</div>
                                        <div class="sync-change-meta">
                                            <span>Seq {{ $change['sequence'] }}</span>
                                        </div>
                                        <div class="sync-change-text">{{ $change['objective_text'] }}</div>
                                    </div>
                                @empty
                                    <div class="sync-empty">No new objectives will be created.</div>
                                @endforelse
                            </div>
                        </details>

                        <details class="preview-section">
                            <summary>Objective Deletes ({{ count($syncPreview['changes']['objectives']['deleted']) }})</summary>
                            <div class="preview-section-content">
                                @forelse ($syncPreview['changes']['objectives']['deleted'] as $change)
                                    <div class="sync-change-item">
                                        <div class="sync-change-title">{{ $change['code'] ?: 'No code' }} · {{ $change['topic_name'] ?: 'Unassigned topic' }}</div>
                                        <div class="sync-change-meta">
                                            <span>Seq {{ $change['sequence'] }}</span>
                                            <span>{{ $change['reason'] }}</span>
                                        </div>
                                        <div class="sync-change-text">{{ $change['objective_text'] }}</div>
                                    </div>
                                @empty
                                    <div class="sync-empty">No local objectives will be deleted.</div>
                                @endforelse
                            </div>
                        </details>

                        <details class="preview-section">
                            <summary>Objective Preserved ({{ count($syncPreview['changes']['objectives']['preserved']) }})</summary>
                            <div class="preview-section-content">
                                @forelse ($syncPreview['changes']['objectives']['preserved'] as $change)
                                    <div class="sync-change-item">
                                        <div class="sync-change-title">{{ $change['code'] ?: 'No code' }} · {{ $change['topic_name'] ?: 'Unassigned topic' }}</div>
                                        <div class="sync-change-meta">
                                            <span>Seq {{ $change['sequence'] }} -> {{ $change['new_sequence'] }}</span>
                                            <span>{{ $change['reason'] }}</span>
                                        </div>
                                        <div class="sync-change-text">{{ $change['objective_text'] }}</div>
                                    </div>
                                @empty
                                    <div class="sync-empty">No linked legacy objectives need to be preserved.</div>
                                @endforelse
                            </div>
                        </details>
                    </div>
                </div>
            @endif

            {{-- ================================================================ --}}
            {{-- Section 2: Topics & Objectives Management                         --}}
            {{-- ================================================================ --}}
            <div class="section-title" style="margin-top: 40px;">Topics &amp; Objectives</div>

            <div class="help-text">
                <div class="help-title">Managing Topics &amp; Objectives</div>
                <div class="help-content">
                    @if ($canEditSyllabus ?? false)
                        Add topics to your syllabus and then add objectives within each topic. Changes are saved immediately via AJAX.
                    @else
                        Review the syllabus topics and objectives below. Teachers have read-only access here; only HODs and administrators can change this content.
                    @endif
                </div>
            </div>

            @if ($canPopulateFromCache ?? false)
                <div class="help-text" style="background: #eff6ff; border-left-color: #2563eb;">
                    <div class="help-title" style="color: #1d4ed8;">Cached JSON is ready to import</div>
                    <div class="help-content" style="color: #1e3a8a;">
                        This syllabus has no local topics yet. Use <strong>Populate Topics</strong> to import
                        {{ $cachedImportSummary['topics'] }} topic{{ $cachedImportSummary['topics'] === 1 ? '' : 's' }}
                        and {{ $cachedImportSummary['objectives'] }} objective{{ $cachedImportSummary['objectives'] === 1 ? '' : 's' }}
                        from the cached JSON into the editable syllabus records below.
                    </div>
                </div>
            @elseif ($canSyncFromCache ?? false)
                <div class="help-text" style="background: #f8fafc; border-left-color: #94a3b8;">
                    <div class="help-title" style="color: #334155;">Cached JSON can sync into local topics</div>
                    <div class="help-content" style="color: #475569;">
                        Run <strong>Preview Sync</strong> to see what will be created, updated, deleted, or preserved.
                        Applying the sync updates matching rows in place, adds new items, and only removes unlinked stale rows.
                    </div>
                </div>
            @endif

            <div id="topics-container">
                @forelse ($syllabus->topics as $topic)
                    <div class="topic-card" id="topic-card-{{ $topic->id }}" data-topic-id="{{ $topic->id }}">
                        {{-- Topic view mode --}}
                        <div class="topic-view-mode" id="topic-view-{{ $topic->id }}">
                            <div class="topic-header">
                                <div class="topic-seq-badge">{{ $topic->sequence }}</div>
                                <div class="topic-title">{{ $topic->name }}</div>
                                @if ($topic->suggested_weeks)
                                    <span class="topic-weeks">{{ $topic->suggested_weeks }} wk{{ $topic->suggested_weeks != 1 ? 's' : '' }}</span>
                                @endif
                                @if ($canEditSyllabus ?? false)
                                    <div class="ms-auto d-flex gap-2">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary btn-edit-topic"
                                                data-topic-id="{{ $topic->id }}"
                                                title="Edit Topic">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger btn-delete-topic"
                                                data-topic-id="{{ $topic->id }}"
                                                data-topic-name="{{ $topic->name }}"
                                                title="Delete Topic">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            @if ($topic->description)
                                <div class="topic-description">{{ $topic->description }}</div>
                            @endif

                            {{-- Objectives list --}}
                            <div class="objectives-list" id="objectives-list-{{ $topic->id }}">
                                @forelse ($topic->objectives as $objective)
                                    <div class="objective-row" id="objective-row-{{ $objective->id }}" data-objective-id="{{ $objective->id }}">
                                        <div class="obj-view-mode" id="obj-view-{{ $objective->id }}"
                                             style="display: flex; align-items: center; gap: 8px; flex: 1;">
                                            <span class="obj-seq">{{ $objective->sequence }}</span>
                                            <span class="obj-code">{{ $objective->code }}</span>
                                            <span class="obj-text">{{ $objective->objective_text }}</span>
                                            <span class="obj-cog-badge">{{ $objective->cognitive_level }}</span>
                                            @if ($canEditSyllabus ?? false)
                                                <div class="d-flex gap-1">
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-primary btn-edit-objective"
                                                            data-objective-id="{{ $objective->id }}"
                                                            data-topic-id="{{ $topic->id }}"
                                                            title="Edit Objective">
                                                        <i class="fas fa-edit" style="font-size: 11px;"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger btn-delete-objective"
                                                            data-objective-id="{{ $objective->id }}"
                                                            data-topic-id="{{ $topic->id }}"
                                                            title="Delete Objective">
                                                        <i class="fas fa-trash" style="font-size: 11px;"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Objective inline edit form --}}
                                        @if ($canEditSyllabus ?? false)
                                            <div class="inline-edit-form obj-edit-form" id="obj-edit-form-{{ $objective->id }}" style="display: none; flex: 1;">
                                                <div class="row g-2 mb-2">
                                                    <div class="col-md-1">
                                                        <label class="form-label" style="font-size: 12px;">Seq</label>
                                                        <input type="number" class="form-control obj-seq-input" value="{{ $objective->sequence }}" min="1">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label" style="font-size: 12px;">Code</label>
                                                        <input type="text" class="form-control obj-code-input" value="{{ $objective->code }}" maxlength="50">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" style="font-size: 12px;">Objective Text <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control obj-text-input" value="{{ $objective->objective_text }}" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label" style="font-size: 12px;">Cognitive Level</label>
                                                        <select class="form-select obj-cog-input">
                                                            <option value="">Select...</option>
                                                            @foreach ($cognitivelevels as $cog)
                                                                <option value="{{ $cog }}" {{ $objective->cognitive_level == $cog ? 'selected' : '' }}>{{ $cog }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="button"
                                                            class="btn btn-sm btn-primary btn-save-objective"
                                                            data-objective-id="{{ $objective->id }}"
                                                            data-topic-id="{{ $topic->id }}">
                                                        <i class="fas fa-check me-1"></i> Save
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm btn-secondary btn-cancel-obj-edit"
                                                            data-objective-id="{{ $objective->id }}">
                                                        Cancel
                                                    </button>
                                                </div>
                                                <div class="obj-edit-errors" style="margin-top: 6px;"></div>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-muted" style="font-size: 13px; padding: 8px 0;" id="no-objectives-msg-{{ $topic->id }}">
                                        {{ $canEditSyllabus ? 'No objectives yet. Add one below.' : 'No objectives have been added yet.' }}
                                    </div>
                                @endforelse
                            </div>

                            {{-- Add Objective button and inline form --}}
                            @if ($canEditSyllabus ?? false)
                                <div class="mt-2 ms-0" style="padding-left: 38px;">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary btn-show-add-objective"
                                            data-topic-id="{{ $topic->id }}">
                                        <i class="fas fa-plus me-1"></i> Add Objective
                                    </button>

                                    <div class="inline-add-form add-objective-form" id="add-objective-form-{{ $topic->id }}" style="display: none; margin-top: 8px;">
                                        <div class="row g-2 mb-2">
                                            <div class="col-md-1">
                                                <label class="form-label" style="font-size: 12px;">Seq</label>
                                                <input type="number" class="form-control new-obj-seq" min="1" placeholder="1">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label" style="font-size: 12px;">Code</label>
                                                <input type="text" class="form-control new-obj-code" maxlength="50" placeholder="e.g. A1.1">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" style="font-size: 12px;">Objective Text <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control new-obj-text" placeholder="Enter objective..." required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" style="font-size: 12px;">Cognitive Level</label>
                                                <select class="form-select new-obj-cog">
                                                    <option value="">Select...</option>
                                                    @foreach ($cognitivelevels as $cog)
                                                        <option value="{{ $cog }}">{{ $cog }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button"
                                                    class="btn btn-sm btn-primary btn-submit-new-objective"
                                                    data-topic-id="{{ $topic->id }}">
                                                <i class="fas fa-plus me-1"></i> Add
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-secondary btn-cancel-add-objective"
                                                    data-topic-id="{{ $topic->id }}">
                                                Cancel
                                            </button>
                                        </div>
                                        <div class="add-obj-errors" style="margin-top: 6px;"></div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Topic inline edit form --}}
                        @if ($canEditSyllabus ?? false)
                            <div class="inline-edit-form topic-edit-form" id="topic-edit-form-{{ $topic->id }}" style="display: none;">
                                <div class="row g-2 mb-2">
                                    <div class="col-md-1">
                                        <label class="form-label" style="font-size: 12px;">Seq</label>
                                        <input type="number" class="form-control topic-edit-seq" value="{{ $topic->sequence }}" min="1">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-size: 12px;">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control topic-edit-name" value="{{ $topic->name }}" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label" style="font-size: 12px;">Description</label>
                                        <input type="text" class="form-control topic-edit-description" value="{{ $topic->description }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" style="font-size: 12px;">Weeks</label>
                                        <input type="number" class="form-control topic-edit-weeks" value="{{ $topic->suggested_weeks }}" min="0" max="52" step="0.5">
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button"
                                            class="btn btn-sm btn-primary btn-save-topic"
                                            data-topic-id="{{ $topic->id }}">
                                        <i class="fas fa-check me-1"></i> Save Topic
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-secondary btn-cancel-topic-edit"
                                            data-topic-id="{{ $topic->id }}">
                                        Cancel
                                    </button>
                                </div>
                                <div class="topic-edit-errors" style="margin-top: 6px;"></div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-muted text-center" style="padding: 20px 0;" id="no-topics-msg">
                        <i class="bx bx-list-ul" style="font-size: 32px; opacity: 0.3; display: block; margin-bottom: 8px;"></i>
                        No topics have been added yet. Add your first topic below.
                    </div>
                @endforelse
            </div>

            {{-- Add New Topic Button + Form --}}
            @if ($canEditSyllabus ?? false)
                <div class="mt-3">
                    <button type="button" id="btn-show-add-topic" class="btn btn-outline-secondary">
                        <i class="fas fa-plus me-1"></i> Add New Topic
                    </button>

                    <div id="add-topic-form" style="display: none; margin-top: 12px;">
                        <div class="row g-2 mb-2">
                            <div class="col-md-1">
                                <label class="form-label" style="font-size: 12px;">Seq</label>
                                <input type="number" id="new-topic-seq" class="form-control" min="1" placeholder="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="font-size: 12px;">Topic Name <span class="text-danger">*</span></label>
                                <input type="text" id="new-topic-name" class="form-control" placeholder="Enter topic name..." required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" style="font-size: 12px;">Description</label>
                                <input type="text" id="new-topic-description" class="form-control" placeholder="Optional description...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" style="font-size: 12px;">Suggested Weeks</label>
                                <input type="number" id="new-topic-weeks" class="form-control" min="0" max="52" step="0.5" placeholder="e.g. 2">
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" id="btn-submit-new-topic" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i> Add Topic
                            </button>
                            <button type="button" id="btn-cancel-add-topic" class="btn btn-sm btn-secondary">
                                Cancel
                            </button>
                        </div>
                        <div id="add-topic-errors" style="margin-top: 6px;"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Template for dynamically added topic cards --}}
    @if ($canEditSyllabus ?? false)
        <template id="topic-template">
            <div class="topic-card" id="topic-card-__TOPIC_ID__" data-topic-id="__TOPIC_ID__">
                <div class="topic-view-mode" id="topic-view-__TOPIC_ID__">
                    <div class="topic-header">
                        <div class="topic-seq-badge">__SEQ__</div>
                        <div class="topic-title">__NAME__</div>
                        __WEEKS_BADGE__
                        <div class="ms-auto d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-edit-topic"
                                    data-topic-id="__TOPIC_ID__"
                                    title="Edit Topic">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-delete-topic"
                                    data-topic-id="__TOPIC_ID__"
                                    data-topic-name="__NAME__"
                                    title="Delete Topic">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    __DESCRIPTION_HTML__
                    <div class="objectives-list" id="objectives-list-__TOPIC_ID__">
                        <div class="text-muted" style="font-size: 13px; padding: 8px 0;" id="no-objectives-msg-__TOPIC_ID__">
                            No objectives yet. Add one below.
                        </div>
                    </div>
                    <div class="mt-2" style="padding-left: 38px;">
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary btn-show-add-objective"
                                data-topic-id="__TOPIC_ID__">
                            <i class="fas fa-plus me-1"></i> Add Objective
                        </button>
                        <div class="inline-add-form add-objective-form" id="add-objective-form-__TOPIC_ID__" style="display: none; margin-top: 8px;">
                            <div class="row g-2 mb-2">
                                <div class="col-md-1">
                                    <label class="form-label" style="font-size: 12px;">Seq</label>
                                    <input type="number" class="form-control new-obj-seq" min="1" placeholder="1">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" style="font-size: 12px;">Code</label>
                                    <input type="text" class="form-control new-obj-code" maxlength="50" placeholder="e.g. A1.1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 12px;">Objective Text <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control new-obj-text" placeholder="Enter objective..." required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size: 12px;">Cognitive Level</label>
                                    <select class="form-select new-obj-cog">
                                        __COGNITIVE_OPTIONS__
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button"
                                        class="btn btn-sm btn-primary btn-submit-new-objective"
                                        data-topic-id="__TOPIC_ID__">
                                    <i class="fas fa-plus me-1"></i> Add
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-secondary btn-cancel-add-objective"
                                        data-topic-id="__TOPIC_ID__">
                                    Cancel
                                </button>
                            </div>
                            <div class="add-obj-errors" style="margin-top: 6px;"></div>
                        </div>
                    </div>
                </div>
                <div class="inline-edit-form topic-edit-form" id="topic-edit-form-__TOPIC_ID__" style="display: none;">
                    <div class="row g-2 mb-2">
                        <div class="col-md-1">
                            <label class="form-label" style="font-size: 12px;">Seq</label>
                            <input type="number" class="form-control topic-edit-seq" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-size: 12px;">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control topic-edit-name" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" style="font-size: 12px;">Description</label>
                            <input type="text" class="form-control topic-edit-description">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" style="font-size: 12px;">Weeks</label>
                            <input type="number" class="form-control topic-edit-weeks" min="0" max="52" step="0.5">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button"
                                class="btn btn-sm btn-primary btn-save-topic"
                                data-topic-id="__TOPIC_ID__">
                            <i class="fas fa-check me-1"></i> Save Topic
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-secondary btn-cancel-topic-edit"
                                data-topic-id="__TOPIC_ID__">
                            Cancel
                        </button>
                    </div>
                    <div class="topic-edit-errors" style="margin-top: 6px;"></div>
                </div>
            </div>
        </template>

        <template id="objective-row-template">
            <div class="objective-row" id="objective-row-__OBJ_ID__" data-objective-id="__OBJ_ID__">
                <div class="obj-view-mode" id="obj-view-__OBJ_ID__"
                     style="display: flex; align-items: center; gap: 8px; flex: 1;">
                    <span class="obj-seq">__SEQ__</span>
                    <span class="obj-code">__CODE__</span>
                    <span class="obj-text">__OBJ_TEXT__</span>
                    <span class="obj-cog-badge">__COG__</span>
                    <div class="d-flex gap-1">
                        <button type="button"
                                class="btn btn-sm btn-outline-primary btn-edit-objective"
                                data-objective-id="__OBJ_ID__"
                                data-topic-id="__TOPIC_ID__"
                                title="Edit Objective">
                            <i class="fas fa-edit" style="font-size: 11px;"></i>
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger btn-delete-objective"
                                data-objective-id="__OBJ_ID__"
                                data-topic-id="__TOPIC_ID__"
                                title="Delete Objective">
                            <i class="fas fa-trash" style="font-size: 11px;"></i>
                        </button>
                    </div>
                </div>
                <div class="inline-edit-form obj-edit-form" id="obj-edit-form-__OBJ_ID__" style="display: none; flex: 1;">
                    <div class="row g-2 mb-2">
                        <div class="col-md-1">
                            <label class="form-label" style="font-size: 12px;">Seq</label>
                            <input type="number" class="form-control obj-seq-input" value="__SEQ__" min="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" style="font-size: 12px;">Code</label>
                            <input type="text" class="form-control obj-code-input" value="__CODE__" maxlength="50">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size: 12px;">Objective Text <span class="text-danger">*</span></label>
                            <input type="text" class="form-control obj-text-input" value="__OBJ_TEXT__" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size: 12px;">Cognitive Level</label>
                            <select class="form-select obj-cog-input">
                                __COGNITIVE_OPTIONS_SELECTED__
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button"
                                class="btn btn-sm btn-primary btn-save-objective"
                                data-objective-id="__OBJ_ID__"
                                data-topic-id="__TOPIC_ID__">
                            <i class="fas fa-check me-1"></i> Save
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-secondary btn-cancel-obj-edit"
                                data-objective-id="__OBJ_ID__">
                            Cancel
                        </button>
                    </div>
                    <div class="obj-edit-errors" style="margin-top: 6px;"></div>
                </div>
            </div>
        </template>
    @endif
@endsection
@section('script')
    <script>
    (function () {
        'use strict';

        const SYLLABUS_ID = {{ $syllabus->id }};
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
        const COGNITIVE_LEVELS = @json($cognitivelevels);
        const CAN_EDIT_SYLLABUS = @json($canEditSyllabus ?? false);

        // ================================================================
        // Syllabus Details Form — btn-loading
        // ================================================================
        const syllabusForm = document.getElementById('syllabus-form');
        syllabusForm.addEventListener('submit', function () {
            const submitBtn = syllabusForm.querySelector('button[type="submit"].btn-loading');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }
        });

        // ================================================================
        // Document Picker
        // ================================================================
        const docSearchInput = document.getElementById('doc-search-input');
        const docSearchResults = document.getElementById('doc-search-results');
        const docSelectedArea = document.getElementById('doc-selected');
        const documentIdHidden = document.getElementById('document_id_hidden');
        let debounceTimer = null;

        if (docSearchInput && docSearchResults && docSelectedArea && documentIdHidden) {
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
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        docSearchResults.innerHTML = '';
                        if (!data.length) {
                            docSearchResults.innerHTML = '<div class="doc-result-item text-muted">No documents found.</div>';
                            docSearchResults.style.display = 'block';
                            return;
                        }
                        data.forEach(doc => {
                            const item = document.createElement('div');
                            item.className = 'doc-result-item';
                            item.textContent = doc.title || doc.original_name;
                            item.addEventListener('click', () => selectDocument(doc.id, doc.title || doc.original_name));
                            docSearchResults.appendChild(item);
                        });
                        docSearchResults.style.display = 'block';
                    })
                    .catch(() => {
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
                    + '<span>' + escapeHtml(title) + '</span>'
                    + '<button type="button" onclick="clearDocument()" title="Remove document">&times;</button>'
                    + '</div>';
            }

            window.clearDocument = function () {
                documentIdHidden.value = '';
                docSelectedArea.innerHTML = '';
                docSearchInput.value = '';
            };

            document.addEventListener('click', function (e) {
                if (!docSearchInput.contains(e.target) && !docSearchResults.contains(e.target)) {
                    docSearchResults.style.display = 'none';
                }
            });
        }

        // ================================================================
        // Helpers
        // ================================================================
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str || '';
            return div.innerHTML;
        }

        function showToast(message, icon) {
            icon = icon || 'success';
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: message,
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
        }

        function showErrors(container, errors) {
            if (!container) return;
            let html = '';
            if (typeof errors === 'string') {
                html = '<div class="field-error">' + escapeHtml(errors) + '</div>';
            } else {
                Object.values(errors).forEach(msgs => {
                    (Array.isArray(msgs) ? msgs : [msgs]).forEach(msg => {
                        html += '<div class="field-error">' + escapeHtml(msg) + '</div>';
                    });
                });
            }
            container.innerHTML = html;
        }

        function clearErrors(container) {
            if (container) container.innerHTML = '';
        }

        function buildCognitiveOptions(selected) {
            let html = '<option value="">Select...</option>';
            COGNITIVE_LEVELS.forEach(cog => {
                html += '<option value="' + cog + '"' + (cog === selected ? ' selected' : '') + '>' + cog + '</option>';
            });
            return html;
        }

        if (!CAN_EDIT_SYLLABUS) {
            return;
        }

        // ================================================================
        // Add New Topic (Show/Hide Form)
        // ================================================================
        document.getElementById('btn-show-add-topic').addEventListener('click', function () {
            document.getElementById('add-topic-form').style.display = 'block';
            document.getElementById('new-topic-name').focus();
            this.style.display = 'none';
        });

        document.getElementById('btn-cancel-add-topic').addEventListener('click', function () {
            document.getElementById('add-topic-form').style.display = 'none';
            document.getElementById('btn-show-add-topic').style.display = '';
            document.getElementById('add-topic-errors').innerHTML = '';
            document.getElementById('new-topic-name').value = '';
            document.getElementById('new-topic-seq').value = '';
            document.getElementById('new-topic-description').value = '';
            document.getElementById('new-topic-weeks').value = '';
        });

        document.getElementById('btn-submit-new-topic').addEventListener('click', function () {
            const name = document.getElementById('new-topic-name').value.trim();
            const seq = document.getElementById('new-topic-seq').value;
            const description = document.getElementById('new-topic-description').value.trim();
            const weeks = document.getElementById('new-topic-weeks').value;
            const errContainer = document.getElementById('add-topic-errors');

            clearErrors(errContainer);

            if (!name) {
                showErrors(errContainer, 'Topic name is required.');
                return;
            }

            const payload = { name, sequence: seq || null, description: description || null, suggested_weeks: weeks || null };

            fetch('{{ route('syllabi.topics.store', $syllabus) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify(payload),
            })
            .then(res => res.json().then(data => ({ status: res.status, data })))
            .then(({ status, data }) => {
                if (status === 422) {
                    showErrors(errContainer, data.errors || data.message);
                    return;
                }
                if (status >= 400) {
                    showErrors(errContainer, data.message || 'An error occurred.');
                    return;
                }

                // Remove empty state message
                const noTopicsMsg = document.getElementById('no-topics-msg');
                if (noTopicsMsg) noTopicsMsg.remove();

                // Build and append topic card from template
                const topic = data.topic || data;
                appendTopicCard(topic);

                // Reset form
                document.getElementById('btn-cancel-add-topic').click();
                showToast('Topic added successfully.');
            })
            .catch(() => showErrors(errContainer, 'Network error. Please try again.'));
        });

        function appendTopicCard(topic) {
            const template = document.getElementById('topic-template');
            const clone = document.importNode(template.content, true);
            const div = clone.querySelector('.topic-card');

            const weeks = topic.suggested_weeks;
            const weeksBadge = weeks ? '<span class="topic-weeks">' + weeks + ' wk' + (weeks != 1 ? 's' : '') + '</span>' : '';
            const descHtml = topic.description ? '<div class="topic-description">' + escapeHtml(topic.description) + '</div>' : '';
            const cogOptions = '<option value="">Select...</option>' + COGNITIVE_LEVELS.map(c => '<option value="' + c + '">' + c + '</option>').join('');

            div.innerHTML = div.innerHTML
                .split('__TOPIC_ID__').join(topic.id)
                .split('__SEQ__').join(topic.sequence || '')
                .split('__NAME__').join(escapeHtml(topic.name))
                .split('__WEEKS_BADGE__').join(weeksBadge)
                .split('__DESCRIPTION_HTML__').join(descHtml)
                .split('__COGNITIVE_OPTIONS__').join(cogOptions);

            // Fill edit form defaults
            const seqInput = div.querySelector('.topic-edit-seq');
            if (seqInput) seqInput.value = topic.sequence || '';
            const nameInput = div.querySelector('.topic-edit-name');
            if (nameInput) nameInput.value = topic.name;
            const descInput = div.querySelector('.topic-edit-description');
            if (descInput) descInput.value = topic.description || '';
            const weeksInput = div.querySelector('.topic-edit-weeks');
            if (weeksInput) weeksInput.value = topic.suggested_weeks || '';

            document.getElementById('topics-container').appendChild(div);
        }

        // ================================================================
        // Event Delegation on #topics-container
        // ================================================================
        document.getElementById('topics-container').addEventListener('click', function (e) {
            const target = e.target.closest('button, [data-action]');
            if (!target) return;

            // ---- TOPIC: Edit ----
            if (target.classList.contains('btn-edit-topic')) {
                const topicId = target.dataset.topicId;
                document.getElementById('topic-view-' + topicId).style.display = 'none';
                document.getElementById('topic-edit-form-' + topicId).style.display = 'block';
            }

            // ---- TOPIC: Cancel Edit ----
            if (target.classList.contains('btn-cancel-topic-edit')) {
                const topicId = target.dataset.topicId;
                document.getElementById('topic-edit-form-' + topicId).style.display = 'none';
                document.getElementById('topic-view-' + topicId).style.display = 'block';
                clearErrors(document.querySelector('#topic-edit-form-' + topicId + ' .topic-edit-errors'));
            }

            // ---- TOPIC: Save ----
            if (target.classList.contains('btn-save-topic')) {
                const topicId = target.dataset.topicId;
                const editForm = document.getElementById('topic-edit-form-' + topicId);
                const errContainer = editForm.querySelector('.topic-edit-errors');

                clearErrors(errContainer);

                const payload = {
                    name: editForm.querySelector('.topic-edit-name').value.trim(),
                    sequence: editForm.querySelector('.topic-edit-seq').value || null,
                    description: editForm.querySelector('.topic-edit-description').value.trim() || null,
                    suggested_weeks: editForm.querySelector('.topic-edit-weeks').value || null,
                    _method: 'PUT',
                };

                if (!payload.name) {
                    showErrors(errContainer, 'Topic name is required.');
                    return;
                }

                fetch('/syllabi/' + SYLLABUS_ID + '/topics/' + topicId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: JSON.stringify(payload),
                })
                .then(res => res.json().then(data => ({ status: res.status, data })))
                .then(({ status, data }) => {
                    if (status === 422) { showErrors(errContainer, data.errors || data.message); return; }
                    if (status >= 400) { showErrors(errContainer, data.message || 'Error saving topic.'); return; }

                    const topic = data.topic || data;
                    // Update DOM view mode
                    const viewMode = document.getElementById('topic-view-' + topicId);
                    viewMode.querySelector('.topic-seq-badge').textContent = topic.sequence || '';
                    viewMode.querySelector('.topic-title').textContent = topic.name;

                    let weeksBadge = viewMode.querySelector('.topic-weeks');
                    if (topic.suggested_weeks) {
                        const txt = topic.suggested_weeks + ' wk' + (topic.suggested_weeks != 1 ? 's' : '');
                        if (weeksBadge) {
                            weeksBadge.textContent = txt;
                        } else {
                            const span = document.createElement('span');
                            span.className = 'topic-weeks';
                            span.textContent = txt;
                            viewMode.querySelector('.topic-header').insertBefore(span, viewMode.querySelector('.ms-auto'));
                        }
                    } else if (weeksBadge) {
                        weeksBadge.remove();
                    }

                    let descEl = viewMode.querySelector('.topic-description');
                    if (topic.description) {
                        if (descEl) {
                            descEl.textContent = topic.description;
                        } else {
                            descEl = document.createElement('div');
                            descEl.className = 'topic-description';
                            descEl.textContent = topic.description;
                            viewMode.insertBefore(descEl, viewMode.querySelector('.objectives-list'));
                        }
                    } else if (descEl) {
                        descEl.remove();
                    }

                    // Update delete button name
                    const deleteBtn = viewMode.querySelector('.btn-delete-topic');
                    if (deleteBtn) deleteBtn.dataset.topicName = topic.name;

                    editForm.style.display = 'none';
                    viewMode.style.display = 'block';
                    showToast('Topic updated.');
                })
                .catch(() => showErrors(errContainer, 'Network error.'));
            }

            // ---- TOPIC: Delete ----
            if (target.classList.contains('btn-delete-topic')) {
                const topicId = target.dataset.topicId;
                const topicName = target.dataset.topicName || 'this topic';

                Swal.fire({
                    title: 'Delete Topic?',
                    html: 'This will also delete <strong>all objectives</strong> in "<em>' + escapeHtml(topicName) + '</em>". This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch('/syllabi/' + SYLLABUS_ID + '/topics/' + topicId, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                        },
                        body: JSON.stringify({ _method: 'DELETE' }),
                    })
                    .then(res => {
                        if (res.status >= 400) {
                            return res.json().then(data => { throw new Error(data.message || 'Error deleting topic.'); });
                        }
                        const card = document.getElementById('topic-card-' + topicId);
                        if (card) card.remove();

                        if (!document.querySelector('#topics-container .topic-card')) {
                            const container = document.getElementById('topics-container');
                            const msg = document.createElement('div');
                            msg.className = 'text-muted text-center';
                            msg.id = 'no-topics-msg';
                            msg.style.padding = '20px 0';
                            msg.innerHTML = '<i class="bx bx-list-ul" style="font-size: 32px; opacity: 0.3; display: block; margin-bottom: 8px;"></i>No topics have been added yet.';
                            container.prepend(msg);
                        }

                        showToast('Topic deleted.');
                    })
                    .catch(err => Swal.fire('Error', err.message, 'error'));
                });
            }

            // ---- OBJECTIVE: Show Add Form ----
            if (target.classList.contains('btn-show-add-objective')) {
                const topicId = target.dataset.topicId;
                const form = document.getElementById('add-objective-form-' + topicId);
                if (form) {
                    form.style.display = 'block';
                    form.querySelector('.new-obj-text').focus();
                    target.style.display = 'none';
                }
            }

            // ---- OBJECTIVE: Cancel Add ----
            if (target.classList.contains('btn-cancel-add-objective')) {
                const topicId = target.dataset.topicId;
                const form = document.getElementById('add-objective-form-' + topicId);
                if (form) {
                    form.style.display = 'none';
                    form.querySelector('.new-obj-seq').value = '';
                    form.querySelector('.new-obj-code').value = '';
                    form.querySelector('.new-obj-text').value = '';
                    form.querySelector('.new-obj-cog').value = '';
                    clearErrors(form.querySelector('.add-obj-errors'));
                }
                const showBtn = document.querySelector('.btn-show-add-objective[data-topic-id="' + topicId + '"]');
                if (showBtn) showBtn.style.display = '';
            }

            // ---- OBJECTIVE: Submit New ----
            if (target.classList.contains('btn-submit-new-objective')) {
                const topicId = target.dataset.topicId;
                const form = document.getElementById('add-objective-form-' + topicId);
                const errContainer = form.querySelector('.add-obj-errors');
                clearErrors(errContainer);

                const objText = form.querySelector('.new-obj-text').value.trim();
                if (!objText) {
                    showErrors(errContainer, 'Objective text is required.');
                    return;
                }

                const payload = {
                    objective_text: objText,
                    sequence: form.querySelector('.new-obj-seq').value || null,
                    code: form.querySelector('.new-obj-code').value.trim() || null,
                    cognitive_level: form.querySelector('.new-obj-cog').value || null,
                };

                fetch('/syllabi/' + SYLLABUS_ID + '/topics/' + topicId + '/objectives', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: JSON.stringify(payload),
                })
                .then(res => res.json().then(data => ({ status: res.status, data })))
                .then(({ status, data }) => {
                    if (status === 422) { showErrors(errContainer, data.errors || data.message); return; }
                    if (status >= 400) { showErrors(errContainer, data.message || 'Error adding objective.'); return; }

                    const obj = data.objective || data;

                    // Remove "no objectives" message
                    const noObjMsg = document.getElementById('no-objectives-msg-' + topicId);
                    if (noObjMsg) noObjMsg.remove();

                    // Append objective row
                    appendObjectiveRow(topicId, obj);

                    // Reset add form
                    const cancelBtn = form.querySelector('.btn-cancel-add-objective');
                    if (cancelBtn) cancelBtn.click();

                    showToast('Objective added.');
                })
                .catch(() => showErrors(errContainer, 'Network error.'));
            }

            // ---- OBJECTIVE: Edit (show form) ----
            if (target.classList.contains('btn-edit-objective')) {
                const objId = target.dataset.objectiveId;
                const viewMode = document.getElementById('obj-view-' + objId);
                const editForm = document.getElementById('obj-edit-form-' + objId);
                if (viewMode) viewMode.style.display = 'none';
                if (editForm) editForm.style.display = 'flex';
            }

            // ---- OBJECTIVE: Cancel Edit ----
            if (target.classList.contains('btn-cancel-obj-edit')) {
                const objId = target.dataset.objectiveId;
                const viewMode = document.getElementById('obj-view-' + objId);
                const editForm = document.getElementById('obj-edit-form-' + objId);
                if (viewMode) viewMode.style.display = 'flex';
                if (editForm) {
                    editForm.style.display = 'none';
                    clearErrors(editForm.querySelector('.obj-edit-errors'));
                }
            }

            // ---- OBJECTIVE: Save Edit ----
            if (target.classList.contains('btn-save-objective')) {
                const objId = target.dataset.objectiveId;
                const topicId = target.dataset.topicId;
                const editForm = document.getElementById('obj-edit-form-' + objId);
                const errContainer = editForm.querySelector('.obj-edit-errors');
                clearErrors(errContainer);

                const objText = editForm.querySelector('.obj-text-input').value.trim();
                if (!objText) {
                    showErrors(errContainer, 'Objective text is required.');
                    return;
                }

                const payload = {
                    objective_text: objText,
                    sequence: editForm.querySelector('.obj-seq-input').value || null,
                    code: editForm.querySelector('.obj-code-input').value.trim() || null,
                    cognitive_level: editForm.querySelector('.obj-cog-input').value || null,
                    _method: 'PUT',
                };

                fetch('/syllabi/' + SYLLABUS_ID + '/topics/' + topicId + '/objectives/' + objId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: JSON.stringify(payload),
                })
                .then(res => res.json().then(data => ({ status: res.status, data })))
                .then(({ status, data }) => {
                    if (status === 422) { showErrors(errContainer, data.errors || data.message); return; }
                    if (status >= 400) { showErrors(errContainer, data.message || 'Error updating objective.'); return; }

                    const obj = data.objective || data;
                    // Update view mode
                    const viewMode = document.getElementById('obj-view-' + objId);
                    if (viewMode) {
                        viewMode.querySelector('.obj-seq').textContent = obj.sequence || '';
                        viewMode.querySelector('.obj-code').textContent = obj.code || '';
                        viewMode.querySelector('.obj-text').textContent = obj.objective_text;
                        viewMode.querySelector('.obj-cog-badge').textContent = obj.cognitive_level || '';
                    }

                    editForm.style.display = 'none';
                    if (viewMode) viewMode.style.display = 'flex';
                    showToast('Objective updated.');
                })
                .catch(() => showErrors(errContainer, 'Network error.'));
            }

            // ---- OBJECTIVE: Delete ----
            if (target.classList.contains('btn-delete-objective')) {
                const objId = target.dataset.objectiveId;
                const topicId = target.dataset.topicId;

                Swal.fire({
                    title: 'Delete Objective?',
                    text: 'This will permanently remove the objective. This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch('/syllabi/' + SYLLABUS_ID + '/topics/' + topicId + '/objectives/' + objId, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                        },
                        body: JSON.stringify({ _method: 'DELETE' }),
                    })
                    .then(res => {
                        if (res.status >= 400) {
                            return res.json().then(data => { throw new Error(data.message || 'Error deleting objective.'); });
                        }
                        const row = document.getElementById('objective-row-' + objId);
                        if (row) row.remove();

                        // Show empty message if no objectives left
                        const objList = document.getElementById('objectives-list-' + topicId);
                        if (objList && !objList.querySelector('.objective-row')) {
                            const msg = document.createElement('div');
                            msg.className = 'text-muted';
                            msg.id = 'no-objectives-msg-' + topicId;
                            msg.style.cssText = 'font-size: 13px; padding: 8px 0;';
                            msg.textContent = 'No objectives yet. Add one below.';
                            objList.appendChild(msg);
                        }

                        showToast('Objective deleted.');
                    })
                    .catch(err => Swal.fire('Error', err.message, 'error'));
                });
            }
        });

        // ================================================================
        // Append Objective Row (helper)
        // ================================================================
        function appendObjectiveRow(topicId, obj) {
            const template = document.getElementById('objective-row-template');
            const clone = document.importNode(template.content, true);
            const div = clone.querySelector('.objective-row');

            const cogOptionsSelected = buildCognitiveOptions(obj.cognitive_level);

            div.innerHTML = div.innerHTML
                .split('__OBJ_ID__').join(obj.id)
                .split('__TOPIC_ID__').join(topicId)
                .split('__SEQ__').join(obj.sequence || '')
                .split('__CODE__').join(escapeHtml(obj.code || ''))
                .split('__OBJ_TEXT__').join(escapeHtml(obj.objective_text || ''))
                .split('__COG__').join(escapeHtml(obj.cognitive_level || ''))
                .split('__COGNITIVE_OPTIONS_SELECTED__').join(cogOptionsSelected);

            const objList = document.getElementById('objectives-list-' + topicId);
            if (objList) objList.appendChild(div);
        }

    })();
    </script>
@endsection
