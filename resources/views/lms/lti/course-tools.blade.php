@extends('layouts.master')

@section('title', 'LTI Tools - ' . $course->title)

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('lms.courses.index') }}">Courses</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('lms.courses.show', $course) }}">{{ $course->title }}</a></li>
                    <li class="breadcrumb-item active">LTI Tools</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-0"><i class="fas fa-plug me-2"></i>External Tools for {{ $course->title }}</h4>
            <p class="text-muted mb-0">Enable or disable external LTI tools for this course</p>
        </div>
    </div>

    @if($availableTools->count())
        <div class="row g-4">
            @foreach($availableTools as $tool)
                @php
                    $courseTool = $courseTools->firstWhere('tool_id', $tool->id);
                    $isEnabled = $courseTool && $courseTool->is_enabled;
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100 {{ $isEnabled ? 'border-success' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    @if($tool->icon_url)
                                        <img src="{{ $tool->icon_url }}" class="me-2" width="40" height="40" alt="">
                                    @else
                                        <div class="bg-light rounded p-2 me-2">
                                            <i class="fas fa-external-link-alt text-muted"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <h6 class="mb-0">{{ $tool->name }}</h6>
                                        <span class="badge bg-{{ $tool->lti_version === '1.3' ? 'primary' : 'secondary' }} badge-sm">
                                            LTI {{ $tool->lti_version }}
                                        </span>
                                    </div>
                                </div>
                                <form action="{{ route('lms.lti.toggle-course-tool', [$course, $tool]) }}" method="POST">
                                    @csrf
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                            onchange="this.form.submit()" {{ $isEnabled ? 'checked' : '' }}>
                                    </div>
                                </form>
                            </div>

                            @if($tool->description)
                                <p class="text-muted small mb-3">{{ Str::limit($tool->description, 100) }}</p>
                            @endif

                            <div class="small">
                                <span class="text-muted">Privacy:</span>
                                <span class="badge bg-{{ $tool->privacy_level === 'public' ? 'success' : ($tool->privacy_level === 'name_only' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst(str_replace('_', ' ', $tool->privacy_level)) }}
                                </span>
                            </div>
                        </div>
                        @if($isEnabled)
                            <div class="card-footer bg-transparent">
                                <a href="{{ route('lms.lti.launch', ['tool' => $tool, 'course_id' => $course->id]) }}"
                                    class="btn btn-sm btn-success w-100" target="_blank">
                                    <i class="fas fa-rocket me-2"></i>Launch Tool
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-plug fa-4x text-muted mb-3"></i>
                <h5>No LTI Tools Available</h5>
                <p class="text-muted mb-3">No external tools have been configured in the system.</p>
                @can('manage-lms-content')
                    <a href="{{ route('lms.lti.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add LTI Tool
                    </a>
                @endcan
            </div>
        </div>
    @endif
</div>
@endsection
