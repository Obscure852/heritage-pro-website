@extends('layouts.master')
@section('title')
    Create Payment Plan
@endsection
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

        .invoice-info {
            background: #eff6ff;
            border: 1px solid #93c5fd;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .invoice-info p {
            margin-bottom: 4px;
        }

        .preview-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin-top: 24px;
        }

        .preview-section h6 {
            margin-bottom: 16px;
            color: #1f2937;
        }

        .preview-table {
            width: 100%;
        }

        .preview-table th {
            background: #f1f5f9;
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }

        .preview-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .preview-table tfoot td {
            font-weight: 600;
            background: #f8fafc;
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

        .btn-primary:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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

        .btn-outline-primary {
            background: transparent;
            border: 1px solid #3b82f6;
            color: #3b82f6;
        }

        .btn-outline-primary:hover {
            background: #3b82f6;
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
            <a class="text-muted font-size-14" href="{{ route('fees.collection.invoices.index') }}">Fee Administration</a>
        @endslot
        @slot('title')
            Create Payment Plan
        @endslot
    @endcomponent

    @if(session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

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
            <h1 class="page-title">Create Payment Plan</h1>
        </div>

        <div class="help-text">
            <div class="help-title">Set Up Installment Schedule</div>
            <div class="help-content">
                Create a payment plan to allow the student to pay this invoice in installments. Choose the number of installments and frequency, then preview the schedule before confirming.
            </div>
        </div>

        <!-- Invoice Info -->
        <div class="invoice-info">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                    <p><strong>Student:</strong> {{ $invoice->student->full_name }}</p>
                    <p><strong>Student #:</strong> {{ $invoice->student->student_number }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p><strong>Total:</strong> P{{ number_format($invoice->total_amount, 2) }}</p>
                    <p><strong>Paid:</strong> P{{ number_format($invoice->amount_paid, 2) }}</p>
                    <p class="h5 mb-0"><strong>Balance:</strong> <span class="text-danger">P{{ number_format($invoice->balance, 2) }}</span></p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('fees.payment-plans.store') }}" id="planForm" class="needs-validation" novalidate>
            @csrf
            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

            <h3 class="section-title">Plan Configuration</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="name">Plan Name <span class="text-muted">(Optional)</span></label>
                    <input type="text" name="name" id="name" class="form-control"
                           placeholder="e.g., 2026 Termly Plan"
                           value="{{ old('name') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="numInstallments">Number of Installments <span class="text-danger">*</span></label>
                    <select name="number_of_installments" id="numInstallments" class="form-select" required>
                        @for($i = 2; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ old('number_of_installments', 3) == $i ? 'selected' : '' }}>
                                {{ $i }} Installments
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="frequency">Frequency <span class="text-danger">*</span></label>
                    <select name="frequency" id="frequency" class="form-select" required>
                        @foreach($frequencies as $value => $label)
                            <option value="{{ $value }}" {{ old('frequency', 'termly') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="startDate">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" id="startDate" class="form-control" required
                           value="{{ old('start_date', date('Y-m-d')) }}"
                           min="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="button" id="previewBtn" class="btn btn-outline-primary">
                    <i class="fas fa-eye"></i> Preview Installments
                </button>
            </div>

            <!-- Installment Preview -->
            <div id="previewSection" class="preview-section" style="display: none;">
                <h6 class="fw-bold">Installment Schedule Preview</h6>
                <div class="table-responsive">
                    <table class="preview-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Due Date</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="previewBody">
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">Total</td>
                                <td class="text-end" id="previewTotal">P0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('fees.collection.invoices.show', $invoice) }}">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-loading" id="submitBtn" disabled>
                    <span class="btn-text"><i class="fas fa-check"></i> Create Payment Plan</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Creating...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const previewBtn = document.getElementById('previewBtn');
            const submitBtn = document.getElementById('submitBtn');
            const previewSection = document.getElementById('previewSection');
            const previewBody = document.getElementById('previewBody');
            const previewTotal = document.getElementById('previewTotal');
            const form = document.getElementById('planForm');

            // Preview button click
            previewBtn.addEventListener('click', async function() {
                const formData = {
                    invoice_id: '{{ $invoice->id }}',
                    number_of_installments: document.getElementById('numInstallments').value,
                    frequency: document.getElementById('frequency').value,
                    start_date: document.getElementById('startDate').value,
                    _token: '{{ csrf_token() }}'
                };

                try {
                    previewBtn.disabled = true;
                    previewBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Loading...';

                    const response = await fetch('{{ route("fees.payment-plans.preview") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();

                    if (response.ok) {
                        previewBody.innerHTML = '';
                        data.installments.forEach(inst => {
                            previewBody.innerHTML += `
                                <tr>
                                    <td>${inst.installment_number}</td>
                                    <td>${inst.due_date_formatted}</td>
                                    <td class="text-end">P${parseFloat(inst.amount).toFixed(2)}</td>
                                </tr>
                            `;
                        });
                        previewTotal.textContent = 'P' + parseFloat(data.total_amount).toFixed(2);
                        previewSection.style.display = 'block';
                        submitBtn.disabled = false;
                    } else {
                        alert('Error: ' + (data.message || 'Failed to preview'));
                    }
                } catch (error) {
                    console.error(error);
                    alert('An error occurred while previewing.');
                } finally {
                    previewBtn.disabled = false;
                    previewBtn.innerHTML = '<i class="fas fa-eye"></i> Preview Installments';
                }
            });

            // Reset preview when options change
            ['numInstallments', 'frequency', 'startDate'].forEach(id => {
                document.getElementById(id).addEventListener('change', function() {
                    previewSection.style.display = 'none';
                    submitBtn.disabled = true;
                });
            });

            // Form submit with loading animation
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    // Show loading state on submit button
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
                form.classList.add('was-validated');
            }, false);

            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    }
                }, 5000);
            });
        });
    </script>
@endsection
