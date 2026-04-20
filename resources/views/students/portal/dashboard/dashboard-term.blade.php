<!-- Hidden element to pass stats to parent page -->
<div id="dashboard-stats-data" class="d-none"
    data-total="{{ $termData['totalCourses'] }}"
    data-progress="{{ $termData['inProgressCourses'] }}"
    data-completed="{{ $termData['completedCourses'] }}">
</div>

<style>
    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #3b82f6;
        font-size: 1.4rem;
    }

    .course-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 20px;
        margin-bottom: 16px;
        transition: all 0.2s ease;
    }

    .course-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }

    .course-card .course-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 8px;
        line-height: 1.3;
    }

    .course-card .course-meta {
        display: flex;
        align-items: center;
        gap: 16px;
        color: #6b7280;
        font-size: 0.9rem;
        margin-bottom: 16px;
    }

    .course-card .course-meta span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .course-card .course-meta i {
        font-size: 1rem;
    }

    .module-badge {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 3px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .progress-section {
        margin-bottom: 16px;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .progress-label span {
        font-size: 0.9rem;
        color: #4b5563;
    }

    .progress-label strong {
        font-size: 1rem;
        color: #1f2937;
    }

    .progress-bar-wrapper {
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981 0%, #059669 100%);
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .progress-bar-fill.in-progress {
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    }

    .btn-continue {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 3px;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-continue:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    .btn-browse {
        background: white;
        border: 2px solid #e5e7eb;
        color: #374151;
        padding: 10px 20px;
        border-radius: 3px;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-browse:hover {
        border-color: #3b82f6;
        color: #3b82f6;
        background: #f8fafc;
    }

    .activity-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }

    .activity-card:hover {
        border-color: #d1d5db;
        background: #f9fafb;
    }

    .activity-card .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .activity-card .activity-icon.completed {
        background: #d1fae5;
        color: #059669;
    }

    .activity-card .activity-icon.in-progress {
        background: #fef3c7;
        color: #d97706;
    }

    .activity-card .activity-icon.not-started {
        background: #f3f4f6;
        color: #9ca3af;
    }

    .activity-card .activity-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
        line-height: 1.3;
    }

    .activity-card .activity-course {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 6px;
    }

    .activity-card .activity-time {
        font-size: 0.8rem;
        color: #9ca3af;
    }

    .empty-state {
        text-align: center;
        padding: 48px 24px;
    }

    .empty-state i {
        font-size: 64px;
        color: #d1d5db;
        margin-bottom: 16px;
    }

    .empty-state h5 {
        font-size: 1.1rem;
        color: #4b5563;
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 0.95rem;
        color: #9ca3af;
        margin-bottom: 20px;
    }

    .card-section {
        background: #f9fafb;
        border-radius: 3px;
        padding: 24px;
        height: 100%;
    }
</style>

<div class="row">
    <!-- Course Progress Column -->
    <div class="col-lg-8 mb-4 mb-lg-0">
        <div class="card-section">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="section-title mb-0">
                    <i class="bx bx-book-reader"></i> My Courses
                </h4>
                <a href="{{ route('student.lms.courses') }}" class="btn-browse">
                    <i class="bx bx-search me-1"></i> Browse All
                </a>
            </div>

            @forelse($termData['enrollments'] as $enrollment)
                <div class="course-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="course-title mb-0">{{ $enrollment->course->title }}</h5>
                        <span class="module-badge">{{ $enrollment->course->modules->count() }} Modules</span>
                    </div>

                    <div class="course-meta">
                        @if($enrollment->course->instructor)
                            <span><i class="bx bx-user"></i> {{ $enrollment->course->instructor->name }}</span>
                        @endif
                        @if($enrollment->course->grade)
                            <span><i class="bx bx-layer"></i> {{ $enrollment->course->grade->name }}</span>
                        @endif
                    </div>

                    <div class="progress-section">
                        <div class="progress-label">
                            <span>Course Progress</span>
                            <strong>{{ number_format($enrollment->progress_percentage ?? 0, 0) }}%</strong>
                        </div>
                        <div class="progress-bar-wrapper">
                            <div class="progress-bar-fill {{ ($enrollment->progress_percentage ?? 0) < 100 ? 'in-progress' : '' }}"
                                style="width: {{ $enrollment->progress_percentage ?? 0 }}%"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        @php
                            $totalContent = $enrollment->course->modules->sum(fn($m) => $m->contentItems->count());
                            $completedContent = $termData['contentProgress']
                                ->whereIn('content_item_id', $enrollment->course->modules->pluck('contentItems')->flatten()->pluck('id'))
                                ->where('status', 'completed')
                                ->count();
                        @endphp
                        <span style="font-size: 0.9rem; color: #6b7280;">
                            {{ $completedContent }} of {{ $totalContent }} lessons completed
                        </span>
                        <a href="{{ route('student.lms.learn', $enrollment->course) }}" class="btn-continue">
                            {{ ($enrollment->progress_percentage ?? 0) > 0 ? 'Continue' : 'Start' }} Learning
                            <i class="bx bx-right-arrow-alt"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="bx bx-book"></i>
                    <h5>No Courses Yet</h5>
                    <p>You haven't enrolled in any courses. Browse available courses to get started.</p>
                    <a href="{{ route('student.lms.courses') }}" class="btn-continue">
                        <i class="bx bx-search me-1"></i> Browse Courses
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Activity Column -->
    <div class="col-lg-4">
        <div class="card-section">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="section-title mb-0">
                    <i class="bx bx-time-five"></i> Recent Activity
                </h4>
                <a href="{{ route('student.lms.my-courses') }}" class="btn-browse" style="padding: 8px 14px; font-size: 0.85rem;">
                    View All
                </a>
            </div>

            @php
                $recentProgress = $termData['contentProgress']
                    ->sortByDesc('updated_at')
                    ->take(5);
            @endphp

            @forelse($recentProgress as $progress)
                <div class="activity-card">
                    <div class="d-flex gap-3">
                        <div class="activity-icon {{ $progress->status }}">
                            @if($progress->status === 'completed')
                                <i class="bx bx-check"></i>
                            @elseif($progress->status === 'in_progress')
                                <i class="bx bx-play"></i>
                            @else
                                <i class="bx bx-circle"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="activity-title">
                                {{ Str::limit($progress->contentItem->title ?? 'Content Item', 40) }}
                            </div>
                            <div class="activity-course">
                                {{ Str::limit($progress->contentItem->module->course->title ?? '', 35) }}
                            </div>
                            <div class="activity-time">
                                @if($progress->status === 'completed')
                                    <i class="bx bx-check-circle me-1"></i>
                                    Completed {{ $progress->completed_at?->diffForHumans() ?? 'recently' }}
                                @elseif($progress->started_at)
                                    <i class="bx bx-time me-1"></i>
                                    Started {{ $progress->started_at->diffForHumans() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state" style="padding: 32px 16px;">
                    <i class="bx bx-history" style="font-size: 48px;"></i>
                    <h5>No Activity Yet</h5>
                    <p style="margin-bottom: 0;">Start a course to see your activity here.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
