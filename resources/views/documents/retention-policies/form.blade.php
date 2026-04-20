@extends('layouts.master')
@section('title') {{ $policy ? 'Edit' : 'Create' }} Retention Policy @endsection
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
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (max-width: 576px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 0;
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

        .action-option {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-option:hover {
            border-color: #3b82f6;
            background: #f8f9ff;
        }

        .action-option input[type="radio"] {
            margin-top: 3px;
        }

        .action-option .action-label {
            font-weight: 500;
            color: #374151;
        }

        .action-option .action-desc {
            font-size: 13px;
            color: #6b7280;
            margin-top: 2px;
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
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('documents.index') }}">Documents</a>
        @endslot
        @slot('li_2')
            <a class="text-muted font-size-14" href="{{ route('documents.settings', ['tab' => 'retention']) }}">Retention Policies</a>
        @endslot
        @slot('title')
            {{ $policy ? 'Edit' : 'Create' }} Retention Policy
        @endslot
    @endcomponent

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
            <h1 class="page-title">{{ $policy ? 'Edit' : 'Create' }} Retention Policy</h1>
        </div>

        <div class="help-text">
            <div class="help-title">{{ $policy ? 'Update Retention Policy' : 'Define Document Lifecycle Rules' }}</div>
            <div class="help-content">
                {{ $policy ? 'Update the retention policy settings below. Changes will apply to all documents matching this policy.' : 'Create automated document lifecycle rules. Documents matching the conditions will be subject to the configured retention period and action. Fields marked with' }}
                @if(!$policy) <span class="text-danger">*</span> are required. @endif
            </div>
        </div>

        <form id="policyForm"
              action="{{ $policy ? route('documents.retention-policies.update', $policy) : route('documents.retention-policies.store') }}"
              method="POST" class="needs-validation" novalidate>
            @csrf
            @if ($policy)
                @method('PUT')
            @endif

            <h3 class="section-title">Policy Details</h3>

            <div class="form-grid">
                <div class="form-group">
                    <label for="name" class="form-label">Policy Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                           value="{{ old('name', $policy->name ?? '') }}" required maxlength="255"
                           placeholder="e.g. Financial Records Retention">
                    <small class="text-muted">A descriptive name to identify this retention policy</small>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="category_id" class="form-label">Category</label>
                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('category_id', $policy->conditions['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Leave blank to apply this policy to all document categories</small>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid" style="margin-top: 16px;">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3"
                              maxlength="1000" placeholder="Describe the purpose and scope of this retention policy...">{{ old('description', $policy->description ?? '') }}</textarea>
                    <small class="text-muted">Optional description explaining what this policy covers and why</small>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h3 class="section-title">Retention Settings</h3>

            <div class="help-text">
                <div class="help-title">How retention works</div>
                <div class="help-content">
                    Documents matching the conditions above will be subject to this policy.
                    After the retention period expires, the grace period gives owners time to renew before the action is executed.
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="retention_days" class="form-label">Retention Period (days) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('retention_days') is-invalid @enderror" id="retention_days" name="retention_days"
                           value="{{ old('retention_days', $policy->retention_days ?? 2555) }}" required min="1"
                           placeholder="e.g. 2555 (approx. 7 years)">
                    <small class="text-muted">Documents older than this number of days will trigger the policy action</small>
                    @error('retention_days')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="grace_period_days" class="form-label">Grace Period (days) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('grace_period_days') is-invalid @enderror" id="grace_period_days" name="grace_period_days"
                           value="{{ old('grace_period_days', $policy->grace_period_days ?? 30) }}" required min="0"
                           placeholder="e.g. 30">
                    <small class="text-muted">Days owners have to renew before the action is executed</small>
                    @error('grace_period_days')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h3 class="section-title">Policy Action</h3>

            <label class="action-option">
                <input type="radio" name="action" value="archive"
                    {{ old('action', $policy->action ?? 'archive') === 'archive' ? 'checked' : '' }}>
                <div>
                    <div class="action-label">Archive</div>
                    <div class="action-desc">Move documents to archived status. They remain accessible but are no longer active.</div>
                </div>
            </label>

            <label class="action-option">
                <input type="radio" name="action" value="delete"
                    {{ old('action', $policy->action ?? '') === 'delete' ? 'checked' : '' }}>
                <div>
                    <div class="action-label">Delete</div>
                    <div class="action-desc">Soft-delete documents. They can be recovered from trash within 30 days.</div>
                </div>
            </label>

            <label class="action-option">
                <input type="radio" name="action" value="notify_owner"
                    {{ old('action', $policy->action ?? '') === 'notify_owner' ? 'checked' : '' }}>
                <div>
                    <div class="action-label">Notify Owner</div>
                    <div class="action-desc">Send a notification to the document owner. No automated action is taken.</div>
                </div>
            </label>

            <div class="mt-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active', $policy->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Active — enable this policy for automated processing
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('documents.settings', ['tab' => 'retention']) }}">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> {{ $policy ? 'Update Policy' : 'Create Policy' }}</span>
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
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('policyForm');

            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();

                    var firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    var submitBtn = form.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                }

                form.classList.add('was-validated');
            }, false);

            // Auto-dismiss error alerts
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    var btn = alert.querySelector('.btn-close');
                    if (btn) btn.click();
                }, 5000);
            });
        });
    </script>
@endsection
