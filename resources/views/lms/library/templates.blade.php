@extends('layouts.master')

@section('title', 'Course Templates')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('li_2')
            <a class="text-muted font-size-14" href="{{ route('lms.library.index') }}">Content Library</a>
        @endslot
        @slot('title')
            Course Templates
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0"><i class="fas fa-clone me-2"></i>Course Templates</h4>
                <p class="text-muted mb-0">Pre-built course structures to help you get started quickly</p>
            </div>
            @can('manage-lms-courses')
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                    <i class="fas fa-plus me-2"></i>Create Template
                </button>
            @endcan
        </div>
    </div>

    <!-- Filter by Category -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('lms.library.templates') }}"
                    class="btn btn-sm {{ !request('category') ? 'btn-primary' : 'btn-outline-primary' }}">
                    All Templates
                </a>
                @foreach(App\Models\Lms\CourseTemplate::$categories as $key => $label)
                    <a href="{{ route('lms.library.templates', ['category' => $key]) }}"
                        class="btn btn-sm {{ request('category') === $key ? 'btn-primary' : 'btn-outline-primary' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @if($templates->count())
        <div class="row g-4">
            @foreach($templates as $template)
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100">
                        @if($template->thumbnail_path)
                            <img src="{{ Storage::url($template->thumbnail_path) }}" class="card-img-top" alt="{{ $template->name }}" style="height: 150px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-gradient d-flex align-items-center justify-content-center" style="height: 150px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="fas fa-graduation-cap fa-3x text-white opacity-75"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0">{{ $template->name }}</h6>
                                @if($template->is_public)
                                    <span class="badge bg-success">Public</span>
                                @else
                                    <span class="badge bg-secondary">Private</span>
                                @endif
                            </div>

                            @if($template->description)
                                <p class="card-text text-muted small mb-2">{{ Str::limit($template->description, 80) }}</p>
                            @endif

                            <div class="d-flex flex-wrap gap-1 mb-2">
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-cubes me-1"></i>{{ $template->module_count }} Modules
                                </span>
                                <span class="badge bg-light text-dark">
                                    {{ App\Models\Lms\CourseTemplate::$categories[$template->category] ?? $template->category }}
                                </span>
                            </div>

                            @if($template->usage_count > 0)
                                <small class="text-muted">
                                    <i class="fas fa-chart-line me-1"></i>Used {{ $template->usage_count }} times
                                </small>
                            @endif
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">By {{ $template->creator?->full_name ?? 'System' }}</small>
                                <a href="{{ route('lms.courses.create', ['template_id' => $template->id]) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-copy me-1"></i>Use Template
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-clone fa-4x text-muted mb-3"></i>
                <h5>No Templates Available</h5>
                <p class="text-muted mb-3">
                    @if(request('category'))
                        No templates found in this category.
                    @else
                        Create course templates to quickly start new courses with pre-defined structures.
                    @endif
                </p>
                @can('manage-lms-courses')
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                        <i class="fas fa-plus me-2"></i>Create First Template
                    </button>
                @endcan
            </div>
        </div>
    @endif
</div>

<!-- Create Template Modal -->
@can('manage-lms-courses')
<div class="modal fade" id="createTemplateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('lms.library.create-template') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-clone me-2"></i>Create Course Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-2"></i>
                        Create a template from an existing course to reuse its structure.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Source Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Select a course...</option>
                            @foreach(\App\Models\Lms\Course::where('created_by', auth()->id())->orWhere('status', 'published')->orderBy('title')->get() as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">The course structure will be copied to the template.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                @foreach(App\Models\Lms\CourseTemplate::$categories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Visibility</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="is_public" value="1" id="isPublic">
                                <label class="form-check-label" for="isPublic">
                                    Make public (visible to all instructors)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
