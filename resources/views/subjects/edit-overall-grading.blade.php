@extends('layouts.master')
@section('title')
    Edit Overall Grading | Academic Management
@endsection

@section('css')
    <style>
        .settings-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-header h3 {
            margin: 0 0 8px 0;
            font-size: 22px;
            font-weight: 600;
        }

        .settings-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-body {
            padding: 24px;
        }

        /* Grade Badge */
        .grade-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            font-size: 12px;
            margin-top: 8px;
        }

        .grade-badge i {
            margin-right: 6px;
        }

        /* Help Text */
        .help-text {
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 16px;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
        }

        .help-text p {
            margin: 0;
            color: #1e40af;
            font-size: 14px;
            line-height: 1.5;
        }

        .help-text p i {
            margin-right: 8px;
        }

        /* Alert Styling */
        .alert-styled {
            border-radius: 3px;
            padding: 16px;
            margin-bottom: 20px;
            border: none;
        }

        .alert-styled.alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-styled.alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert-styled ul {
            margin: 0;
            padding-left: 20px;
        }

        .alert-styled li {
            margin-bottom: 4px;
        }

        .alert-styled li:last-child {
            margin-bottom: 0;
        }

        /* Grading Table */
        .grading-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .grading-table thead th {
            background: #f9fafb;
            padding: 12px 16px;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
        }

        .grading-table tbody td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .grading-table tbody tr:hover {
            background: #f9fafb;
        }

        .grading-table tbody tr:last-child td {
            border-bottom: none;
        }

        .grading-table .form-control {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .grading-table .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .grading-table .input-description {
            width: 100%;
            min-width: 200px;
        }

        .grading-table .input-score {
            width: 80px;
            text-align: center;
        }

        .grading-table .input-grade {
            width: 80px;
            text-align: center;
            text-transform: uppercase;
        }

        /* Row Number */
        .row-number {
            width: 40px;
            font-weight: 600;
            color: #6b7280;
            font-size: 13px;
        }

        /* Buttons */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            color: #4b5563;
            font-weight: 500;
            font-size: 14px;
            background: white;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: #f9fafb;
            color: #374151;
            border-color: #d1d5db;
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border: none;
            border-radius: 3px;
            color: white;
            font-weight: 500;
            font-size: 14px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-save:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .btn-save:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-save.loading .btn-text {
            display: none;
        }

        .btn-save.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-body {
                padding: 16px;
            }

            .grading-table {
                display: block;
                overflow-x: auto;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-back,
            .btn-save {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('academic.configurations') }}">Configurations</a>
        @endslot
        @slot('title')
            Edit Overall Grading
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-10 col-xl-8">
            @if (session('message'))
                <div class="alert alert-styled alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>{{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-styled alert-danger">
                    <strong><i class="bx bx-error-circle me-2"></i>Please fix the following errors:</strong>
                    <ul class="mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="settings-container">
                <div class="settings-header">
                    <h3><i class="bx bx-edit me-2"></i>Edit Overall Grading Scale</h3>
                    <p>Modify the overall grading matrix for this grade</p>
                    <div class="grade-badge">
                        <i class="bx bx-graduation"></i>
                        {{ $grade->name ?? 'Unknown Grade' }}
                    </div>
                </div>
                <div class="settings-body">
                    <div class="help-text">
                        <p><i class="bx bx-info-circle"></i>Update the overall grading scale by modifying score ranges and their corresponding grades. Changes will affect how final scores are converted to grades for this grade level.</p>
                    </div>

                    <form class="needs-validation" method="post" action="{{ route('academic.save-overall-grading') }}" id="gradingForm">
                        @csrf
                        <input name="term_id" type="hidden" value="{{ old('term_id', $currentTerm->id) }}">
                        <input name="year" type="hidden" value="{{ old('year', $currentTerm->year) }}">
                        <input name="grade_id" type="hidden" value="{{ old('grade_id', $overallGrading->first()->grade_id) }}">

                        <div class="table-responsive">
                            <table class="grading-table">
                                <thead>
                                    <tr>
                                        <th class="row-number">#</th>
                                        <th>Description</th>
                                        <th>From (%)</th>
                                        <th>To (%)</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($overallGrading as $index => $gradeItem)
                                        <tr>
                                            <td class="row-number">{{ $index + 1 }}</td>
                                            <td>
                                                <input name="description[]" type="text"
                                                    value="{{ old('description.' . $index, $gradeItem->description) }}"
                                                    class="form-control input-description"
                                                    placeholder="e.g., Excellent">
                                            </td>
                                            <td>
                                                <input name="min_score[]" type="text"
                                                    value="{{ old('min_score.' . $index, $gradeItem->min_score) }}"
                                                    class="form-control input-score"
                                                    placeholder="0">
                                            </td>
                                            <td>
                                                <input name="max_score[]" type="text"
                                                    value="{{ old('max_score.' . $index, $gradeItem->max_score) }}"
                                                    class="form-control input-score"
                                                    placeholder="100">
                                            </td>
                                            <td>
                                                <input name="grade[]" type="text"
                                                    value="{{ old('grade.' . $index, $gradeItem->grade) }}"
                                                    class="form-control input-grade"
                                                    placeholder="A"
                                                    maxlength="2">
                                            </td>
                                        </tr>
                                    @endforeach

                                    @for ($i = $overallGrading->count(); $i < 8; $i++)
                                        <tr>
                                            <td class="row-number">{{ $i + 1 }}</td>
                                            <td>
                                                <input name="description[]" type="text"
                                                    value="{{ old('description.' . $i) }}"
                                                    class="form-control input-description"
                                                    placeholder="e.g., Excellent">
                                            </td>
                                            <td>
                                                <input name="min_score[]" type="text"
                                                    value="{{ old('min_score.' . $i) }}"
                                                    class="form-control input-score"
                                                    placeholder="0">
                                            </td>
                                            <td>
                                                <input name="max_score[]" type="text"
                                                    value="{{ old('max_score.' . $i) }}"
                                                    class="form-control input-score"
                                                    placeholder="100">
                                            </td>
                                            <td>
                                                <input name="grade[]" type="text"
                                                    value="{{ old('grade.' . $i) }}"
                                                    class="form-control input-grade"
                                                    placeholder="A"
                                                    maxlength="2">
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('academic.configurations') }}" class="btn-back">
                                <i class="bx bx-arrow-back"></i>
                                Back to Configurations
                            </a>
                            <button type="submit" class="btn-save">
                                <span class="btn-text"><i class="bx bx-save"></i> Update Grading Scale</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Updating...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('gradingForm');
            form.addEventListener('submit', function(e) {
                const btn = form.querySelector('.btn-save');
                if (btn) {
                    btn.classList.add('loading');
                    btn.disabled = true;
                }
            });
        });
    </script>
@endsection
