@extends('layouts.master')

@section('title')
    Learning Paths
@endsection

@section('css')
    <style>
        .paths-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .paths-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .paths-body {
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

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #f59e0b;
        }

        /* Form Controls */
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

        .form-control-sm {
            padding: 8px 12px;
            font-size: 13px;
        }

        /* Buttons */
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

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
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

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-outline-primary {
            border: 1px solid #3b82f6;
            color: #3b82f6;
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: #3b82f6;
            color: white;
        }

        .btn-outline-secondary {
            border: 1px solid #6b7280;
            color: #6b7280;
            background: transparent;
        }

        .btn-outline-secondary:hover {
            background: #6b7280;
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
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

        /* Path Cards */
        .path-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            transition: all 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .path-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .path-card.featured {
            border-color: #f59e0b;
            border-width: 2px;
        }

        .path-thumbnail {
            height: 140px;
            object-fit: cover;
            width: 100%;
        }

        .path-thumbnail-placeholder {
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        }

        .path-thumbnail-placeholder i {
            font-size: 48px;
            color: white;
            opacity: 0.5;
        }

        .path-card-body {
            padding: 16px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .path-title {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .path-description {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
            flex-grow: 1;
        }

        .path-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #6b7280;
        }

        .path-card-footer {
            padding: 12px 16px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-beginner {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-intermediate {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-advanced {
            background: #fee2e2;
            color: #991b1b;
        }

        .category-badge {
            background: #f3f4f6;
            color: #374151;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
            margin-right: 4px;
        }

        .enrolled-badge {
            background: #d1fae5;
            color: #065f46;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            color: #374151;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .paths-header {
                padding: 20px;
            }

            .paths-body {
                padding: 16px;
            }
        }
    </style>
@endsection

@section('content')
    @if (session('success'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
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

    <div class="paths-container">
        <div class="paths-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;"><i class="fas fa-route me-2"></i>Learning Paths</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Structured subject sequences to master skills</p>
                </div>
                <div class="col-md-6">
                    @if($paths->count() > 0)
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $paths->total() }}</h4>
                                    <small class="opacity-75">Total</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $paths->where('is_published', true)->count() }}</h4>
                                    <small class="opacity-75">Published</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $featuredPaths->count() }}</h4>
                                    <small class="opacity-75">Featured</small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="paths-body">
            <div class="help-text">
                <div class="help-title">Manage Learning Paths</div>
                <div class="help-content">
                    Learning paths are structured sequences of subjects designed to guide students through a curriculum.
                    Create and manage paths to help students achieve their learning objectives.
                </div>
            </div>

            <!-- Filters -->
            <div class="row align-items-center mb-4">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search paths..." id="searchInput" value="{{ $search }}">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <select class="form-select" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->slug }}" {{ $categorySlug === $category->slug ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <select class="form-select" id="gradeFilter">
                                    <option value="">All Grades</option>
                                    @foreach(\App\Models\Grade::where('active', true)->orderBy('sequence')->get() as $grade)
                                        <option value="{{ $grade->id }}" {{ $gradeId == $grade->id ? 'selected' : '' }}>
                                            {{ $grade->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    @can('manage-lms-content')
                        <a href="{{ route('lms.learning-paths.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Create Path
                        </a>
                    @endcan
                </div>
            </div>

            <!-- Featured Paths -->
            @if($featuredPaths->count())
                <div class="mb-5">
                    <div class="section-title">
                        <i class="fas fa-star"></i>
                        Featured Learning Paths
                    </div>
                    <div class="row g-4">
                        @foreach($featuredPaths as $path)
                            <div class="col-lg-4 col-md-6">
                                <div class="path-card featured">
                                    @if($path->thumbnail_url)
                                        <img src="{{ $path->thumbnail_url }}" class="path-thumbnail" alt="{{ $path->title }}">
                                    @else
                                        <div class="path-thumbnail-placeholder">
                                            <i class="fas fa-route"></i>
                                        </div>
                                    @endif
                                    <div class="path-card-body">
                                        <div class="mb-2">
                                            <span class="badge badge-{{ $path->level }}">{{ $path->level_label }}</span>
                                        </div>
                                        <h6 class="path-title">{{ $path->title }}</h6>
                                        <p class="path-description">{{ Str::limit($path->description, 100) }}</p>
                                        <div class="path-meta">
                                            <span><i class="fas fa-book me-1"></i>{{ $path->courses_count }} subjects</span>
                                            <span><i class="fas fa-clock me-1"></i>{{ $path->estimated_duration }}</span>
                                        </div>
                                    </div>
                                    <div class="path-card-footer d-flex gap-2">
                                        <a href="{{ route('lms.learning-paths.show', $path) }}" class="btn btn-outline-primary btn-sm flex-grow-1">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        @can('manage-lms-content')
                                            <a href="{{ route('lms.learning-paths.edit', $path) }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- All Paths Grid -->
            <div class="section-title">
                <i class="fas fa-th-large" style="color: #6b7280;"></i>
                All Learning Paths
            </div>
            <div class="row g-4">
                @forelse($paths as $path)
                    <div class="col-lg-4 col-md-6">
                        <div class="path-card">
                            @if($path->thumbnail_url)
                                <img src="{{ $path->thumbnail_url }}" class="path-thumbnail" alt="{{ $path->title }}">
                            @else
                                <div class="path-thumbnail-placeholder">
                                    <i class="fas fa-route"></i>
                                </div>
                            @endif
                            <div class="path-card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge badge-{{ $path->level }}">{{ $path->level_label }}</span>
                                    @if(!$path->is_published)
                                        <span class="badge" style="background: #fef3c7; color: #92400e;">Draft</span>
                                    @endif
                                </div>
                                <h6 class="path-title">{{ $path->title }}</h6>
                                <p class="path-description">{{ Str::limit($path->description, 80) }}</p>

                                @if($path->categories->count())
                                    <div class="mb-2">
                                        @foreach($path->categories->take(2) as $cat)
                                            <span class="category-badge">{{ $cat->name }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="path-meta">
                                    <span><i class="fas fa-book me-1"></i>{{ $path->courses_count }} subjects</span>
                                    <span><i class="fas fa-clock me-1"></i>{{ $path->estimated_duration }}</span>
                                </div>
                            </div>
                            <div class="path-card-footer d-flex gap-2">
                                <a href="{{ route('lms.learning-paths.show', $path) }}" class="btn btn-outline-primary btn-sm flex-grow-1">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                                @can('manage-lms-content')
                                    <a href="{{ route('lms.learning-paths.edit', $path) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="fas fa-route"></i>
                            <h5>No learning paths found</h5>
                            <p>Check back later for new learning paths.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $paths->links() }}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const gradeFilter = document.getElementById('gradeFilter');
            const resetButton = document.getElementById('resetFilters');

            let searchTimeout;

            function applyFilters() {
                const params = new URLSearchParams();

                const search = searchInput.value.trim();
                const category = categoryFilter.value;
                const gradeId = gradeFilter.value;

                if (search) params.set('search', search);
                if (category) params.set('category', category);
                if (gradeId) params.set('grade_id', gradeId);

                const queryString = params.toString();
                window.location.href = '{{ route('lms.learning-paths.index') }}' + (queryString ? '?' + queryString : '');
            }

            // Search with debounce
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(applyFilters, 500);
            });

            // Search on Enter key
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    clearTimeout(searchTimeout);
                    applyFilters();
                }
            });

            // Filter dropdowns
            categoryFilter.addEventListener('change', applyFilters);
            gradeFilter.addEventListener('change', applyFilters);

            // Reset button
            resetButton.addEventListener('click', function() {
                window.location.href = '{{ route('lms.learning-paths.index') }}';
            });
        });
    </script>
@endsection
