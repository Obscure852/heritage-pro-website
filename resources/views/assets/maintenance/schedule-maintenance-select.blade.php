@extends('layouts.master')
@section('title')
    Schedule Asset Maintenance
@endsection

@section('css')
    <style>
        .maintenance-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .maintenance-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .maintenance-header h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .maintenance-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .maintenance-body {
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

        .asset-select-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .asset-select-section label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
            display: block;
        }

        .asset-details-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 24px;
            display: none;
        }

        .asset-details-card.visible {
            display: block;
        }

        .asset-details-card h6 {
            color: #1e40af;
            margin-bottom: 16px;
            font-weight: 600;
            font-size: 14px;
        }

        .asset-details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .asset-details-item {
            font-size: 14px;
        }

        .asset-details-item strong {
            color: #374151;
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

        .status-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-badge.available { background: #d1fae5; color: #065f46; }
        .status-badge.assigned { background: #dbeafe; color: #1e40af; }
        .status-badge.maintenance { background: #fef3c7; color: #b45309; }
        .status-badge.disposed { background: #fee2e2; color: #991b1b; }

        .warranty-badge {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
            margin-left: 8px;
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

        .status-warning {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 3px;
            padding: 12px 16px;
            margin-top: 12px;
            font-size: 13px;
            color: #92400e;
        }

        .maintenance-warning {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 3px;
            padding: 12px 16px;
            margin-top: 16px;
            font-size: 13px;
            color: #92400e;
        }

        @media (max-width: 768px) {
            .maintenance-header {
                padding: 20px;
            }

            .asset-details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.maintenance.index') }}">Back</a>
        @endslot
        @slot('title')
            Schedule Asset Maintenance
        @endslot
    @endcomponent

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

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    @endif

    <div class="maintenance-container">
        <div class="maintenance-header">
            <h4><i class="bx bx-wrench me-2"></i>Schedule Asset Maintenance</h4>
            <p>Select an asset and create a new maintenance record</p>
        </div>

        <div class="maintenance-body">
            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title"><i class="fas fa-info-circle me-2"></i>Scheduling Maintenance</div>
                <p class="help-content">
                    Select an asset from the dropdown below to view its details, then fill in the maintenance information.
                    You can schedule preventive maintenance, corrective repairs, or upgrades for any asset in your inventory.
                </p>
            </div>

            <form action="{{ route('assets.store-asset-maintenance') }}" method="POST" id="maintenanceForm">
                @csrf

                <!-- Asset Selection -->
                <div class="asset-select-section">
                    <label for="asset_id"><i class="bx bx-package me-2"></i>Select Asset <span class="text-danger">*</span></label>
                    <select class="form-select" id="asset_id" name="asset_id" required>
                        <option value="">-- Select an Asset --</option>
                        @foreach ($assets as $asset)
                            <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                {{ $asset->name }} ({{ $asset->asset_code }}) -
                                {{ $asset->category->name ?? 'Uncategorized' }} -
                                @if ($asset->status == 'Available')
                                    Available
                                @elseif($asset->status == 'Assigned')
                                    Assigned
                                @elseif($asset->status == 'In Maintenance')
                                    In Maintenance
                                @elseif($asset->status == 'Disposed')
                                    Disposed
                                @else
                                    {{ $asset->status }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Asset Details Card (shown when asset is selected) -->
                <div class="asset-details-card" id="asset-details">
                    <h6><i class="bx bx-info-circle me-2"></i>Asset Information</h6>
                    <div class="asset-details-grid">
                        <div class="asset-details-item">
                            <strong>Asset:</strong> <span id="asset-name"></span>
                        </div>
                        <div class="asset-details-item">
                            <strong>Category:</strong> <span id="asset-category"></span>
                        </div>
                        <div class="asset-details-item">
                            <strong>Status:</strong> <span id="asset-status"></span>
                        </div>
                        <div class="asset-details-item">
                            <strong>Condition:</strong> <span id="asset-condition"></span>
                        </div>
                        <div class="asset-details-item">
                            <strong>Purchase Date:</strong> <span id="asset-purchase-date"></span>
                        </div>
                        <div class="asset-details-item">
                            <strong>Warranty Expiry:</strong> <span id="asset-warranty"></span>
                        </div>
                    </div>
                    <div id="asset-maintenance-warning"></div>
                </div>

                <div class="form-section">
                    <h6 class="form-section-title"><i class="bx bx-cog me-2"></i>Maintenance Details</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="maintenance_type" class="form-label">Maintenance Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="maintenance_type" name="maintenance_type" required>
                                <option value="Preventive" {{ old('maintenance_type') == 'Preventive' ? 'selected' : '' }}>Preventive (Regular)</option>
                                <option value="Corrective" {{ old('maintenance_type') == 'Corrective' ? 'selected' : '' }}>Corrective (Repair)</option>
                                <option value="Upgrade" {{ old('maintenance_type') == 'Upgrade' ? 'selected' : '' }}>Upgrade/Enhancement</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Scheduled" {{ old('status', 'Scheduled') == 'Scheduled' ? 'selected' : '' }}>Scheduled (Future)</option>
                                <option value="In Progress" {{ old('status') == 'In Progress' ? 'selected' : '' }}>In Progress (Currently Under Maintenance)</option>
                                <option value="Completed" {{ old('status') == 'Completed' ? 'selected' : '' }}>Completed (Already Done)</option>
                            </select>
                            <div id="status-warning-container"></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h6 class="form-section-title"><i class="bx bx-calendar me-2"></i>Schedule</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="maintenance_date" class="form-label">Maintenance Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="maintenance_date" name="maintenance_date"
                                value="{{ old('maintenance_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="next_maintenance_date" class="form-label">Next Maintenance Date</label>
                            <input type="date" class="form-control" id="next_maintenance_date" name="next_maintenance_date"
                                value="{{ old('next_maintenance_date') }}">
                            <div class="form-text">Leave blank if not applicable</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h6 class="form-section-title"><i class="bx bx-store me-2"></i>Business Contact & Cost</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="contact_id" class="form-label">Business Contact / Service Provider</label>
                            <select class="form-select" id="contact_id" name="contact_id">
                                <option value="">In-house Maintenance</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ old('contact_id') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }}{{ $vendor->primary_person_label ? ' - ' . $vendor->primary_person_label : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="cost" class="form-label">Estimated Cost</label>
                            <input type="number" step="0.01" class="form-control" id="cost" name="cost"
                                placeholder="0.00" value="{{ old('cost') }}">
                            <div class="form-text">Leave blank if unknown</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h6 class="form-section-title"><i class="bx bx-note me-2"></i>Description</h6>
                    <div class="mb-3">
                        <label for="description" class="form-label">Maintenance Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="4"
                            placeholder="Describe what needs to be done or what was done..." required>{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('assets.maintenance.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-loading" id="submitBtn">
                        <span class="btn-text"><i class="fas fa-save"></i> Save Maintenance Record</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
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
            const form = document.getElementById('maintenanceForm');
            const submitBtn = document.getElementById('submitBtn');
            const assetSelect = document.getElementById('asset_id');
            const assetDetails = document.getElementById('asset-details');
            const maintenanceTypeSelect = document.getElementById('maintenance_type');
            const maintenanceDateInput = document.getElementById('maintenance_date');
            const nextMaintenanceDateInput = document.getElementById('next_maintenance_date');
            const statusSelect = document.getElementById('status');
            const statusWarningContainer = document.getElementById('status-warning-container');

            // Asset data from server
            const assetsData = {
                @foreach ($assets as $asset)
                    "{{ $asset->id }}": {
                        name: "{{ $asset->name }} ({{ $asset->asset_code }})",
                        category: "{{ $asset->category->name ?? 'N/A' }}",
                        status: "{{ $asset->status }}",
                        statusClass: "{{ $asset->status == 'Available' ? 'available' : ($asset->status == 'Assigned' ? 'assigned' : ($asset->status == 'In Maintenance' ? 'maintenance' : ($asset->status == 'Disposed' ? 'disposed' : ''))) }}",
                        condition: "{{ $asset->condition }}",
                        purchaseDate: "{{ $asset->purchase_date ? $asset->purchase_date->format('M d, Y') : 'N/A' }}",
                        warrantyExpiry: "{{ $asset->warranty_expiry ? $asset->warranty_expiry->format('M d, Y') : 'Not specified' }}",
                        warrantyExpired: {{ $asset->warranty_expiry && $asset->warranty_expiry->isPast() ? 'true' : 'false' }}
                    },
                @endforeach
            };

            // Form submission loading state
            form.addEventListener('submit', function() {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });

            // Update asset details when selection changes
            function updateAssetDetails() {
                const selectedAssetId = assetSelect.value;

                if (selectedAssetId && assetsData[selectedAssetId]) {
                    const asset = assetsData[selectedAssetId];

                    document.getElementById('asset-name').textContent = asset.name;
                    document.getElementById('asset-category').textContent = asset.category;

                    const statusSpan = document.getElementById('asset-status');
                    statusSpan.innerHTML = `<span class="status-badge ${asset.statusClass}">${asset.status}</span>`;

                    document.getElementById('asset-condition').textContent = asset.condition;
                    document.getElementById('asset-purchase-date').textContent = asset.purchaseDate;

                    const warrantySpan = document.getElementById('asset-warranty');
                    if (asset.warrantyExpired) {
                        warrantySpan.innerHTML = `${asset.warrantyExpiry} <span class="warranty-badge">Expired</span>`;
                    } else {
                        warrantySpan.textContent = asset.warrantyExpiry;
                    }

                    assetDetails.classList.add('visible');

                    // Show warning if asset is already in maintenance
                    const maintenanceWarning = document.getElementById('asset-maintenance-warning');
                    if (asset.status === 'In Maintenance') {
                        maintenanceWarning.innerHTML = `
                            <div class="maintenance-warning">
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Note:</strong> This asset is already under maintenance. Adding another maintenance record will not change its status.
                            </div>
                        `;
                    } else {
                        maintenanceWarning.innerHTML = '';
                    }
                } else {
                    assetDetails.classList.remove('visible');
                }
            }

            assetSelect.addEventListener('change', updateAssetDetails);

            // Check if asset is pre-selected
            if (assetSelect.value) {
                updateAssetDetails();
            }

            // Auto-suggest next maintenance date for preventive maintenance
            maintenanceTypeSelect.addEventListener('change', function() {
                if (this.value === 'Preventive' && nextMaintenanceDateInput.value === '') {
                    const maintenanceDate = new Date(maintenanceDateInput.value);
                    if (!isNaN(maintenanceDate)) {
                        const nextDate = new Date(maintenanceDate);
                        nextDate.setMonth(nextDate.getMonth() + 6);
                        const year = nextDate.getFullYear();
                        const month = String(nextDate.getMonth() + 1).padStart(2, '0');
                        const day = String(nextDate.getDate()).padStart(2, '0');
                        nextMaintenanceDateInput.value = `${year}-${month}-${day}`;
                    }
                }
            });

            // Show warning when selecting "In Progress" status
            statusSelect.addEventListener('change', function() {
                if (this.value === 'In Progress') {
                    statusWarningContainer.innerHTML = `
                        <div class="status-warning">
                            <i class="bx bx-info-circle me-1"></i>
                            <strong>Note:</strong> Setting status to "In Progress" will mark the asset as "In Maintenance" and make it unavailable for assignment.
                        </div>
                    `;
                } else {
                    statusWarningContainer.innerHTML = '';
                }
            });
        });
    </script>
@endsection
