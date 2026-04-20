@extends('layouts.master')

@section('title')
    Edit House
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
            Edit House
        @endslot
    @endcomponent

    @include('houses.partials.alerts')

    @php
        $houseColor = old('color_code', $house->color_code ?? '#2563EB');
    @endphp

    <div class="form-container house-page-accent"
        style="--house-color: {{ $house->color_code ?? '#2563EB' }}; --house-color-soft: {{ $house->colorWithAlpha(0.14) }};">
        <div class="page-header">
            <div>
                <div class="house-title-row">
                    <span class="house-color-swatch" id="houseColorPreview" style="background: {{ $houseColor }};"></span>
                    <div>
                        <h1 class="page-title" id="houseColorPreviewName">{{ old('name', $house->name) }}</h1>
                        <p class="page-subtitle mb-0">
                            <span class="summary-chip house-chip" id="houseColorPreviewCode"
                                style="--house-color: {{ $houseColor }}; --house-color-soft: {{ $house->colorWithAlpha(0.14) }};">
                                {{ strtoupper($houseColor) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="summary-chip-group">
                <span class="summary-chip pill-muted"><i class="fas fa-user-graduate"></i> {{ $house->students()->count() }} students</span>
                <span class="summary-chip pill-muted"><i class="fas fa-user-tie"></i> {{ $house->users()->count() }} users</span>
            </div>
        </div>

        @include('houses.partials.module-nav', ['current' => 'manager'])

        <div class="help-text">
            <div class="help-title">Update House Record</div>
            <div class="help-content">
                Update leadership assignments, adjust the house color used in reports, and keep the saved metadata aligned with the selected term.
            </div>
        </div>

        <form class="needs-validation" method="post" action="{{ route('house.update-house', $house->id) }}" novalidate data-house-form>
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="name">House Name <span class="required">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $house->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="year">Academic Year <span class="required">*</span></label>
                    <select name="year" id="year" class="form-select @error('year') is-invalid @enderror" required>
                        <option value="">Select year</option>
                        @for ($year = date('Y'); $year <= date('Y') + 3; $year++)
                            <option value="{{ $year }}"
                                {{ (string) old('year', $house->year) === (string) $year ? 'selected' : '' }}>
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
                        <input type="color" id="color_code_picker" value="{{ $houseColor }}">
                        <div>
                            <input type="text" name="color_code" id="color_code_text"
                                class="form-control @error('color_code') is-invalid @enderror"
                                value="{{ $houseColor }}" maxlength="7" required>
                            <div class="field-help">Stored as a hex value and used for list, detail, and chart accents.</div>
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
                            <option value="{{ $user->id }}"
                                {{ (string) old('head', $house->head) === (string) $user->id ? 'selected' : '' }}>
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
                            <option value="{{ $user->id }}"
                                {{ (string) old('assistant', $house->assistant) === (string) $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }}{{ $user->position ? ' - ' . $user->position : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('assistant')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="form-actions">
                <a href="{{ route('house.index') }}" class="btn btn-light">
                    <i class="bx bx-arrow-back"></i> Back
                </a>
                @if (!session('is_past_term'))
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Update House</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Updating...
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
                return (value || '#2563EB').trim().toUpperCase();
            }

            function syncPreview() {
                const color = normalizeColor(text.value);
                text.value = color;

                if (/^#[0-9A-F]{6}$/.test(color)) {
                    picker.value = color;
                    preview.style.background = color;
                    previewCode.textContent = color;
                    previewCode.style.setProperty('--house-color', color);
                    previewCode.style.setProperty('--house-color-soft', 'rgba(37, 99, 235, 0.14)');
                }

                previewName.textContent = nameInput.value.trim() || '{{ $house->name }}';
            }

            picker.addEventListener('input', function() {
                text.value = this.value.toUpperCase();
                syncPreview();
            });

            text.addEventListener('input', syncPreview);
            nameInput.addEventListener('input', syncPreview);
            syncPreview();
        });
    </script>
@endsection
