@extends('layouts.master')
@section('title')
    Edit Class | Academic Management
@endsection

@section('css')
    <style>
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

        .select2-container--default .select2-selection--single {
            height: 38px;
            border-radius: 3px;
            border-color: #d1d5db;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
            padding-left: 12px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .select2-dropdown {
            border-color: #d1d5db;
            font-size: 14px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('academic.index') }}">Classes</a>
        @endslot
        @slot('title')
            New Class
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-md-10">
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            @if (session('message'))
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <h5>Create a New Class</h4>
                </div>
                <div class="card-body">
                    <form class="needs-validation" method="post" action="{{ route('academic.store') }}" novalidate>
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label" for="validationCustom01">Class Name<span
                                            style="color:red;">*</span> </label>
                                    <input type="text" name="name" class="form-control form-control-sm"
                                        placeholder="e.g. 1A*" value="{{ $class->name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="validationCustom01">Class Tsseacher<span
                                            style="color:red;">*</span> </label>
                                    <select name="user_id" id="user_id" class="form-select" data-trigger>
                                        <option value="">Select Class Teacher ...</option>
                                        @if (!empty($teachers))
                                            @foreach ($teachers as $teacher)
                                                <option
                                                    value="{{ $teacher->id }}"{{ $teacher->id == $class->user_id ? 'selected' : '' }}>
                                                    {{ $teacher->firstname . ' ' . $teacher->lastname }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="validationCustom01">Term<span style="color:red;">*</span>
                                    </label>
                                    <select name="term_id" class="form-control form-control-sm">
                                        <option value="" selected>Select Term ...</option>
                                        @if (!empty($terms))
                                            @foreach ($terms as $term)
                                                <option
                                                    value="{{ $term->id }}"{{ $term->id == $class->term_id ? 'selected' : '' }}>
                                                    {{ 'Term ' . $term->term . ',' . $term->year }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="validationCustom01">Grade<span
                                            style="color:red;">*</span> </label>
                                    <select name="grade" class="form-control form-control-sm">
                                        <option value="" selected>Select Grade ...</option>
                                        @if (!empty($grades))
                                            @foreach ($grades as $grade)
                                                <option
                                                    value="{{ $grade->id }}"{{ $grade->id == $class->grade_id ? 'selected' : '' }}>
                                                    {{ $grade->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="monitor_id">Monitor</label>
                                    <select name="monitor_id" class="form-control form-control-sm">
                                        <option value="" selected>Select Monitor ...</option>
                                        @if (!empty($maleStudents))
                                            @foreach ($maleStudents as $student)
                                                <option value="{{ $student->id }}"
                                                    {{ $class->monitor_id == $student->id ? 'selected' : '' }}>
                                                    {{ $student->full_name }}
                                                </option>
                                            @endforeach
                                        @elseif (!empty($students))
                                            @foreach ($students as $student)
                                                @if ($student->gender === 'M')
                                                    <option value="{{ $student->id }}"
                                                        {{ $class->monitor_id == $student->id ? 'selected' : '' }}>
                                                        {{ $student->full_name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="monitress_id">Monitress</label>
                                    <select name="monitress_id" class="form-control form-control-sm">
                                        <option value="" selected>Select Monitress ...</option>
                                        @if (!empty($femaleStudents))
                                            @foreach ($femaleStudents as $student)
                                                <option value="{{ $student->id }}"
                                                    {{ $class->monitress_id == $student->id ? 'selected' : '' }}>
                                                    {{ $student->full_name }}
                                                </option>
                                            @endforeach
                                        @elseif (!empty($students))
                                            @foreach ($students as $student)
                                                @if ($student->gender === 'F')
                                                    <option value="{{ $student->id }}"
                                                        {{ $class->monitress_id == $student->id ? 'selected' : '' }}>
                                                        {{ $student->full_name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="validationCustom01">Academic Year<span
                                            style="color:red;">*</span></label>
                                    <select name="year" data-trigger class="form-select form-select-sm">
                                        <option value="" selected>Select Year ...</option>
                                        @for ($year = date('Y') - 1; $year <= date('Y') + 3; $year++)
                                            <option value="{{ $year }}"
                                                {{ $year == $class->year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm btn-loading">
                                    <span class="btn-text"><i class="fas fa-save me-1"></i> Update Class</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"
                                            aria-hidden="true"></span>
                                        Updating...
                                    </span>
                                </button>
                            </div>
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
            const form = document.querySelector('.needs-validation');

            form.addEventListener('submit', function(e) {
                if (form.checkValidity()) {
                    const btn = form.querySelector('.btn-loading');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                }
            });
        });
    </script>
@endsection
