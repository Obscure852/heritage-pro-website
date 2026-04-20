@extends('layouts.master')

@section('title', 'My Learning Analytics')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('title')
            My Learning Analytics
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="help-text mb-4" style="background: #f8f9fa; padding: 12px 16px; border-left: 4px solid #3b82f6; border-radius: 0 3px 3px 0;">
        <div class="help-title" style="font-weight: 600; color: #374151; margin-bottom: 4px;">Your Learning Dashboard</div>
        <div class="help-content" style="color: #6b7280; font-size: 13px; line-height: 1.5;">
            Track your progress across all enrolled courses, view study time statistics, and see personalized insights to help improve your learning outcomes.
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i>My Learning Analytics</h4>
            <p class="text-muted mb-0">Track your learning progress and engagement</p>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm text-center bg-primary text-white">
                <div class="card-body">
                    <div class="h2 mb-0">{{ $overview['total_courses'] }}</div>
                    <small>Total Courses</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center bg-success text-white">
                <div class="card-body">
                    <div class="h2 mb-0">{{ $overview['completed_courses'] }}</div>
                    <small>Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center bg-info text-white">
                <div class="card-body">
                    <div class="h2 mb-0">{{ $overview['average_progress'] }}%</div>
                    <small>Average Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center bg-warning text-dark">
                <div class="card-body">
                    @php $hours = floor($overview['total_time_this_month'] / 3600); @endphp
                    <div class="h2 mb-0">{{ $hours }}h</div>
                    <small>This Month</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Study Time Chart -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Study Time (Last 30 Days)</h6>
                </div>
                <div class="card-body">
                    <canvas id="studyTimeChart" height="200"></canvas>
                </div>
            </div>

            <!-- Course Progress -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-book me-2"></i>Course Progress</h6>
                </div>
                <div class="card-body">
                    @foreach($overview['enrollments'] as $enrollment)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>{{ $enrollment->course->title }}</span>
                                <span class="badge bg-{{ $enrollment->status === 'completed' ? 'success' : 'primary' }}">
                                    {{ round($enrollment->progress) }}%
                                </span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-{{ $enrollment->progress >= 100 ? 'success' : 'primary' }}"
                                     style="width: {{ $enrollment->progress }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Time per Course -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-pie-chart me-2"></i>Time Distribution by Course</h6>
                </div>
                <div class="card-body">
                    <canvas id="courseTimeChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Insights -->
            @if($overview['insights']->count())
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Insights</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($overview['insights'] as $insight)
                            <div class="list-group-item">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-{{ $insight->insight_icon }} text-{{ $insight->severity_color }} me-2 mt-1"></i>
                                    <div>
                                        <strong>{{ $insight->title }}</strong>
                                        <p class="mb-0 small text-muted">{{ $insight->description }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recent Activity -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h6>
                </div>
                <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                    @forelse($overview['recent_activity'] as $activity)
                        <div class="list-group-item py-2">
                            <div class="d-flex justify-content-between">
                                <span class="small">
                                    {{ \App\Models\Lms\ActivityLog::$activityTypes[$activity->activity_type] ?? $activity->activity_type }}
                                </span>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                            @if($activity->course)
                                <small class="text-muted">{{ $activity->course->title }}</small>
                            @endif
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-3">
                            No recent activity
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Study Time Chart
const studyCtx = document.getElementById('studyTimeChart').getContext('2d');
new Chart(studyCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlyEngagement->pluck('date')->map(fn($d) => $d->format('M j'))) !!},
        datasets: [{
            label: 'Minutes',
            data: {!! json_encode($monthlyEngagement->pluck('total_time_seconds')->map(fn($s) => round($s / 60))) !!},
            backgroundColor: '#3b82f6'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// Course Time Chart
const courseCtx = document.getElementById('courseTimeChart').getContext('2d');
new Chart(courseCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($courseEngagement->pluck('course.title')) !!},
        datasets: [{
            data: {!! json_encode($courseEngagement->pluck('total_time')->map(fn($t) => round($t / 60))) !!},
            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'right' } }
    }
});
</script>
@endpush
@endsection
