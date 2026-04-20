@extends('layouts.master')

@section('title', 'My Progress - Gamification')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('title')
            My Progress
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="help-text mb-4" style="background: #f8f9fa; padding: 12px 16px; border-left: 4px solid #3b82f6; border-radius: 0 3px 3px 0;">
        <div class="help-title" style="font-weight: 600; color: #374151; margin-bottom: 4px;">Gamification Dashboard</div>
        <div class="help-content" style="color: #6b7280; font-size: 13px; line-height: 1.5;">
            Track your achievements, earn badges, and compete with peers on the leaderboard. Complete courses and activities to level up and unlock rewards.
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-0"><i class="fas fa-gamepad me-2"></i>My Progress</h4>
        </div>
    </div>

    <!-- Level & Points Overview -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-3">
            <div class="card shadow-sm h-100 bg-gradient-primary text-white">
                <div class="card-body text-center">
                    <div class="display-1 fw-bold">{{ $stats['level'] }}</div>
                    <h5>{{ $stats['level_title'] }}</h5>
                    <div class="progress bg-white bg-opacity-25 mt-3" style="height: 10px;">
                        <div class="progress-bar bg-warning" style="width: {{ $stats['progress_to_next_level'] }}%"></div>
                    </div>
                    <small class="text-white-50">{{ $stats['xp_to_next_level'] }} XP to next level</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold text-success">{{ number_format($stats['total_points']) }}</div>
                    <h5 class="text-muted">Total Points</h5>
                    <div class="mt-3">
                        <span class="badge bg-info me-2"><i class="fas fa-trophy me-1"></i>Rank #{{ $stats['rank'] ?? 'N/A' }}</span>
                        <span class="badge bg-warning"><i class="fas fa-medal me-1"></i>{{ $stats['badges_count'] }} Badges</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold text-danger">
                        <i class="fas fa-fire"></i> {{ $stats['current_streak'] }}
                    </div>
                    <h5 class="text-muted">Day Streak</h5>
                    <div class="mt-3">
                        <small class="text-muted">Longest: {{ $stats['longest_streak'] }} days</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Badges -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-award me-2"></i>Recent Badges</h5>
                    <a href="{{ route('lms.gamification.badges') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($badges->count())
                        <div class="row g-3">
                            @foreach($badges->take(6) as $studentBadge)
                                <div class="col-4 text-center">
                                    <div class="badge-icon mb-2" style="font-size: 2.5rem; color: {{ $studentBadge->badge->color }};">
                                        <i class="{{ $studentBadge->badge->icon_class }}"></i>
                                    </div>
                                    <h6 class="small mb-0">{{ $studentBadge->badge->name }}</h6>
                                    <small class="text-muted">{{ $studentBadge->earned_at->diffForHumans() }}</small>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">No badges earned yet. Keep learning!</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Points -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-coins me-2"></i>Recent Points</h5>
                    <a href="{{ route('lms.gamification.points-history') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentPoints->count())
                        <ul class="list-group list-group-flush">
                            @foreach($recentPoints as $transaction)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-medium">{{ $transaction->type_label }}</span>
                                        @if($transaction->description)
                                            <br><small class="text-muted">{{ $transaction->description }}</small>
                                        @endif
                                    </div>
                                    <span class="badge {{ $transaction->is_positive ? 'bg-success' : 'bg-danger' }}">
                                        {{ $transaction->is_positive ? '+' : '' }}{{ $transaction->points }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted text-center py-4 mb-0">No points earned yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Achievements Progress -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Achievements</h5>
                    <a href="{{ route('lms.gamification.achievements') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($achievements->take(8) as $achievement)
                            @php
                                $progress = $achievement->studentAchievements->first();
                                $isUnlocked = $progress?->is_unlocked;
                            @endphp
                            <div class="col-md-3 col-6">
                                <div class="card h-100 {{ $isUnlocked ? 'border-success' : 'border-light' }}">
                                    <div class="card-body text-center py-3">
                                        <div style="font-size: 2rem; color: {{ $isUnlocked ? $achievement->color : '#ccc' }};">
                                            <i class="{{ $achievement->icon_class }}"></i>
                                        </div>
                                        <h6 class="mt-2 mb-1 small">{{ $achievement->name }}</h6>
                                        @if($isUnlocked)
                                            <span class="badge bg-success"><i class="fas fa-check"></i> Unlocked</span>
                                        @else
                                            <div class="progress mt-2" style="height: 5px;">
                                                <div class="progress-bar" style="width: {{ $progress?->progress ?? 0 }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $progress?->progress ?? 0 }}%</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
