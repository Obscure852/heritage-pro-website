@extends('layouts.master')

@section('title', 'Leaderboard')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.gamification.dashboard') }}">My Progress</a>
        @endslot
        @slot('title')
            Leaderboard
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="help-text mb-4" style="background: #f8f9fa; padding: 12px 16px; border-left: 4px solid #3b82f6; border-radius: 0 3px 3px 0;">
        <div class="help-title" style="font-weight: 600; color: #374151; margin-bottom: 4px;">Leaderboard Rankings</div>
        <div class="help-content" style="color: #6b7280; font-size: 13px; line-height: 1.5;">
            See how you rank against other learners. Points are earned through completing courses, quizzes, and achieving milestones. Filter by time period to see different rankings.
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0"><i class="fas fa-trophy me-2"></i>Leaderboard</h4>
        </div>
        <div class="col-md-6">
            <div class="d-flex gap-2 justify-content-md-end">
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href=this.value">
                    <option value="{{ route('lms.gamification.leaderboard', ['period' => 'all_time']) }}" {{ $period === 'all_time' ? 'selected' : '' }}>All Time</option>
                    <option value="{{ route('lms.gamification.leaderboard', ['period' => 'monthly']) }}" {{ $period === 'monthly' ? 'selected' : '' }}>This Month</option>
                    <option value="{{ route('lms.gamification.leaderboard', ['period' => 'weekly']) }}" {{ $period === 'weekly' ? 'selected' : '' }}>This Week</option>
                </select>
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href=this.value">
                    <option value="{{ route('lms.gamification.leaderboard', ['period' => $period]) }}">All Courses</option>
                    @foreach($courses as $c)
                        <option value="{{ route('lms.gamification.leaderboard', ['course_id' => $c->id, 'period' => $period]) }}" {{ $course?->id === $c->id ? 'selected' : '' }}>{{ $c->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($student && $studentRank)
        <div class="alert alert-info mb-4">
            <i class="fas fa-user me-2"></i>
            Your current rank: <strong>#{{ $studentRank }}</strong>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 80px;">Rank</th>
                            <th>Student</th>
                            <th class="text-center">Level</th>
                            <th class="text-center">Points</th>
                            <th class="text-center">Badges</th>
                            <th class="text-center">Courses</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaderboard as $entry)
                            <tr class="{{ $student && $entry->student_id === $student->id ? 'table-warning' : '' }}">
                                <td class="text-center">
                                    @if($entry->rank === 1)
                                        <span class="badge bg-warning text-dark fs-5"><i class="fas fa-crown"></i></span>
                                    @elseif($entry->rank === 2)
                                        <span class="badge bg-secondary fs-6">2nd</span>
                                    @elseif($entry->rank === 3)
                                        <span class="badge bg-danger fs-6">3rd</span>
                                    @else
                                        <span class="text-muted">#{{ $entry->rank }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($entry->student->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong>{{ $entry->student->name ?? 'Unknown' }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">Lvl {{ $entry->student->studentPoints?->level ?? 1 }}</span>
                                </td>
                                <td class="text-center">
                                    <strong class="text-success">{{ number_format($entry->points) }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">{{ $entry->badges_count }}</span>
                                </td>
                                <td class="text-center">
                                    {{ $entry->courses_completed }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No leaderboard data available yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
