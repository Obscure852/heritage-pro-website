@extends('layouts.master')

@section('title', 'All Badges')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.gamification.dashboard') }}">My Progress</a>
        @endslot
        @slot('title')
            All Badges
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="help-text mb-4" style="background: #f8f9fa; padding: 12px 16px; border-left: 4px solid #3b82f6; border-radius: 0 3px 3px 0;">
        <div class="help-title" style="font-weight: 600; color: #374151; margin-bottom: 4px;">Badge Collection</div>
        <div class="help-content" style="color: #6b7280; font-size: 13px; line-height: 1.5;">
            Browse all available badges organized by category. Earned badges are highlighted while locked badges show the requirements needed to unlock them.
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-0"><i class="fas fa-award me-2"></i>All Badges</h4>
            <p class="text-muted mb-0">Collect badges by completing courses, achieving milestones, and more!</p>
        </div>
    </div>

    @foreach($badgesByCategory as $category => $categoryBadges)
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0 text-capitalize">
                    @switch($category)
                        @case('completion') <i class="fas fa-check-circle me-2 text-success"></i>Completion @break
                        @case('achievement') <i class="fas fa-trophy me-2 text-warning"></i>Achievement @break
                        @case('streak') <i class="fas fa-fire me-2 text-danger"></i>Streak @break
                        @case('social') <i class="fas fa-users me-2 text-info"></i>Social @break
                        @case('special') <i class="fas fa-star me-2 text-purple"></i>Special @break
                        @default <i class="fas fa-medal me-2"></i>{{ $category }}
                    @endswitch
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($categoryBadges as $badge)
                        <div class="col-lg-2 col-md-3 col-4">
                            <a href="{{ route('lms.gamification.badges.show', $badge) }}" class="text-decoration-none">
                                <div class="card h-100 {{ $badge->is_earned ? 'border-success' : 'border-light' }} text-center hover-shadow">
                                    <div class="card-body py-3">
                                        <div class="badge-icon mb-2" style="font-size: 3rem; color: {{ $badge->is_earned ? $badge->color : '#ccc' }};">
                                            <i class="{{ $badge->icon_class }}"></i>
                                        </div>
                                        <h6 class="mb-1 {{ $badge->is_earned ? '' : 'text-muted' }}">{{ $badge->name }}</h6>
                                        <span class="badge" style="background-color: {{ $badge->rarity_color }};">{{ $badge->rarity }}</span>
                                        @if($badge->is_earned)
                                            <div class="mt-2">
                                                <span class="badge bg-success"><i class="fas fa-check"></i> Earned</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>

@push('styles')
<style>
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
        transition: all 0.2s ease;
    }
</style>
@endpush
@endsection
