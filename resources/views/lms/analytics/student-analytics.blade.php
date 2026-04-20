@extends('layouts.master')

@section('title', $course->title . ' - Student Analytics')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.analytics.course', $course) }}">{{ Str::limit($course->title, 30) }}</a>
        @endslot
        @slot('title')
            Student Analytics
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="help-text mb-4" style="background: #f8f9fa; padding: 12px 16px; border-left: 4px solid #3b82f6; border-radius: 0 3px 3px 0;">
        <div class="help-title" style="font-weight: 600; color: #374151; margin-bottom: 4px;">Student Performance Tracking</div>
        <div class="help-content" style="color: #6b7280; font-size: 13px; line-height: 1.5;">
            View detailed analytics for each enrolled student including progress, time spent, content views, and quiz performance. Click on a student to see their full learning history.
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-8">
            <h4 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Student Analytics: {{ $course->title }}</h4>
        </div>
        <div class="col-md-4 text-md-end">
            <input type="text" class="form-control form-control-sm d-inline-block w-auto"
                   id="studentSearch" placeholder="Search students...">
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" id="studentsTable">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th class="text-center">Progress</th>
                        <th class="text-center">Time Spent</th>
                        <th class="text-center">Content Views</th>
                        <th class="text-center">Quiz Attempts</th>
                        <th class="text-center">Last Activity</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $enrollment)
                        @php
                            $engagement = $studentEngagement[$enrollment->student_id] ?? collect();
                            $studentInsights = $insights[$enrollment->student_id] ?? collect();
                            $totalTime = $engagement->sum('total_time_seconds');
                            $contentViews = $engagement->sum('content_views');
                            $quizAttempts = $engagement->sum('quiz_attempts');
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $enrollment->student->name }}</strong>
                                @if($studentInsights->where('severity', 'critical')->count())
                                    <span class="badge bg-danger ms-1">At Risk</span>
                                @elseif($studentInsights->where('severity', 'warning')->count())
                                    <span class="badge bg-warning ms-1">Warning</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="progress" style="height: 20px; width: 100px; display: inline-block;">
                                    <div class="progress-bar bg-{{ $enrollment->progress >= 100 ? 'success' : ($enrollment->progress >= 50 ? 'info' : 'warning') }}"
                                         style="width: {{ $enrollment->progress }}%">
                                        {{ round($enrollment->progress) }}%
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                {{ $totalTime > 3600 ? round($totalTime / 3600, 1) . 'h' : round($totalTime / 60) . 'm' }}
                            </td>
                            <td class="text-center">{{ $contentViews }}</td>
                            <td class="text-center">{{ $quizAttempts }}</td>
                            <td class="text-center">
                                @if($enrollment->last_activity_at)
                                    <span title="{{ $enrollment->last_activity_at }}">
                                        {{ $enrollment->last_activity_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $enrollment->status === 'completed' ? 'success' : ($enrollment->status === 'active' ? 'primary' : 'secondary') }}">
                                    {{ ucfirst($enrollment->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('lms.analytics.student-detail', [$course, $enrollment->student_id]) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No students enrolled</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('studentSearch').addEventListener('input', function() {
    const search = this.value.toLowerCase();
    document.querySelectorAll('#studentsTable tbody tr').forEach(row => {
        const name = row.querySelector('td:first-child').textContent.toLowerCase();
        row.style.display = name.includes(search) ? '' : 'none';
    });
});
</script>
@endpush
@endsection
