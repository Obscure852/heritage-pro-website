@extends('layouts.master')

@section('title')
    Assessment Module | Comments & Venues
@endsection

@section('css')
    <style>
        .settings-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-body {
            padding: 24px;
        }

        /* Stats in Header */
        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        /* Venue Form */
        .venue-form {
            background: #f9fafb;
            padding: 20px;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
            margin-bottom: 24px;
        }

        .venue-form h5 {
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        /* Controls/Filter Row */
        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
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

        /* Progress Bar in Venues */
        .utilization-bar {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .utilization-bar .progress {
            flex: 1;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
        }

        .utilization-bar .progress-bar {
            border-radius: 3px;
        }

        .utilization-bar .utilization-text {
            font-size: 12px;
            font-weight: 500;
            min-width: 40px;
            text-align: right;
        }

        /* Modal Styling */
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

        .modal-footer {
            border-top: 1px solid #e5e7eb;
        }

        /* Button styling */
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
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

        /* Type Badge */
        .type-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }

        .type-badge.classroom {
            background: #dbeafe;
            color: #1e40af;
        }

        .type-badge.hall {
            background: #fef3c7;
            color: #92400e;
        }

        .type-badge.laboratory {
            background: #d1fae5;
            color: #065f46;
        }

        .type-badge.other {
            background: #e5e7eb;
            color: #374151;
        }

        /* Score Range Badge */
        .score-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            background: #dbeafe;
            color: #1e40af;
        }

        .points-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            background: #f3e8ff;
            color: #6b21a8;
        }

        /* Pagination Container */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Management
        @endslot
        @slot('title')
            Comments & Venues
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

    <div class="settings-container">
        <div class="settings-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-1 text-white"><i class="fas fa-clipboard-list me-2"></i>Comments & Venues</h4>
                    <p class="mb-0 opacity-75">Manage subject comments, overall comments, and venue settings</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $scoreComments->count() ?? 0 }}</h4>
                                <small class="opacity-75">Subject Comments</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $comments->count() ?? 0 }}</h4>
                                <small class="opacity-75">Overall Comments</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $venues->count() ?? 0 }}</h4>
                                <small class="opacity-75">Venues</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="settings-body">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start" role="tablist" id="commentsVenuesTabs">
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#subjectComments" role="tab">
                                <i class="fas fa-graduation-cap me-2 text-muted"></i>Subject Comments
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#overallComments" role="tab">
                                <i class="fas fa-file-alt me-2 text-muted"></i>Overall Comments
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#venues" role="tab">
                                <i class="fas fa-map-marker-alt me-2 text-muted"></i>Venues
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        <!-- Subject Comments Tab -->
                        <div class="tab-pane active" id="subjectComments" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Subject Comments Bank</div>
                                <div class="help-content">
                                    Define comments that are automatically generated based on student score ranges. These comments appear on subject reports.
                                </div>
                            </div>

                            <!-- Filter Row -->
                            <div class="row align-items-center mb-3">
                                <div class="col-lg-8 col-md-12">
                                    <div class="controls">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                    <input type="text" class="form-control" placeholder="Search comments..." id="searchSubjectComments">
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-6">
                                                <button type="button" class="btn btn-light w-100" id="resetSubjectCommentsFilter">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                                    <a href="{{ route('assessment.create-subject-comment') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> New Subject Comment
                                    </a>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="scorecomments-table" class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;">#</th>
                                            <th style="width: 150px;">Score Range</th>
                                            <th>Comment</th>
                                            <th style="width: 100px;" class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($scoreComments as $index => $scoreComment)
                                            <tr class="subject-comment-row" data-comment="{{ strtolower($scoreComment->comment) }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <span class="score-badge">{{ $scoreComment->min_score }} - {{ $scoreComment->max_score }}</span>
                                                </td>
                                                <td>{{ $scoreComment->comment }}</td>
                                                <td class="text-end">
                                                    <div class="action-buttons">
                                                        <a href="{{ route('assessment.edit-subject-comment', $scoreComment->id) }}"
                                                            class="btn btn-outline-info"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Edit">
                                                            <i class="bx bx-edit-alt"></i>
                                                        </a>
                                                        <form method="POST"
                                                            action="{{ route('assessment.delete-subject-comment', $scoreComment->id) }}"
                                                            class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Delete"
                                                                onclick="return confirmDelete('Score Comment');">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">
                                                    <i class="fas fa-graduation-cap" style="font-size: 32px; opacity: 0.3;"></i>
                                                    <p class="mt-2 mb-0">No subject comments created yet</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="pagination-container" id="subjectCommentsPagination">
                                <div class="text-muted">
                                    Showing <span id="subject-showing-from">0</span> to <span id="subject-showing-to">0</span> of <span id="subject-total-count">{{ $scoreComments->count() }}</span> comments
                                </div>
                                <nav id="subject-pagination-nav"></nav>
                            </div>
                        </div>

                        <!-- Overall Comments Tab -->
                        <div class="tab-pane" id="overallComments" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Overall Comments Bank</div>
                                <div class="help-content">
                                    Define comments based on overall points ranges. These comments appear on final report cards.
                                </div>
                            </div>

                            <!-- Filter Row -->
                            <div class="row align-items-center mb-3">
                                <div class="col-lg-8 col-md-12">
                                    <div class="controls">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                    <input type="text" class="form-control" placeholder="Search comments..." id="searchOverallComments">
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-6">
                                                <button type="button" class="btn btn-light w-100" id="resetOverallCommentsFilter">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                                    <a href="{{ route('assessment.create-comment') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> New Comment
                                    </a>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="comments-table" class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;">#</th>
                                            <th style="width: 120px;">Min Points</th>
                                            <th style="width: 120px;">Max Points</th>
                                            <th>Comment</th>
                                            <th style="width: 100px;" class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($comments as $index => $comment)
                                            <tr class="overall-comment-row" data-comment="{{ strtolower($comment->body ?? '') }}">
                                                <td>{{ $index + 1 }}</td>
                                                <td><span class="points-badge">{{ $comment->min_points }}</span></td>
                                                <td><span class="points-badge">{{ $comment->max_points }}</span></td>
                                                <td>{{ $comment->body ?? '' }}</td>
                                                <td class="text-end">
                                                    <div class="action-buttons">
                                                        <a href="{{ route('assessment.edit-comment', $comment->id) }}"
                                                            class="btn btn-outline-info"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Edit">
                                                            <i class="bx bx-edit-alt"></i>
                                                        </a>
                                                        <form method="POST"
                                                            action="{{ route('assessment.delete-comment', $comment->id) }}"
                                                            class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Delete"
                                                                onclick="return confirmDelete('Comment');">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    <i class="fas fa-file-alt" style="font-size: 32px; opacity: 0.3;"></i>
                                                    <p class="mt-2 mb-0">No overall comments created yet</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="pagination-container" id="overallCommentsPagination">
                                <div class="text-muted">
                                    Showing <span id="overall-showing-from">0</span> to <span id="overall-showing-to">0</span> of <span id="overall-total-count">{{ $comments->count() }}</span> comments
                                </div>
                                <nav id="overall-pagination-nav"></nav>
                            </div>
                        </div>

                        <!-- Venues Tab -->
                        <div class="tab-pane" id="venues" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Venue Management</div>
                                <div class="help-content">
                                    Add and manage classrooms, halls, laboratories, and other venues for class allocation and scheduling.
                                </div>
                            </div>

                            <div class="venue-form">
                                <h5><i class="fas fa-plus-circle me-2 text-primary"></i>Add New Venue</h5>
                                <form action="{{ route('assessment.create-venue') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Venue Name <small style="color:red">*</small></label>
                                            <input type="text" placeholder="e.g., Room 12" class="form-control" name="name" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Capacity <small style="color:red">*</small></label>
                                            <input type="number" placeholder="e.g., 32" class="form-control" name="capacity">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Type <small style="color:red">*</small></label>
                                            <select class="form-select" name="type">
                                                <option value="classroom">Classroom</option>
                                                <option value="hall">Hall</option>
                                                <option value="laboratory">Laboratory</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i> Add Venue
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Filter Row -->
                            <div class="row align-items-center mb-3">
                                <div class="col-lg-8 col-md-12">
                                    <div class="controls">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-lg-4 col-md-4 col-sm-6">
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                    <input type="text" class="form-control" placeholder="Search venues..." id="searchVenues">
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-6">
                                                <select class="form-select" id="venueTypeFilter">
                                                    <option value="">All Types</option>
                                                    <option value="classroom">Classroom</option>
                                                    <option value="hall">Hall</option>
                                                    <option value="laboratory">Laboratory</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-6">
                                                <button type="button" class="btn btn-light w-100" id="resetVenuesFilter">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="venues-table" class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;">#</th>
                                            <th>Name</th>
                                            <th style="width: 120px;">Type</th>
                                            <th style="width: 100px;">Capacity</th>
                                            <th style="width: 180px;">Utilization</th>
                                            <th style="width: 100px;" class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($venues as $index => $venue)
                                            <tr class="venue-row"
                                                data-name="{{ strtolower($venue->name) }}"
                                                data-type="{{ strtolower($venue->type) }}">
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong>{{ $venue->name }}</strong></td>
                                                <td>
                                                    <span class="type-badge {{ $venue->type }}">{{ ucfirst($venue->type) }}</span>
                                                </td>
                                                <td>{{ $venue->capacity }}</td>
                                                <td>
                                                    <div class="utilization-bar">
                                                        <div class="progress">
                                                            <div class="progress-bar {{ $venue->is_over_capacity ? 'bg-danger' : 'bg-success' }}"
                                                                role="progressbar"
                                                                style="width: {{ min($venue->utilization_percentage, 100) }}%">
                                                            </div>
                                                        </div>
                                                        <span class="utilization-text {{ $venue->is_over_capacity ? 'text-danger' : 'text-success' }}">
                                                            {{ $venue->utilization_percentage }}%
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="action-buttons">
                                                        <button type="button" class="btn btn-outline-info"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editVenueModal{{ $venue->id }}"
                                                            title="Edit">
                                                            <i class="bx bx-edit-alt"></i>
                                                        </button>
                                                        <form method="POST"
                                                            action="{{ route('assessment.destroy-venue', $venue->id) }}"
                                                            class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Delete"
                                                                onclick="return confirmDelete('venue');">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Edit Venue Modal -->
                                            <div class="modal fade" id="editVenueModal{{ $venue->id }}" tabindex="-1"
                                                aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Venue</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <form action="{{ route('assessment.update-venue', $venue->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Venue Name</label>
                                                                    <input type="text" class="form-control"
                                                                        name="name" value="{{ $venue->name }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Type</label>
                                                                    <select class="form-select" name="type" required>
                                                                        <option value="classroom" {{ $venue->type == 'classroom' ? 'selected' : '' }}>
                                                                            Classroom
                                                                        </option>
                                                                        <option value="hall" {{ $venue->type == 'hall' ? 'selected' : '' }}>
                                                                            Hall
                                                                        </option>
                                                                        <option value="laboratory" {{ $venue->type == 'laboratory' ? 'selected' : '' }}>
                                                                            Laboratory
                                                                        </option>
                                                                        <option value="other" {{ $venue->type == 'other' ? 'selected' : '' }}>
                                                                            Other
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Capacity</label>
                                                                    <input type="number" class="form-control"
                                                                        name="capacity" value="{{ $venue->capacity }}" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">
                                                                    <i class="fas fa-save me-1"></i> Update Venue
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">
                                                    <i class="fas fa-map-marker-alt" style="font-size: 32px; opacity: 0.3;"></i>
                                                    <p class="mt-2 mb-0">No venues created yet</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="pagination-container" id="venuesPagination">
                                <div class="text-muted">
                                    Showing <span id="venues-showing-from">0</span> to <span id="venues-showing-to">0</span> of <span id="venues-total-count">{{ $venues->count() }}</span> venues
                                </div>
                                <nav id="venues-pagination-nav"></nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Client-side filtering and pagination
        const itemsPerPage = 15;

        // Subject Comments
        let subjectCurrentPage = 1;

        function filterSubjectComments(resetPage = true) {
            if (resetPage) subjectCurrentPage = 1;

            const searchTerm = document.getElementById('searchSubjectComments').value.toLowerCase();
            const allRows = document.querySelectorAll('.subject-comment-row');
            let filteredRows = [];

            allRows.forEach(row => {
                const comment = row.dataset.comment || '';
                const matchesSearch = !searchTerm || comment.includes(searchTerm);

                if (matchesSearch) {
                    filteredRows.push(row);
                }
            });

            paginateRows(filteredRows, subjectCurrentPage, itemsPerPage, 'subject');
        }

        // Overall Comments
        let overallCurrentPage = 1;

        function filterOverallComments(resetPage = true) {
            if (resetPage) overallCurrentPage = 1;

            const searchTerm = document.getElementById('searchOverallComments').value.toLowerCase();
            const allRows = document.querySelectorAll('.overall-comment-row');
            let filteredRows = [];

            allRows.forEach(row => {
                const comment = row.dataset.comment || '';
                const matchesSearch = !searchTerm || comment.includes(searchTerm);

                if (matchesSearch) {
                    filteredRows.push(row);
                }
            });

            paginateRows(filteredRows, overallCurrentPage, itemsPerPage, 'overall');
        }

        // Venues
        let venuesCurrentPage = 1;

        function filterVenues(resetPage = true) {
            if (resetPage) venuesCurrentPage = 1;

            const searchTerm = document.getElementById('searchVenues').value.toLowerCase();
            const typeFilter = document.getElementById('venueTypeFilter').value.toLowerCase();
            const allRows = document.querySelectorAll('.venue-row');
            let filteredRows = [];

            allRows.forEach(row => {
                const name = row.dataset.name || '';
                const type = row.dataset.type || '';

                const matchesSearch = !searchTerm || name.includes(searchTerm);
                const matchesType = !typeFilter || type === typeFilter;

                if (matchesSearch && matchesType) {
                    filteredRows.push(row);
                }
            });

            paginateRows(filteredRows, venuesCurrentPage, itemsPerPage, 'venues');
        }

        function paginateRows(filteredRows, currentPage, perPage, prefix) {
            const allRows = document.querySelectorAll(`.${prefix === 'subject' ? 'subject-comment' : prefix === 'overall' ? 'overall-comment' : 'venue'}-row`);
            const totalFiltered = filteredRows.length;
            const totalPages = Math.ceil(totalFiltered / perPage);
            const startIndex = (currentPage - 1) * perPage;
            const endIndex = startIndex + perPage;

            // Hide all rows first
            allRows.forEach(row => row.style.display = 'none');

            // Show filtered and paginated rows
            filteredRows.forEach((row, index) => {
                if (index >= startIndex && index < endIndex) {
                    row.style.display = '';
                }
            });

            // Update showing info
            const showingFrom = totalFiltered > 0 ? startIndex + 1 : 0;
            const showingTo = Math.min(endIndex, totalFiltered);
            document.getElementById(`${prefix}-showing-from`).textContent = showingFrom;
            document.getElementById(`${prefix}-showing-to`).textContent = showingTo;
            document.getElementById(`${prefix}-total-count`).textContent = totalFiltered;

            // Generate pagination
            generatePagination(totalPages, currentPage, prefix);
        }

        function generatePagination(totalPages, current, prefix) {
            const paginationNav = document.getElementById(`${prefix}-pagination-nav`);

            if (totalPages <= 1) {
                paginationNav.innerHTML = '';
                return;
            }

            let html = '<ul class="pagination mb-0">';

            html += `<li class="page-item ${current === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage('${prefix}', ${current - 1}); return false;">Previous</a>
            </li>`;

            const maxVisible = 5;
            let startPage = Math.max(1, current - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);

            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            if (startPage > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="goToPage('${prefix}', 1); return false;">1</a></li>`;
                if (startPage > 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<li class="page-item ${i === current ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="goToPage('${prefix}', ${i}); return false;">${i}</a>
                </li>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                html += `<li class="page-item"><a class="page-link" href="#" onclick="goToPage('${prefix}', ${totalPages}); return false;">${totalPages}</a></li>`;
            }

            html += `<li class="page-item ${current === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage('${prefix}', ${current + 1}); return false;">Next</a>
            </li>`;

            html += '</ul>';
            paginationNav.innerHTML = html;
        }

        function goToPage(prefix, page) {
            if (prefix === 'subject') {
                subjectCurrentPage = page;
                filterSubjectComments(false);
            } else if (prefix === 'overall') {
                overallCurrentPage = page;
                filterOverallComments(false);
            } else if (prefix === 'venues') {
                venuesCurrentPage = page;
                filterVenues(false);
            }
        }

        $(document).ready(function() {
            // Tab persistence
            var activeTab = localStorage.getItem('commentsVenuesActiveTab');
            if (activeTab) {
                $('#commentsVenuesTabs a[href="' + activeTab + '"]').tab('show');
            } else {
                $('#commentsVenuesTabs a:first').tab('show');
            }

            $('#commentsVenuesTabs a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                var tabId = $(e.target).attr('href');
                localStorage.setItem('commentsVenuesActiveTab', tabId);
            });

            // Initialize filtering
            filterSubjectComments(true);
            filterOverallComments(true);
            filterVenues(true);

            // Search listeners
            document.getElementById('searchSubjectComments').addEventListener('input', () => filterSubjectComments(true));
            document.getElementById('searchOverallComments').addEventListener('input', () => filterOverallComments(true));
            document.getElementById('searchVenues').addEventListener('input', () => filterVenues(true));
            document.getElementById('venueTypeFilter').addEventListener('change', () => filterVenues(true));

            // Reset listeners
            document.getElementById('resetSubjectCommentsFilter').addEventListener('click', function() {
                document.getElementById('searchSubjectComments').value = '';
                filterSubjectComments(true);
            });

            document.getElementById('resetOverallCommentsFilter').addEventListener('click', function() {
                document.getElementById('searchOverallComments').value = '';
                filterOverallComments(true);
            });

            document.getElementById('resetVenuesFilter').addEventListener('click', function() {
                document.getElementById('searchVenues').value = '';
                document.getElementById('venueTypeFilter').value = '';
                filterVenues(true);
            });

            initializeTooltips();
        });

        function initializeTooltips() {
            var existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            existingTooltips.forEach(function(el) {
                var tooltip = bootstrap.Tooltip.getInstance(el);
                if (tooltip) {
                    tooltip.dispose();
                }
            });

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        function confirmDelete(type) {
            return confirm(`Are you sure you want to delete this ${type}? This action cannot be undone.`);
        }
    </script>
@endsection
