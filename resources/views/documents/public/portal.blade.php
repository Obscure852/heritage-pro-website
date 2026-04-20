<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Documents - Heritage Junior Secondary School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            background: #f3f4f6;
        }

        /* Navbar */
        .public-navbar {
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 12px 0;
        }
        .public-navbar .brand {
            font-weight: 700;
            font-size: 18px;
            color: #1f2937;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .public-navbar .brand i {
            color: #4e73df;
            font-size: 22px;
        }
        .public-navbar .nav-link {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }
        .public-navbar .nav-link:hover {
            color: #4e73df;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 60px 0 50px;
            text-align: center;
        }
        .hero h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .hero p {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 28px;
        }
        .hero .search-wrapper {
            max-width: 560px;
            margin: 0 auto;
        }
        .hero .search-wrapper .input-group {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            overflow: hidden;
        }
        .hero .search-wrapper .form-control {
            border: none;
            padding: 14px 18px;
            font-size: 15px;
        }
        .hero .search-wrapper .form-control:focus {
            box-shadow: none;
        }
        .hero .search-wrapper .btn {
            padding: 14px 24px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
        }
        .hero .search-wrapper .btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }

        /* Content */
        .portal-content {
            max-width: 1140px;
            margin: 0 auto;
            padding: 32px 16px 60px;
        }

        .section-heading {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-heading i {
            color: #4e73df;
        }

        /* Featured Carousel */
        .featured-section {
            margin-bottom: 40px;
        }
        .carousel-wrapper {
            position: relative;
        }
        .carousel-track {
            display: flex;
            overflow-x: auto;
            gap: 20px;
            padding: 4px 4px 16px;
            scroll-snap-type: x mandatory;
            scrollbar-width: thin;
            scrollbar-color: #d1d5db transparent;
        }
        .carousel-track::-webkit-scrollbar {
            height: 6px;
        }
        .carousel-track::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }
        .featured-card {
            min-width: 280px;
            max-width: 320px;
            flex: 0 0 auto;
            scroll-snap-align: start;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
            padding: 24px;
            transition: box-shadow 0.2s, transform 0.2s;
            border-top: 3px solid #4e73df;
        }
        .featured-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        .featured-card .card-title {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .featured-card .card-desc {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .featured-card .card-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: #9ca3af;
        }
        .featured-card .card-meta .owner {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .category-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: white;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #374151;
            z-index: 2;
            transition: all 0.2s;
        }
        .carousel-arrow:hover {
            background: #4e73df;
            color: white;
            border-color: #4e73df;
        }
        .carousel-arrow.left { left: -12px; }
        .carousel-arrow.right { right: -12px; }

        /* Category Grid */
        .categories-section {
            margin-bottom: 40px;
        }
        .category-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
            padding: 24px;
            text-align: center;
            transition: box-shadow 0.2s, transform 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .category-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
            color: inherit;
        }
        .category-card .cat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            font-size: 22px;
        }
        .category-card .cat-name {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
        }
        .category-card .cat-count {
            font-size: 12px;
            color: #6b7280;
        }

        /* Document Cards */
        .doc-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
            padding: 20px;
            transition: box-shadow 0.2s, transform 0.2s;
            height: 100%;
        }
        .doc-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        .doc-card .doc-header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 10px;
        }
        .doc-card .file-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .doc-card .doc-title {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .doc-card .doc-desc {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .doc-card .doc-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: #9ca3af;
        }

        /* Search Results Header */
        .search-results-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .search-results-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }
        .back-link {
            color: #4e73df;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }

        /* Filter pills */
        .filter-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 24px;
        }
        .filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid #e5e7eb;
            background: white;
            color: #374151;
            transition: all 0.2s;
        }
        .filter-pill:hover {
            border-color: #4e73df;
            color: #4e73df;
        }
        .filter-pill.active {
            background: #4e73df;
            color: white;
            border-color: #4e73df;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }
        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
        }
        .empty-state p {
            font-size: 15px;
        }

        /* Footer */
        .public-footer {
            background: white;
            border-top: 1px solid #e5e7eb;
            padding: 24px 0;
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
        }
    </style>
</head>
<body>
    {{-- Navbar --}}
    <nav class="public-navbar">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="{{ route('documents.public.portal') }}" class="brand">
                <i class="fas fa-graduation-cap"></i>
                Heritage Junior Secondary School
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="d-none d-md-inline" style="color: #9ca3af; font-size: 13px;">Public Documents</span>
                <a href="{{ url('/login') }}" class="nav-link">
                    <i class="fas fa-sign-in-alt me-1"></i> Staff Login
                </a>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="hero">
        <div class="container">
            <h1>Public Documents</h1>
            <p>Browse publicly available documents from Heritage Junior Secondary School.</p>
            <div class="search-wrapper">
                <form action="{{ route('documents.public.portal.search') }}" method="GET">
                    @if(isset($selectedCategory) && $selectedCategory)
                        <input type="hidden" name="category" value="{{ $selectedCategory }}">
                    @endif
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" placeholder="Search documents by title or description..." value="{{ $search ?? '' }}">
                        <button type="submit" class="btn">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <div class="portal-content">

        @if(!isset($documents))
            {{-- ==================== LANDING PAGE ==================== --}}

            {{-- Featured Documents Section --}}
            @if(isset($featured) && $featured->isNotEmpty())
                <section class="featured-section">
                    <h2 class="section-heading">
                        <i class="fas fa-star"></i> Featured Documents
                    </h2>
                    <div class="carousel-wrapper">
                        <button class="carousel-arrow left" onclick="document.getElementById('featuredTrack').scrollBy({left: -320, behavior: 'smooth'})" aria-label="Scroll left">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="carousel-track" id="featuredTrack">
                            @foreach($featured as $doc)
                                <div class="featured-card">
                                    <div class="card-title">{{ $doc->title }}</div>
                                    @if($doc->category)
                                        <span class="category-badge" style="background: {{ $doc->category->color ?? '#eff6ff' }}20; color: {{ $doc->category->color ?? '#3b82f6' }};">
                                            {{ $doc->category->name }}
                                        </span>
                                    @endif
                                    @if($doc->description)
                                        <div class="card-desc">{{ Str::limit($doc->description, 120) }}</div>
                                    @endif
                                    <div class="card-meta">
                                        <div class="owner">
                                            <i class="fas fa-user"></i>
                                            {{ $doc->owner->full_name ?? 'Unknown' }}
                                        </div>
                                        <span>{{ $doc->published_at?->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button class="carousel-arrow right" onclick="document.getElementById('featuredTrack').scrollBy({left: 320, behavior: 'smooth'})" aria-label="Scroll right">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </section>
            @endif

            {{-- Categories Grid --}}
            @if(isset($categories) && $categories->isNotEmpty())
                <section class="categories-section">
                    <h2 class="section-heading">
                        <i class="fas fa-th-large"></i> Browse by Category
                    </h2>
                    <div class="row g-3">
                        @foreach($categories as $cat)
                            <div class="col-6 col-md-4 col-lg-3">
                                <a href="{{ route('documents.public.portal.search', ['category' => $cat->id]) }}" class="category-card">
                                    <div class="cat-icon" style="background: {{ $cat->color ?? '#4e73df' }}15; color: {{ $cat->color ?? '#4e73df' }};">
                                        <i class="fas {{ $cat->icon ?? 'fa-folder' }}"></i>
                                    </div>
                                    <div class="cat-name">{{ $cat->name }}</div>
                                    <div class="cat-count">{{ $cat->documents_count }} {{ Str::plural('document', $cat->documents_count) }}</div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Recent Documents --}}
            @if(isset($recentDocuments) && $recentDocuments->isNotEmpty())
                <section class="recent-section">
                    <h2 class="section-heading">
                        <i class="fas fa-clock"></i> Recently Published
                    </h2>
                    <div class="row g-3">
                        @foreach($recentDocuments as $doc)
                            <div class="col-md-6 col-lg-4">
                                <div class="doc-card">
                                    <div class="doc-header">
                                        <div class="file-icon" style="background: {{ $doc->category->color ?? '#4e73df' }}15; color: {{ $doc->category->color ?? '#4e73df' }};">
                                            @php
                                                $iconMap = [
                                                    'pdf' => 'fa-file-pdf',
                                                    'doc' => 'fa-file-word', 'docx' => 'fa-file-word',
                                                    'xls' => 'fa-file-excel', 'xlsx' => 'fa-file-excel',
                                                    'ppt' => 'fa-file-powerpoint', 'pptx' => 'fa-file-powerpoint',
                                                    'png' => 'fa-file-image', 'jpg' => 'fa-file-image', 'jpeg' => 'fa-file-image', 'gif' => 'fa-file-image',
                                                    'zip' => 'fa-file-zipper', 'rar' => 'fa-file-zipper',
                                                    'txt' => 'fa-file-lines', 'csv' => 'fa-file-csv',
                                                ];
                                                $icon = $iconMap[strtolower($doc->extension)] ?? 'fa-file';
                                            @endphp
                                            <i class="fas {{ $icon }}"></i>
                                        </div>
                                        <div class="doc-title">{{ $doc->title }}</div>
                                    </div>
                                    @if($doc->description)
                                        <div class="doc-desc">{{ Str::limit($doc->description, 100) }}</div>
                                    @endif
                                    <div class="doc-footer">
                                        @if($doc->category)
                                            <span class="category-badge" style="background: {{ $doc->category->color ?? '#eff6ff' }}20; color: {{ $doc->category->color ?? '#3b82f6' }};">
                                                {{ $doc->category->name }}
                                            </span>
                                        @else
                                            <span></span>
                                        @endif
                                        <span>{{ $doc->published_at?->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @else
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>No published documents yet.</p>
                </div>
            @endif

        @else
            {{-- ==================== SEARCH RESULTS ==================== --}}

            <div class="search-results-header">
                <h2>
                    @if(isset($search) && $search)
                        Results for "{{ $search }}"
                    @elseif(isset($selectedCategory) && $selectedCategory)
                        @php
                            $catName = $categories->firstWhere('id', $selectedCategory)?->name ?? 'Category';
                        @endphp
                        Documents in {{ $catName }}
                    @else
                        All Published Documents
                    @endif
                </h2>
                <a href="{{ route('documents.public.portal') }}" class="back-link">
                    <i class="fas fa-arrow-left me-1"></i> Back to Portal
                </a>
            </div>

            {{-- Category Filter Pills --}}
            @if(isset($categories) && $categories->isNotEmpty())
                <div class="filter-pills">
                    <a href="{{ route('documents.public.portal.search', array_filter(['q' => $search ?? ''])) }}"
                       class="filter-pill {{ !$selectedCategory ? 'active' : '' }}">
                        All Categories
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('documents.public.portal.search', array_filter(['q' => $search ?? '', 'category' => $cat->id])) }}"
                           class="filter-pill {{ (int)($selectedCategory ?? 0) === $cat->id ? 'active' : '' }}">
                            <i class="fas {{ $cat->icon ?? 'fa-folder' }}" style="font-size: 11px;"></i>
                            {{ $cat->name }}
                            <span style="opacity: 0.7;">({{ $cat->documents_count }})</span>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Search Results Grid --}}
            @if($documents->isNotEmpty())
                <div class="row g-3">
                    @foreach($documents as $doc)
                        <div class="col-md-6 col-lg-4">
                            <div class="doc-card">
                                <div class="doc-header">
                                    <div class="file-icon" style="background: {{ $doc->category->color ?? '#4e73df' }}15; color: {{ $doc->category->color ?? '#4e73df' }};">
                                        @php
                                            $iconMap = [
                                                'pdf' => 'fa-file-pdf',
                                                'doc' => 'fa-file-word', 'docx' => 'fa-file-word',
                                                'xls' => 'fa-file-excel', 'xlsx' => 'fa-file-excel',
                                                'ppt' => 'fa-file-powerpoint', 'pptx' => 'fa-file-powerpoint',
                                                'png' => 'fa-file-image', 'jpg' => 'fa-file-image', 'jpeg' => 'fa-file-image', 'gif' => 'fa-file-image',
                                                'zip' => 'fa-file-zipper', 'rar' => 'fa-file-zipper',
                                                'txt' => 'fa-file-lines', 'csv' => 'fa-file-csv',
                                            ];
                                            $icon = $iconMap[strtolower($doc->extension)] ?? 'fa-file';
                                        @endphp
                                        <i class="fas {{ $icon }}"></i>
                                    </div>
                                    <div class="doc-title">{{ $doc->title }}</div>
                                </div>
                                @if($doc->description)
                                    <div class="doc-desc">{{ Str::limit($doc->description, 100) }}</div>
                                @endif
                                <div class="doc-footer">
                                    @if($doc->category)
                                        <span class="category-badge" style="background: {{ $doc->category->color ?? '#eff6ff' }}20; color: {{ $doc->category->color ?? '#3b82f6' }};">
                                            {{ $doc->category->name }}
                                        </span>
                                    @else
                                        <span></span>
                                    @endif
                                    <span>{{ $doc->published_at?->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    {{ $documents->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <p>No documents found matching your search criteria.</p>
                    <a href="{{ route('documents.public.portal') }}" class="back-link mt-2 d-inline-block">Browse all documents</a>
                </div>
            @endif

        @endif

    </div>

    {{-- Footer --}}
    <footer class="public-footer">
        <div class="container">
            Heritage Junior Secondary School &copy; {{ date('Y') }} &mdash; Powered by Heritage DMS
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
