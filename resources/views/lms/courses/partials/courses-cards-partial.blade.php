@if($courses->count() > 0)
    <div class="row" id="coursesGrid">
        @foreach($courses as $course)
        <div class="col-md-6 col-lg-4 mb-3 course-item">
            <div class="card course-card h-100">
                <div class="card-header border-0">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center">
                            @if($course->thumbnail_path)
                                <img src="{{ Storage::url($course->thumbnail_path) }}"
                                     alt="{{ $course->title }}"
                                     class="course-card-thumbnail me-2">
                            @else
                                <div class="course-card-thumbnail d-flex align-items-center justify-content-center me-2" style="background: rgba(255,255,255,0.2);">
                                    <i class="fas fa-book text-white"></i>
                                </div>
                            @endif
                            <div>
                                <h6 class="card-title mb-0" style="font-size: 14px;">
                                    <a href="{{ route('lms.courses.show', $course) }}" class="text-decoration-none">
                                        {{ Str::limit($course->title, 25) }}
                                    </a>
                                </h6>
                                <small>{{ $course->code }}</small>
                            </div>
                        </div>
                        <span class="badge student-count-badge"
                              data-bs-toggle="tooltip"
                              title="Students Enrolled">
                            {{ $course->enrollments_count }} <i class="fas fa-users ms-1"></i>
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge status-{{ $course->status }}" style="font-size: 11px;">
                            {{ ucfirst($course->status) }}
                        </span>
                        @if($course->grade)
                            <span class="badge bg-secondary" style="font-size: 11px;">{{ $course->grade->name }}</span>
                        @endif
                        @if($course->gradeSubject && $course->gradeSubject->subject)
                            <span class="text-muted" style="font-size: 12px;">{{ Str::limit($course->gradeSubject->subject->name, 15) }}</span>
                        @endif
                    </div>

                    @if($course->instructor)
                    <div class="mb-2" style="font-size: 13px;">
                        <i class="fas fa-chalkboard-teacher text-muted me-1"></i>
                        <span>{{ $course->instructor->firstname }} {{ $course->instructor->lastname }}</span>
                    </div>
                    @endif

                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <i class="fas fa-layer-group text-muted me-1" style="font-size: 12px;"></i>
                            <span style="font-size: 12px;" class="text-muted">Modules</span>
                            <span class="badge bg-primary ms-1" style="font-size: 10px;">{{ $course->modules_count }}</span>
                        </div>
                        @if($course->modules->count() > 0)
                            <div class="ps-3" style="font-size: 12px;">
                                @foreach($course->modules->take(2) as $module)
                                    <div class="text-truncate">
                                        <i class="fas fa-cube text-secondary me-1" style="font-size: 10px;"></i>
                                        {{ Str::limit($module->title, 30) }}
                                    </div>
                                @endforeach
                                @if($course->modules->count() > 2)
                                    <small class="text-muted">+{{ $course->modules->count() - 2 }} more</small>
                                @endif
                            </div>
                        @else
                            <small class="text-muted ps-3">No modules yet</small>
                        @endif
                    </div>
                </div>

                <div class="card-footer bg-light border-top">
                    <div class="d-flex justify-content-end align-items-center">
                        <div class="action-buttons">
                            <a href="{{ route('lms.courses.show', $course) }}" class="btn btn-sm btn-light" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('manage-lms-courses')
                                <a href="{{ route('lms.courses.edit', $course) }}" class="btn btn-sm btn-light" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('lms.enrollments.index', $course) }}" class="btn btn-sm btn-light" title="Enrollments">
                                    <i class="fas fa-user-plus"></i>
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@else
    <div class="text-center py-5">
        <div class="mb-3">
            <i class="fas fa-book-open fa-3x text-muted" style="opacity: 0.5;"></i>
        </div>
        <h5 class="text-muted">No Content Found</h5>
        <p class="text-muted mb-0">
            No learning content matches your current filters.
        </p>
    </div>
@endif
