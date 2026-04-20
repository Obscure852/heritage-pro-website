@extends('layouts.master')
@section('title')
    Edit Student
@endsection
@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .text-muted {
            font-size: 14px;
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

        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-suspended {
            background: #fef3c7;
            color: #92400e;
        }

        .status-withdrawn {
            background: #f3f4f6;
            color: #4b5563;
        }

        .status-graduated {
            background: #e9d5ff;
            color: #6b21a8;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .form-control,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-control:disabled,
        .form-select:disabled {
            background: #f3f4f6;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin-top: 3px;
            cursor: pointer;
        }

        .form-check-label {
            margin-left: 8px;
            cursor: pointer;
            color: #374151;
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            padding: 16px;
            background: #f9fafb;
            border-radius: 3px;
            margin-bottom: 16px;
        }

        .required {
            color: #dc2626;
        }

        .text-danger {
            color: #dc2626;
            font-size: 12px;
            margin-top: 4px;
        }

        .text-muted {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        .form-actions-left {
            display: flex;
            gap: 8px;
        }

        .form-actions-right {
            display: flex;
            gap: 8px;
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
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
            transform: translateY(-1px);
        }

        .form-tabs {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 32px;
        }

        .form-tabs .nav-tabs {
            border-bottom: none;
            gap: 0;
            flex-wrap: wrap;
        }

        .form-tabs .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            background: none;
            color: #6b7280;
            font-weight: 500;
            padding: 16px 24px;
            margin-bottom: 0;
            border-radius: 0;
            transition: all 0.2s;
        }

        .form-tabs .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: #374151;
            background: #f9fafb;
        }

        .form-tabs .nav-tabs .nav-link.active {
            color: #3b82f6;
            background: none;
            border-bottom-color: #3b82f6;
        }

        .tab-icon {
            margin-right: 8px;
            font-size: 16px;
        }

        .tab-content {
            padding-top: 0px;
        }

        .tab-pane {
            min-height: 400px;
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

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
        }

        .info-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            background: #fafbfc;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 12px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .existing-attachments {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            background: white;
            margin-bottom: 20px;
        }

        .existing-attachment-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 12px;
        }

        .existing-attachment-item:last-child {
            margin-bottom: 0;
        }

        .attachment-icon {
            font-size: 24px;
            color: #6b7280;
        }

        .attachment-details {
            flex: 1;
        }

        .attachment-name {
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .attachment-info {
            font-size: 12px;
            color: #6b7280;
        }

        .attachment-status {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .attachment-status-received {
            background: #dbeafe;
            color: #1e40af;
        }

        .attachment-status-verified {
            background: #d1fae5;
            color: #065f46;
        }

        .attachment-status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .meta-info {
            background: #f9fafb;
            padding: 16px;
            border-radius: 3px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .meta-info-row {
            display: flex;
            gap: 24px;
            margin-bottom: 8px;
        }

        .meta-info-row:last-child {
            margin-bottom: 0;
        }

        .meta-info-label {
            font-weight: 600;
            color: #374151;
            min-width: 120px;
        }

        .meta-info-value {
            color: #6b7280;
        }

        .credit-info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 16px;
            border-radius: 3px;
            margin-bottom: 20px;
        }

        .credit-amount {
            font-size: 24px;
            font-weight: 700;
        }

        .credit-label {
            font-size: 13px;
            opacity: 0.9;
        }

        .profile-image-section {
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            height: fit-content;
            margin-top: 25px;
            width: 100%;
            max-width: 100%;
        }

        .profile-image-container {
            text-align: center;
            position: relative;
            width: 100%;
        }

        .image-upload-area {
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 12px;
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .image-upload-area:hover {
            transform: translateY(-2px);
        }

        .profile-preview {
            width: 100%;
            height: auto;
            max-width: 250px;
            max-height: 250px;
            min-width: 150px;
            min-height: 150px;
            aspect-ratio: 1;
            object-fit: cover;
            transition: all 0.3s ease;
            cursor: pointer;
            border-radius: 3px;
        }

        .profile-preview:hover {
            border-color: #3b82f6;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }

        .no-image-placeholder {
            width: 100%;
            height: auto;
            max-width: 250px;
            max-height: 250px;
            min-width: 150px;
            min-height: 150px;
            aspect-ratio: 1;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border-radius: 3px;
        }

        .no-image-placeholder:hover {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            transform: scale(1.02);
        }

        .no-image-placeholder i {
            font-size: 52px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }

        .no-image-placeholder:hover i {
            font-size: 56px;
            color: #3b82f6;
        }

        .no-image-placeholder .upload-text {
            font-size: 10px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .no-image-placeholder .upload-hint {
            font-size: 11px;
            opacity: 0.8;
            font-weight: 400;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .profile-preview {
                max-width: 200px;
                max-height: 200px;
                min-width: 120px;
                min-height: 120px;
            }

            .no-image-placeholder {
                max-width: 200px;
                max-height: 200px;
                min-width: 120px;
                min-height: 120px;
            }

            .no-image-placeholder i {
                font-size: 40px;
                margin-bottom: 8px;
            }

            .no-image-placeholder .upload-text {
                font-size: 9px;
            }

            .no-image-placeholder .upload-hint {
                font-size: 10px;
            }
        }

        @media (max-width: 576px) {
            .profile-preview {
                max-width: 150px;
                max-height: 150px;
                min-width: 100px;
                min-height: 100px;
            }

            .no-image-placeholder {
                max-width: 150px;
                max-height: 150px;
                min-width: 100px;
                min-height: 100px;
            }

            .no-image-placeholder i {
                font-size: 32px;
                margin-bottom: 6px;
            }

            .no-image-placeholder .upload-text {
                font-size: 8px;
                margin-bottom: 2px;
            }

            .no-image-placeholder .upload-hint {
                font-size: 9px;
            }

            .file-info {
                font-size: 10px;
            }
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: transparent;
            border-radius: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .image-upload-area:hover .image-overlay {
            opacity: 1;
        }

        .image-overlay i {
            color: white;
            font-size: 24px;
        }

        .overlay-actions {
            display: flex;
            gap: 12px;
        }

        .overlay-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            color: #fff;
        }

        .overlay-btn.upload-trigger {
            background: rgba(55, 65, 81, 0.95);
        }

        .overlay-btn.remove-trigger {
            background: rgba(239, 68, 68, 0.95);
        }

        .overlay-btn:hover {
            filter: brightness(0.95);
        }

        .remove-btn-top {
            position: absolute;
            top: 6px;
            right: 6px;
            z-index: 3;
            background: #ef4444;
            color: #fff;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .remove-btn-top:hover {
            background: #dc2626;
        }

        .upload-controls {
            display: flex;
            gap: 12px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .upload-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .upload-btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .remove-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .remove-btn:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .file-info {
            padding: 2px;
            margin-top: 4px;
            font-size: 11px;
            color: #64748b;
            text-align: center;
        }

        #profileImageInput {
            display: none;
        }

        .drag-active {
            border-color: #3b82f6 !important;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%) !important;
            transform: scale(1.05) !important;
        }

        .image-removed {
            opacity: 0.4;
            filter: grayscale(100%);
            position: relative;
        }

        .image-removed::after {
            content: '✕';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(239, 68, 68, 0.9);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions-left,
            .form-actions-right {
                width: 100%;
                justify-content: center;
            }

            .form-tabs .nav-tabs .nav-link {
                padding: 12px 16px;
                font-size: 13px;
            }

            .meta-info-row {
                flex-direction: column;
                gap: 4px;
            }

            .profile-preview,
            .no-image-placeholder {
                width: 140px;
                height: 140px;
            }

            .no-image-placeholder i {
                font-size: 40px;
            }

            .upload-controls {
                flex-direction: column;
                gap: 8px;
            }

            .upload-btn,
            .remove-btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Communications Tab Styling */
        .comm-controls .form-control,
        .comm-controls .form-select {
            font-size: 14px;
        }

        .comm-table {
            font-size: 14px;
        }

        .comm-table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
        }

        .comm-subject-line {
            font-size: 14px;
            color: #111827;
        }

        .comm-preview {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        .comm-row {
            transition: background-color 0.2s;
        }

        .comm-row:hover {
            background-color: #f9fafb;
        }

        .comm-row.hidden {
            display: none;
        }

        .comm-pagination {
            font-size: 14px;
        }

        .pagination-controls {
            display: flex;
            gap: 4px;
        }

        .pagination-controls button {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .pagination-controls button:hover:not(:disabled) {
            background: #f3f4f6;
        }

        .pagination-controls button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-controls button.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('students.index') }}">Back</a>
        @endslot
        @slot('title')
            Edit Student
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-circle label-icon"></i>
                    <strong>Please correct the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="form-container">
                <div class="page-header">
                    <h4 class="page-title">{{ $student->full_name }}</h4>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('students.module-plan', $student) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-tasks"></i> Module Plan
                        </a>
                        <span class="status-badge status-{{ $student->status }}">{{ ucfirst($student->status) }}</span>
                    </div>
                </div>
                <form action="{{ route('students.update', $student) }}" method="POST" id="editStudentForm"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="form-tabs">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#student-info"
                                    type="button" role="tab">
                                    <i class="fas fa-user tab-icon"></i>
                                    Student Information
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#academic-details"
                                    type="button" role="tab">
                                    <i class="fas fa-graduation-cap tab-icon"></i>
                                    Academic Details
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#historical-data"
                                    type="button" role="tab">
                                    <i class="fas fa-history tab-icon"></i>
                                    Historical Data
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#contact-details"
                                    type="button" role="tab">
                                    <i class="fas fa-address-card tab-icon"></i>
                                    Contact Details
                                </button>
                            </li>
                            @can('students.health')
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#medical-info" type="button"
                                        role="tab">
                                        <i class="fas fa-heartbeat tab-icon"></i>
                                        Medical Information
                                    </button>
                                </li>
                            @endcan
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#behavior-info" type="button"
                                    role="tab">
                                    <i class="fas fa-clipboard-list tab-icon"></i>
                                    Behavior Records
                                    @if ($student->studentBehaviour->count() > 0)
                                        <span class="badge bg-warning ms-1">{{ $student->studentBehaviour->count() }}</span>
                                    @endif
                                </button>
                            </li>
                            @if ($student->admission && $student->admission->attachments->count() > 0)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents" type="button"
                                        role="tab">
                                        <i class="fas fa-paperclip tab-icon"></i>
                                        Documents
                                        <span
                                            class="badge bg-primary ms-1">{{ $student->admission->attachments->count() }}</span>
                                    </button>
                                </li>
                            @endif
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#communications"
                                    type="button" role="tab">
                                    <i class="fas fa-envelope tab-icon"></i>
                                    Communications
                                    @php
                                        $commCount = $student->emails()->count() + $student->messages()->count();
                                    @endphp
                                    @if ($commCount > 0)
                                        <span class="badge bg-info ms-1">{{ $commCount }}</span>
                                    @endif
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content">
                        <!-- Student Information Tab -->
                        <div class="tab-pane fade show active" id="student-info" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Student Information</div>
                                <div class="help-content">
                                    Basic information about the student including personal details and student number.
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-10">
                                    <h5 class="section-title">Personal Information</h5>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label class="form-label">First Name <span class="required">*</span></label>
                                            <input type="text" name="first_name" class="form-control"
                                                value="{{ old('first_name', $student->first_name) }}"
                                                placeholder="First name" required>
                                            @error('first_name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Last Name <span class="required">*</span></label>
                                            <input type="text" name="last_name" class="form-control"
                                                value="{{ old('last_name', $student->last_name) }}"
                                                placeholder="Last name" required>
                                            @error('last_name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Other Names</label>
                                            <input type="text" name="other_names" class="form-control"
                                                value="{{ old('other_names', $student->other_names) }}"
                                                placeholder="Middle/Other names">
                                            @error('other_names')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Gender {{ $student->gender ?? '' }}<span
                                                    class="required">*</span></label>
                                            <select name="gender" class="form-select" required>
                                                <option value="">Select gender</option>
                                                @foreach (['Male' => 'Male', 'Female' => 'Female'] as $k => $v)
                                                    <option value="{{ $k }}"
                                                        {{ old('gender', $student->gender) === $k || (old('gender', $student->gender) === 'M' && $k === 'Male') || (old('gender', $student->gender) === 'F' && $k === 'Female') ? 'selected' : '' }}>
                                                        {{ $v }}</option>
                                                @endforeach
                                            </select>
                                            @error('gender')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Date of Birth <span
                                                    class="required">*</span></label>
                                            <input type="date" name="date_of_birth" class="form-control"
                                                value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}"
                                                required>
                                            @error('date_of_birth')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Phone</label>
                                            <input type="text" name="phone" class="form-control"
                                                value="{{ old('phone', $student->phone) }}"
                                                placeholder="e.g., +267 71 000 000">
                                            @error('phone')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Nationality</label>
                                            <input type="text" name="nationality" class="form-control"
                                                value="{{ old('nationality', $student->nationality) }}"
                                                placeholder="Nationality">
                                            @error('nationality')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">National ID / Passport</label>
                                            <input type="text" name="id_number" class="form-control"
                                                value="{{ old('id_number', $student->id_number) }}"
                                                placeholder="e.g., 123456789">
                                            @error('id_number')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control"
                                                value="{{ old('email', $student->email) }}"
                                                placeholder="student@email.com">
                                            @error('email')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <h5 class="section-title">Academic Details</h5>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label class="form-label">Student Number <span
                                                    class="required">*</span></label>
                                            <input type="text" name="student_number" class="form-control"
                                                value="{{ old('student_number', $student->admission->admission_number ?? $student->student_number) }}"
                                                placeholder="Student number" required>
                                            @error('student_number')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Program</label>
                                            <select name="program_id" class="form-select">
                                                <option value="">Select program</option>
                                                @foreach ($programs as $program)
                                                    <option value="{{ $program->id }}"
                                                        {{ old('program_id', $student->activeProgramEnrollment?->program_id) == $program->id ? 'selected' : '' }}>
                                                        {{ $program->code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('program_id')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Semester</label>
                                            <select name="semester_id" class="form-select">
                                                <option value="">Select semester</option>
                                                @php
                                                    $currentSemesterId = old(
                                                        'semester_id',
                                                        $student->semesterRegistrations
                                                            ->sortByDesc('created_at')
                                                            ->first()?->semester_id,
                                                    );
                                                @endphp
                                                @foreach ($semesters as $semester)
                                                    <option value="{{ $semester->id }}"
                                                        {{ $currentSemesterId == $semester->id ? 'selected' : '' }}>
                                                        Semester
                                                        {{ $semester->semester_number }}
                                                        @if ($semester->label)
                                                            ({{ $semester->academic_year }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('semester_id')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Sponsor <a href="{{ route('sponsors.create') }}">
                                                    <i class="bx bx-link-external"></i> </a> </label>
                                            <select name="sponsor_id" class="form-select">
                                                <option value="">Select sponsor</option>
                                                @foreach ($sponsors as $sponsor)
                                                    <option value="{{ $sponsor->id }}"
                                                        {{ old('sponsor_id', $student->sponsors->where('pivot.is_primary', true)->first()?->id) == $sponsor->id ? 'selected' : '' }}>
                                                        {{ $sponsor->display_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('sponsor_id')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Filters<a href="{{ route('students.settings') }}">
                                                    <i class="bx bx-link-external"></i> </a> </label>
                                            <select name="student_filter_id" class="form-select">
                                                <option value="">No filter</option>
                                                @foreach ($filters as $filter)
                                                    <option value="{{ $filter->id }}"
                                                        {{ old('student_filter_id', $student->student_filter_id) == $filter->id ? 'selected' : '' }}>
                                                        {{ $filter->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('student_filter_id')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Status <a href="{{ route('students.settings') }}">
                                                    <i class="bx bx-link-external"></i> </a> </label>
                                            <select name="status" class="form-select">
                                                <option value="">Select status {{ $student->status ?? '' }}</option>
                                                @foreach ($statuses as $status)
                                                    <option value="{{ $status->name }}"
                                                        {{ strtolower(old('status', $student->status)) === strtolower($status->name) ? 'selected' : '' }}>
                                                        {{ $status->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('status')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="col-2">
                                    <div class="profile-image-section mt-2">
                                        <div class="profile-image-container">
                                            <div class="image-upload-area" id="uploadArea">
                                                @if ($student->photo_path)
                                                    <img src="{{ URL::asset('storage/' . $student->photo_path) }}"
                                                        alt="Student Photo" id="currentImage" class="profile-preview">
                                                    <div class="image-overlay">
                                                        <div class="overlay-actions">
                                                            <button type="button" class="overlay-btn upload-trigger"
                                                                id="overlayUpload" title="Change photo">
                                                                <i class="fas fa-camera"></i>
                                                            </button>
                                                            <button type="button" class="overlay-btn remove-trigger"
                                                                id="removeBtn" title="Remove photo">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="no-image-placeholder" id="imagePlaceholder">
                                                        <i class="fas fa-user"></i>
                                                        <div class="upload-text">Click to upload</div>
                                                    </div>
                                                @endif
                                            </div>
                                            <input type="file" name="profile_image" id="profileImageInput"
                                                accept="image/jpeg,image/jpg,image/png,image/gif">
                                            <div class="upload-controls"></div>
                                            <div class="file-info">
                                                JPG, PNG, GIF up to 2MB
                                            </div>
                                            @error('profile_image')
                                                <div class="text-danger mt-2 text-center">{{ $message }}</div>
                                            @enderror
                                            <input type="hidden" name="remove_image" id="removeImageFlag"
                                                value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Details Tab -->
                        <div class="tab-pane fade" id="academic-details" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Academic Details</div>
                                <div class="help-content">
                                    Current program enrollment and academic standing information.
                                </div>
                            </div>

                            @if ($student->admission)
                                <div class="info-card">
                                    <div class="info-item">
                                        <strong>Admission Number:</strong> {{ $student->admission->admission_number }}
                                    </div>
                                    <div class="info-item">
                                        <strong>Admission Program:</strong>
                                        {{ $student->admission->program->name ?? 'N/A' }}
                                        <small class="text-muted d-block">Program they were admitted to</small>
                                    </div>
                                    <div class="info-item">
                                        <strong>Academic Year:</strong> {{ $student->admission->academic_year }}
                                    </div>
                                    <div class="info-item">
                                        <strong>Semester:</strong> {{ $student->admission->semester_number }}
                                    </div>
                                    <div class="info-item">
                                        <strong>Admission Status:</strong>
                                        <span class="status-badge status-{{ $student->admission->status }}">
                                            {{ ucwords(str_replace('_', ' ', $student->admission->status)) }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            @if ($student->activeProgramEnrollment)
                                <h5 class="section-title">Current Program Enrollment</h5>
                                <div class="info-card">
                                    <div class="info-item">
                                        <strong>Program:</strong>
                                        {{ $student->activeProgramEnrollment->program->name ?? 'N/A' }}
                                    </div>
                                    <div class="info-item">
                                        <strong>Enrollment Status:</strong>
                                        <span
                                            class="badge bg-{{ strtolower($student->activeProgramEnrollment->status) === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($student->activeProgramEnrollment->status) }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning d-flex align-items-start">
                                    <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                    <div>
                                        <strong>No Active Program Enrollment</strong>
                                        <div class="mt-1 text-muted">
                                            This student is not currently enrolled in any program.
                                            @if ($student->admission?->program)
                                                They were admitted to
                                                <strong>{{ $student->admission->program->name }}</strong> but haven't been
                                                enrolled yet.
                                            @endif
                                            <br><small>Use the form below to enroll them in a program.</small>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php $currentReg = $student->getCurrentSemesterRegistration(); @endphp
                            @if ($currentReg)
                                <h5 class="section-title">Current Semester Registration</h5>
                                <div class="info-card">
                                    <div class="info-item">
                                        <strong>Status:</strong>
                                        <span
                                            class="badge bg-{{ $currentReg->status === 'registered' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($currentReg->status) }}
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <strong>Registered Credits:</strong>
                                        {{ $currentReg->total_registered_credits ?? 0 }}
                                    </div>
                                    <div class="info-item">
                                        <strong>Earned Credits:</strong> {{ $currentReg->total_earned_credits ?? 0 }}
                                    </div>
                                    @if ($currentReg->sgpa)
                                        <div class="info-item">
                                            <strong>SGPA:</strong> {{ number_format($currentReg->sgpa, 3) }}
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <h5 class="section-title">Academic Statistics</h5>
                            <div class="form-grid">
                                <div class="info-card">
                                    <div class="credit-amount">{{ $student->semesterRegistrations->count() }}</div>
                                    <div class="credit-label">Semester Registrations</div>
                                </div>
                                <div class="info-card">
                                    <div class="credit-amount">{{ $student->moduleEnrollments->count() }}</div>
                                    <div class="credit-label">Module Enrollments</div>
                                </div>
                                <div class="info-card">
                                    <div class="credit-amount">{{ $student->absentDaysCount() }}</div>
                                    <div class="credit-label">Total Absent Days</div>
                                </div>
                            </div>
                        </div>

                        <!-- Historical Data Tab -->
                        <div class="tab-pane fade" id="historical-data" role="tabpanel">
                            @include('students.partials.historical-data-tab', ['student' => $student])
                        </div>

                        <!-- Contact Details Tab -->
                        <div class="tab-pane fade" id="contact-details" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Contact Information</div>
                                <div class="help-content">
                                    Contact details and address information for the student.
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Next of Kin Full Name</label>
                                    <input type="text" name="next_of_kin_name" class="form-control"
                                        value="{{ old('next_of_kin_name', $student->next_of_kin_name) }}"
                                        placeholder="e.g., Jane Doe">
                                    @error('next_of_kin_name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Next of Kin Email</label>
                                    <input type="email" name="next_of_kin_email" class="form-control"
                                        value="{{ old('next_of_kin_email', $student->next_of_kin_email) }}"
                                        placeholder="nextofkin@email.com">
                                    @error('next_of_kin_email')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Next of Kin Phone</label>
                                    <input type="text" name="next_of_kin_phone" class="form-control"
                                        value="{{ old('next_of_kin_phone', $student->next_of_kin_phone) }}"
                                        placeholder="e.g., +267 71 000 000">
                                    @error('next_of_kin_phone')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Address Line 1</label>
                                <input type="text" name="address_line1" class="form-control"
                                    value="{{ old('address_line1', $student->address_line1) }}"
                                    placeholder="Street address, P.O. Box">
                                @error('address_line1')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Address Line 2</label>
                                <input type="text" name="address_line2" class="form-control"
                                    value="{{ old('address_line2', $student->address_line2) }}"
                                    placeholder="Apartment, suite, etc.">
                                @error('address_line2')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">City</label>
                                    <input type="text" name="city" class="form-control"
                                        value="{{ old('city', $student->city) }}" placeholder="City">
                                    @error('city')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">District</label>
                                    <input type="text" name="district" class="form-control"
                                        value="{{ old('district', $student->district) }}" placeholder="District/Region">
                                    @error('district')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" name="postal_code" class="form-control"
                                        value="{{ old('postal_code', $student->postal_code) }}"
                                        placeholder="ZIP/Postal code">
                                    @error('postal_code')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Medical Information Tab -->
                        @can('students.health')
                            <div class="tab-pane fade" id="medical-info" role="tabpanel">
                                <div class="help-text">
                                    <div class="help-title">Medical Information</div>
                                    <div class="help-content">
                                        Manage the student's medical information including health history, allergies,
                                        disabilities, and medical conditions.
                                    </div>
                                </div>

                                @php $medical = $student->medicalInformation; @endphp

                                <div class="form-group">
                                    <label class="form-label">Health History</label>
                                    <textarea name="health_history" class="form-textarea" rows="3" placeholder="Any relevant health history...">{{ old('health_history', $medical->health_history ?? '') }}</textarea>
                                    @error('health_history')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Immunization Records</label>
                                    <textarea name="immunization_records" class="form-textarea" rows="3" placeholder="List of immunizations...">{{ old('immunization_records', $medical->immunization_records ?? '') }}</textarea>
                                    @error('immunization_records')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <h5 class="section-title">Blood Type</h5>
                                <div class="checkbox-group">
                                    @foreach (['a_positive' => 'A+', 'a_negative' => 'A-', 'b_positive' => 'B+', 'b_negative' => 'B-', 'ab_positive' => 'AB+', 'ab_negative' => 'AB-', 'o_positive' => 'O+', 'o_negative' => 'O-'] as $field => $label)
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input blood-type-checkbox"
                                                id="{{ $field }}" name="{{ $field }}" value="1"
                                                {{ old($field, $medical->$field ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="{{ $field }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>

                                <h5 class="section-title">Dietary Restrictions / Allergies</h5>
                                <div class="checkbox-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="peanuts" name="peanuts"
                                            value="1" {{ old('peanuts', $medical->peanuts ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="peanuts">Peanut Allergy</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="red_meat" name="red_meat"
                                            value="1"
                                            {{ old('red_meat', $medical->red_meat ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="red_meat">No Red Meat</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="vegetarian" name="vegetarian"
                                            value="1"
                                            {{ old('vegetarian', $medical->vegetarian ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="vegetarian">Vegetarian</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Other Allergies</label>
                                    <textarea name="other_allergies" class="form-textarea" rows="2"
                                        placeholder="Please specify any other allergies...">{{ old('other_allergies', $medical->other_allergies ?? '') }}</textarea>
                                    @error('other_allergies')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <h5 class="section-title">Physical Disabilities</h5>
                                <div class="checkbox-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="left_leg" name="left_leg"
                                            value="1"
                                            {{ old('left_leg', $medical->left_leg ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="left_leg">Left Leg</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="right_leg" name="right_leg"
                                            value="1"
                                            {{ old('right_leg', $medical->right_leg ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="right_leg">Right Leg</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="left_hand" name="left_hand"
                                            value="1"
                                            {{ old('left_hand', $medical->left_hand ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="left_hand">Left Hand</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="right_hand" name="right_hand"
                                            value="1"
                                            {{ old('right_hand', $medical->right_hand ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="right_hand">Right Hand</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Other Disabilities</label>
                                    <textarea name="other_disabilities" class="form-textarea" rows="2"
                                        placeholder="Please specify any other disabilities...">{{ old('other_disabilities', $medical->other_disabilities ?? '') }}</textarea>
                                    @error('other_disabilities')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <h5 class="section-title">Sensory Impairments</h5>
                                <div class="checkbox-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="left_eye" name="left_eye"
                                            value="1"
                                            {{ old('left_eye', $medical->left_eye ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="left_eye">Left Eye</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="right_eye" name="right_eye"
                                            value="1"
                                            {{ old('right_eye', $medical->right_eye ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="right_eye">Right Eye</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="left_ear" name="left_ear"
                                            value="1"
                                            {{ old('left_ear', $medical->left_ear ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="left_ear">Left Ear</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="right_ear" name="right_ear"
                                            value="1"
                                            {{ old('right_ear', $medical->right_ear ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="right_ear">Right Ear</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Medical Conditions</label>
                                    <textarea name="medical_conditions" class="form-textarea" rows="3"
                                        placeholder="Any medical conditions the institution should be aware of...">{{ old('medical_conditions', $medical->medical_conditions ?? '') }}</textarea>
                                    @error('medical_conditions')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Medical Record Year</label>
                                    <input type="number" name="medical_year" class="form-control" min="2020"
                                        max="{{ date('Y') + 1 }}"
                                        value="{{ old('medical_year', $medical->year ?? date('Y')) }}"
                                        placeholder="{{ date('Y') }}">
                                    @error('medical_year')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endcan

                        <!-- Behavior Records Tab - ALWAYS AVAILABLE -->
                        <div class="tab-pane fade" id="behavior-info" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Behavior Records</div>
                                <div class="help-content">
                                    Manage behavioral records and incidents for this student.
                                </div>
                            </div>

                            @if ($student->studentBehaviour->count() > 0)
                                <h5 class="section-title">Existing Behavior Records</h5>
                                <div class="existing-attachments">
                                    @foreach ($student->studentBehaviour->sortByDesc('date') as $behavior)
                                        <div class="existing-attachment-item">
                                            <i class="fas fa-exclamation-triangle attachment-icon text-warning"></i>
                                            <div class="attachment-details">
                                                <div class="attachment-name">
                                                    <strong>{{ ucfirst(str_replace('_', ' ', $behavior->behaviour_type)) }}</strong>
                                                    - {{ $behavior->date->format('M d, Y') }}
                                                </div>
                                                <div class="attachment-info">
                                                    @if ($behavior->description)
                                                        {{ Str::limit($behavior->description, 100) }}<br>
                                                    @endif
                                                    @if ($behavior->action_taken)
                                                        <strong>Action:</strong> {{ $behavior->action_taken }}<br>
                                                    @endif
                                                    <strong>Reported by:</strong> {{ $behavior->reported_by }} •
                                                    <strong>Year:</strong> {{ $behavior->year }}
                                                </div>
                                            </div>
                                            <span
                                                class="attachment-status attachment-status-{{ $behavior->behaviour_type === 'positive' ? 'verified' : 'received' }}">
                                                {{ ucfirst($behavior->behaviour_type) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <h5 class="section-title">Add New Behavior Record</h5>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="behavior_date" class="form-control"
                                        value="{{ old('behavior_date') }}">
                                    @error('behavior_date')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Behavior Type</label>
                                    <select name="behaviour_type" class="form-select">
                                        <option value="">Select behavior type...</option>
                                        <option value="positive"
                                            {{ old('behaviour_type') === 'positive' ? 'selected' : '' }}>Positive</option>
                                        <option value="negative"
                                            {{ old('behaviour_type') === 'negative' ? 'selected' : '' }}>Negative</option>
                                        <option value="academic_misconduct"
                                            {{ old('behaviour_type') === 'academic_misconduct' ? 'selected' : '' }}>
                                            Academic Misconduct</option>
                                        <option value="disciplinary"
                                            {{ old('behaviour_type') === 'disciplinary' ? 'selected' : '' }}>Disciplinary
                                        </option>
                                        <option value="attendance"
                                            {{ old('behaviour_type') === 'attendance' ? 'selected' : '' }}>Attendance
                                            Issue</option>
                                        <option value="other" {{ old('behaviour_type') === 'other' ? 'selected' : '' }}>
                                            Other</option>
                                    </select>
                                    @error('behaviour_type')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Reported By</label>
                                    <input type="text" name="reported_by" class="form-control"
                                        value="{{ old('reported_by', auth()->user()->name ?? '') }}"
                                        placeholder="Name of person reporting">
                                    @error('reported_by')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="behavior_description" class="form-textarea" rows="4"
                                    placeholder="Detailed description of the behavior or incident...">{{ old('behavior_description') }}</textarea>
                                @error('behavior_description')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Action Taken</label>
                                <input type="text" name="action_taken" class="form-control"
                                    value="{{ old('action_taken') }}" placeholder="What action was taken (if any)">
                                @error('action_taken')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Remarks</label>
                                <textarea name="behavior_remarks" class="form-textarea" rows="3"
                                    placeholder="Additional remarks or follow-up notes...">{{ old('behavior_remarks') }}</textarea>
                                @error('behavior_remarks')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Year</label>
                                <input type="number" name="behavior_year" class="form-control" min="2020"
                                    max="{{ date('Y') + 1 }}" value="{{ old('behavior_year', date('Y')) }}"
                                    placeholder="{{ date('Y') }}">
                                @error('behavior_year')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Documents Tab -->
                        @if ($student->admission && $student->admission->attachments->count() > 0)
                            <div class="tab-pane fade" id="documents" role="tabpanel">
                                <div class="help-text">
                                    <div class="help-title">Student Documents</div>
                                    <div class="help-content">
                                        Documents attached to the student's admission record.
                                    </div>
                                </div>

                                <div class="existing-attachments">
                                    <h5 class="section-title">Admission Documents</h5>
                                    @foreach ($student->admission->attachments as $attachment)
                                        <div class="existing-attachment-item">
                                            @php
                                                $iconClass = Str::contains($attachment->mime_type, 'pdf')
                                                    ? 'fa-file-pdf'
                                                    : 'fa-file-image';
                                            @endphp
                                            <i class="fas {{ $iconClass }} attachment-icon"></i>
                                            <div class="attachment-details">
                                                <div class="attachment-name">
                                                    <a
                                                        href="{{ route('admissions.download-attachment', ['admission' => $student->admission, 'attachment' => $attachment]) }}">
                                                        {{ $attachment->original_name }}
                                                    </a>
                                                </div>
                                                <div class="attachment-info">
                                                    {{ $attachment->category }} •
                                                    {{ number_format($attachment->size_bytes / 1024, 1) }} KB •
                                                    Uploaded {{ $attachment->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                            <span class="attachment-status attachment-status-{{ $attachment->status }}">
                                                {{ $attachment->status }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Communications Tab -->
                        <div class="tab-pane fade" id="communications" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Communications History</div>
                                <div class="help-content">
                                    All emails and SMS messages sent directly to this student.
                                </div>
                            </div>

                            @php
                                $emails = $student
                                    ->emails()
                                    ->orderBy('created_at', 'desc')
                                    ->get()
                                    ->map(function ($email) {
                                        $email->comm_type = 'email';
                                        return $email;
                                    });
                                $messages = $student
                                    ->messages()
                                    ->orderBy('created_at', 'desc')
                                    ->get()
                                    ->map(function ($msg) {
                                        $msg->comm_type = 'sms';
                                        return $msg;
                                    });
                                $allComms = $emails->concat($messages)->sortByDesc('created_at')->values();
                            @endphp

                            <div class="comm-controls mb-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="text" id="commSearch" class="form-control"
                                            placeholder="Search communications...">
                                    </div>
                                    <div class="col-md-3">
                                        <select id="commTypeFilter" class="form-select">
                                            <option value="">All Types</option>
                                            <option value="email">Email Only</option>
                                            <option value="sms">SMS Only</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select id="commStatusFilter" class="form-select">
                                            <option value="">All Status</option>
                                            <option value="sent">Sent</option>
                                            <option value="queued">Queued</option>
                                            <option value="failed">Failed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            @if ($allComms->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover comm-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 80px;">Type</th>
                                                <th>Subject/Message</th>
                                                <th style="width: 150px;">Sent By</th>
                                                <th style="width: 150px;">Date</th>
                                                <th style="width: 100px;">Status</th>
                                                <th style="width: 80px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="commTableBody">
                                            @foreach ($allComms as $index => $comm)
                                                <tr class="comm-row" data-type="{{ $comm->comm_type }}"
                                                    data-status="{{ $comm->status }}"
                                                    data-search="{{ strtolower(($comm->comm_type === 'email' ? $comm->subject . ' ' . $comm->body : $comm->body) ?? '') }}">
                                                    <td>
                                                        @if ($comm->comm_type === 'email')
                                                            <span class="badge bg-primary"><i class="fas fa-envelope"></i>
                                                                Email</span>
                                                        @else
                                                            <span class="badge bg-success"><i class="fas fa-sms"></i>
                                                                SMS</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($comm->comm_type === 'email')
                                                            <div class="comm-subject-line">
                                                                <strong>{{ Str::limit($comm->subject, 50) }}</strong>
                                                            </div>
                                                            <div class="comm-preview">
                                                                {{ Str::limit(strip_tags($comm->body), 80) }}</div>
                                                            @if ($comm->attachment_path)
                                                                <small class="text-muted"><i class="fas fa-paperclip"></i>
                                                                    Attachment</small>
                                                            @endif
                                                        @else
                                                            <div class="comm-preview">{{ Str::limit($comm->body, 100) }}
                                                            </div>
                                                            @if ($comm->sms_count)
                                                                <small class="text-muted">{{ $comm->sms_count }}
                                                                    SMS</small>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td><small>{{ $comm->sender->full_name ?? ($comm->user->full_name ?? 'System') }}</small>
                                                    </td>
                                                    <td><small>{{ $comm->created_at->format('M d, Y g:i A') }}</small>
                                                    </td>
                                                    <td>
                                                        @if ($comm->status === 'sent')
                                                            <span class="badge bg-success">Sent</span>
                                                        @elseif($comm->status === 'queued')
                                                            <span class="badge bg-info">Queued</span>
                                                        @elseif($comm->status === 'failed')
                                                            <span class="badge bg-danger">Failed</span>
                                                        @else
                                                            <span
                                                                class="badge bg-secondary">{{ ucfirst($comm->status) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary"
                                                            onclick="viewCommDetail({{ $index }})">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="comm-pagination d-flex justify-content-between align-items-center mt-3">
                                    <div class="comm-info">
                                        Showing <span id="commStart">1</span> to <span id="commEnd">10</span> of <span
                                            id="commTotal">{{ $allComms->count() }}</span> communications
                                    </div>
                                    <div class="pagination-controls" id="commPagination"></div>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No communications sent to this student yet.
                                </div>
                            @endif
                        </div>

                        <!-- Communication Detail Modal -->
                        <div class="modal fade" id="commDetailModal" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="commDetailTitle"></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body" id="commDetailBody"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="form-actions">
                    <div class="form-actions-left">
                        <a href="{{ route('students.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Students
                        </a>
                    </div>
                    <div class="form-actions-right">
                        @can('students.delete')
                            @if (strtolower($student->status) === 'active')
                                <button type="button" class="btn btn-warning"
                                    onclick="showWithdrawalModal({{ $student->id }}, '{{ $student->full_name }}')">
                                    <i class="fas fa-user-times"></i> Withdraw Student
                                </button>
                            @endif
                        @endcan
                        @can('students.edit')
                            <button type="button" class="btn btn-primary" id="submitBtn" onclick="submitStudentForm()">
                                <i class="fas fa-save"></i> Update Student
                            </button>
                        @endcan
                    </div>
                </div>

                <div class="meta-info mt-2">
                    <div class="meta-info-row">
                        <span class="meta-info-label">Student Since:</span>
                        <span class="meta-info-value">{{ $student->created_at->format('M d, Y g:i A') }}</span>
                    </div>
                    <div class="meta-info-row">
                        <span class="meta-info-label">Last Updated:</span>
                        <span class="meta-info-value">{{ $student->updated_at->format('M d, Y g:i A') }}</span>
                    </div>
                    @if ($student->admission)
                        <div class="meta-info-row">
                            <span class="meta-info-label">Admission Number:</span>
                            <span class="meta-info-value">{{ $student->admission->admission_number }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Withdrawal Modal -->
    <div class="modal fade" id="withdrawalModal" tabindex="-1" aria-labelledby="withdrawalModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="withdrawalModalLabel">
                        <i class="fas fa-user-times text-warning me-2"></i>
                        Withdraw Student
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="withdrawalForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Student:</strong> <span id="studentName"></span>
                        </div>

                        <div class="mb-3">
                            <label for="withdrawalType" class="form-label">
                                <i class="fas fa-cogs me-2"></i>Withdrawal Type
                            </label>
                            <select class="form-select" id="withdrawalType" name="withdrawal_type" required>
                                <option value="">Select withdrawal type...</option>
                                <option value="soft">Soft Withdrawal - Keep Records</option>
                                <option value="hard">Hard Withdrawal - Delete Records</option>
                            </select>
                            <div class="form-text">
                                <strong>Soft Withdrawal:</strong> Marks student as withdrawn but keeps all records for audit
                                purposes.<br>
                                <strong>Hard Withdrawal:</strong> Deletes student records and resets admission to
                                'submitted' status for fresh start.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="withdrawalReason" class="form-label">
                                <i class="fas fa-comment me-2"></i>Reason for Withdrawal
                            </label>
                            <textarea class="form-control" id="withdrawalReason" name="withdrawal_reason" rows="3"
                                placeholder="Enter reason for withdrawal..."></textarea>
                        </div>

                        <div id="hardWithdrawalWarning" class="alert alert-danger" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> Hard withdrawal will permanently delete all student records including:
                            <ul class="mb-0 mt-2">
                                <li>Student program enrollments</li>
                                <li>Semester registrations</li>
                                <li>Module enrollments</li>
                                <li>Base class allocations</li>
                                <li>Assessment results</li>
                                <li>Sponsor relationships</li>
                            </ul>
                            The admission will be reset to 'submitted' status for potential re-admission.
                        </div>

                        <div id="softWithdrawalInfo" class="alert alert-warning" style="display: none;">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Soft Withdrawal:</strong> This will mark the student as withdrawn but preserve all
                            records for audit and reporting purposes.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-warning" id="confirmWithdrawalBtn">
                            <i class="fas fa-user-times me-2"></i>Confirm Withdrawal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        function submitStudentForm() {
            const form = document.getElementById('editStudentForm');
            const submitBtn = document.getElementById('submitBtn');

            if (!form) {
                console.error('Form not found');
                return;
            }

            if (submitBtn) {
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
                submitBtn.disabled = true;
            }

            if (!form.checkValidity()) {
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Student';
                    submitBtn.disabled = false;
                }
                form.reportValidity();
                return;
            }

            form.submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const firstInput = document.querySelector('input[name="first_name"]');
            if (firstInput) {
                firstInput.focus();
            }

            const bloodTypeCheckboxes = document.querySelectorAll('.blood-type-checkbox');
            bloodTypeCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        bloodTypeCheckboxes.forEach(otherCheckbox => {
                            if (otherCheckbox !== this) {
                                otherCheckbox.checked = false;
                            }
                        });
                    }
                });
            });

            const behaviorForm = document.querySelector('form');
            if (behaviorForm) {
                const behaviorInputs = ['behavior_date', 'behaviour_type', 'behavior_description'];
                const hasAnyBehaviorData = behaviorInputs.some(inputName => {
                    const input = document.querySelector(`[name="${inputName}"]`);
                    return input && input.value.trim() !== '';
                });

                if (hasAnyBehaviorData) {
                    behaviorForm.addEventListener('submit', function(e) {
                        const behaviorDate = document.querySelector('[name="behavior_date"]');
                        const behaviorType = document.querySelector('[name="behaviour_type"]');

                        if (behaviorDate && behaviorDate.value && behaviorType && behaviorType.value) {
                            const confirmMsg =
                                `You are about to add a ${behaviorType.value} behavior record for ${behaviorDate.value}. Continue?`;
                            if (!confirm(confirmMsg)) {
                                e.preventDefault();
                                return false;
                            }
                        }
                    });
                }
            }

            const profileImageInput = document.getElementById('profileImageInput');
            const uploadArea = document.getElementById('uploadArea');
            const removeBtn = document.getElementById('removeBtn');
            const removeImageFlag = document.getElementById('removeImageFlag');
            const currentImage = document.getElementById('currentImage');
            const imagePlaceholder = document.getElementById('imagePlaceholder');

            let isRemovedState = false;

            if (uploadArea) {
                uploadArea.addEventListener('click', () => profileImageInput.click());
            }
            const overlayUpload = document.getElementById('overlayUpload');
            if (overlayUpload) {
                overlayUpload.addEventListener('click', function(e) {
                    e.stopPropagation();
                    profileImageInput.click();
                });
            }

            if (profileImageInput) {
                profileImageInput.addEventListener('change', handleFileSelect);
            }

            if (removeBtn) {
                removeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (isRemovedState) {
                        restoreImage();
                    } else {
                        removeImage();
                    }
                });
            }

            if (uploadArea) {
                uploadArea.addEventListener('dragover', handleDragOver);
                uploadArea.addEventListener('dragleave', handleDragLeave);
                uploadArea.addEventListener('drop', handleFileDrop);
            }

            function handleFileSelect(e) {
                const file = e.target.files[0];
                if (file && validateFile(file)) {
                    previewImage(file);
                    restoreImageState();
                }
            }

            function validateFile(file) {
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                const maxSize = 2 * 1024 * 1024; // 2MB

                if (!validTypes.includes(file.type)) {
                    showAlert('Please select a valid image file (JPG, PNG, or GIF)', 'error');
                    profileImageInput.value = '';
                    return false;
                }

                if (file.size > maxSize) {
                    showAlert('File size must be less than 2MB', 'error');
                    profileImageInput.value = '';
                    return false;
                }

                return true;
            }

            function previewImage(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (currentImage) {
                        currentImage.src = e.target.result;
                        const overlay = uploadArea.querySelector('.image-overlay');
                        if (overlay) {
                            overlay.innerHTML = '<i class="fas fa-check"></i>';
                        }
                    } else if (imagePlaceholder) {
                        const newImageHtml = `
                            <img src="${e.target.result}" alt="Student Photo Preview" id="currentImage" class="profile-preview" 
                                 style="border-radius: 3px;">
                            <div class="image-overlay">
                                <i class="fas fa-check"></i>
                            </div>
                        `;
                        uploadArea.innerHTML = newImageHtml;
                        uploadArea.addEventListener('click', () => profileImageInput.click());
                    }

                    showAlert('Image ready for upload!', 'success');
                };
                reader.readAsDataURL(file);
            }

            function removeImage() {
                isRemovedState = true;
                removeImageFlag.value = '1';
                profileImageInput.value = '';

                if (currentImage) {
                    currentImage.classList.add('image-removed');
                }

                if (removeBtn) {
                    removeBtn.innerHTML = '<i class="fas fa-undo"></i> Restore';
                    removeBtn.className = 'upload-btn';
                }

                showAlert('Image will be removed when you save', 'warning');
            }

            function restoreImage() {
                isRemovedState = false;
                removeImageFlag.value = '0';

                if (currentImage) {
                    currentImage.classList.remove('image-removed');
                }

                if (removeBtn) {
                    removeBtn.innerHTML = '<i class="fas fa-trash"></i> Remove';
                    removeBtn.className = 'remove-btn';
                }

                restoreImageState();
            }

            function restoreImageState() {
                if (currentImage) {
                    currentImage.style.border = '4px solid #e2e8f0';
                    currentImage.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
                }
                isRemovedState = false;
                removeImageFlag.value = '0';
            }

            function handleDragOver(e) {
                e.preventDefault();
                uploadArea.classList.add('drag-active');
            }

            function handleDragLeave(e) {
                e.preventDefault();
                uploadArea.classList.remove('drag-active');
            }

            function handleFileDrop(e) {
                e.preventDefault();
                uploadArea.classList.remove('drag-active');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];
                    if (validateFile(file)) {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        profileImageInput.files = dt.files;

                        previewImage(file);
                        restoreImageState();
                    }
                }
            }

            function showAlert(message, type) {
                const alertDiv = document.createElement('div');
                alertDiv.className =
                    `alert alert-${type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'success'} alert-dismissible fade show position-fixed`;
                alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 350px;';
                alertDiv.innerHTML = `
                    <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : type === 'warning' ? 'exclamation-circle' : 'check-circle'}"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                document.body.appendChild(alertDiv);
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 3000);
            }
        });

        function showWithdrawalModal(studentId, studentName) {
            document.getElementById('studentName').textContent = studentName;
            document.getElementById('withdrawalForm').action = `/students/${studentId}`;
            document.getElementById('withdrawalType').value = '';
            document.getElementById('withdrawalReason').value = '';
            document.getElementById('hardWithdrawalWarning').style.display = 'none';
            document.getElementById('softWithdrawalInfo').style.display = 'none';

            const modal = new bootstrap.Modal(document.getElementById('withdrawalModal'));
            modal.show();
        }

        // Handle withdrawal type change
        document.getElementById('withdrawalType').addEventListener('change', function() {
            const withdrawalType = this.value;
            const hardWarning = document.getElementById('hardWithdrawalWarning');
            const softInfo = document.getElementById('softWithdrawalInfo');

            if (withdrawalType === 'hard') {
                hardWarning.style.display = 'block';
                softInfo.style.display = 'none';
            } else if (withdrawalType === 'soft') {
                hardWarning.style.display = 'none';
                softInfo.style.display = 'block';
            } else {
                hardWarning.style.display = 'none';
                softInfo.style.display = 'none';
            }
        });

        // Handle form submission
        document.getElementById('withdrawalForm').addEventListener('submit', function(e) {
            const withdrawalType = document.getElementById('withdrawalType').value;
            if (!withdrawalType) {
                e.preventDefault();
                alert('Please select a withdrawal type.');
                return;
            }

            if (withdrawalType === 'hard') {
                const confirmed = confirm(
                    'Are you absolutely sure you want to permanently delete all records for this student? This action cannot be undone!'
                );
                if (!confirmed) {
                    e.preventDefault();
                    return;
                }
            } else {
                const confirmed = confirm('Are you sure you want to withdraw this student?');
                if (!confirmed) {
                    e.preventDefault();
                    return;
                }
            }
        });

        // Communications Tab - Pagination and Filtering
        const commsData = @json($allComms ?? []);
        let currentPage = 1;
        const perPage = 10;

        function filterComms() {
            const searchTerm = document.getElementById('commSearch')?.value.toLowerCase() || '';
            const typeFilter = document.getElementById('commTypeFilter')?.value || '';
            const statusFilter = document.getElementById('commStatusFilter')?.value || '';

            const rows = document.querySelectorAll('.comm-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const searchText = row.getAttribute('data-search') || '';
                const type = row.getAttribute('data-type') || '';
                const status = row.getAttribute('data-status') || '';

                const matchesSearch = searchText.includes(searchTerm);
                const matchesType = !typeFilter || type === typeFilter;
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesType && matchesStatus) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            currentPage = 1;
            paginateComms();
            return visibleCount;
        }

        function paginateComms() {
            const rows = Array.from(document.querySelectorAll('.comm-row:not(.hidden)'));
            const totalVisible = rows.length;
            const totalPages = Math.ceil(totalVisible / perPage);

            rows.forEach(row => row.style.display = 'none');
            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            rows.slice(start, end).forEach(row => row.style.display = '');

            document.getElementById('commStart').textContent = totalVisible > 0 ? start + 1 : 0;
            document.getElementById('commEnd').textContent = Math.min(end, totalVisible);
            document.getElementById('commTotal').textContent = totalVisible;

            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            const container = document.getElementById('commPagination');
            if (!container) return;

            let html = '';

            // Previous button
            html += `<button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                <i class="fas fa-chevron-left"></i>
            </button>`;

            // Page numbers (show max 5 pages)
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);

            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<button onclick="changePage(${i})" class="${i === currentPage ? 'active' : ''}">${i}</button>`;
            }

            // Next button
            html += `<button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}>
                <i class="fas fa-chevron-right"></i>
            </button>`;

            container.innerHTML = html;
        }

        function changePage(page) {
            const rows = document.querySelectorAll('.comm-row:not(.hidden)');
            const totalPages = Math.ceil(rows.length / perPage);

            if (page < 1 || page > totalPages) return;

            currentPage = page;
            paginateComms();
        }

        function viewCommDetail(index) {
            const comm = commsData[index];
            if (!comm) return;

            const modal = new bootstrap.Modal(document.getElementById('commDetailModal'));
            const title = document.getElementById('commDetailTitle');
            const body = document.getElementById('commDetailBody');

            if (comm.comm_type === 'email') {
                title.innerHTML = '<i class="fas fa-envelope text-primary me-2"></i> Email Details';
                body.innerHTML = `
                    <div class="mb-3">
                        <strong>Subject:</strong><br>
                        <div class="mt-1">${comm.subject || 'No subject'}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Message:</strong><br>
                        <div class="mt-1 p-3 bg-light rounded">${comm.body || 'No content'}</div>
                    </div>
                    ${comm.attachment_path ? '<div class="mb-3"><strong>Attachment:</strong> <i class="fas fa-paperclip"></i> File attached</div>' : ''}
                    <div class="mb-3">
                        <strong>Sent by:</strong> ${comm.sender?.full_name || comm.user?.full_name || 'System'}<br>
                        <strong>Date:</strong> ${new Date(comm.created_at).toLocaleString()}<br>
                        <strong>Status:</strong> <span class="badge bg-${comm.status === 'sent' ? 'success' : comm.status === 'queued' ? 'info' : 'danger'}">${comm.status}</span>
                    </div>
                `;
            } else {
                title.innerHTML = '<i class="fas fa-sms text-success me-2"></i> SMS Details';
                body.innerHTML = `
                    <div class="mb-3">
                        <strong>Message:</strong><br>
                        <div class="mt-1 p-3 bg-light rounded">${comm.body || 'No content'}</div>
                    </div>
                    ${comm.sms_count ? `<div class="mb-3"><strong>SMS Count:</strong> ${comm.sms_count}</div>` : ''}
                    <div class="mb-3">
                        <strong>Sent by:</strong> ${comm.sender?.full_name || comm.user?.full_name || 'System'}<br>
                        <strong>Date:</strong> ${new Date(comm.created_at).toLocaleString()}<br>
                        <strong>Status:</strong> <span class="badge bg-${comm.status === 'sent' ? 'success' : comm.status === 'queued' ? 'info' : 'danger'}">${comm.status}</span>
                    </div>
                `;
            }

            modal.show();
        }

        // Initialize filters
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('commSearch')?.addEventListener('input', filterComms);
            document.getElementById('commTypeFilter')?.addEventListener('change', filterComms);
            document.getElementById('commStatusFilter')?.addEventListener('change', filterComms);

            // Initial pagination
            if (document.getElementById('commPagination')) {
                paginateComms();
            }
        });
    </script>
@endsection
