@extends('layouts.master')

@section('title', 'Grading Rubrics')

@section('css')
    <style>
        .page-container {
            background: white;
            border-radius: 3px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-header-left h1 {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 4px 0;
        }

        .page-header-left p {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 10px;
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

        .btn-outline {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-outline:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            color: #1f2937;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        /* Search and Filter Bar */
        .filter-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 280px;
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .search-box input {
            width: 100%;
            padding: 10px 12px 10px 38px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 16px;
        }

        .rubric-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            transition: all 0.2s;
        }

        .rubric-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .rubric-card-header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }

        .rubric-icon {
            width: 48px;
            height: 48px;
            background: #dbeafe;
            color: #2563eb;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .rubric-icon i {
            font-size: 20px;
        }

        .rubric-info {
            flex: 1;
            min-width: 0;
        }

        .rubric-info h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 4px 0;
        }

        .rubric-info h3 a {
            color: inherit;
            text-decoration: none;
        }

        .rubric-info h3 a:hover {
            color: #3b82f6;
        }

        .rubric-meta {
            font-size: 13px;
            color: #6b7280;
        }

        .rubric-description {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .rubric-stats {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #6b7280;
        }

        .stat-item i {
            color: #9ca3af;
        }

        .stat-item strong {
            color: #374151;
        }

        .rubric-badges {
            display: flex;
            gap: 6px;
            margin-bottom: 16px;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-template {
            background: #d1fae5;
            color: #059669;
        }

        .badge-points {
            background: #dbeafe;
            color: #2563eb;
        }

        .rubric-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
        }

        .rubric-creator {
            font-size: 12px;
            color: #9ca3af;
        }

        .rubric-actions {
            display: flex;
            gap: 6px;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
            background: white;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .action-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #eff6ff;
        }

        .action-btn.danger:hover {
            border-color: #dc2626;
            color: #dc2626;
            background: #fef2f2;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state h3 {
            font-size: 18px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 8px 0;
        }

        .empty-state p {
            color: #6b7280;
            margin: 0 0 20px 0;
        }

        /* Sidebar */
        .sidebar-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 16px;
        }

        .sidebar-header {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            font-size: 14px;
            color: #374151;
        }

        .sidebar-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .sidebar-list li a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            color: #4b5563;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.15s;
            border-bottom: 1px solid #f3f4f6;
        }

        .sidebar-list li:last-child a {
            border-bottom: none;
        }

        .sidebar-list li a:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .sidebar-list li a.active {
            background: #eff6ff;
            color: #2563eb;
            font-weight: 500;
        }

        .sidebar-list li a i {
            margin-right: 10px;
            width: 16px;
            text-align: center;
        }

        .sidebar-badge {
            background: #e5e7eb;
            color: #6b7280;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
        }

        .sidebar-list li a.active .sidebar-badge {
            background: #bfdbfe;
            color: #1d4ed8;
        }

        .alert {
            border-radius: 3px;
        }

        @media (max-width: 992px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .filter-bar {
                flex-direction: column;
            }

            .search-box {
                min-width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('title')
            Grading Rubrics
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="sidebar-section">
                    <ul class="sidebar-list">
                        <li>
                            <a href="{{ route('lms.rubrics.index') }}" class="{{ !request('filter') ? 'active' : '' }}">
                                <span><i class="fas fa-th-list"></i> All Rubrics</span>
                                <span class="sidebar-badge">{{ $rubrics->count() }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('lms.rubrics.index', ['filter' => 'templates']) }}"
                                class="{{ request('filter') === 'templates' ? 'active' : '' }}">
                                <span><i class="fas fa-clone"></i> Templates</span>
                                <span class="sidebar-badge">{{ $rubrics->where('is_template', true)->count() }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('lms.rubrics.index', ['filter' => 'mine']) }}"
                                class="{{ request('filter') === 'mine' ? 'active' : '' }}">
                                <span><i class="fas fa-user"></i> My Rubrics</span>
                                <span
                                    class="sidebar-badge">{{ $rubrics->where('created_by', auth()->id())->count() }}</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-header">Quick Actions</div>
                    <div class="p-3">
                        <a href="{{ route('lms.rubrics.create') }}" class="btn btn-primary w-100">
                            <i class="fas fa-plus"></i> Create Rubric
                        </a>
                    </div>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-header">Help</div>
                    <div class="p-3">
                        <p class="text-muted small mb-0">
                            Rubrics help standardize grading by defining criteria and performance levels.
                            Create templates to reuse across multiple assignments.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="page-container">
                    <div class="page-header">
                        <div class="page-header-left">
                            <h1>Grading Rubrics</h1>
                            <p>Create and manage rubrics for consistent assignment grading</p>
                        </div>
                        <div class="header-actions">
                            <a href="{{ route('lms.settings.index') }}" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Back to Settings
                            </a>
                            <a href="{{ route('lms.rubrics.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Rubric
                            </a>
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="filter-bar">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Search rubrics by title..."
                                onkeyup="filterRubrics()">
                        </div>
                    </div>

                    <!-- Rubrics Grid -->
                    @if ($rubrics->count())
                        <div class="content-grid" id="rubricsGrid">
                            @foreach ($rubrics as $rubric)
                                <div class="rubric-card" data-title="{{ strtolower($rubric->title) }}">
                                    <div class="rubric-card-header">
                                        <div class="rubric-icon">
                                            <i class="fas fa-th-list"></i>
                                        </div>
                                        <div class="rubric-info">
                                            <h3><a
                                                    href="{{ route('lms.rubrics.edit', $rubric) }}">{{ $rubric->title }}</a>
                                            </h3>
                                            <div class="rubric-meta">
                                                {{ $rubric->criteria_count }} criteria &bull;
                                                {{ number_format($rubric->total_points) }} points
                                            </div>
                                        </div>
                                    </div>

                                    @if ($rubric->description)
                                        <div class="rubric-description">{{ $rubric->description }}</div>
                                    @endif

                                    <div class="rubric-badges">
                                        @if ($rubric->is_template)
                                            <span class="badge badge-template"><i
                                                    class="fas fa-clone me-1"></i>Template</span>
                                        @endif
                                        <span class="badge badge-points">{{ number_format($rubric->total_points) }}
                                            pts</span>
                                    </div>

                                    <div class="rubric-stats">
                                        <div class="stat-item">
                                            <i class="fas fa-list"></i>
                                            <span><strong>{{ $rubric->criteria_count }}</strong> criteria</span>
                                        </div>
                                        <div class="stat-item">
                                            <i class="fas fa-book"></i>
                                            <span><strong>{{ $rubric->assignments_count }}</strong> assignments</span>
                                        </div>
                                    </div>

                                    <div class="rubric-card-footer">
                                        <span class="rubric-creator">
                                            <i class="fas fa-user me-1"></i>{{ $rubric->creator->name ?? 'Unknown' }}
                                        </span>
                                        <div class="rubric-actions">
                                            <a href="{{ route('lms.rubrics.edit', $rubric) }}" class="action-btn"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('lms.rubrics.duplicate', $rubric) }}" method="POST"
                                                style="display: inline;">
                                                @csrf
                                                <button type="submit" class="action-btn" title="Duplicate">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </form>
                                            @if ($rubric->assignments_count == 0)
                                                <form action="{{ route('lms.rubrics.destroy', $rubric) }}" method="POST"
                                                    style="display: inline;"
                                                    onsubmit="return confirm('Are you sure you want to delete this rubric?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-btn danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-th-list"></i>
                            <h3>No Rubrics Yet</h3>
                            <p>Create your first rubric to standardize grading across assignments.</p>
                            <a href="{{ route('lms.rubrics.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Rubric
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function filterRubrics() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.rubric-card');

            cards.forEach(card => {
                const title = card.dataset.title;
                if (title.includes(searchValue)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
@endsection
