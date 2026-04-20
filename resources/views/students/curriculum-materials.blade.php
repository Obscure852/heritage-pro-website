@extends('layouts.master')
@section('title')
    Curriculum Materials
@endsection
@section('css')
    <style>
        .students-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .students-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .students-body {
            padding: 24px;
        }

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

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #8b5cf6;
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
            color: #8b5cf6;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active {
            color: #8b5cf6;
            border-bottom-color: #8b5cf6;
            background: transparent;
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

        /* Button Styling (Primary) */
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

        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            color: #495057;
        }

        /* Accordion Styling */
        .accordion-button {
            font-weight: 500;
            color: #374151;
        }

        .accordion-button:not(.collapsed) {
            background-color: #f9fafb;
            color: #8b5cf6;
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: transparent;
        }

        /* Form Controls */
        .form-control, .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        /* Status Badges */
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #fee2e2; color: #991b1b; }
        .status-available { background: #dbeafe; color: #1e40af; }

        .bx-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('students.index') }}">Students</a>
        @endslot
        @slot('title')
            Curriculum Materials
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
                <div class="alert alert-danger alert-border-left alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-block-helper me-3 align-middle"></i><strong>{{ $errors->all()[0] }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="students-container">
        <div class="students-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-1 text-white"><i class="fas fa-book me-2"></i>Curriculum Materials</h4>
                    <p class="mb-0 opacity-75">Manage textbooks, authors, and publishers</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $textbooks->count() ?? 0 }}</h4>
                                <small class="opacity-75">Textbooks</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $authors->count() ?? 0 }}</h4>
                                <small class="opacity-75">Authors</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $publishers->count() ?? 0 }}</h4>
                                <small class="opacity-75">Publishers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="students-body">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#textbooks" role="tab">
                                <i class="fas fa-book me-2 text-muted"></i>Textbooks
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#authors" role="tab">
                                <i class="fas fa-user-edit me-2 text-muted"></i>Authors
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#publishers" role="tab">
                                <i class="fas fa-building me-2 text-muted"></i>Publishers
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content p-3 text-muted">
                        <div class="tab-pane active" id="textbooks" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Textbook Management</div>
                                <p class="help-content">
                                    Add, edit, and manage textbooks in your curriculum. Track ISBN numbers, authors, and availability status.
                                </p>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12 d-flex justify-content-end">
                                    <a href="{{ route('students.add-book') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i>Add Textbook
                                    </a>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-12 col-md-12">
                                    <div class="controls">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-lg-5 col-md-4 col-sm-6">
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                    <input type="text" class="form-control" placeholder="Search by title or author..." id="textbookSearchInput">
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-sm-6">
                                                <select id="genreFilter" class="form-select">
                                                    <option value="">All Genres</option>
                                                    @php
                                                        $genres = $textbooks->pluck('genre')->unique()->sort();
                                                    @endphp
                                                    @foreach($genres as $genre)
                                                        <option value="{{ strtolower($genre) }}">{{ ucfirst($genre) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg-2 col-md-3 col-sm-6">
                                                <select id="statusFilter" class="form-select">
                                                    <option value="">All Status</option>
                                                    @php
                                                        $statuses = $textbooks->pluck('status')->unique()->sort();
                                                    @endphp
                                                    @foreach($statuses as $status)
                                                        <option value="{{ strtolower($status) }}">{{ ucfirst($status) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg-2 col-md-2 col-sm-6">
                                                <select id="authorFilter" class="form-select">
                                                    <option value="">All Authors</option>
                                                    @foreach($authors as $author)
                                                        <option value="{{ strtolower($author->fullName) }}">{{ $author->fullName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg-1 col-md-2 col-sm-6">
                                                <button type="button" class="btn btn-light w-100" id="resetTextbookFilters">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="textbooks-table" class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>ISBN</th>
                                            <th>Genre</th>
                                            <th>Status</th>
                                            <th style="width: 100px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($textbooks as $book)
                                            <tr class="textbook-row"
                                                data-title="{{ strtolower($book->title ?? '') }}"
                                                data-author="{{ strtolower($book->author->fullName ?? '') }}"
                                                data-genre="{{ strtolower($book->genre ?? '') }}"
                                                data-status="{{ strtolower($book->status ?? '') }}">
                                                <td>{{ $book->title ?? '' }}</td>
                                                <td>{{ $book->author->fullName ?? '' }}</td>
                                                <td>{{ $book->isbn ?? '—' }}</td>
                                                <td>{{ ucfirst($book->genre) ?? '' }}</td>
                                                <td>
                                                    <span class="status-badge status-{{ strtolower($book->status) }}">
                                                        {{ ucfirst($book->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="{{ route('students.edit-book', $book->id) }}"
                                                            class="btn btn-outline-info"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Edit">
                                                            <i class="bx bx-edit-alt"></i>
                                                        </a>
                                                        <form action="{{ route('students.delete-book', $book->id) }}"
                                                            method="POST" class="d-inline delete-book-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button"
                                                                class="btn btn-outline-danger delete-book-btn"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Delete"
                                                                data-book-title="{{ $book->title }}">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane" id="authors" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Author Management</div>
                                <p class="help-content">
                                    Manage textbook authors. Add new authors or edit existing ones to maintain accurate book records.
                                </p>
                            </div>

                            <div class="accordion" id="authorAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="authorHeadingOne">
                                        <button class="accordion-button" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#authorCollapseOne"
                                            aria-expanded="true" aria-controls="authorCollapseOne">
                                            Add Author
                                        </button>
                                    </h2>
                                    <div id="authorCollapseOne" class="accordion-collapse collapse show"
                                        aria-labelledby="authorHeadingOne" data-bs-parent="#authorAccordion">
                                        <div class="accordion-body">
                                            <form action="{{ route('students.create-author') }}" method="POST"
                                                class="row g-3 align-items-end">
                                                @csrf
                                                <div class="col-md-5">
                                                    <label class="form-label">First Name</label>
                                                    <input type="text" class="form-control"
                                                        name="first_name" placeholder="Enter first name" required>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Last Name</label>
                                                    <input type="text" class="form-control"
                                                        name="last_name" placeholder="Enter last name" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <button class="btn btn-primary w-100" type="submit">
                                                        <i class="fas fa-plus me-1"></i>Add
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseTwo"
                                            aria-expanded="false" aria-controls="collapseTwo">
                                            Authors List
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse"
                                        aria-labelledby="headingTwo" data-bs-parent="#authorAccordion">
                                        <div class="accordion-body">
                                            <div class="table-responsive">
                                                <table id="authors-table" class="table table-striped align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>First Name</th>
                                                            <th>Last Name</th>
                                                            <th style="width: 100px;">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($authors as $author)
                                                            <tr data-id="{{ $author->id }}">
                                                                <td>{{ $author->id }}</td>
                                                                <td>{{ $author->first_name }}</td>
                                                                <td>{{ $author->last_name }}</td>
                                                                <td>
                                                                    <div class="action-buttons">
                                                                        <button type="button"
                                                                            class="btn btn-outline-info edit-author-btn"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#editAuthorModal"
                                                                            data-author-id="{{ $author->id }}"
                                                                            data-first-name="{{ $author->first_name }}"
                                                                            data-last-name="{{ $author->last_name }}"
                                                                            title="Edit">
                                                                            <i class="bx bx-edit-alt"></i>
                                                                        </button>
                                                                        <form action="{{ route('students.delete-author', $author->id) }}"
                                                                            method="POST" class="d-inline delete-author-form">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="button"
                                                                                class="btn btn-outline-danger delete-author-btn"
                                                                                data-author-name="{{ $author->fullName }}"
                                                                                title="Delete">
                                                                                <i class="bx bx-trash"></i>
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane" id="publishers" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Publisher Management</div>
                                <p class="help-content">
                                    Manage textbook publishers. Add new publishers or edit existing ones for comprehensive book cataloging.
                                </p>
                            </div>

                            <div class="accordion" id="publisherAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="publisherHeadingOne">
                                        <button class="accordion-button" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#publisherCollapseOne"
                                            aria-expanded="true" aria-controls="publisherCollapseOne">
                                            Add Publisher
                                        </button>
                                    </h2>
                                    <div id="publisherCollapseOne" class="accordion-collapse collapse show"
                                        aria-labelledby="publisherHeadingOne" data-bs-parent="#publisherAccordion">
                                        <div class="accordion-body">
                                            <form action="{{ route('students.store-publisher') }}" method="POST"
                                                class="row g-3 align-items-end">
                                                @csrf
                                                <div class="col-md-10">
                                                    <label class="form-label">Publisher Name</label>
                                                    <input type="text" class="form-control"
                                                        name="name" placeholder="Enter publisher name" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <button class="btn btn-primary w-100" type="submit">
                                                        <i class="fas fa-plus me-1"></i>Add
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="publisherHeadingTwo">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#publisherCollapseTwo"
                                            aria-expanded="false" aria-controls="publisherCollapseTwo">
                                            Publishers List
                                        </button>
                                    </h2>
                                    <div id="publisherCollapseTwo" class="accordion-collapse collapse"
                                        aria-labelledby="publisherHeadingTwo" data-bs-parent="#publisherAccordion">
                                        <div class="accordion-body">
                                            <div class="table-responsive">
                                                <table id="publishers-table" class="table table-striped align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Publisher</th>
                                                            <th style="width: 100px;">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($publishers as $index => $publisher)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ $publisher->name ?? '' }}</td>
                                                                <td>
                                                                    <div class="action-buttons">
                                                                        <button type="button"
                                                                            class="btn btn-outline-info edit-publisher-btn"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#editPublisherModal"
                                                                            data-publisher-id="{{ $publisher->id }}"
                                                                            data-name="{{ $publisher->name }}"
                                                                            title="Edit">
                                                                            <i class="bx bx-edit-alt"></i>
                                                                        </button>
                                                                        <form action="{{ route('students.delete-publisher', $publisher->id) }}"
                                                                            method="POST" class="d-inline delete-publisher-form">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="button"
                                                                                class="btn btn-outline-danger delete-publisher-btn"
                                                                                data-publisher-name="{{ $publisher->name }}"
                                                                                title="Delete">
                                                                                <i class="bx bx-trash"></i>
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Author Modal -->
    <div class="modal fade" id="editAuthorModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Author</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editAuthorForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="author_id" id="editAuthorId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" id="editAuthorFirstName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" id="editAuthorLastName" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Author</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Publisher Modal -->
    <div class="modal fade" id="editPublisherModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Publisher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editPublisherForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Publisher Name</label>
                            <input type="text" class="form-control" name="name" id="editPublisherName" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Publisher</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab persistence
            let activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                let tabElement = document.querySelector(`a.nav-link[href="${activeTab}"]`);
                if (tabElement) {
                    new bootstrap.Tab(tabElement).show();
                }
            }

            const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    let selectedTab = event.target.getAttribute('href');
                    localStorage.setItem('activeTab', selectedTab);
                });
            });

            // Initialize DataTables
            const tableSelectors = ['#textbooks-table', '#authors-table', '#publishers-table'];
            tableSelectors.forEach(function(selector) {
                if ($.fn.DataTable.isDataTable(selector)) {
                    $(selector).DataTable().destroy();
                }
                $(selector).DataTable({
                    responsive: true,
                    autoWidth: false,
                    searching: false,
                    info: false,
                    lengthChange: false,
                    language: {
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>"
                        }
                    },
                    drawCallback: function() {
                        $('.dataTables_paginate > .pagination').addClass('pagination-rounded');

                        // Reinitialize tooltips after table redraw
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }
                });
            });

            // Textbook filtering
            function filterTextbooks() {
                const searchTerm = document.getElementById('textbookSearchInput').value.toLowerCase();
                const genreFilter = document.getElementById('genreFilter').value;
                const statusFilter = document.getElementById('statusFilter').value;
                const authorFilter = document.getElementById('authorFilter').value;

                const rows = document.querySelectorAll('.textbook-row');

                rows.forEach(row => {
                    const title = row.dataset.title || '';
                    const author = row.dataset.author || '';
                    const genre = row.dataset.genre || '';
                    const status = row.dataset.status || '';

                    const matchesSearch = !searchTerm || title.includes(searchTerm) || author.includes(searchTerm);
                    const matchesGenre = !genreFilter || genre === genreFilter;
                    const matchesStatus = !statusFilter || status === statusFilter;
                    const matchesAuthor = !authorFilter || author === authorFilter;

                    if (matchesSearch && matchesGenre && matchesStatus && matchesAuthor) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // Real-time search
            document.getElementById('textbookSearchInput').addEventListener('input', filterTextbooks);

            // Filter dropdowns
            document.getElementById('genreFilter').addEventListener('change', filterTextbooks);
            document.getElementById('statusFilter').addEventListener('change', filterTextbooks);
            document.getElementById('authorFilter').addEventListener('change', filterTextbooks);

            // Reset filters
            document.getElementById('resetTextbookFilters').addEventListener('click', function() {
                document.getElementById('textbookSearchInput').value = '';
                document.getElementById('genreFilter').value = '';
                document.getElementById('statusFilter').value = '';
                document.getElementById('authorFilter').value = '';
                filterTextbooks();
            });

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Edit Author Modal
            const editAuthorButtons = document.querySelectorAll('.edit-author-btn');
            editAuthorButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const authorId = button.getAttribute('data-author-id');
                    const firstName = button.getAttribute('data-first-name');
                    const lastName = button.getAttribute('data-last-name');

                    document.getElementById('editAuthorFirstName').value = firstName;
                    document.getElementById('editAuthorLastName').value = lastName;
                    document.getElementById('editAuthorId').value = authorId;
                });
            });

            // Edit Publisher Modal
            const editPublisherButtons = document.querySelectorAll('.edit-publisher-btn');
            editPublisherButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const publisherId = button.getAttribute('data-publisher-id');
                    const publisherName = button.getAttribute('data-name');

                    document.getElementById('editPublisherName').value = publisherName;
                    const form = document.getElementById('editPublisherForm');
                    const updatePublisherUrl = `{{ route('students.update-publisher', ['id' => ':publisherId']) }}`
                        .replace(':publisherId', publisherId);
                    form.action = updatePublisherUrl;
                });
            });

            // Update Author Form
            document.getElementById('editAuthorForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const authorId = document.getElementById('editAuthorId').value;
                const updateAuthorUrl = `{{ route('students.update-author', ['id' => ':authorId']) }}`
                    .replace(':authorId', authorId);

                this.action = updateAuthorUrl;
                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Updating...';
                submitButton.disabled = true;
                this.submit();
            });

            // Update Publisher Form
            document.getElementById('editPublisherForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Updating...';
                submitButton.disabled = true;
                this.submit();
            });

            // Delete Confirmations
            document.body.addEventListener('click', function(e) {
                if (e.target.closest('.delete-author-btn')) {
                    e.preventDefault();
                    const button = e.target.closest('.delete-author-btn');
                    const authorName = button.getAttribute('data-author-name');
                    const form = button.closest('form');
                    if (confirm(`Are you sure you want to delete author "${authorName}"?\nThis action cannot be undone.`)) {
                        button.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i>';
                        form.submit();
                    }
                } else if (e.target.closest('.delete-publisher-btn')) {
                    e.preventDefault();
                    const button = e.target.closest('.delete-publisher-btn');
                    const publisherName = button.getAttribute('data-publisher-name');
                    const form = button.closest('form');
                    if (confirm(`Are you sure you want to delete publisher "${publisherName}"?\nThis action cannot be undone.`)) {
                        button.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i>';
                        form.submit();
                    }
                } else if (e.target.closest('.delete-book-btn')) {
                    e.preventDefault();
                    const button = e.target.closest('.delete-book-btn');
                    const bookTitle = button.getAttribute('data-book-title');
                    const form = button.closest('form');
                    if (confirm(`Are you sure you want to delete "${bookTitle}"?\nThis action cannot be undone.`)) {
                        button.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i>';
                        form.submit();
                    }
                }
            });
        });
    </script>
@endsection
