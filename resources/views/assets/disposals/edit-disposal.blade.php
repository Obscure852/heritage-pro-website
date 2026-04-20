@extends('layouts.master')
@section('title')
    Edit Disposal Record
@endsection

@section('css')
    <style>
        .disposal-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .disposal-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .disposal-header h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .disposal-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .disposal-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            border-left: 4px solid #4e73df;
            padding: 16px 20px;
            margin-bottom: 24px;
            border-radius: 0 3px 3px 0;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            margin: 0;
            line-height: 1.5;
        }

        .asset-info-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .asset-info-card h6 {
            color: #1e40af;
            margin-bottom: 12px;
            font-weight: 600;
            font-size: 14px;
        }

        .asset-info-card .asset-name {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .asset-info-card .asset-code {
            font-size: 13px;
            color: #6b7280;
        }

        .asset-info-card .form-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 8px;
        }

        .form-section {
            margin-bottom: 24px;
        }

        .form-section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .btn-loading {
            position: relative;
        }

        .btn-loading .btn-text {
            display: inline-flex;
            align-items: center;
        }

        .btn-loading .btn-spinner {
            display: none;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex;
            align-items: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        @media (max-width: 768px) {
            .disposal-header {
                padding: 20px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('disposals.index') }}">Back</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('disposals.index') }}">Disposals</a>
        @endslot
        @slot('title')
            Edit Disposal
        @endslot
    @endcomponent

    @if (session('error'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('message'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    @endif

    <div class="disposal-container">
        <div class="disposal-header">
            <h4><i class="bx bx-edit me-2"></i>Edit Disposal Record</h4>
            <p>Update the details for this disposal record</p>
        </div>

        <div class="disposal-body">
            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title"><i class="fas fa-info-circle me-2"></i>Editing Disposal Record</div>
                <p class="help-content">
                    Update the disposal date, method, amount, or reason. Note that the asset cannot be changed
                    after the disposal has been recorded.
                </p>
            </div>

            <!-- Asset Info Card -->
            <div class="asset-info-card">
                <h6><i class="bx bx-package me-2"></i>Disposed Asset</h6>
                <div class="asset-name">{{ $disposal->asset->name }}</div>
                <div class="asset-code">{{ $disposal->asset->asset_code }}</div>
                <div class="form-text">Asset cannot be changed after disposal is recorded</div>
            </div>

            <form action="{{ route('disposals.update', $disposal->id) }}" method="POST" id="disposalForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-section">
                            <h6 class="form-section-title"><i class="bx bx-calendar me-2"></i>Disposal Details</h6>
                            <div class="mb-3">
                                <label for="disposal_date" class="form-label">Disposal Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="disposal_date"
                                    name="disposal_date" value="{{ old('disposal_date', $disposal->disposal_date->format('Y-m-d')) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="disposal_method" class="form-label">Disposal Method <span class="text-danger">*</span></label>
                                <select class="form-select" id="disposal_method" name="disposal_method" required>
                                    <option value="">-- Select Method --</option>
                                    <option value="Sold" {{ old('disposal_method', $disposal->disposal_method) == 'Sold' ? 'selected' : '' }}>Sold</option>
                                    <option value="Scrapped" {{ old('disposal_method', $disposal->disposal_method) == 'Scrapped' ? 'selected' : '' }}>Scrapped</option>
                                    <option value="Donated" {{ old('disposal_method', $disposal->disposal_method) == 'Donated' ? 'selected' : '' }}>Donated</option>
                                    <option value="Recycled" {{ old('disposal_method', $disposal->disposal_method) == 'Recycled' ? 'selected' : '' }}>Recycled</option>
                                </select>
                            </div>

                            <div class="mb-3 sold-field" style="display: none;">
                                <label for="disposal_amount" class="form-label">Sale Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control"
                                        id="disposal_amount" name="disposal_amount" value="{{ old('disposal_amount', $disposal->disposal_amount) }}" placeholder="0.00">
                                </div>
                            </div>

                            <div class="mb-3 donated-field" style="display: none;">
                                <label for="recipient" class="form-label">Recipient <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="recipient"
                                    name="recipient" value="{{ old('recipient', $disposal->recipient) }}"
                                    placeholder="Person or organization receiving the asset">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-section">
                            <h6 class="form-section-title"><i class="bx bx-note me-2"></i>Reason & Notes</h6>
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for Disposal <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="reason" name="reason"
                                    rows="4" placeholder="Explain why this asset was disposed..." required>{{ old('reason', $disposal->reason) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="notes" name="notes"
                                    rows="4" placeholder="Any additional information...">{{ old('notes', $disposal->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('disposals.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-loading" id="submitBtn">
                        <span class="btn-text"><i class="bx bx-save me-1"></i> Update Disposal Record</span>
                        <span class="btn-spinner">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Updating...
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
            const form = document.getElementById('disposalForm');
            const submitBtn = document.getElementById('submitBtn');
            const disposalMethodSelect = document.getElementById('disposal_method');
            const disposalAmountField = document.querySelector('.sold-field');
            const recipientField = document.querySelector('.donated-field');

            // Form submission loading state
            form.addEventListener('submit', function() {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });

            // Update conditional fields based on disposal method
            function updateFields() {
                const method = disposalMethodSelect.value;

                disposalAmountField.style.display = 'none';
                recipientField.style.display = 'none';

                if (method === 'Sold') {
                    disposalAmountField.style.display = 'block';
                    document.getElementById('disposal_amount').setAttribute('required', 'required');
                } else {
                    document.getElementById('disposal_amount').removeAttribute('required');
                }

                if (method === 'Donated') {
                    recipientField.style.display = 'block';
                    document.getElementById('recipient').setAttribute('required', 'required');
                } else {
                    document.getElementById('recipient').removeAttribute('required');
                }
            }

            // Initialize
            updateFields();
            disposalMethodSelect.addEventListener('change', updateFields);
        });
    </script>
@endsection
