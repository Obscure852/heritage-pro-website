@extends('layouts.master-student-portal')

@section('title')
    Browse Courses
@endsection

@section('css')
    <style>
        .page-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 24px;
            border-radius: 3px;
            margin-bottom: 24px;
        }

        .page-header h4 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }

        .page-header p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.75;
        }

        .search-section {
            background: white;
            padding: 20px;
            border-radius: 3px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .search-form {
            display: flex;
            gap: 12px;
        }

        .search-input {
            flex: 1;
            padding: 10px 16px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-search {
            padding: 10px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }

        .btn-search:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .course-card {
            background: white;
            border-radius: 3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .course-thumbnail {
            height: 150px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            position: relative;
        }

        .course-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .enrolled-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #10b981;
            color: white;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
        }

        .course-body {
            padding: 20px;
        }

        .course-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .course-description {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .course-meta {
            display: flex;
            gap: 16px;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        .course-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .course-actions {
            display: flex;
            gap: 10px;
        }

        .btn-view {
            flex: 1;
            padding: 10px 16px;
            background: #f3f4f6;
            color: #374151;
            border: none;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-view:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-enroll {
            flex: 1;
            padding: 10px 16px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-enroll:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
        }

        .btn-continue {
            flex: 1;
            padding: 10px 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
        }

        .btn-continue:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #f9fafb;
            border-radius: 3px;
        }

        .empty-state i {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .pagination-wrapper {
            margin-top: 24px;
            display: flex;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .courses-grid {
                grid-template-columns: 1fr;
            }

            .search-form {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            LMS
        @endslot
        @slot('title')
            Browse Courses
        @endslot
    @endcomponent

    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4><i class="fas fa-book-open me-2"></i>Browse Courses</h4>
                <p>Discover courses and expand your knowledge</p>
            </div>
            <div class="col-md-6">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="stat-item">
                            <h4 class="text-white">{{ $courses->total() }}</h4>
                            <small>Available</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-item">
                            <h4 class="text-white">{{ count($enrolledCourseIds) }}</h4>
                            <small>Enrolled</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="search-section">
        <form action="{{ route('student.lms.courses') }}" method="GET" class="search-form">
            <input type="text" name="search" class="search-input" placeholder="Search courses..." value="{{ request('search') }}">
            <button type="submit" class="btn-search">
                <i class="fas fa-search me-2"></i>Search
            </button>
        </form>
    </div>

    @if($courses->isEmpty())
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h5>No Courses Found</h5>
            <p>No courses match your search criteria.</p>
        </div>
    @else
        <div class="courses-grid">
            @foreach($courses as $course)
                @php
                    $isEnrolled = in_array($course->id, $enrolledCourseIds);
                @endphp
                <div class="course-card">
                    <div class="course-thumbnail">
                        @if($course->thumbnail)
                            <img src="{{ Storage::url($course->thumbnail) }}" alt="{{ $course->title }}">
                        @else
                            <i class="fas fa-book"></i>
                        @endif
                        @if($isEnrolled)
                            <span class="enrolled-badge"><i class="fas fa-check me-1"></i>Enrolled</span>
                        @endif
                    </div>
                    <div class="course-body">
                        <h5 class="course-title">{{ $course->title }}</h5>
                        <p class="course-description">{{ $course->description }}</p>
                        <div class="course-meta">
                            @if($course->grade)
                                <span><i class="fas fa-layer-group"></i> {{ $course->grade->name }}</span>
                            @endif
                            @if($course->instructor)
                                <span><i class="fas fa-user"></i> {{ $course->instructor->name }}</span>
                            @endif
                            @if($course->enrollments_count ?? false)
                                <span><i class="fas fa-users"></i> {{ $course->enrollments_count }} enrolled</span>
                            @endif
                        </div>
                        <div class="course-actions">
                            <a href="{{ route('student.lms.course', $course) }}" class="btn-view">
                                <i class="fas fa-eye me-1"></i>View
                            </a>
                            @if($isEnrolled)
                                <a href="{{ route('student.lms.learn', $course) }}" class="btn-continue">
                                    <i class="fas fa-play me-1"></i>Continue
                                </a>
                            @elseif($course->self_enrollment)
                                <form action="{{ route('student.lms.enroll', $course) }}" method="POST" style="flex: 1;">
                                    @csrf
                                    <button type="submit" class="btn-enroll" style="width: 100%;">
                                        <i class="fas fa-plus me-1"></i>Enroll
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pagination-wrapper">
            {{ $courses->links() }}
        </div>
    @endif
@endsection
