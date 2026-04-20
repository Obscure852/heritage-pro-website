@extends('layouts.master')
@section('title')
    Edit Policy {{ $policy->leave_year }} - {{ $leaveType->name }}
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

        .section-title:first-of-type {
            margin-top: 0;
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

        .form-control:disabled,
        .form-select:disabled {
            background-color: #f3f4f6;
            cursor: not-allowed;
        }

        .form-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .text-danger {
            color: #dc2626;
        }

        .leave-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: #f3f4f6;
            border-radius: 3px;
            font-size: 13px;
        }

        .leave-type-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            display: inline-block;
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
            <a class="text-muted font-size-14" href="{{ route('leave.policies.index', $leaveType) }}">{{ $leaveType->name }} Policies</a>
        @endslot
        @slot('title')
            Edit Policy {{ $policy->leave_year }}
        @endslot
    @endcomponent

    @if (session('error'))
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
            <h1 class="page-title">Edit Policy: {{ $policy->leave_year }}</h1>
            <div class="leave-type-badge">
                @if($leaveType->color)
                    <span class="leave-type-color" style="background-color: {{ $leaveType->color }};"></span>
                @endif
                <span>{{ $leaveType->name }} ({{ $leaveType->code }})</span>
            </div>
        </div>

        <div class="help-text">
            <div class="help-title">Update Policy Settings</div>
            <div class="help-content">
                Modify the policy settings for <strong>{{ $leaveType->name }}</strong> in <strong>{{ $policy->leave_year }}</strong>.
                Changes will affect how leave balances are calculated for this year.
                Leave year starts in <strong>{{ DateTime::createFromFormat('!m', $leaveYearStartMonth)->format('F') }}</strong>.
            </div>
        </div>

        <form class="needs-validation" method="post" action="{{ route('leave.policies.update', [$leaveType, $policy]) }}" novalidate>
            @csrf
            @method('PUT')

            <h3 class="section-title">Year & Balance Settings</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="leave_year">Leave Year</label>
                    <input type="text" class="form-control" value="{{ $policy->leave_year }}" disabled>
                    <input type="hidden" name="leave_year" value="{{ $policy->leave_year }}">
                    <div class="form-text">Year cannot be changed after creation</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="balance_mode">Balance Mode <span class="text-danger">*</span></label>
                    <select class="form-select @error('balance_mode') is-invalid @enderror" name="balance_mode" id="balance_mode" required>
                        <option value="allocation" {{ old('balance_mode', $policy->balance_mode) == 'allocation' ? 'selected' : '' }}>Allocation (Full balance at year start)</option>
                        <option value="accrual" {{ old('balance_mode', $policy->balance_mode) == 'accrual' ? 'selected' : '' }}>Accrual (Balance accumulates monthly)</option>
                    </select>
                    @error('balance_mode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid" style="margin-top: 16px;">
                <div class="form-group" id="accrual_rate_group" style="display: none;">
                    <label class="form-label" for="accrual_rate">Accrual Rate (days/month) <span class="text-danger">*</span></label>
                    <input type="number"
                        class="form-control @error('accrual_rate') is-invalid @enderror"
                        name="accrual_rate" id="accrual_rate"
                        placeholder="e.g., 1.75"
                        value="{{ old('accrual_rate', $policy->accrual_rate) }}"
                        step="0.01" min="0" max="31">
                    <div class="form-text">Days accrued per month of employment</div>
                    @error('accrual_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="prorate_new_employees">Prorate for New Employees <span class="text-danger">*</span></label>
                    <select class="form-select @error('prorate_new_employees') is-invalid @enderror" name="prorate_new_employees" id="prorate_new_employees" required>
                        <option value="1" {{ old('prorate_new_employees', $policy->prorate_new_employees) == '1' || old('prorate_new_employees', $policy->prorate_new_employees) === true ? 'selected' : '' }}>Yes (Entitlement based on employment start date)</option>
                        <option value="0" {{ old('prorate_new_employees', $policy->prorate_new_employees) == '0' || old('prorate_new_employees', $policy->prorate_new_employees) === false ? 'selected' : '' }}>No (Full entitlement regardless of join date)</option>
                    </select>
                    <div class="form-text">Applies when staff join mid-year</div>
                    @error('prorate_new_employees')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h3 class="section-title">Carry-Over Settings</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="carry_over_mode">Carry-Over Mode <span class="text-danger">*</span></label>
                    <select class="form-select @error('carry_over_mode') is-invalid @enderror" name="carry_over_mode" id="carry_over_mode" required>
                        <option value="none" {{ old('carry_over_mode', $policy->carry_over_mode) == 'none' ? 'selected' : '' }}>None (No carry-over)</option>
                        <option value="limited" {{ old('carry_over_mode', $policy->carry_over_mode) == 'limited' ? 'selected' : '' }}>Limited (Up to specified days)</option>
                        <option value="full" {{ old('carry_over_mode', $policy->carry_over_mode) == 'full' ? 'selected' : '' }}>Full (All unused days carry over)</option>
                    </select>
                    @error('carry_over_mode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" id="carry_over_limit_group" style="display: none;">
                    <label class="form-label" for="carry_over_limit">Carry-Over Limit (days) <span class="text-danger">*</span></label>
                    <input type="number"
                        class="form-control @error('carry_over_limit') is-invalid @enderror"
                        name="carry_over_limit" id="carry_over_limit"
                        placeholder="e.g., 10"
                        value="{{ old('carry_over_limit', $policy->carry_over_limit) }}"
                        step="0.5" min="0" max="365">
                    <div class="form-text">Maximum days that can be carried over</div>
                    @error('carry_over_limit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid" style="margin-top: 16px;">
                <div class="form-group" id="carry_over_expiry_group" style="display: none;">
                    <label class="form-label" for="carry_over_expiry_months">Carry-Over Expiry (months)</label>
                    <input type="number"
                        class="form-control @error('carry_over_expiry_months') is-invalid @enderror"
                        name="carry_over_expiry_months" id="carry_over_expiry_months"
                        placeholder="e.g., 3"
                        value="{{ old('carry_over_expiry_months', $policy->carry_over_expiry_months) }}"
                        min="1" max="12">
                    <div class="form-text">Months after which carried-over leave expires (leave blank for no expiry)</div>
                    @error('carry_over_expiry_months')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('leave.policies.index', $leaveType) }}">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Update Policy</span>
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
            const balanceModeSelect = document.getElementById('balance_mode');
            const carryOverModeSelect = document.getElementById('carry_over_mode');
            const accrualRateGroup = document.getElementById('accrual_rate_group');
            const carryOverLimitGroup = document.getElementById('carry_over_limit_group');
            const carryOverExpiryGroup = document.getElementById('carry_over_expiry_group');

            function updateBalanceModeFields() {
                if (balanceModeSelect.value === 'accrual') {
                    accrualRateGroup.style.display = 'block';
                } else {
                    accrualRateGroup.style.display = 'none';
                }
            }

            function updateCarryOverFields() {
                const mode = carryOverModeSelect.value;
                if (mode === 'limited') {
                    carryOverLimitGroup.style.display = 'block';
                    carryOverExpiryGroup.style.display = 'block';
                } else if (mode === 'full') {
                    carryOverLimitGroup.style.display = 'none';
                    carryOverExpiryGroup.style.display = 'block';
                } else {
                    carryOverLimitGroup.style.display = 'none';
                    carryOverExpiryGroup.style.display = 'none';
                }
            }

            balanceModeSelect.addEventListener('change', updateBalanceModeFields);
            carryOverModeSelect.addEventListener('change', updateCarryOverFields);

            // Initialize on page load
            updateBalanceModeFields();
            updateCarryOverFields();

            // Form validation
            const forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    } else {
                        const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                        if (submitBtn) {
                            submitBtn.classList.add('loading');
                            submitBtn.disabled = true;
                        }
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        });
    </script>
@endsection
