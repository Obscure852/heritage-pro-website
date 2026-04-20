@extends('layouts.master-without-nav')
@section('title')
    @if (!empty($school_data->school_name))
        {{ $school_data->school_name }} Online Application
    @else
        Online School Application
    @endif
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            min-height: 100vh;
        }

        .form-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .form-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .form-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .form-body {
            padding: 24px;
        }

        .form-tabs {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 24px;
        }

        .form-tabs .nav-tabs {
            border: none;
            flex-wrap: nowrap;
            overflow-x: auto;
        }

        .form-tabs .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            background: none;
            color: #6b7280;
            font-weight: 500;
            padding: 16px 20px;
            border-radius: 0;
            transition: all 0.2s;
            white-space: nowrap;
            font-size: 14px;
        }

        .form-tabs .nav-tabs .nav-link:hover {
            color: #374151;
            background: #f9fafb;
        }

        .form-tabs .nav-tabs .nav-link.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
            background: none;
        }

        .form-tabs .nav-tabs .nav-link i {
            margin-right: 6px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .form-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        @media (max-width: 768px) {
            .form-grid,
            .form-grid-3 {
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

        .required {
            color: #dc2626;
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

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 3px;
            margin-top: 8px;
        }

        .checkbox-group .form-check {
            margin: 0;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            margin-top: 24px;
            border-top: 1px solid #f3f4f6;
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

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        @media (max-width: 1024px) {
            .col-xxl-7.col-lg-6.col-md-6.p-0 {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .form-header {
                padding: 20px;
            }

            .form-body {
                padding: 16px;
            }

            .form-tabs .nav-tabs .nav-link {
                padding: 12px 16px;
                font-size: 13px;
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
    <div class="auth-page">
        <div class="container-fluid p-0 align-items-center">
            <div class="row justify-content-center">
                <div class="col-11 col-lg-10 mt-4 mb-4">
                    <div class="form-container">
                        <div class="form-header">
                            @if (!empty($school_data->school_name))
                                <h4>{{ $school_data->school_name ?? '' }} Online Application</h4>
                                <p>Complete all required sections to submit your application</p>
                            @else
                                <h4>Online School Application</h4>
                                <p>Complete all required sections to submit your application</p>
                            @endif
                        </div>
                        <div class="form-body">
                            <div class="form-tabs">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link @if ($activeTab == 'student') active @endif" data-bs-toggle="tab"
                                            href="#student" role="tab">
                                            <i class="bx bxs-graduation"></i>Student Info <span class="required">*</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link @if ($activeTab == 'parent') active @endif" data-bs-toggle="tab"
                                            href="#parent" role="tab">
                                            <i class="bx bxs-group"></i>Sponsor/Parent <span class="required">*</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link @if ($activeTab == 'health') active @endif" data-bs-toggle="tab"
                                            href="#health" role="tab">
                                            <i class="bx bx-health"></i>Health Info
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link @if ($activeTab == 'academic') active @endif" data-bs-toggle="tab"
                                            href="#academic" role="tab">
                                            <i class="bx bxs-book"></i>Academic Records
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link @if ($activeTab == 'attachments') active @endif" data-bs-toggle="tab"
                                            href="#attachments" role="tab">
                                            <i class="bx bxs-file-pdf"></i>Attachments <span class="required">*</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div class="tab-content">
                                <!-- Student Information Tab -->
                                <div class="tab-pane fade @if ($activeTab == 'student') show active @endif"
                                    id="student" role="tabpanel">
                                    <form class="needs-validation" method="post"
                                        action="{{ route('admissions.create-online-application') }}" novalidate>
                                        @csrf
                                        <h6 class="section-title">Personal Information</h6>
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label class="form-label">First Name <span class="required">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('first_name') is-invalid @enderror"
                                                    name="first_name" placeholder="First name"
                                                    value="{{ old('first_name', $admission->first_name ?? '') }}" required>
                                                @error('first_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Last Name <span class="required">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('last_name') is-invalid @enderror"
                                                    name="last_name" placeholder="Last name"
                                                    value="{{ old('last_name', $admission->last_name ?? '') }}" required>
                                                @error('last_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-grid" style="margin-top: 16px;">
                                            <div class="form-group">
                                                <label class="form-label">Middle Name</label>
                                                <input type="text"
                                                    class="form-control @error('middle_name') is-invalid @enderror"
                                                    name="middle_name" placeholder="Middle name"
                                                    value="{{ old('middle_name', $admission->middle_name ?? '') }}">
                                                @error('middle_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Gender <span class="required">*</span></label>
                                                <select class="form-select @error('gender') is-invalid @enderror"
                                                    name="gender" required>
                                                    <option value="">Select Gender</option>
                                                    <option value="M"
                                                        {{ old('gender', $admission->gender ?? '') == 'M' ? 'selected' : '' }}>
                                                        Male</option>
                                                    <option value="F"
                                                        {{ old('gender', $admission->gender ?? '') == 'F' ? 'selected' : '' }}>
                                                        Female</option>
                                                </select>
                                                @error('gender')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-grid" style="margin-top: 16px;">
                                            <div class="form-group">
                                                <label class="form-label">Date of Birth <span class="required">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('date_of_birth') is-invalid @enderror"
                                                    name="date_of_birth"
                                                    value="{{ old('date_of_birth', isset($admission->date_of_birth) ? \Carbon\Carbon::parse($admission->date_of_birth)->format('d/m/Y') : '') }}"
                                                    placeholder="dd/mm/yyyy" maxlength="10" required>
                                                @error('date_of_birth')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Nationality <span class="required">*</span></label>
                                                <select class="form-select @error('nationality') is-invalid @enderror"
                                                    data-trigger name="nationality" required>
                                                    <option value="">Select Nationality</option>
                                                    @foreach ($nationalities as $nationality)
                                                        <option value="{{ $nationality->name }}"
                                                            {{ old('nationality', $admission->nationality ?? '') == $nationality->name ? 'selected' : '' }}>
                                                            {{ $nationality->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('nationality')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-grid" style="margin-top: 16px;">
                                            <div class="form-group">
                                                <label class="form-label">Phone <span class="required">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('phone') is-invalid @enderror"
                                                    name="phone" placeholder="Phone"
                                                    value="{{ old('phone', $admission->phone ?? '') }}">
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">ID Number <span class="required">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('id_number') is-invalid @enderror"
                                                    name="id_number" placeholder="ID Number"
                                                    value="{{ old('id_number', $admission->id_number ?? '') }}" required>
                                                @error('id_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <h6 class="section-title" style="margin-top: 32px;">Academic Information</h6>
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label class="form-label">Grade Applying For</label>
                                                <select class="form-select @error('grade') is-invalid @enderror"
                                                    name="grade" required>
                                                    @foreach ($grades as $grade)
                                                        <option value="{{ $grade->name }}"
                                                            {{ old('grade', $admission->grade_applying_for ?? '') == $grade->name ? 'selected' : '' }}>
                                                            {{ $grade->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('grade')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Academic Year <span class="required">*</span></label>
                                                <select name="year"
                                                    class="form-select @error('year') is-invalid @enderror" required>
                                                    <option value="">Select Year</option>
                                                    @php
                                                        $currentYear = date('Y');
                                                        $endYear = $currentYear + 3;
                                                    @endphp
                                                    @for ($year = $currentYear; $year <= $endYear; $year++)
                                                        <option value="{{ $year }}"
                                                            {{ old('year', $admission->academic_year_applying_for ?? '') == $year ? 'selected' : '' }}>
                                                            {{ $year }}
                                                        </option>
                                                    @endfor
                                                </select>
                                                @error('year')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-grid-3" style="margin-top: 16px;">
                                            <div class="form-group">
                                                <label class="form-label">Application Date <span class="required">*</span></label>
                                                <input type="date"
                                                    class="form-control @error('application_date') is-invalid @enderror"
                                                    name="application_date"
                                                    value="{{ old('application_date', $admission->application_date ?? '') }}"
                                                    required>
                                                @error('application_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Status <span class="required">*</span></label>
                                                <select class="form-select" name="status">
                                                    <option value="">Select status</option>
                                                    <option value="New Online">New Online</option>
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Term <span class="required">*</span></label>
                                                <select name="term_id"
                                                    class="form-select @error('term_id') is-invalid @enderror" required>
                                                    <option value="">Select Term</option>
                                                    @foreach ($terms as $term)
                                                        <option value="{{ $term->id }}"
                                                            {{ old('term_id', $admission->term_id ?? '') == $term->id ? 'selected' : '' }}>
                                                            {{ $term->start_date . ' to ' . $term->end_date }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('term_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary">
                                                Save & Next <i class="bx bx-arrow-forward"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Parent/Sponsor Tab -->
                                <div class="tab-pane fade @if ($activeTab == 'parent') show active @endif"
                                    id="parent" role="tabpanel">
                                    <form class="needs-validation" method="POST"
                                        action="{{ route('admissions.create-parent-online-application') }}" novalidate>
                                        @csrf
                                        <input type="hidden" name="admission_id" value="{{ $admission->id ?? '' }}">

                                        <h6 class="section-title">Sponsor/Parent Information</h6>
                                        <div class="form-grid-3">
                                            <div class="form-group">
                                                <label class="form-label">Title <span class="required">*</span></label>
                                                <select class="form-select @error('title') is-invalid @enderror"
                                                    name="title">
                                                    <option value="">Title...</option>
                                                    <option value="Mr" {{ old('title', $parent->title ?? '') == 'Mr' ? 'selected' : '' }}>Mr</option>
                                                    <option value="Mrs" {{ old('title', $parent->title ?? '') == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                                    <option value="Ms" {{ old('title', $parent->title ?? '') == 'Ms' ? 'selected' : '' }}>Ms</option>
                                                    <option value="Dr" {{ old('title', $parent->title ?? '') == 'Dr' ? 'selected' : '' }}>Dr</option>
                                                    <option value="Miss" {{ old('title', $parent->title ?? '') == 'Miss' ? 'selected' : '' }}>Miss</option>
                                                </select>
                                                @error('title')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">First Name <span class="required">*</span></label>
                                                <input type="text" name="first_name"
                                                    class="form-control @error('first_name') is-invalid @enderror"
                                                    placeholder="First name"
                                                    value="{{ old('first_name', $parent->first_name ?? '') }}" required>
                                                @error('first_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Last Name <span class="required">*</span></label>
                                                <input type="text" name="last_name"
                                                    class="form-control @error('last_name') is-invalid @enderror"
                                                    placeholder="Last name"
                                                    value="{{ old('last_name', $parent->last_name ?? '') }}" required>
                                                @error('last_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-grid-3" style="margin-top: 16px;">
                                            <div class="form-group">
                                                <label class="form-label">Email <span class="required">*</span></label>
                                                <input type="email" name="email"
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    placeholder="Email"
                                                    value="{{ old('email', $parent->email ?? '') }}" required>
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Date of Birth <span class="required">*</span></label>
                                                <input type="text" name="date_of_birth"
                                                    class="form-control @error('date_of_birth') is-invalid @enderror"
                                                    value="{{ old('date_of_birth', isset($parent->date_of_birth) ? \Carbon\Carbon::parse($parent->date_of_birth)->format('d/m/Y') : '') }}"
                                                    placeholder="dd/mm/yyyy" maxlength="10" required>
                                                @error('date_of_birth')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Gender <span class="required">*</span></label>
                                                <select name="gender"
                                                    class="form-select @error('gender') is-invalid @enderror" required>
                                                    <option value="">Select gender...</option>
                                                    <option value="M" {{ old('gender', $parent->gender ?? '') == 'M' ? 'selected' : '' }}>M</option>
                                                    <option value="F" {{ old('gender', $parent->gender ?? '') == 'F' ? 'selected' : '' }}>F</option>
                                                </select>
                                                @error('gender')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-grid-3" style="margin-top: 16px;">
                                            <div class="form-group">
                                                <label class="form-label">ID/Passport No <span class="required">*</span></label>
                                                <input type="text" name="id_number"
                                                    class="form-control @error('id_number') is-invalid @enderror"
                                                    placeholder="988824887"
                                                    value="{{ old('id_number', $parent->id_number ?? '') }}" required>
                                                @error('id_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Nationality</label>
                                                <select name="nationality" data-trigger
                                                    class="form-select @error('nationality') is-invalid @enderror">
                                                    <option value="">Select Nationality...</option>
                                                    @foreach ($nationalities as $nationality)
                                                        <option value="{{ $nationality->name }}"
                                                            {{ old('nationality', $parent->nationality ?? '') == $nationality->name ? 'selected' : '' }}>
                                                            {{ $nationality->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('nationality')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Relation</label>
                                                <select name="relation" data-trigger
                                                    class="form-select @error('relation') is-invalid @enderror">
                                                    <option value="">Select relation...</option>
                                                    <option value="Mother" {{ old('relation', $parent->relation ?? '') == 'Mother' ? 'selected' : '' }}>Mother</option>
                                                    <option value="Father" {{ old('relation', $parent->relation ?? '') == 'Father' ? 'selected' : '' }}>Father</option>
                                                    <option value="Grandmother" {{ old('relation', $parent->relation ?? '') == 'Grandmother' ? 'selected' : '' }}>Grandmother</option>
                                                    <option value="Grandfather" {{ old('relation', $parent->relation ?? '') == 'Grandfather' ? 'selected' : '' }}>Grandfather</option>
                                                    <option value="Brother" {{ old('relation', $parent->relation ?? '') == 'Brother' ? 'selected' : '' }}>Brother</option>
                                                    <option value="Sister" {{ old('relation', $parent->relation ?? '') == 'Sister' ? 'selected' : '' }}>Sister</option>
                                                    <option value="Uncle" {{ old('relation', $parent->relation ?? '') == 'Uncle' ? 'selected' : '' }}>Uncle</option>
                                                    <option value="Auntie" {{ old('relation', $parent->relation ?? '') == 'Auntie' ? 'selected' : '' }}>Auntie</option>
                                                    <option value="Relative" {{ old('relation', $parent->relation ?? '') == 'Relative' ? 'selected' : '' }}>Relative</option>
                                                    <option value="Other" {{ old('relation', $parent->relation ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                                @error('relation')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-grid-3" style="margin-top: 16px;">
                                            <div class="form-group">
                                                <label class="form-label">Status <span class="required">*</span></label>
                                                <select name="status"
                                                    class="form-select @error('status') is-invalid @enderror" required>
                                                    <option value="">Select status...</option>
                                                    <option value="Current" {{ old('status', $parent->status ?? '') == 'Current' ? 'selected' : '' }}>Current</option>
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Phone</label>
                                                <input type="text" name="phone"
                                                    placeholder="+267 78654123"
                                                    class="form-control @error('phone') is-invalid @enderror"
                                                    value="{{ old('phone', $parent->phone ?? '') }}">
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Telephone</label>
                                                <input type="text" name="telephone"
                                                    placeholder="+267 3950555"
                                                    class="form-control @error('telephone') is-invalid @enderror"
                                                    value="{{ old('telephone', $parent->telephone ?? '') }}">
                                                @error('telephone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-grid-3" style="margin-top: 16px;">
                                            <div class="form-group">
                                                <label class="form-label">Year <span class="required">*</span></label>
                                                <select name="year"
                                                    class="form-select @error('year') is-invalid @enderror" required>
                                                    <option value="">Select Year...</option>
                                                    @for ($year = date('Y'); $year <= date('Y') + 3; $year++)
                                                        <option value="{{ $year }}"
                                                            {{ old('year', $parent->year ?? '') == $year ? 'selected' : '' }}>
                                                            {{ $year }}
                                                        </option>
                                                    @endfor
                                                </select>
                                                @error('year')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Profession</label>
                                                <input type="text" class="form-control"
                                                    name="profession" placeholder="Teacher">
                                                @error('profession')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Work Place</label>
                                                <input type="text" name="work_place"
                                                    placeholder="Sedilega Hospital"
                                                    class="form-control @error('work_place') is-invalid @enderror"
                                                    value="{{ old('work_place', $parent->work_place ?? '') }}">
                                                @error('work_place')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-actions">
                                            <a class="btn btn-secondary" href="#">
                                                <i class="bx bx-arrow-back"></i> Back
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                Save & Next <i class="bx bx-arrow-forward"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Health Tab -->
                                <div class="tab-pane fade @if ($activeTab == 'health') show active @endif"
                                    id="health" role="tabpanel">
                                    <form class="needs-validation" method="post"
                                        action="{{ route('admissions.create-student-health-online-application') }}"
                                        enctype="multipart/form-data" novalidate>
                                        @csrf
                                        <input type="hidden" name="admission_id" value="{{ $admission->id ?? '' }}">

                                        <h6 class="section-title">Student Health Information</h6>

                                        <div class="form-group mb-3">
                                            <label class="form-label">Health History</label>
                                            <textarea name="health_history" class="form-control" rows="3">{{ old('health_history', $healthInfo->health_history ?? '') }}</textarea>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label">Immunization Records
                                                <small class="required">(Max 10MB)</small>
                                            </label>
                                            <input type="file" name="immunization_records" class="form-control">
                                            @if (isset($healthInfo->immunization_records))
                                                <small class="text-muted">Current file: {{ $healthInfo->immunization_records }}</small>
                                            @endif
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label">Allergies & Food Preferences</label>
                                            <div class="checkbox-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="peanuts" value="1" id="peanuts"
                                                        {{ old('peanuts', $healthInfo->peanuts ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="peanuts">Peanuts</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="red_meat" value="1" id="red_meat"
                                                        {{ old('red_meat', $healthInfo->red_meat ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="red_meat">Red Meat</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="vegetarian" value="1" id="vegetarian"
                                                        {{ old('vegetarian', $healthInfo->vegetarian ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="vegetarian">Vegetarian</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label">Other Allergies</label>
                                            <textarea name="other_allergies" class="form-control" rows="2">{{ old('other_allergies', $healthInfo->other_allergies ?? '') }}</textarea>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label">Limb Disabilities</label>
                                            <div class="checkbox-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="left_leg" value="1" id="left_leg"
                                                        {{ old('left_leg', $healthInfo->left_leg ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="left_leg">Left Leg</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="right_leg" value="1" id="right_leg"
                                                        {{ old('right_leg', $healthInfo->right_leg ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="right_leg">Right Leg</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="left_hand" value="1" id="left_hand"
                                                        {{ old('left_hand', $healthInfo->left_hand ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="left_hand">Left Arm</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="right_hand" value="1" id="right_hand"
                                                        {{ old('right_hand', $healthInfo->right_hand ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="right_hand">Right Arm</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label">Other Disabilities</label>
                                            <textarea name="other_disabilities" class="form-control" rows="2">{{ old('other_disabilities', $healthInfo->other_disabilities ?? '') }}</textarea>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label">Eye Sight & Hearing</label>
                                            <div class="checkbox-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="left_eye" value="1" id="left_eye"
                                                        {{ old('left_eye', $healthInfo->left_eye ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="left_eye">Left Eye</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="right_eye" value="1" id="right_eye"
                                                        {{ old('right_eye', $healthInfo->right_eye ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="right_eye">Right Eye</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="left_ear" value="1" id="left_ear"
                                                        {{ old('left_ear', $healthInfo->left_ear ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="left_ear">Left Ear</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="right_ear" value="1" id="right_ear"
                                                        {{ old('right_ear', $healthInfo->right_ear ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="right_ear">Right Ear</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label">Medical Conditions</label>
                                            <textarea name="medical_conditions" class="form-control" rows="3">{{ old('medical_conditions', $healthInfo->medical_conditions ?? '') }}</textarea>
                                        </div>

                                        <div class="form-actions">
                                            <a class="btn btn-secondary" href="#">
                                                <i class="bx bx-arrow-back"></i> Back
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                Save & Next <i class="bx bx-arrow-forward"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Academic Tab -->
                                <div class="tab-pane fade @if ($activeTab == 'academic') show active @endif"
                                    id="academic" role="tabpanel">
                                    <form class="needs-validation" method="post"
                                        action="{{ route('admissions.create-student-academic-online-application') }}"
                                        novalidate>
                                        @csrf
                                        <input type="hidden" name="admission_id" value="{{ $admission->id ?? '' }}">

                                        <h6 class="section-title">Student Academic Information</h6>

                                        <div class="form-grid-3">
                                            <div class="form-group">
                                                <label class="form-label">Science</label>
                                                <input type="text"
                                                    class="form-control @error('science') is-invalid @enderror"
                                                    name="science" placeholder="A*"
                                                    value="{{ old('science', $academicInfo->science ?? '') }}">
                                                @error('science')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Mathematics</label>
                                                <input type="text"
                                                    class="form-control @error('mathematics') is-invalid @enderror"
                                                    name="mathematics" placeholder="A*"
                                                    value="{{ old('mathematics', $academicInfo->mathematics ?? '') }}">
                                                @error('mathematics')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">English</label>
                                                <input type="text"
                                                    class="form-control @error('english') is-invalid @enderror"
                                                    name="english" placeholder="C"
                                                    value="{{ old('english', $academicInfo->english ?? '') }}">
                                                @error('english')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-actions">
                                            <a class="btn btn-secondary" href="#">
                                                <i class="bx bx-arrow-back"></i> Back
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                Save & Next <i class="bx bx-arrow-forward"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Attachments Tab -->
                                <div class="tab-pane fade @if ($activeTab == 'attachments') show active @endif"
                                    id="attachments" role="tabpanel">
                                    <form class="needs-validation" method="post"
                                        action="{{ route('admissions.create-student-attachments-online-application') }}"
                                        enctype="multipart/form-data" novalidate>
                                        @csrf
                                        <input type="hidden" name="admission_id" value="{{ $admission->id ?? '' }}">

                                        <h6 class="section-title">Required Documents</h6>

                                        <div class="help-text">
                                            <div class="help-content">
                                                Please upload the required documents. All attachments must not exceed 10MB.
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label">Identification (Passport, ID, or Birth Certificate) <span class="required">*</span></label>
                                            <input type="file"
                                                class="form-control @error('attachments.identification') is-invalid @enderror"
                                                name="attachments[identification]" required>
                                            @error('attachments.identification')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label">Report from Previous School <span class="required">*</span></label>
                                            <input type="file"
                                                class="form-control @error('attachments.report') is-invalid @enderror"
                                                name="attachments[report]" required>
                                            @error('attachments.report')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label">Application Fee Receipt <span class="required">*</span></label>
                                            <input type="file"
                                                class="form-control @error('attachments.application_fee_receipt') is-invalid @enderror"
                                                name="attachments[application_fee_receipt]" required>
                                            @error('attachments.application_fee_receipt')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-check"></i> Save & Complete Application
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('/assets/js/pages/pass-addon.init.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
@endsection
