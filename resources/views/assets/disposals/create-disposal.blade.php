@extends('layouts.master')
@section('title')
    Record Asset Disposal
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
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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

        .warning-banner {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 3px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .warning-banner i {
            color: #b45309;
            font-size: 20px;
            flex-shrink: 0;
        }

        .warning-banner .warning-content {
            color: #92400e;
            font-size: 14px;
            line-height: 1.5;
        }

        .warning-banner .warning-content strong {
            color: #78350f;
        }

        .help-text {
            background: #f8f9fa;
            border-left: 4px solid #ef4444;
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
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .form-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .asset-info-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 20px;
            margin-top: 16px;
            display: none;
        }

        .asset-info-card.visible {
            display: block;
        }

        .asset-info-card h6 {
            color: #1e40af;
            margin-bottom: 12px;
            font-weight: 600;
            font-size: 14px;
        }

        .asset-info-card p {
            margin-bottom: 6px;
            font-size: 14px;
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

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
        }

        .btn-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
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
            Record Asset Disposal
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
            <h4><i class="bx bx-trash me-2"></i>Record Asset Disposal</h4>
            <p>Permanently dispose of an asset from the inventory</p>
        </div>

        <div class="disposal-body">
            <!-- Warning Banner -->
            <div class="warning-banner" id="warning-banner">
                <i class="bx bx-error-circle"></i>
                <div class="warning-content">
                    <strong>Warning:</strong> Recording a disposal will update the Total Value of Assets to be Disposed and the asset will no longer be available for assignment.
                </div>
            </div>

            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title"><i class="fas fa-info-circle me-2"></i>About Asset Disposal</div>
                <p class="help-content">
                    Use this form to record when an asset is being sold, scrapped, donated, or recycled.
                    The disposal will be permanently recorded and the asset status will be updated.
                </p>
            </div>

            <form action="{{ route('disposals.store') }}" method="POST" id="disposalForm">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-section">
                            <h6 class="form-section-title"><i class="bx bx-package me-2"></i>Asset Selection</h6>
                            <div class="mb-3">
                                <label for="asset_id" class="form-label">Select Asset <span class="text-danger">*</span></label>
                                @if(isset($asset))
                                    <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                            value="{{ $asset->name }} ({{ $asset->asset_code }})" disabled>
                                        <a href="{{ route('disposals.create') }}" class="btn btn-outline-secondary">
                                            <i class="bx bx-refresh"></i> Change
                                        </a>
                                    </div>
                                @else
                                    <select class="form-select" id="asset_id" name="asset_id" required>
                                        <option value="">-- Select an Asset --</option>
                                        @foreach($availableAssets as $availableAsset)
                                            <option value="{{ $availableAsset->id }}" {{ old('asset_id') == $availableAsset->id ? 'selected' : '' }}>
                                                {{ $availableAsset->name }} ({{ $availableAsset->asset_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            <!-- Asset Info Card -->
                            <div class="asset-info-card" id="asset-details">
                                <h6><i class="bx bx-info-circle me-2"></i>Asset Information</h6>
                                <p id="asset-name"></p>
                                <p id="asset-category"></p>
                                <p id="asset-status"></p>
                                <p id="asset-value"></p>
                            </div>
                        </div>

                        <div class="form-section">
                            <h6 class="form-section-title"><i class="bx bx-calendar me-2"></i>Disposal Details</h6>
                            <div class="mb-3">
                                <label for="disposal_date" class="form-label">Disposal Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="disposal_date"
                                    name="disposal_date" value="{{ old('disposal_date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="disposal_method" class="form-label">Method of Disposal <span class="text-danger">*</span></label>
                                <select class="form-select" id="disposal_method" name="disposal_method" required>
                                    <option value="">-- Select Method --</option>
                                    <option value="Sold" {{ old('disposal_method') == 'Sold' ? 'selected' : '' }}>Sold</option>
                                    <option value="Scrapped" {{ old('disposal_method') == 'Scrapped' ? 'selected' : '' }}>Scrapped</option>
                                    <option value="Donated" {{ old('disposal_method') == 'Donated' ? 'selected' : '' }}>Donated</option>
                                    <option value="Recycled" {{ old('disposal_method') == 'Recycled' ? 'selected' : '' }}>Recycled</option>
                                </select>
                            </div>

                            <div class="mb-3 sold-field" style="display: none;">
                                <label for="disposal_amount" class="form-label">Sale Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">BWP</span>
                                    <input type="number" step="0.01" class="form-control"
                                        id="disposal_amount" name="disposal_amount" value="{{ old('disposal_amount') }}" placeholder="0.00">
                                </div>
                            </div>

                            <div class="mb-3 donated-field" style="display: none;">
                                <label for="recipient" class="form-label">Recipient <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="recipient"
                                    name="recipient" value="{{ old('recipient') }}"
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
                                    rows="4" placeholder="Explain why this asset is being disposed..." required>{{ old('reason') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="notes" name="notes"
                                    rows="4" placeholder="Any additional information...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('disposals.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-danger btn-loading" id="submitBtn">
                        <span class="btn-text"><i class="bx bx-trash me-1"></i> Record Disposal</span>
                        <span class="btn-spinner">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Processing...
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
            const assetSelect = document.getElementById('asset_id');
            const assetDetails = document.getElementById('asset-details');
            const warningBanner = document.getElementById('warning-banner');

            // Asset data from server
            const assetData = {
                @if(isset($availableAssets))
                    @foreach($availableAssets as $asset)
                        "{{ $asset->id }}": {
                            name: "{{ $asset->name }}",
                            code: "{{ $asset->asset_code }}",
                            category: "{{ $asset->category->name ?? 'Uncategorized' }}",
                            status: "{{ $asset->status }}",
                            value: "{{ $asset->current_value ? 'P' . number_format($asset->current_value, 2) : 'N/A' }}",
                            purchase_date: "{{ $asset->purchase_date ? $asset->purchase_date->format('M d, Y') : 'N/A' }}"
                        },
                    @endforeach
                @endif
            };

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

            // Update asset details display
            function updateAssetDetails() {
                if (assetSelect) {
                    const selectedAssetId = assetSelect.value;

                    if (selectedAssetId && assetData[selectedAssetId]) {
                        const asset = assetData[selectedAssetId];

                        document.getElementById('asset-name').innerHTML = '<strong>Name:</strong> ' + asset.name + ' (' + asset.code + ')';
                        document.getElementById('asset-category').innerHTML = '<strong>Category:</strong> ' + asset.category;
                        document.getElementById('asset-status').innerHTML = '<strong>Status:</strong> ' + asset.status;
                        document.getElementById('asset-value').innerHTML = '<strong>Current Value:</strong> ' + asset.value;

                        assetDetails.classList.add('visible');
                    } else {
                        assetDetails.classList.remove('visible');
                    }
                }
            }

            // Update warning message based on disposal method
            function updateWarningMessage() {
                const method = disposalMethodSelect.value;
                let warningMessage = '<i class="bx bx-error-circle"></i><div class="warning-content"><strong>Warning:</strong> Recording a disposal will update the Total Value of Assets to be Disposed and the asset will no longer be available for assignment.</div>';

                if (method === 'Sold') {
                    warningMessage = '<i class="bx bx-error-circle"></i><div class="warning-content"><strong>Warning:</strong> When selling assets, ensure you follow proper financial procedures and record the sale amount accurately.</div>';
                } else if (method === 'Donated') {
                    warningMessage = '<i class="bx bx-error-circle"></i><div class="warning-content"><strong>Warning:</strong> For donated assets, make sure to record the recipient organization or individual for tracking purposes.</div>';
                }

                warningBanner.innerHTML = warningMessage;
            }

            // Initialize and add event listeners
            updateFields();
            disposalMethodSelect.addEventListener('change', function() {
                updateFields();
                updateWarningMessage();
            });

            if (assetSelect) {
                assetSelect.addEventListener('change', updateAssetDetails);
                if (assetSelect.value) {
                    updateAssetDetails();
                }
            }
        });
    </script>
@endsection
