@extends('layouts.master')
@section('title')
    Edit Points Matrix | Academic Management
@endsection

@section('css')
    <style>
        /* Main Container */
        .settings-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .settings-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
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

        /* Table Styling */
        .points-table {
            width: 100%;
            border-collapse: collapse;
        }

        .points-table thead th {
            background: #f9fafb;
            padding: 12px 16px;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }

        .points-table tbody td {
            padding: 12px 16px;
            color: #4b5563;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .points-table tbody tr:hover {
            background: #f9fafb;
        }

        .points-table tbody tr:last-child td {
            border-bottom: none;
        }

        .points-table .form-control {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .points-table .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-back:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-save:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
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
            .settings-header {
                padding: 20px;
            }

            .settings-body {
                padding: 16px;
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
            <a class="text-muted" href="{{ route('academic.configurations') }}">Overall Grading</a>
        @endslot
        @slot('title')
            Edit Points Matrix
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

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

    <div class="row">
        <div class="col-12">
            <div class="settings-container">
                <div class="settings-header">
                    <h3><i class="fas fa-edit me-2"></i>Edit Points Matrix</h3>
                    <p>Update the points matrix for {{ $academicYear }}</p>
                </div>

                <div class="settings-body">
                    <div class="help-text">
                        <div class="help-title">Points Configuration</div>
                        <div class="help-content">
                            Edit the minimum and maximum points for each grade level. Ensure minimum points do not exceed maximum points.
                            Empty rows at the bottom can be used to add new grade ranges.
                        </div>
                    </div>

                    <form action="{{ route('academic.update-overall-points') }}" method="POST">
                        @csrf
                        <input type="hidden" name="academic_year" value="{{ $academicYear }}">

                        <div class="table-responsive">
                            <table class="points-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Min Points</th>
                                        <th>Max Points</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pointsMatrix as $index => $point)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <input type="hidden" name="id[{{ $index }}]" value="{{ $point->id }}">
                                                <input type="number" class="form-control form-control-sm"
                                                    name="min[{{ $index }}]" value="{{ $point->min }}"
                                                    min="0" required>
                                                @error('min.' . $index)
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm"
                                                    name="max[{{ $index }}]" value="{{ $point->max }}"
                                                    min="0" required>
                                                @error('max.' . $index)
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm"
                                                    name="grade[{{ $index }}]" value="{{ $point->grade }}"
                                                    required>
                                                @error('grade.' . $index)
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                    @endforeach

                                    @php
                                        $startIndex = count($pointsMatrix);
                                    @endphp

                                    @for ($i = 0; $i < 3; $i++)
                                        <tr>
                                            <td>{{ $startIndex + $i + 1 }}</td>
                                            <td>
                                                <input type="hidden" name="new_rows[{{ $i }}]" value="1">
                                                <input type="number" class="form-control form-control-sm"
                                                    name="new_min[{{ $i }}]" min="0">
                                                @error('new_min.' . $i)
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm"
                                                    name="new_max[{{ $i }}]" min="0">
                                                @error('new_max.' . $i)
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm"
                                                    name="new_grade[{{ $i }}]">
                                                @error('new_grade.' . $i)
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('academic.configurations') }}" class="btn-back">
                                <i class="bx bx-arrow-back"></i> Back
                            </a>
                            <button type="submit" class="btn-save" id="saveBtn">
                                <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Saving...
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
        $(document).ready(function() {
            $('form').on('submit', function(e) {
                let isValid = true;

                $('tbody tr').each(function() {
                    const min = parseInt($(this).find('input[name^="min"]').val());
                    const max = parseInt($(this).find('input[name^="max"]').val());

                    if (min > max) {
                        alert('Minimum points cannot be greater than maximum points.');
                        isValid = false;
                        return false;
                    }
                });

                if (isValid) {
                    const btn = document.getElementById('saveBtn');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                }

                return isValid;
            });
        });
    </script>
@endsection
