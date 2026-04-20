@extends('layouts.master')

@section('title', 'My Grades')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('title')
            My Grades
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="help-text mb-4" style="background: #f8f9fa; padding: 12px 16px; border-left: 4px solid #3b82f6; border-radius: 0 3px 3px 0;">
        <div class="help-title" style="font-weight: 600; color: #374151; margin-bottom: 4px;">Your Academic Performance</div>
        <div class="help-content" style="color: #6b7280; font-size: 13px; line-height: 1.5;">
            View your grades across all enrolled courses. Click on a course card to see detailed grade breakdowns for individual assignments and quizzes.
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i>My Grades</h4>
            <p class="text-muted mb-0">View your academic performance across all courses</p>
        </div>
    </div>

    @if($enrollments->count())
        <div class="row">
            @foreach($enrollments as $enrollment)
                @php $courseGrade = $courseGrades[$enrollment->course_id] ?? null; @endphp
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">
                            <h6 class="mb-0">{{ $enrollment->course->title }}</h6>
                        </div>
                        <div class="card-body text-center">
                            <!-- Grade Circle -->
                            <div class="grade-circle mx-auto mb-3 {{ $courseGrade?->is_passing ? 'border-success' : 'border-danger' }}">
                                <div class="grade-letter {{ $courseGrade?->is_passing ? 'text-success' : 'text-danger' }}">
                                    {{ $courseGrade?->letter_grade ?? '-' }}
                                </div>
                                <div class="grade-percentage">{{ $courseGrade?->percentage ?? 0 }}%</div>
                            </div>

                            <!-- Points -->
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <div class="h5 mb-0">{{ $courseGrade?->total_points_earned ?? 0 }}</div>
                                    <small class="text-muted">Earned</small>
                                </div>
                                <div class="col-6">
                                    <div class="h5 mb-0">{{ $courseGrade?->total_points_possible ?? 0 }}</div>
                                    <small class="text-muted">Possible</small>
                                </div>
                            </div>

                            <!-- Progress -->
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar {{ $courseGrade?->is_passing ? 'bg-success' : 'bg-danger' }}"
                                     style="width: {{ $courseGrade?->percentage ?? 0 }}%"></div>
                            </div>

                            <!-- Items Progress -->
                            <div class="text-muted small mb-3">
                                <i class="fas fa-tasks me-1"></i>
                                {{ $courseGrade?->items_graded ?? 0 }}/{{ $courseGrade?->items_total ?? 0 }} items graded
                            </div>

                            <a href="{{ route('lms.gradebook.student', $enrollment->course) }}" class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-eye me-1"></i>View Details
                            </a>
                        </div>
                        <div class="card-footer bg-white text-center">
                            @if($courseGrade?->is_passing)
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Passing</span>
                            @else
                                <span class="badge bg-warning"><i class="fas fa-exclamation me-1"></i>In Progress</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- GPA Summary (if applicable) -->
        @php
            $totalGpaPoints = 0;
            $gpaCount = 0;
            foreach ($courseGrades as $cg) {
                if ($cg && $cg->gpa_points !== null) {
                    $totalGpaPoints += $cg->gpa_points;
                    $gpaCount++;
                }
            }
            $gpa = $gpaCount > 0 ? round($totalGpaPoints / $gpaCount, 2) : null;
        @endphp

        @if($gpa !== null)
            <div class="row mt-4">
                <div class="col-md-4 mx-auto">
                    <div class="card shadow-sm bg-primary text-white">
                        <div class="card-body text-center">
                            <h6 class="text-white-50 mb-2">Cumulative GPA</h6>
                            <div class="display-4 fw-bold">{{ $gpa }}</div>
                            <small class="text-white-50">Based on {{ $gpaCount }} courses</small>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                <h5>No Courses Yet</h5>
                <p class="text-muted">Enroll in courses to see your grades.</p>
                <a href="{{ route('lms.courses.index') }}" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Browse Courses
                </a>
            </div>
        </div>
    @endif
</div>

<style>
.grade-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.grade-letter {
    font-size: 2.5rem;
    font-weight: bold;
    line-height: 1;
}
.grade-percentage {
    font-size: 0.9rem;
    color: #6c757d;
}
</style>
@endsection
