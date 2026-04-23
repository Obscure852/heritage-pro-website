@extends('layouts.crm')

@section('title', $leaveType ? 'Edit Leave Type' : 'Create Leave Type')
@section('crm_heading', $leaveType ? 'Edit Leave Type' : 'Create Leave Type')
@section('crm_subheading', $leaveType ? 'Update ' . $leaveType->name . ' configuration.' : 'Add a new leave type for employees.')

@section('crm_actions')
    <a href="{{ route('crm.leave.types.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to leave types
    </a>
@endsection

@push('head')
    <style>
        .lt-color-field {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .lt-color-swatch {
            width: 42px;
            height: 42px;
            border-radius: 3px;
            border: 1px solid #d1d5db;
            padding: 2px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .lt-color-swatch::-webkit-color-swatch-wrapper {
            padding: 0;
        }

        .lt-color-swatch::-webkit-color-swatch {
            border: 0;
            border-radius: 2px;
        }

        .lt-color-hex {
            font-family: 'SFMono-Regular', Consolas, monospace;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
        }

        .lt-preview-strip {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
        }

        .lt-preview-dot {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            flex-shrink: 0;
        }

        .lt-preview-name {
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
        }

        .lt-preview-code {
            font-size: 12px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 3px;
            margin-left: auto;
        }

        .lt-toggles-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .lt-section-divider {
            border: 0;
            border-top: 1px solid #e5e7eb;
            margin: 24px 0;
        }

        .lt-section-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin: 0 0 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .lt-section-label i {
            font-size: 16px;
            color: #6b7280;
        }

        .lt-attachment-conditional {
            margin-top: 10px;
            padding: 14px 16px;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 3px;
            display: none;
        }

        .lt-attachment-conditional.is-visible {
            display: block;
        }

        @media (max-width: 767.98px) {
            .lt-toggles-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="crm-stack">
        @if ($leaveType)
            <div class="lt-preview-strip" id="lt-preview">
                <span class="lt-preview-dot" id="lt-preview-dot" style="background: {{ old('color', $leaveType->color ?? '#299cdb') }};"></span>
                <span class="lt-preview-name" id="lt-preview-name">{{ old('name', $leaveType->name ?? 'New Leave Type') }}</span>
                <span class="lt-preview-code" id="lt-preview-code" style="background: {{ old('color', $leaveType->color ?? '#299cdb') }}20; color: {{ old('color', $leaveType->color ?? '#299cdb') }};">{{ old('code', $leaveType->code ?? '—') }}</span>
            </div>
        @endif

        <form method="POST"
              action="{{ $leaveType ? route('crm.leave.types.update', $leaveType) : route('crm.leave.types.store') }}"
              class="crm-form"
              id="type-form">
            @csrf
            @if ($leaveType) @method('PUT') @endif

            {{-- Identity --}}
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Identity</p>
                        <h2>Basic information</h2>
                    </div>
                </div>

                <div class="crm-field-grid">
                    <div class="crm-field">
                        <label for="name">Name <span class="text-danger">*</span></label>
                        <input id="name" name="name" value="{{ old('name', $leaveType?->name) }}" required maxlength="100" placeholder="e.g. Annual Leave">
                        @error('name') <div class="crm-field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="crm-field">
                        <label for="code">Code <span class="text-danger">*</span></label>
                        <input id="code" name="code" value="{{ old('code', $leaveType?->code) }}" required maxlength="10" placeholder="e.g. AL" style="text-transform: uppercase;">
                        @error('code') <div class="crm-field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="crm-field">
                        <label for="gender_restriction">Gender restriction</label>
                        <select id="gender_restriction" name="gender_restriction">
                            <option value="" @selected(old('gender_restriction', $leaveType?->gender_restriction) === null)>None — available to all</option>
                            <option value="female" @selected(old('gender_restriction', $leaveType?->gender_restriction) === 'female')>Female only</option>
                            <option value="male" @selected(old('gender_restriction', $leaveType?->gender_restriction) === 'male')>Male only</option>
                        </select>
                    </div>

                    <div class="crm-field">
                        <label for="sort_order">Sort order</label>
                        <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $leaveType?->sort_order ?? 0) }}">
                    </div>

                    <div class="crm-field">
                        <label for="color">Color <span class="text-danger">*</span></label>
                        <div class="lt-color-field">
                            <input type="color" id="color" name="color" value="{{ old('color', $leaveType?->color ?? '#299cdb') }}" class="lt-color-swatch">
                            <span id="color-hex" class="lt-color-hex">{{ old('color', $leaveType?->color ?? '#299cdb') }}</span>
                        </div>
                        @error('color') <div class="crm-field-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </section>

            {{-- Entitlement & Limits --}}
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Entitlement</p>
                        <h2>Days and limits</h2>
                    </div>
                </div>

                <div class="crm-field-grid">
                    <div class="crm-field">
                        <label for="default_days_per_year">Days per year</label>
                        <input id="default_days_per_year" name="default_days_per_year" type="number" step="0.5" min="0" max="365" value="{{ old('default_days_per_year', $leaveType?->default_days_per_year) }}" placeholder="Leave blank for unlimited">
                        <span class="crm-muted-copy">Annual entitlement. Leave blank for unlimited types like unpaid leave.</span>
                        @error('default_days_per_year') <div class="crm-field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="crm-field">
                        <label for="carry_over_limit">Carry-over limit (days)</label>
                        <input id="carry_over_limit" name="carry_over_limit" type="number" step="0.5" min="0" value="{{ old('carry_over_limit', $leaveType?->carry_over_limit) }}" placeholder="No carry-over">
                        <span class="crm-muted-copy">Max unused days that roll into the next year.</span>
                    </div>

                    <div class="crm-field">
                        <label for="max_consecutive_days">Max consecutive days</label>
                        <input id="max_consecutive_days" name="max_consecutive_days" type="number" min="1" value="{{ old('max_consecutive_days', $leaveType?->max_consecutive_days) }}" placeholder="No limit">
                        <span class="crm-muted-copy">Cap on a single leave request duration.</span>
                    </div>

                    <div class="crm-field">
                        <label for="min_notice_days">Min notice days</label>
                        <input id="min_notice_days" name="min_notice_days" type="number" min="0" value="{{ old('min_notice_days', $leaveType?->min_notice_days ?? 0) }}">
                        <span class="crm-muted-copy">How many days in advance the request must be submitted.</span>
                    </div>
                </div>
            </section>

            {{-- Behaviour Toggles --}}
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Behaviour</p>
                        <h2>Toggles and attachments</h2>
                    </div>
                </div>

                <p class="lt-section-label"><i class="bx bx-toggle-left"></i> Leave behaviour</p>

                <div class="lt-toggles-grid">
                    <label class="crm-check">
                        <input type="hidden" name="allow_half_day" value="0">
                        <input type="checkbox" name="allow_half_day" value="1" @checked(old('allow_half_day', $leaveType?->allow_half_day ?? true))>
                        <span>Allow half-day requests</span>
                    </label>

                    <label class="crm-check">
                        <input type="hidden" name="is_paid" value="0">
                        <input type="checkbox" name="is_paid" value="1" @checked(old('is_paid', $leaveType?->is_paid ?? true))>
                        <span>Paid leave</span>
                    </label>

                    <label class="crm-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $leaveType?->is_active ?? true))>
                        <span>Active</span>
                    </label>

                    <label class="crm-check" id="attachment-toggle">
                        <input type="hidden" name="requires_attachment" value="0">
                        <input type="checkbox" name="requires_attachment" value="1" id="requires_attachment_check" @checked(old('requires_attachment', $leaveType?->requires_attachment ?? false))>
                        <span>Requires attachment</span>
                    </label>
                </div>

                <div class="lt-attachment-conditional {{ old('requires_attachment', $leaveType?->requires_attachment ?? false) ? 'is-visible' : '' }}" id="attachment-days-section">
                    <div class="crm-field" style="margin: 0;">
                        <label for="attachment_required_after_days">Only require attachment after (days)</label>
                        <input id="attachment_required_after_days" name="attachment_required_after_days" type="number" min="1" value="{{ old('attachment_required_after_days', $leaveType?->attachment_required_after_days) }}" placeholder="Always required">
                        <span class="crm-muted-copy">Leave blank to always require an attachment. Set a number to only require after that many consecutive days.</span>
                    </div>
                </div>
            </section>

            {{-- Actions --}}
            <div class="form-actions">
                <a href="{{ route('crm.leave.types.index') }}" class="btn btn-light crm-btn-light">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> {{ $leaveType ? 'Save leave type' : 'Create leave type' }}</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('type-form');
    var colorInput = document.getElementById('color');
    var colorHex = document.getElementById('color-hex');
    var attachCheck = document.getElementById('requires_attachment_check');
    var attachSection = document.getElementById('attachment-days-section');
    var previewDot = document.getElementById('lt-preview-dot');
    var previewName = document.getElementById('lt-preview-name');
    var previewCode = document.getElementById('lt-preview-code');
    var nameInput = document.getElementById('name');
    var codeInput = document.getElementById('code');

    // Color sync
    colorInput.addEventListener('input', function () {
        colorHex.textContent = colorInput.value;
        updatePreview();
    });

    // Attachment toggle
    attachCheck.addEventListener('change', function () {
        attachSection.classList.toggle('is-visible', attachCheck.checked);
    });

    // Live preview
    function updatePreview() {
        if (!previewDot) return;
        var c = colorInput.value;
        previewDot.style.background = c;
        previewCode.style.background = c + '20';
        previewCode.style.color = c;
        previewName.textContent = nameInput.value || 'New Leave Type';
        previewCode.textContent = codeInput.value.toUpperCase() || '—';
    }

    if (nameInput) nameInput.addEventListener('input', updatePreview);
    if (codeInput) codeInput.addEventListener('input', updatePreview);

    // Submit loading
    form.addEventListener('submit', function () {
        var btn = form.querySelector('button[type="submit"].btn-loading');
        if (btn) {
            btn.classList.add('loading');
            btn.disabled = true;
        }
    });
});
</script>
@endpush
