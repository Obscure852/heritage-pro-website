@extends('layouts.master')

@section('title', $course->title . ' - My Grades')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.courses.learn', $course) }}">{{ Str::limit($course->title, 30) }}</a>
        @endslot
        @slot('title')
            My Grades
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i>My Grades: {{ $course->title }}</h4>
        </div>
    </div>

    <div class="row">
        <!-- Overall Grade Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-award me-2"></i>Overall Grade</h6>
                </div>
                <div class="card-body text-center">
                    <div class="display-1 fw-bold {{ $course_grade?->is_passing ? 'text-success' : 'text-danger' }}">
                        {{ $course_grade?->letter_grade ?? '-' }}
                    </div>
                    <div class="h3 text-muted">{{ $course_grade?->percentage ?? 0 }}%</div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h5 mb-0">{{ $course_grade?->total_points_earned ?? 0 }}</div>
                            <small class="text-muted">Points Earned</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-0">{{ $course_grade?->total_points_possible ?? 0 }}</div>
                            <small class="text-muted">Points Possible</small>
                        </div>
                    </div>

                    <hr>

                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar {{ $course_grade?->is_passing ? 'bg-success' : 'bg-danger' }}"
                             style="width: {{ $course_grade?->percentage ?? 0 }}%">
                            {{ $course_grade?->percentage ?? 0 }}%
                        </div>
                    </div>

                    @if($settings->show_rank_to_students && $course_grade?->rank)
                        <div class="mt-3">
                            <span class="badge bg-info fs-6">Rank: #{{ $course_grade->rank }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Grade Breakdown -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>Grade Breakdown</h6>
                </div>
                <div class="card-body p-0">
                    @foreach($categories as $category)
                        <div class="border-bottom">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light">
                                <div>
                                    <strong style="color: {{ $category->color }}">{{ $category->name }}</strong>
                                    <span class="badge bg-secondary ms-2">{{ $category->weight }}%</span>
                                </div>
                                @php $catGrade = $category_grades[$category->id] ?? null; @endphp
                                <div class="text-end">
                                    <strong>{{ $catGrade['percentage'] ?? 0 }}%</strong>
                                    <small class="text-muted ms-2">({{ $catGrade['earned'] ?? 0 }}/{{ $catGrade['possible'] ?? 0 }})</small>
                                </div>
                            </div>
                            <ul class="list-group list-group-flush">
                                @foreach($category->items as $item)
                                    @if(!$item->is_hidden || $settings->show_grade_to_students)
                                        @php $grade = $grades[$item->id] ?? null; @endphp
                                        <li class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span>{{ $item->name }}</span>
                                                    @if($item->due_date)
                                                        <small class="text-muted ms-2">
                                                            <i class="fas fa-calendar"></i> {{ $item->due_date->format('M j') }}
                                                        </small>
                                                    @endif
                                                    @if($grade?->is_late)
                                                        <span class="badge bg-warning ms-2">Late</span>
                                                    @endif
                                                </div>
                                                <div class="text-end">
                                                    @if($grade && $grade->status === 'graded')
                                                        <strong>{{ $grade->score }}/{{ $item->max_points }}</strong>
                                                        <span class="badge {{ $grade->percentage >= 50 ? 'bg-success' : 'bg-danger' }} ms-2">
                                                            {{ $grade->percentage }}%
                                                        </span>
                                                    @elseif($grade && $grade->status === 'excused')
                                                        <span class="badge bg-info">Excused</span>
                                                    @else
                                                        <span class="text-muted">- / {{ $item->max_points }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($grade?->feedback)
                                                <div class="mt-2 small text-muted">
                                                    <i class="fas fa-comment me-1"></i>{{ $grade->feedback }}
                                                </div>
                                            @endif
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endforeach

                    <!-- Uncategorized Items -->
                    @if($uncategorized_items->count())
                        <div class="border-bottom">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light">
                                <strong>Other</strong>
                            </div>
                            <ul class="list-group list-group-flush">
                                @foreach($uncategorized_items as $item)
                                    @php $grade = $grades[$item->id] ?? null; @endphp
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>{{ $item->name }}</span>
                                            <div class="text-end">
                                                @if($grade && $grade->status === 'graded')
                                                    <strong>{{ $grade->score }}/{{ $item->max_points }}</strong>
                                                    <span class="badge {{ $grade->percentage >= 50 ? 'bg-success' : 'bg-danger' }} ms-2">
                                                        {{ $grade->percentage }}%
                                                    </span>
                                                @else
                                                    <span class="text-muted">- / {{ $item->max_points }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
