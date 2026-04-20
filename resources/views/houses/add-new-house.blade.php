@extends('layouts.master')

@section('title')
    New House
@endsection

@section('css')
    @include('houses.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('house.index') }}">Houses</a>
        @endslot
        @slot('title')
            New House
        @endslot
    @endcomponent

    @include('houses.partials.alerts')

    @php
        $defaultColor = old('color_code', '#2563EB');
    @endphp

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Create House</h1>
                <p class="page-subtitle">Create a term house record, assign leadership, and set the saved color used across listings and reports.</p>
            </div>
        </div>

        @include('houses.partials.module-nav', ['current' => 'manager'])

        <div class="help-text">
            <div class="help-title">House Setup</div>
            <div class="help-content">
                The saved color code becomes the house accent for manager pages, detail views, and charts. Use a distinct, accessible color so records remain easy to scan.
            </div>
        </div>

        <form class="needs-validation" method="post" action="{{ route('house.store') }}" novalidate data-house-form>
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="name">House Name <span class="required">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" placeholder="e.g. Kgosi" required>
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="year">Academic Year <span class="required">*</span></label>
                    <select name="year" id="year" class="form-select @error('year') is-invalid @enderror" required>
                        <option value="">Select year</option>
                        @for ($year = date('Y'); $year <= date('Y') + 3; $year++)
                            <option value="{{ $year }}" {{ (string) old('year') === (string) $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                    @error('year')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="color_code_text">House Color <span class="required">*</span></label>
                    <div class="color-input-shell">
                        <input type="color" id="color_code_picker" value="{{ $defaultColor }}">
                        <div>
                            <input type="text" name="color_code" id="color_code_text"
                                class="form-control @error('color_code') is-invalid @enderror"
                                value="{{ $defaultColor }}" placeholder="#2563EB" maxlength="7" required>
                            <div class="field-help">Enter a hex value in the format <code>#RRGGBB</code>.</div>
                        </div>
                    </div>
                    @error('color_code')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="head">House Head <span class="required">*</span></label>
                    <select name="head" id="head" class="form-select @error('head') is-invalid @enderror" data-trigger required>
                        <option value="">Select house head</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ (string) old('head') === (string) $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }}{{ $user->position ? ' - ' . $user->position : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('head')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="assistant">Assistant <span class="required">*</span></label>
                    <select name="assistant" id="assistant" class="form-select @error('assistant') is-invalid @enderror" data-trigger required>
                        <option value="">Select assistant</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ (string) old('assistant') === (string) $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }}{{ $user->position ? ' - ' . $user->position : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('assistant')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Preview</label>
                    <div class="detail-card d-flex align-items-center gap-3" id="houseColorPreviewCard">
                        <span class="house-color-swatch" id="houseColorPreview" style="background: {{ $defaultColor }};"></span>
                        <div>
                            <div class="detail-value" id="houseColorPreviewName">{{ old('name', 'House Name') }}</div>
                            <div class="detail-label mb-0" id="houseColorPreviewCode">{{ strtoupper($defaultColor) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('house.index') }}" class="btn btn-light">
                    <i class="bx bx-arrow-back"></i> Back
                </a>
                @if (!session('is_past_term'))
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Save House</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                @endif
            </div>
        </form>
    </div>
@endsection

@section('script')
    @include('houses.partials.form-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const picker = document.getElementById('color_code_picker');
            const text = document.getElementById('color_code_text');
            const preview = document.getElementById('houseColorPreview');
            const previewCode = document.getElementById('houseColorPreviewCode');
            const previewName = document.getElementById('houseColorPreviewName');
            const nameInput = document.getElementById('name');

            function normalizeColor(value) {
                if (!value) {
                    return '#2563EB';
                }

                const formatted = value.trim().toUpperCase();
                return /^#[0-9A-F]{6}$/.test(formatted) ? formatted : formatted;
            }

            function syncPreviewFromText() {
                const color = normalizeColor(text.value);
                if (/^#[0-9A-F]{6}$/.test(color)) {
                    picker.value = color;
                    preview.style.background = color;
                    previewCode.textContent = color;
                }
                text.value = color;
            }

            picker.addEventListener('input', function() {
                text.value = this.value.toUpperCase();
                syncPreviewFromText();
            });

            text.addEventListener('input', syncPreviewFromText);
            nameInput.addEventListener('input', function() {
                previewName.textContent = this.value.trim() || 'House Name';
            });

            syncPreviewFromText();
        });
    </script>
@endsection
