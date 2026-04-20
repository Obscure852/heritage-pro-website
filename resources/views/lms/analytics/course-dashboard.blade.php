@extends('layouts.master')

@section('title', $course->title . ' - Analytics Dashboard')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.courses.show', $course) }}">{{ Str::limit($course->title, 30) }}</a>
        @endslot
        @slot('title')
            Analytics Dashboard
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Analytics: {{ $course->title }}</h4>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="btn-group">
                <a href="{{ route('lms.analytics.course', [$course, 'range' => 7]) }}"
                   class="btn btn-sm {{ $dateRange == 7 ? 'btn-primary' : 'btn-outline-primary' }}">7 Days</a>
                <a href="{{ route('lms.analytics.course', [$course, 'range' => 30]) }}"
                   class="btn btn-sm {{ $dateRange == 30 ? 'btn-primary' : 'btn-outline-primary' }}">30 Days</a>
                <a href="{{ route('lms.analytics.course', [$course, 'range' => 90]) }}"
                   class="btn btn-sm {{ $dateRange == 90 ? 'btn-primary' : 'btn-outline-primary' }}">90 Days</a>
            </div>
            <form action="{{ route('lms.analytics.refresh', $course) }}" method="POST" class="d-inline ms-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="help-text mb-4" style="background: #f8f9fa; padding: 12px 16px; border-left: 4px solid #3b82f6; border-radius: 0 3px 3px 0;">
        <div class="help-title" style="font-weight: 600; color: #374151; margin-bottom: 4px;">Course Analytics Overview</div>
        <div class="help-content" style="color: #6b7280; font-size: 13px; line-height: 1.5;">
            Monitor student engagement, track progress, and identify at-risk students. Use the date range filter to view trends over different periods.
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="h3 mb-0 text-primary">{{ $overview['total_enrollments'] }}</div>
                    <small class="text-muted">Total Students</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="h3 mb-0 text-success">{{ $overview['active_enrollments'] }}</div>
                    <small class="text-muted">Active</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="h3 mb-0 text-info">{{ $overview['completed'] }}</div>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="h3 mb-0 text-warning">{{ $overview['average_progress'] }}%</div>
                    <small class="text-muted">Avg Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="h3 mb-0 text-purple">{{ $overview['recent_analytics']?->engagement_score ?? 0 }}</div>
                    <small class="text-muted">Engagement Score</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="h3 mb-0 text-danger">{{ $atRiskStudents->count() }}</div>
                    <small class="text-muted">At Risk</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Charts Section -->
        <div class="col-lg-8">
            <!-- Enrollment Trend -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Activity Trends</h6>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="250"></canvas>
                </div>
            </div>

            <!-- Content Performance -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Top Content</h6>
                    <a href="{{ route('lms.analytics.content', $course) }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Content</th>
                                <th class="text-center">Views</th>
                                <th class="text-center">Completions</th>
                                <th class="text-center">Avg Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contentAnalytics as $ca)
                                <tr>
                                    <td>{{ Str::limit($ca->content->title ?? 'Unknown', 40) }}</td>
                                    <td class="text-center">{{ $ca->total_views }}</td>
                                    <td class="text-center">{{ $ca->total_completions }}</td>
                                    <td class="text-center">{{ round($ca->avg_time / 60, 1) }}m</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No data available</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Activity Breakdown -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Activity Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="activityPieChart" height="200"></canvas>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                @foreach($activityBreakdown->take(6) as $activity)
                                    <li class="mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span>{{ \App\Models\Lms\ActivityLog::$activityTypes[$activity->activity_type] ?? $activity->activity_type }}</span>
                                            <strong>{{ number_format($activity->count) }}</strong>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Links -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-link me-2"></i>Analytics Reports</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('lms.analytics.students', $course) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-user-graduate me-2"></i>Student Analytics
                    </a>
                    <a href="{{ route('lms.analytics.content', $course) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-alt me-2"></i>Content Analytics
                    </a>
                    <a href="{{ route('lms.analytics.quizzes', $course) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-question-circle me-2"></i>Quiz Analytics
                    </a>
                    <a href="{{ route('lms.analytics.engagement', $course) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-line me-2"></i>Engagement Analytics
                    </a>
                </div>
            </div>

            <!-- At-Risk Students -->
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>At-Risk Students</h6>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($atRiskStudents as $insight)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $insight->student->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $insight->title }}</small>
                                </div>
                                <span class="badge bg-{{ $insight->severity_color }}">{{ ucfirst($insight->severity) }}</span>
                            </div>
                            <small class="text-muted">{{ $insight->description }}</small>
                            <div class="mt-2">
                                <a href="{{ route('lms.analytics.student-detail', [$course, $insight->student_id]) }}"
                                   class="btn btn-sm btn-outline-primary">View</a>
                                <form action="{{ route('lms.analytics.dismiss-insight', $insight) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Dismiss</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-3">
                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>
                            No at-risk students
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
// Activity Trend Chart
const activityCtx = document.getElementById('activityChart').getContext('2d');
new Chart(activityCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($dailyAnalytics->pluck('date')->map(fn($d) => $d->format('M j'))) !!},
        datasets: [{
            label: 'Active Students',
            data: {!! json_encode($dailyAnalytics->pluck('active_students')) !!},
            borderColor: '#3b82f6',
            tension: 0.3,
            fill: false
        }, {
            label: 'Engagement Score',
            data: {!! json_encode($dailyAnalytics->pluck('engagement_score')) !!},
            borderColor: '#10b981',
            tension: 0.3,
            fill: false
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
    }
});

// Activity Pie Chart
const pieCtx = document.getElementById('activityPieChart').getContext('2d');
new Chart(pieCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($activityBreakdown->take(6)->pluck('activity_type')) !!},
        datasets: [{
            data: {!! json_encode($activityBreakdown->take(6)->pluck('count')) !!},
            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899']
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } }
    }
});
</script>
@endpush
@endsection
