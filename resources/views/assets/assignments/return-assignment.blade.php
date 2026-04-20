@extends('layouts.master')
@section('title')
    Return Asset: {{ $asset->name }}
@endsection

@section('css')
    <style>
        .return-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .return-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .return-header h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .return-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .return-body {
            padding: 24px;
        }

        .assignment-info-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .assignment-info-card h6 {
            color: #1e40af;
            margin-bottom: 16px;
            font-weight: 600;
        }

        .assignment-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .assignment-info-item {
            font-size: 14px;
        }

        .assignment-info-item strong {
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
            .return-header {
                padding: 20px;
            }

            .assignment-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('assets.index') }}">Back</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('assets.show', $asset->id) }}">Asset Details</a>
        @endslot
        @slot('title')
            Return Asset
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

    <div class="return-container">
        <div class="return-header">
            <h4><i class="bx bx-undo me-2"></i>Return Asset</h4>
            <p>Process the return of {{ $asset->name }} ({{ $asset->asset_code }})</p>
        </div>

        <div class="return-body">
            <!-- Current Assignment Info -->
            <div class="assignment-info-card">
                <h6><i class="bx bx-info-circle me-2"></i>Current Assignment Details</h6>
                <div class="assignment-info-grid">
                    <div class="assignment-info-item">
                        <strong>Asset:</strong> {{ $asset->name }} ({{ $asset->asset_code }})
                    </div>
                    <div class="assignment-info-item">
                        <strong>Category:</strong> {{ $asset->category->name ?? 'N/A' }}
                    </div>
                    <div class="assignment-info-item">
                        <strong>Assigned To:</strong>
                        @if ($asset->currentAssignment->assignable_type == 'App\\Models\\User')
                            {{ $asset->currentAssignment->assignable->full_name ?? 'User' }}
                        @endif
                    </div>
                    <div class="assignment-info-item">
                        <strong>Assigned Date:</strong> {{ $asset->currentAssignment->assigned_date->format('M d, Y') }}
                    </div>
                    <div class="assignment-info-item">
                        <strong>Expected Return:</strong>
                        @if ($asset->currentAssignment->expected_return_date)
                            {{ $asset->currentAssignment->expected_return_date->format('M d, Y') }}
                            @if ($asset->currentAssignment->expected_return_date->isPast())
                                <span class="badge bg-danger ms-1">Overdue</span>
                            @endif
                        @else
                            Not specified
                        @endif
                    </div>
                    <div class="assignment-info-item">
                        <strong>Condition on Assignment:</strong> {{ $asset->currentAssignment->condition_on_assignment }}
                    </div>
                </div>
            </div>

            <!-- Return Form -->
            <form action="{{ route('assets.process-return') }}" method="POST" id="returnForm">
                @csrf
                <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                <input type="hidden" name="assignment_id" value="{{ $asset->currentAssignment->id }}">

                <div class="form-section">
                    <h6 class="form-section-title"><i class="bx bx-calendar me-2"></i>Return Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="actual_return_date" class="form-label">Return Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="actual_return_date" name="actual_return_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="condition_on_return" class="form-label">Condition on Return <span class="text-danger">*</span></label>
                            <select class="form-select" id="condition_on_return" name="condition_on_return" required>
                                <option value="New" {{ $asset->condition == 'New' ? 'selected' : '' }}>New</option>
                                <option value="Good" {{ $asset->condition == 'Good' ? 'selected' : '' }}>Good</option>
                                <option value="Fair" {{ $asset->condition == 'Fair' ? 'selected' : '' }}>Fair</option>
                                <option value="Poor" {{ $asset->condition == 'Poor' ? 'selected' : '' }}>Poor</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h6 class="form-section-title"><i class="bx bx-note me-2"></i>Additional Notes</h6>
                    <div class="mb-3">
                        <label for="return_notes" class="form-label">Return Notes</label>
                        <textarea class="form-control" id="return_notes" name="return_notes" rows="4" placeholder="Enter any notes about the condition or circumstances of return..."></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success btn-loading" id="submitBtn">
                        <span class="btn-text"><i class="bx bx-check-circle me-1"></i> Process Return</span>
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
            const form = document.getElementById('returnForm');
            const submitBtn = document.getElementById('submitBtn');

            form.addEventListener('submit', function() {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });
        });
    </script>
@endsection
