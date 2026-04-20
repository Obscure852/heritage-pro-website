@extends('layouts.master')
@section('title')
    Students Settings
@endsection
@section('css')
    <style>
        .students-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
        }

        .students-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .students-body {
            padding: 24px;
        }

        /* Card Border */
        .card {
            border: none;
            box-shadow: none;
        }

        /* Tab Styling */
        .nav-tabs-custom {
            border-bottom: 1px solid #e5e7eb;
            gap: 8px;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: #6b7280;
            font-weight: 500;
            padding: 12px 20px;
            transition: all 0.2s ease;
            border-radius: 0;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #4e73df;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            border-bottom-color: #4e73df;
            background: transparent;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #4e73df;
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
            line-height: 1.5;
            margin: 0;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select,
        textarea.form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus,
        textarea.form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .btn {
            padding: 10px 16px;
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

        .btn-light {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background: #e9ecef;
            color: #495057;
        }

        /* Table Styling */
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        /* Action Buttons (Table) */
        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 16px;
        }

        .required::after {
            content: '*';
            color: #dc2626;
            margin-left: 4px;
        }

        .import-section {
            background: #f9fafb;
            padding: 20px;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
            margin-top: 16px;
        }

        /* Custom File Input */
        .custom-file-input {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .custom-file-input input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #fff;
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-input-label:hover {
            border-color: #4e73df;
            background: #f0f9ff;
        }

        .file-input-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .file-input-text {
            flex: 1;
        }

        .file-input-text .file-label {
            font-weight: 500;
            color: #374151;
            display: block;
            margin-bottom: 2px;
        }

        .file-input-text .file-hint {
            font-size: 13px;
            color: #6b7280;
        }

        .file-input-text .file-selected {
            font-size: 13px;
            color: #4e73df;
            font-weight: 500;
        }

        .example-table {
            font-size: 11px;
            margin-bottom: 16px;
        }

        .example-table th {
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 600;
        }

        .modal-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            border-radius: 3px 3px 0 0;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-title {
            font-weight: 600;
        }

        /* Button Loading State */
        .btn-loading .btn-spinner {
            display: none;
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
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('students.index') }}">Students</a>
        @endslot
        @slot('title')
            Settings
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div id="messageContainer"></div>

    <div class="students-container">
        <div class="students-header">
            <h4 class="mb-1 text-white"><i class="fas fa-cog me-2"></i>Students Settings</h4>
            <p class="mb-0 opacity-75">Manage filters, student types, and import textbooks</p>
        </div>
        <div class="students-body">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start" role="tablist">
                        @can('canImportData')
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#textbooks" role="tab">
                                    <i class="fas fa-book-open me-2 text-muted"></i>Books Import
                                </a>
                            </li>
                        @endcan
                        <li class="nav-item">
                            <a class="nav-link {{ !auth()->user()->can('canImportData') ? 'active' : '' }}" data-bs-toggle="tab" href="#filters" role="tab">
                                <i class="fas fa-filter me-2 text-muted"></i>Filters
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#types" role="tab">
                                <i class="fas fa-tags me-2 text-muted"></i>Student Types
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#settings" role="tab">
                                <i class="fas fa-sliders-h me-2 text-muted"></i>General Settings
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        @can('canImportData')
                            <div class="tab-pane active" id="textbooks" role="tabpanel">
                                <div class="help-text">
                                    <div class="help-title">Import Textbooks</div>
                                    <div class="help-content">
                                        Upload an Excel file (.xls or .xlsx) to bulk import textbooks into the system. Use the format shown in the example table below.
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered example-table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>ISBN</th>
                                                <th>Author</th>
                                                <th>Grade</th>
                                                <th>Publication Year</th>
                                                <th>Publisher</th>
                                                <th>Genre</th>
                                                <th>Language</th>
                                                <th>Format</th>
                                                <th>Quantity</th>
                                                <th>Status</th>
                                                <th>Location</th>
                                                <th>Price</th>
                                                <th>Dewey Decimal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Man's Search For Meaning</td>
                                                <td>9789604537457</td>
                                                <td>Victor Frankel</td>
                                                <td>F1</td>
                                                <td>2000</td>
                                                <td>Beacon Press</td>
                                                <td>Non Fiction</td>
                                                <td>English</td>
                                                <td>Paperback</td>
                                                <td>75</td>
                                                <td>Available</td>
                                                <td>Psychology</td>
                                                <td>321.00</td>
                                                <td>MDS 150.195</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="import-section">
                                    <form action="{{ route('students.import-books') }}" method="post" enctype="multipart/form-data" id="importForm">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Upload Excel File</label>
                                                    <div class="custom-file-input">
                                                        <input type="file" name="file" id="upload_file" accept=".xls,.xlsx" required>
                                                        <label for="upload_file" class="file-input-label">
                                                            <div class="file-input-icon">
                                                                <i class="fas fa-file-excel"></i>
                                                            </div>
                                                            <div class="file-input-text">
                                                                <span class="file-label">Choose Excel File</span>
                                                                <span class="file-hint" id="fileHint">.xls or .xlsx format</span>
                                                                <span class="file-selected d-none" id="fileName"></span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="form-check mb-3">
                                                    <input type="checkbox" class="form-check-input" id="deletebooks" name="delete_books">
                                                    <label class="form-check-label" for="deletebooks">
                                                        Delete existing books before import
                                                    </label>
                                                </div>

                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bx bx-upload"></i> Import Textbooks
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endcan

                        <div class="tab-pane {{ !auth()->user()->can('canImportData') ? 'active' : '' }}" id="filters" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Student Filters</div>
                                <div class="help-content">
                                    Create custom filters to categorize students (e.g., Chess Club, Drama Club, Sports Team). These filters can be used when searching and reporting on students.
                                </div>
                            </div>

                            <form action="{{ route('students.store-students-settings') }}" method="POST" class="mb-4">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="name" placeholder="Enter filter name (e.g., Chess Club)" required>
                                            <button class="btn btn-primary" type="submit">
                                                <i class="bx bx-plus"></i> Add Filter
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;">#</th>
                                            <th>Filter Name</th>
                                            <th style="width: 100px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (!empty($filters))
                                            @foreach ($filters as $index => $filter)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $filter->name ?? '' }}</td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button type="button" class="btn btn-outline-info edit-filter"
                                                                data-bs-toggle="modal" data-bs-target="#editFilterModal"
                                                                data-id="{{ $filter->id }}" data-name="{{ $filter->name }}">
                                                                <i class="bx bx-edit-alt"></i>
                                                            </button>
                                                            <form method="POST" action="{{ route('students.destroy-student-filter', $filter->id) }}" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-outline-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this filter?')">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">
                                                    <i class="fas fa-filter" style="font-size: 32px; opacity: 0.3;"></i>
                                                    <p class="mt-2 mb-0">No filters created yet</p>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane" id="types" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Student Types</div>
                                <div class="help-content">
                                    Define student types to categorize students by special needs or characteristics. You can optionally mark types for payment exemptions.
                                </div>
                            </div>

                            <form action="{{ route('students.store-students-type') }}" method="POST" class="mb-4">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="type" class="form-label required">Type Name</label>
                                        <input class="form-control" placeholder="e.g., Needy, Special Needs"
                                            type="text" name="type" value="{{ old('type') }}" required>
                                        @error('type')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="description" class="form-label required">Description</label>
                                        <textarea placeholder="Describe this student type" class="form-control" name="description"
                                            id="description" rows="3" required>{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="exempt"
                                                id="exempt" value="1" {{ old('exempt') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="exempt">
                                                Eligible for payment exemptions
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="color" class="form-label">Type Color</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" class="form-control form-control-color" name="color"
                                                id="color" value="{{ old('color', '#6366f1') }}" title="Choose a color">
                                            <input type="text" class="form-control" id="colorHex"
                                                value="{{ old('color', '#6366f1') }}"
                                                pattern="^#[0-9A-Fa-f]{6}$" maxlength="7"
                                                style="width: 100px; font-family: monospace;">
                                        </div>
                                        <small class="text-muted">Used to highlight students of this type</small>
                                    </div>
                                </div>

                                <button class="btn btn-primary" type="submit">
                                    <i class="bx bx-plus"></i> Add Type
                                </button>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;">#</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th style="width: 80px;">Color</th>
                                            <th style="width: 120px;">Exemption</th>
                                            <th style="width: 100px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (!empty($types))
                                            @foreach ($types as $index => $type)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td><strong>{{ $type->type ?? '' }}</strong></td>
                                                    <td>{{ $type->description ?? '' }}</td>
                                                    <td>
                                                        @if($type->color)
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span style="display: inline-block; width: 24px; height: 24px; border-radius: 4px; background-color: {{ $type->color }}; border: 1px solid #dee2e6;"></span>
                                                                <small class="text-muted" style="font-family: monospace;">{{ $type->color }}</small>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($type->exempt === 1)
                                                            <span class="badge bg-success">Yes</span>
                                                        @else
                                                            <span class="badge bg-secondary">No</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button type="button" class="btn btn-outline-info edit-type"
                                                                data-bs-toggle="modal" data-bs-target="#editTypeModal"
                                                                data-id="{{ $type->id }}"
                                                                data-name="{{ $type->type }}"
                                                                data-description="{{ $type->description }}"
                                                                data-exempt="{{ $type->exempt }}"
                                                                data-color="{{ $type->color ?? '#6366f1' }}">
                                                                <i class="bx bx-edit-alt"></i>
                                                            </button>
                                                            <form method="POST" action="{{ route('students.destroy-student-type', $type->id) }}" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-outline-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this type?')">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">
                                                    <i class="fas fa-tags" style="font-size: 32px; opacity: 0.3;"></i>
                                                    <p class="mt-2 mb-0">No student types created yet</p>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane" id="settings" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">General Settings</div>
                                <div class="help-content">
                                    Configure general settings for the student module.
                                </div>
                            </div>
                            <p class="text-muted text-center py-5">
                                <i class="fas fa-cog" style="font-size: 48px; opacity: 0.2;"></i><br>
                                No general settings available at this time.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Filter Modal -->
    <div class="modal fade" id="editFilterModal" tabindex="-1" aria-labelledby="editFilterModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFilterModalLabel">Edit Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editFilterForm">
                        <div class="mb-3">
                            <label for="editFilterName" class="form-label">Filter Name</label>
                            <input type="text" class="form-control" id="editFilterName" name="filterName" required>
                        </div>
                        <input type="hidden" id="editFilterId" name="filterId">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Type Modal -->
    <div class="modal fade" id="editTypeModal" tabindex="-1" aria-labelledby="editTypeModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTypeModalLabel">Edit Student Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editTypeForm">
                        <div class="mb-3">
                            <label for="editTypeName" class="form-label">Type Name</label>
                            <input type="text" class="form-control" id="editTypeName" name="type" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTypeDescription" class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editTypeDescription" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="exempt" id="editTypeExempt" value="1">
                                    <label class="form-check-label" for="editTypeExempt">Eligible for payment exemptions</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editTypeColor" class="form-label">Type Color</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" class="form-control form-control-color" name="color"
                                        id="editTypeColor" value="#6366f1" title="Choose a color">
                                    <input type="text" class="form-control" id="editTypeColorHex"
                                        value="#6366f1" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7"
                                        style="width: 100px; font-family: monospace;">
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="editTypeId" name="TypeId">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-loading" id="saveTypeBtn">
                                <span class="btn-text"><i class="fas fa-save me-1"></i> Save Changes</span>
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
        document.addEventListener('DOMContentLoaded', function() {
            // File input handler
            const fileInput = document.getElementById('upload_file');
            if (fileInput) {
                const fileHint = document.getElementById('fileHint');
                const fileName = document.getElementById('fileName');

                fileInput.addEventListener('change', function(e) {
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        fileHint.classList.add('d-none');
                        fileName.classList.remove('d-none');
                        fileName.textContent = file.name;
                    } else {
                        fileHint.classList.remove('d-none');
                        fileName.classList.add('d-none');
                        fileName.textContent = '';
                    }
                });
            }

            // Message display function
            function displayMessage(message, type = 'success') {
                const messageContainer = document.getElementById('messageContainer');
                messageContainer.innerHTML = `
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="alert alert-${type} alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                            <i class="mdi mdi-check-all label-icon"></i>
                            <strong>${message}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </div>`;
            }

            const updateMessage = localStorage.getItem('updateMessage');
            if (updateMessage) {
                displayMessage(updateMessage);
                localStorage.removeItem('updateMessage');
            }

            // Edit Filter Modal
            const editFilterLinks = document.querySelectorAll('.edit-filter');
            editFilterLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    const filterId = this.getAttribute('data-id');
                    const filterName = this.getAttribute('data-name');

                    document.getElementById('editFilterId').value = filterId;
                    document.getElementById('editFilterName').value = filterName;
                });
            });

            document.getElementById('editFilterForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const filterId = document.getElementById('editFilterId').value;
                const filterName = document.getElementById('editFilterName').value;
                const updateFilterUrl =
                    `{{ route('students.update-student-filter', ['filter' => ':tempFilterId']) }}`.replace(
                        ':tempFilterId', filterId);

                fetch(updateFilterUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            name: filterName
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            localStorage.setItem('updateMessage', data.message);
                            var editFilterModal = bootstrap.Modal.getInstance(document.getElementById(
                                'editFilterModal'));
                            editFilterModal.hide();
                            location.reload();
                        } else {
                            alert('Error updating filter');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating filter:', error);
                        alert('Error updating filter');
                    });
            });

            // Color picker sync for create form
            const colorPicker = document.getElementById('color');
            const colorHex = document.getElementById('colorHex');
            if (colorPicker && colorHex) {
                colorPicker.addEventListener('input', function() {
                    colorHex.value = this.value.toUpperCase();
                });
                colorHex.addEventListener('input', function() {
                    if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                        colorPicker.value = this.value;
                    }
                });
            }

            // Edit Type Modal
            const editTypeLinks = document.querySelectorAll('.edit-type');
            editTypeLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    const typeId = this.getAttribute('data-id');
                    const typeName = this.getAttribute('data-name');
                    const typeDescription = this.getAttribute('data-description');
                    const typeExemption = this.getAttribute('data-exempt');
                    const typeColor = this.getAttribute('data-color') || '#6366f1';

                    document.getElementById('editTypeId').value = typeId;
                    document.getElementById('editTypeName').value = typeName;
                    document.getElementById('editTypeDescription').value = typeDescription;
                    document.getElementById('editTypeColor').value = typeColor;
                    document.getElementById('editTypeColorHex').value = typeColor.toUpperCase();

                    const exemptCheckbox = document.getElementById('editTypeExempt');
                    exemptCheckbox.checked = (typeExemption === '1');
                });
            });

            // Color picker sync for edit form
            const editColorPicker = document.getElementById('editTypeColor');
            const editColorHex = document.getElementById('editTypeColorHex');
            if (editColorPicker && editColorHex) {
                editColorPicker.addEventListener('input', function() {
                    editColorHex.value = this.value.toUpperCase();
                });
                editColorHex.addEventListener('input', function() {
                    if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                        editColorPicker.value = this.value;
                    }
                });
            }

            // Reset loading state when modal is hidden
            document.getElementById('editTypeModal').addEventListener('hidden.bs.modal', function() {
                const saveBtn = document.getElementById('saveTypeBtn');
                saveBtn.classList.remove('loading');
                saveBtn.disabled = false;
            });

            document.getElementById('editTypeForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const typeId = document.getElementById('editTypeId').value;
                const typeName = document.getElementById('editTypeName').value;
                const typeDescription = document.getElementById('editTypeDescription').value;
                const typeExempt = document.getElementById('editTypeExempt').checked;
                const typeColor = document.getElementById('editTypeColor').value;

                const saveBtn = document.getElementById('saveTypeBtn');

                // Show loading state
                saveBtn.classList.add('loading');
                saveBtn.disabled = true;

                const updateTypeUrl = `{{ route('students.update-student-type', ['id' => ':tempId']) }}`
                    .replace(':tempId', typeId);

                fetch(updateTypeUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            type: typeName,
                            description: typeDescription,
                            exempt: typeExempt,
                            color: typeColor
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => {
                                throw err;
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            localStorage.setItem('updateMessage', data.message);
                            var editTypeModal = bootstrap.Modal.getInstance(document.getElementById(
                                'editTypeModal'));
                            editTypeModal.hide();
                            location.reload();
                        } else {
                            // Reset loading state
                            saveBtn.classList.remove('loading');
                            saveBtn.disabled = false;
                            alert('Error updating type');
                        }
                    })
                    .catch(error => {
                        // Reset loading state
                        saveBtn.classList.remove('loading');
                        saveBtn.disabled = false;
                        console.error('Error updating type:', error);
                        if (error.message) {
                            alert(error.message);
                        } else {
                            alert('An unexpected error occurred.');
                        }
                    });
            });

            // Tab persistence
            const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    const activeTabHref = event.target.getAttribute('href');
                    localStorage.setItem('activeTab', activeTabHref);
                });
            });

            const activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                const tabTriggerEl = document.querySelector(`.nav-link[href="${activeTab}"]`);
                if (tabTriggerEl) {
                    const tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                }
            } else {
                const firstTabTriggerEl = document.querySelector('.nav-link[data-bs-toggle="tab"]');
                if (firstTabTriggerEl) {
                    const tab = new bootstrap.Tab(firstTabTriggerEl);
                    tab.show();
                }
            }

            // Import form confirmation
            const importForm = document.getElementById('importForm');
            if (importForm) {
                importForm.addEventListener('submit', function(event) {
                    var confirmMessage =
                        'Are you sure you want to import the books? This action cannot be undone.';
                    if (!confirm(confirmMessage)) {
                        event.preventDefault();
                    }
                });
            }
        });
    </script>
@endsection
