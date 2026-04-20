@extends('layouts.master')
@section('title')
    Edit Maintenance: {{ $maintenance->asset->name ?? 'Asset' }}
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

        .asset-info-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .asset-info-card h6 {
            color: #1e40af;
            margin-bottom: 16px;
            font-weight: 600;
            font-size: 14px;
        }

        .asset-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .asset-info-item {
            font-size: 14px;
        }

        .asset-info-item strong {
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

        .record-id-badge {
            background: #e5e7eb;
            color: #374151;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
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

        .results-section {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 3px;
            padding: 16px;
            margin-top: 16px;
        }

        @media (max-width: 768px) {
            .maintenance-header {
                padding: 20px;
            }

            .asset-info-grid {
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
            Edit Maintenance Record
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
            <h4><i class="bx bx-edit me-2"></i>Edit Maintenance Record</h4>
            <p>Update maintenance details for {{ $maintenance->asset->name ?? 'Asset' }}</p>
        </div>

        <div class="maintenance-body">
            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title"><i class="fas fa-info-circle me-2"></i>Editing Maintenance Record</div>
                <p class="help-content">
                    Update the maintenance type, status, dates, vendor, cost, or description. If setting status to "Completed",
                    you will need to provide the maintenance results.
                </p>
            </div>

            <!-- Asset Info Card -->
            <div class="asset-info-card">
                <h6><i class="bx bx-package me-2"></i>Asset & Record Information</h6>
                <div class="asset-info-grid">
                    <div class="asset-info-item">
                        <strong>Asset:</strong> {{ $maintenance->asset->name ?? 'N/A' }}
                        ({{ $maintenance->asset->asset_code ?? 'N/A' }})
                    </div>
                    <div class="asset-info-item">
                        <strong>Category:</strong> {{ $maintenance->asset->category->name ?? 'N/A' }}
                    </div>
                    <div class="asset-info-item">
                        <strong>Assignment:</strong>
                        {{ $maintenance->asset->currentAssignment->assignable->full_name ?? 'Not Assigned' }}
                    </div>
                    <div class="asset-info-item">
                        <strong>Asset Status:</strong>
                        @if ($maintenance->asset)
                            @if ($maintenance->asset->status == 'Available')
                                <span class="status-badge available">Available</span>
                            @elseif($maintenance->asset->status == 'Assigned')
                                <span class="status-badge assigned">Assigned</span>
                            @elseif($maintenance->asset->status == 'In Maintenance')
                                <span class="status-badge maintenance">In Maintenance</span>
                            @elseif($maintenance->asset->status == 'Disposed')
                                <span class="status-badge disposed">Disposed</span>
                            @else
                                <span class="status-badge">{{ $maintenance->asset->status }}</span>
                            @endif
                        @else
                            <span class="status-badge">Unknown</span>
                        @endif
                    </div>
                    <div class="asset-info-item">
                        <strong>Record ID:</strong> <span class="record-id-badge">#{{ $maintenance->id }}</span>
                    </div>
                </div>
                <div id="maintenance-status-warning"></div>
            </div>

            <!-- Edit Form -->
            <form action="{{ route('assets.update-maintenance', $maintenance->id) }}" method="POST" id="maintenanceForm">
                @csrf
                @method('PUT')

                <div class="form-section">
                    <h6 class="form-section-title"><i class="bx bx-cog me-2"></i>Maintenance Details</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="maintenance_type" class="form-label">Maintenance Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="maintenance_type" name="maintenance_type" required>
                                <option value="Preventive" {{ old('maintenance_type', $maintenance->maintenance_type) == 'Preventive' ? 'selected' : '' }}>
                                    Preventive (Regular)</option>
                                <option value="Corrective" {{ old('maintenance_type', $maintenance->maintenance_type) == 'Corrective' ? 'selected' : '' }}>
                                    Corrective (Repair)</option>
                                <option value="Upgrade" {{ old('maintenance_type', $maintenance->maintenance_type) == 'Upgrade' ? 'selected' : '' }}>
                                    Upgrade/Enhancement</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Scheduled" {{ old('status', $maintenance->status) == 'Scheduled' ? 'selected' : '' }}>
                                    Scheduled (Future)</option>
                                <option value="In Progress" {{ old('status', $maintenance->status) == 'In Progress' ? 'selected' : '' }}>
                                    In Progress (Currently Under Maintenance)</option>
                                <option value="Completed" {{ old('status', $maintenance->status) == 'Completed' ? 'selected' : '' }}>
                                    Completed</option>
                                <option value="Cancelled" {{ old('status', $maintenance->status) == 'Cancelled' ? 'selected' : '' }}>
                                    Cancelled</option>
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
                                value="{{ old('maintenance_date', $maintenance->maintenance_date->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="next_maintenance_date" class="form-label">Next Maintenance Date</label>
                            <input type="date" class="form-control" id="next_maintenance_date" name="next_maintenance_date"
                                value="{{ old('next_maintenance_date', $maintenance->next_maintenance_date ? $maintenance->next_maintenance_date->format('Y-m-d') : '') }}">
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
                                    <option value="{{ $vendor->id }}" {{ old('contact_id', $maintenance->contact_id) == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }}{{ $vendor->primary_person_label ? ' - ' . $vendor->primary_person_label : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="cost" class="form-label">Cost</label>
                            <input type="number" step="0.01" class="form-control" id="cost" name="cost"
                                placeholder="0.00" value="{{ old('cost', $maintenance->cost) }}">
                            <div class="form-text">Leave blank if unknown</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h6 class="form-section-title"><i class="bx bx-note me-2"></i>Description</h6>
                    <div class="mb-3">
                        <label for="description" class="form-label">Maintenance Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="4"
                            placeholder="Describe what needs to be done or what was done..." required>{{ old('description', $maintenance->description) }}</textarea>
                    </div>
                </div>

                <div class="form-section" id="results-section" style="{{ $maintenance->status == 'Completed' ? '' : 'display: none;' }}">
                    <h6 class="form-section-title"><i class="bx bx-check-circle me-2"></i>Results</h6>
                    <div class="mb-3">
                        <label for="results" class="form-label">Maintenance Results <span class="text-danger" id="results-required">{{ $maintenance->status == 'Completed' ? '*' : '' }}</span></label>
                        <textarea class="form-control" id="results" name="results" rows="4"
                            placeholder="Describe the work completed, findings, and results..."
                            {{ $maintenance->status == 'Completed' ? 'required' : '' }}>{{ old('results', $maintenance->results) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('assets.maintenance.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-loading" id="submitBtn">
                        <span class="btn-text"><i class="fas fa-save"></i> Update Maintenance Record</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
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
            const form = document.getElementById('maintenanceForm');
            const submitBtn = document.getElementById('submitBtn');
            const statusSelect = document.getElementById('status');
            const resultsSection = document.getElementById('results-section');
            const resultsTextarea = document.getElementById('results');
            const resultsRequired = document.getElementById('results-required');
            const statusWarningContainer = document.getElementById('status-warning-container');

            // Form submission loading state
            form.addEventListener('submit', function(e) {
                if (statusSelect.value === 'Completed') {
                    if (!resultsTextarea.value.trim()) {
                        e.preventDefault();
                        alert('Please provide maintenance results for completed maintenance.');
                        resultsTextarea.focus();
                        return;
                    }
                }
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });

            // Handle status change
            statusSelect.addEventListener('change', function() {
                if (this.value === 'Completed') {
                    resultsSection.style.display = 'block';
                    resultsTextarea.setAttribute('required', 'required');
                    resultsRequired.textContent = '*';
                    statusWarningContainer.innerHTML = '';
                } else if (this.value === 'In Progress') {
                    resultsSection.style.display = 'none';
                    resultsTextarea.removeAttribute('required');
                    resultsRequired.textContent = '';
                    statusWarningContainer.innerHTML = `
                        <div class="status-warning">
                            <i class="bx bx-info-circle me-1"></i>
                            <strong>Note:</strong> Setting status to "In Progress" will mark the asset as "In Maintenance" and make it unavailable for assignment.
                        </div>
                    `;
                } else {
                    resultsSection.style.display = 'none';
                    resultsTextarea.removeAttribute('required');
                    resultsRequired.textContent = '';
                    statusWarningContainer.innerHTML = '';
                }
            });
        });
    </script>
@endsection
