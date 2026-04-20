@include('sponsors.portal.partials.sponsor-portal-styles')

<style>
    /* Dashboard-specific styles */
    .notification-card {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }

    .notification-card:hover {
        border-color: #4e73df;
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.1);
    }

    .notification-card:last-child {
        margin-bottom: 0;
    }

    .notification-title {
        font-weight: 600;
        color: #1f2937;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .notification-body {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
        margin-bottom: 8px;
    }

    .notification-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: #9ca3af;
    }

    .student-overview-card {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        transition: all 0.2s ease;
        height: 100%;
    }

    .student-overview-card:hover {
        border-color: #4e73df;
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.1);
        transform: translateY(-2px);
    }

    .student-avatar-sm {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 18px;
        overflow: hidden;
    }

    .student-avatar-sm img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .quick-stat {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: #f9fafb;
        border-radius: 3px;
        font-size: 13px;
    }

    .quick-stat i {
        color: #4e73df;
    }

    .quick-stat .stat-value {
        font-weight: 600;
        color: #1f2937;
    }
</style>

<div class="container-fluid px-0">
    {{-- Welcome Message --}}
    <div class="help-text mb-4">
        <div class="help-title">
            <i class="bx bx-hand me-1"></i>
            Welcome back, {{ auth('sponsor')->user()->full_name }}!
        </div>
        <div class="help-content">
            Here's an overview of your children for Term {{ $currentTerm->term }}, {{ $currentTerm->year }}.
            Click on a student card to view their full profile, or use the sidebar to navigate to specific sections.
        </div>
    </div>

    {{-- Main Content Row --}}
    <div class="row g-4">
        {{-- Students Overview --}}
        <div class="col-lg-8">
            @if($children->isEmpty())
                <div class="sponsor-container">
                    <div class="sponsor-body">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bx bx-user-x"></i>
                            </div>
                            <h5>No Students Found</h5>
                            <p>No students are linked to your account for the selected term. Please contact the school administration.</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="row g-3">
                    @foreach($children as $child)
                        @php
                            $nameParts = explode(' ', $child->full_name);
                            $initials = '';
                            foreach (array_slice($nameParts, 0, 2) as $part) {
                                $initials .= strtoupper(substr($part, 0, 1));
                            }
                        @endphp
                        <div class="col-md-6">
                            <div class="student-overview-card">
                                <div class="p-3">
                                    {{-- Student Header --}}
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="student-avatar-sm">
                                            @if($child->photo_path)
                                                <img src="{{ asset($child->photo_path) }}" alt="{{ $child->full_name }}">
                                            @else
                                                {{ $initials }}
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $child->full_name }}</h6>
                                            <small class="text-muted">
                                                <i class="bx bx-buildings me-1"></i>
                                                {{ $child->currentClass ? $child->currentClass->name : 'Class not assigned' }}
                                            </small>
                                        </div>
                                    </div>

                                    {{-- Quick Stats --}}
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <div class="quick-stat">
                                            <i class="bx bx-calendar-x"></i>
                                            <span class="stat-value">{{ $child->absentDays->count() }}</span>
                                            <span class="text-muted">days absent</span>
                                        </div>
                                        <div class="quick-stat">
                                            <i class="bx bx-book"></i>
                                            <span class="stat-value">{{ $child->bookAllocations->count() }}</span>
                                            <span class="text-muted">books</span>
                                        </div>
                                    </div>

                                    {{-- Action Button --}}
                                    <a href="{{ route('sponsor.student.show', $child->id) }}" class="btn btn-primary btn-sm w-100">
                                        <i class="bx bx-user me-1"></i> View Full Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Quick Links --}}
                <div class="sponsor-container mt-4">
                    <div class="sponsor-body p-3">
                        <h6 class="mb-3">
                            <i class="bx bx-link me-2"></i>
                            Quick Links
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <a href="{{ route('sponsor.assessment-index') }}" class="btn btn-outline-primary w-100">
                                    <i class="bx bx-bar-chart-alt-2 me-1"></i> View Assessments
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('sponsor.fees') }}" class="btn btn-outline-primary w-100">
                                    <i class="bx bx-money me-1"></i> View Fees
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('sponsor.students.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="bx bx-group me-1"></i> All Students
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Notifications Sidebar --}}
        <div class="col-lg-4">
            <div class="sponsor-container" style="position: sticky; top: 20px;">
                <div class="sponsor-header" style="padding: 16px 20px;">
                    <h6 class="mb-0">
                        <i class="bx bx-bell me-2"></i>
                        Notifications
                    </h6>
                </div>
                <div class="sponsor-body" style="max-height: 500px; overflow-y: auto;">
                    @if($notifications->count() > 0)
                        @foreach($notifications as $notification)
                            <div class="notification-card {{ $notification->is_pinned ? 'pinned' : '' }}" style="{{ $notification->is_pinned ? 'border-left: 3px solid #f59e0b; background: #fffbeb;' : '' }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="notification-title">
                                        @if($notification->is_pinned)
                                            <i class="fas fa-thumbtack text-warning me-1" title="Pinned"></i>
                                        @endif
                                        {{ $notification->title }}
                                    </div>
                                    @if($notification->comments_count ?? 0 > 0)
                                        <span class="badge bg-primary rounded-pill">{{ $notification->comments_count }}</span>
                                    @endif
                                </div>
                                <div class="notification-body">{{ Str::limit($notification->body, 120) }}</div>
                                <div class="notification-meta">
                                    <span>
                                        <i class="bx bx-time-five me-1"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                    @if($notification->attachments())
                                        <a href="#" class="text-primary">
                                            <i class="bx bx-download"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bx bx-bell-off"></i>
                            </div>
                            <h5>No Notifications</h5>
                            <p>New notifications from the school will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
