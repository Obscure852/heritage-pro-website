@extends('layouts.master')
@section('title')
    Complete Maintenance
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
            border-left: 4px solid #10b981;
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

        .maintenance-info-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .maintenance-info-card h6 {
            color: #1e40af;
            margin-bottom: 16px;
            font-weight: 600;
            font-size: 14px;
        }

        .maintenance-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .maintenance-info-item {
            font-size: 14px;
        }

        .maintenance-info-item strong {
            color: #374151;
        }

        .maintenance-description {
            background: #f9fafb;
            border-radius: 3px;
            padding: 12px;
            margin-top: 16px;
            font-size: 14px;
        }

        .maintenance-description strong {
            display: block;
            margin-bottom: 8px;
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
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
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

        .status-badge.scheduled { background: #dbeafe; color: #1e40af; }
        .status-badge.in-progress { background: #fef3c7; color: #b45309; }

        .type-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }

        .type-badge.preventive { background: #dbeafe; color: #1e40af; }
        .type-badge.corrective { background: #fef3c7; color: #b45309; }
        .type-badge.upgrade { background: #ede9fe; color: #6d28d9; }

        .schedule-next-option {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 3px;
            padding: 16px;
            margin-top: 16px;
        }

        .schedule-next-option .form-check-label {
            font-weight: 500;
            color: #065f46;
        }

        .schedule-next-option .form-text {
            color: #059669;
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

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
        }

        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        @media (max-width: 768px) {
            .maintenance-header {
                padding: 20px;
            }

            .maintenance-info-grid {
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
            Complete Maintenance
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

    <div class="maintenance-container">
        <div class="maintenance-header">
            <h4><i class="bx bx-check-circle me-2"></i>Complete Maintenance</h4>
            <p>Mark maintenance as completed and record results</p>
        </div>

        <div class="maintenance-body">
            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title"><i class="fas fa-info-circle me-2"></i>Completing Maintenance</div>
                <p class="help-content">
                    Document the results of the maintenance work, update the final cost if different from the estimate,
                    and optionally schedule the next maintenance. The asset will be marked as available after completion.
                </p>
            </div>

            <!-- Maintenance Info Card -->
            <div class="maintenance-info-card">
                <h6><i class="bx bx-wrench me-2"></i>Maintenance Details</h6>
                <div class="maintenance-info-grid">
                    <div class="maintenance-info-item">
                        <strong>Asset:</strong> {{ $maintenance->asset->name ?? 'N/A' }}
                        ({{ $maintenance->asset->asset_code ?? 'N/A' }})
                    </div>
                    <div class="maintenance-info-item">
                        <strong>Category:</strong> {{ $maintenance->asset->category->name ?? 'N/A' }}
                    </div>
                    <div class="maintenance-info-item">
                        <strong>Type:</strong>
                        @if ($maintenance->maintenance_type == 'Preventive')
                            <span class="type-badge preventive">Preventive</span>
                        @elseif ($maintenance->maintenance_type == 'Corrective')
                            <span class="type-badge corrective">Corrective</span>
                        @else
                            <span class="type-badge upgrade">Upgrade</span>
                        @endif
                    </div>
                    <div class="maintenance-info-item">
                        <strong>Status:</strong>
                        @if ($maintenance->status == 'Scheduled')
                            <span class="status-badge scheduled">Scheduled</span>
                        @elseif($maintenance->status == 'In Progress')
                            <span class="status-badge in-progress">In Progress</span>
                        @endif
                    </div>
                    <div class="maintenance-info-item">
                        <strong>Scheduled Date:</strong> {{ $maintenance->maintenance_date->format('M d, Y') }}
                    </div>
                    <div class="maintenance-info-item">
                        <strong>Business Contact:</strong>
                        @if($maintenance->vendor)
                            {{ $maintenance->vendor->name }}
                            <span class="text-muted">({{ $maintenance->vendor->primary_person_label ?? 'No primary person' }})</span>
                        @else
                            In-house
                        @endif
                    </div>
                </div>
                <div class="maintenance-description">
                    <strong>Description:</strong>
                    {{ $maintenance->description }}
                </div>
            </div>

            <!-- Completion Form -->
            <form action="{{ route('assets.maintenance-complete-process', $maintenance->id) }}" method="POST" id="completeForm">
                @csrf

                <div class="form-section">
                    <h6 class="form-section-title"><i class="bx bx-clipboard me-2"></i>Results</h6>
                    <div class="mb-3">
                        <label for="results" class="form-label">Maintenance Results <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="results" name="results" rows="4"
                            placeholder="Describe the work completed, findings, and results..." required>{{ old('results') }}</textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h6 class="form-section-title"><i class="bx bx-dollar me-2"></i>Cost & Scheduling</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="cost" class="form-label">Final Cost</label>
                            <input type="number" step="0.01" class="form-control" id="cost" name="cost"
                                placeholder="0.00" value="{{ old('cost', $maintenance->cost) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="next_maintenance_date" class="form-label">Next Maintenance Date</label>
                            <input type="date" class="form-control" id="next_maintenance_date" name="next_maintenance_date"
                                value="{{ old('next_maintenance_date', $maintenance->next_maintenance_date ? $maintenance->next_maintenance_date->format('Y-m-d') : '') }}">
                            <div class="form-text">Leave blank if not applicable</div>
                        </div>
                    </div>

                    <div class="schedule-next-option">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="schedule_next" name="schedule_next" value="1">
                            <label class="form-check-label" for="schedule_next">
                                <i class="bx bx-calendar-plus me-1"></i> Automatically schedule next maintenance
                            </label>
                            <div class="form-text">
                                This will create a new scheduled maintenance record based on the next maintenance date
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('assets.maintenance.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success btn-loading" id="submitBtn">
                        <span class="btn-text"><i class="fas fa-check-circle"></i> Mark as Completed</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
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
            const form = document.getElementById('completeForm');
            const submitBtn = document.getElementById('submitBtn');
            const scheduleNextCheckbox = document.getElementById('schedule_next');
            const nextMaintenanceDateInput = document.getElementById('next_maintenance_date');

            // Form submission loading state and validation
            form.addEventListener('submit', function(e) {
                if (scheduleNextCheckbox.checked && !nextMaintenanceDateInput.value) {
                    e.preventDefault();
                    alert('Please specify a next maintenance date or uncheck the "Schedule next maintenance" option.');
                    nextMaintenanceDateInput.focus();
                    return;
                }
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });

            // Auto-suggest next maintenance date when checkbox is checked
            scheduleNextCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    nextMaintenanceDateInput.classList.add('border-success');
                    if (!nextMaintenanceDateInput.value) {
                        const today = new Date();
                        today.setMonth(today.getMonth() + 6);
                        const year = today.getFullYear();
                        const month = String(today.getMonth() + 1).padStart(2, '0');
                        const day = String(today.getDate()).padStart(2, '0');
                        nextMaintenanceDateInput.value = `${year}-${month}-${day}`;
                    }
                } else {
                    nextMaintenanceDateInput.classList.remove('border-success');
                }
            });
        });
    </script>
@endsection
