@extends('layouts.master-layouts')

@section('title', 'Document Settings')

@section('css')
<style>
    .form-container {
        background: white;
        border-radius: 3px;
        padding: 32px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .settings-section {
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 1px solid #f3f4f6;
    }
    .settings-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .section-title {
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 16px 0;
        color: #1f2937;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-title i {
        color: #4e73df;
    }

    .help-text {
        background: #f8f9fa;
        padding: 12px;
        border-left: 4px solid #3b82f6;
        border-radius: 0 3px 3px 0;
        margin-bottom: 20px;
    }
    .help-text .help-content {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.4;
    }

    .form-control:focus,
    .form-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 16px;
        margin-top: 16px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 10px 20px;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
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

    .extension-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
    }
    @media (max-width: 768px) {
        .extension-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    .extension-grid label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #374151;
        padding: 6px 8px;
        background: #f9fafb;
        border-radius: 3px;
        cursor: pointer;
        margin-bottom: 0;
    }
    .extension-grid label:hover {
        background: #f3f4f6;
    }

    .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
    }
    .form-switch .form-check-label {
        padding-top: 2px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Gradient header --}}
            <div style="background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%); color: white; padding: 28px; border-radius: 3px 3px 0 0;">
                <h4 style="margin: 0; font-weight: 600;"><i class="fas fa-cog"></i> Document Settings</h4>
                <p style="margin: 4px 0 0; opacity: 0.85; font-size: 14px;">Configure system-wide document management settings</p>
            </div>

            <div class="form-container" style="border-radius: 0 0 3px 3px;">

                {{-- Section 1: Storage Quotas --}}
                <div class="settings-section">
                    <div class="section-title"><i class="fas fa-hdd"></i> Storage Quotas</div>
                    <div class="help-text">
                        <div class="help-content">Configure default storage quotas for users. Changes apply to newly created quotas.</div>
                    </div>
                    <form class="settings-form" action="{{ route('documents.settings.update', 'quotas') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="default_mb">Default Quota (MB)</label>
                                <input type="number" class="form-control" id="default_mb" name="default_mb" value="{{ $quotas['default_mb'] }}" min="50" max="50000" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="admin_mb">Admin Quota (MB)</label>
                                <input type="number" class="form-control" id="admin_mb" name="admin_mb" value="{{ $quotas['admin_mb'] }}" min="100" max="100000" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="warning_threshold_percent">Warning Threshold (%)</label>
                                <input type="number" class="form-control" id="warning_threshold_percent" name="warning_threshold_percent" value="{{ $quotas['warning_threshold_percent'] }}" min="50" max="99" required>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save"></i> Save Quotas</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Section 2: Retention --}}
                <div class="settings-section">
                    <div class="section-title"><i class="fas fa-calendar-alt"></i> Document Retention</div>
                    <div class="help-text">
                        <div class="help-content">Set default retention periods and grace periods for document lifecycle.</div>
                    </div>
                    <form class="settings-form" action="{{ route('documents.settings.update', 'retention') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="default_days">Default Retention (days)</label>
                                <input type="number" class="form-control" id="default_days" name="default_days" value="{{ $retention['default_days'] }}" min="30" max="36500" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="grace_period_days">Grace Period (days)</label>
                                <input type="number" class="form-control" id="grace_period_days" name="grace_period_days" value="{{ $retention['grace_period_days'] }}" min="1" max="365" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="trash_retention_days">Trash Retention (days)</label>
                                <input type="number" class="form-control" id="trash_retention_days" name="trash_retention_days" value="{{ $retention['trash_retention_days'] }}" min="1" max="365" required>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save"></i> Save Retention</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Section 3: Upload Limits --}}
                <div class="settings-section">
                    <div class="section-title"><i class="fas fa-upload"></i> Upload Limits</div>
                    <div class="help-text">
                        <div class="help-content">Configure maximum file size and allowed file types.</div>
                    </div>
                    <form class="settings-form" action="{{ route('documents.settings.update', 'uploads') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="max_file_size_mb">Max File Size (MB)</label>
                            <input type="number" class="form-control" id="max_file_size_mb" name="max_file_size_mb" value="{{ $uploads['max_file_size_mb'] }}" min="1" max="500" required style="max-width: 200px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Allowed Extensions</label>
                            <div class="extension-grid">
                                @foreach($allExtensions as $ext)
                                <label>
                                    <input type="checkbox" name="allowed_extensions[]" value="{{ $ext }}" {{ in_array($ext, $uploads['allowed_extensions']) ? 'checked' : '' }}>
                                    .{{ $ext }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save"></i> Save Upload Limits</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Section 4: Approval Workflow --}}
                <div class="settings-section">
                    <div class="section-title"><i class="fas fa-check-circle"></i> Approval Workflow</div>
                    <div class="help-text">
                        <div class="help-content">Configure whether documents require approval before publication.</div>
                    </div>
                    <form class="settings-form" action="{{ route('documents.settings.update', 'approval') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input type="hidden" name="require_approval" value="0">
                                    <input class="form-check-input" type="checkbox" id="require_approval" name="require_approval" value="1" {{ $approval['require_approval'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="require_approval">Require Approval Before Publishing</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="review_deadline_days">Review Deadline (days)</label>
                                <input type="number" class="form-control" id="review_deadline_days" name="review_deadline_days" value="{{ $approval['review_deadline_days'] }}" min="1" max="90" required style="max-width: 200px;">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save"></i> Save Workflow</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.querySelectorAll('.settings-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = form.querySelector('button[type="submit"].btn-loading');
            if (btn) {
                btn.classList.add('loading');
                btn.disabled = true;
            }

            $.ajax({
                url: form.action,
                method: 'POST',
                data: $(form).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: response.message || 'Settings updated successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    var msg = 'Failed to save settings.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', msg, 'error');
                },
                complete: function() {
                    if (btn) {
                        btn.classList.remove('loading');
                        btn.disabled = false;
                    }
                }
            });
        });
    });
</script>
@endsection
